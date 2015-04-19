<?php
/*	Project:	EQdkp-Plus
 *	Package:	MediaCenter Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if ( !defined('EQDKP_INC') ){
	die('Do not access this file directly.');
}
				
if ( !class_exists( "pdh_r_mediacenter_categories" ) ) {
	class pdh_r_mediacenter_categories extends pdh_r_generic{
		public static function __shortcuts() {
		$shortcuts = array();
		return array_merge(parent::$shortcuts, $shortcuts);
	}				
	
	public $default_lang = 'english';
	public $mediacenter_categories = null;
	public $sortation = null;
	private $arrTempPermissions = array();

	public $hooks = array(
		'mediacenter_categories_update',
		'mediacenter_media_update',
	);		
			
	public $presets = array(
		'mediacenter_categories_id' => array('id', array('%intCategoryID%'), array()),
		'mediacenter_categories_name' => array('name', array('%intCategoryID%', '%link_url%', '%link_url_suffix%'), array()),
		'mediacenter_categories_alias' => array('alias', array('%intCategoryID%'), array()),
		'mediacenter_categories_description' => array('description', array('%intCategoryID%'), array()),
		'mediacenter_categories_per_page' => array('per_page', array('%intCategoryID%'), array()),
		'mediacenter_categories_permissions' => array('permissions', array('%intCategoryID%'), array()),
		'mediacenter_categories_published' => array('published', array('%intCategoryID%'), array()),
		'mediacenter_categories_parent' => array('parent', array('%intCategoryID%'), array()),
		'mediacenter_categories_sort_id' => array('sort_id', array('%intCategoryID%'), array()),
		'mediacenter_categories_type' => array('type', array('%intCategoryID%'), array()),
		'mediacenter_categories_notify_on_onpublished' => array('notify_on_onpublished', array('%intCategoryID%'), array()),
		'mediacenter_categories_default_published_state' => array('default_published_state', array('%intCategoryID%'), array()),
		'mediacenter_categories_allow_comments' => array('allow_comments', array('%intCategoryID%'), array()),
		'mediacenter_categories_allow_voting' => array('allow_voting', array('%intCategoryID%'), array()),
		'mediacenter_categories_editicon' => array('editicon', array('%intCategoryID%'), array()),
		'mediacenter_categories_album_count' => array('album_count', array('%intCategoryID%'), array()),
		'mediacenter_categories_media_count' => array('media_count', array('%intCategoryID%'), array()),
		'mediacenter_categories_layout' => array('layout', array('%intCategoryID%'), array()),
		'mediacenter_categories_types' => array('types', array('%intCategoryID%'), array()),
	);
	
	public function reset(){
			$this->pdc->del('pdh_mediacenter_categories_table');
			
			$this->mediacenter_categories = NULL;
			$this->sortation = NULL;
			$this->alias = NULL;
	}
					
	public function init(){
			$this->mediacenter_categories	= $this->pdc->get('pdh_mediacenter_categories_table');
			$this->sortation				= $this->pdc->get('pdh_mediacenter_categories_sortation');
			$this->alias					= $this->pdc->get('pdh_mediacenter_categories_alias');
					
			if($this->mediacenter_categories !== NULL){
				return true;
			}		

			$objQuery = $this->db->query('SELECT * FROM __mediacenter_categories ORDER BY sort_id ASC');
			if($objQuery){
				while($drow = $objQuery->fetchAssoc()){

					$this->mediacenter_categories[(int)$drow['id']] = array(
						'id'					=> (int)$drow['id'],
						'name'					=> $drow['name'],
						'alias'					=> $drow['alias'],
						'description'			=> $drow['description'],
						'per_page'				=> (int)$drow['per_page'],
						'permissions'			=> $drow['permissions'],
						'published'				=> (int)$drow['published'],
						'parent'				=> (int)$drow['parent'],
						'sort_id'				=> (int)$drow['sort_id'],
						'notify_on_onpublished'	=> (int)$drow['notify_on_onpublished'],
						'default_published_state'=> (int)$drow['default_published_state'],
						'allow_comments'		=> (int)$drow['allow_comments'],
						'allow_voting'			=> (int)$drow['allow_voting'],
						'layout'				=> $drow['layout'],
						'types'					=> $drow['types'],

					);
					$this->alias[utf8_strtolower($drow['alias'])] = intval($drow['id']);
				}
				
				$this->sortation = $this->get_sortation();
				
				$this->pdc->put('pdh_mediacenter_categories_table', $this->mediacenter_categories, null);
				$this->pdc->put('pdh_mediacenter_categories_sortation', $this->sortation, null);
				$this->pdc->put('pdh_mediacenter_categories_alias', $this->alias, null);
				
			}

		}	//end init function

		/**
		 * @return multitype: List of all IDs
		 */						
		public function get_id_list($blnPublishedOnly = false) {
			if ($this->mediacenter_categories == NULL) return array();
				
			if ($blnPublishedOnly){
				$arrOut = array();
				foreach($this->mediacenter_categories as $intCategoryID => $arrCat){
					if ($this->get_published($intCategoryID)) $arrOut[] = $intCategoryID;
				}
				return $arrOut;
			} else return array_keys($this->mediacenter_categories);
		}
		
		
		//Get all published categories, check permissions
		public function get_published_id_list($intUserID = false){
			if ($intUserID === false) $intUserID = $this->user->id;
			$arrOut = array();
			
			foreach($this->get_id_list(true) as $intCategoryID){				
				//Check cat permission
				$arrPermissions = $this->get_user_permissions($intCategoryID, $intUserID);
				if (!$arrPermissions['read']) continue;
				
				$arrOut[] = $intCategoryID;
			}

			return $arrOut;
		}
		
		//Get all published media IDs, checks permissions
		public function get_published_media($intCategoryID, $intUserID = false){
			if (!$this->get_published($intCategoryID)) return array();
			if ($intUserID === false) $intUserID = $this->user->id;
			$arrOut = array();
				
			//Check cat permission
			$arrPermissions = $this->get_user_permissions($intCategoryID, $intUserID);
			if (!$arrPermissions['read']) continue;
				
				
			//Get Albums
			$arrAlbums = $this->pdh->get('mediacenter_albums', 'albums_for_category', array($intCategoryID));
			foreach($arrAlbums as $intAlbumID){
				$arrMedia = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID));
			
				foreach($arrMedia as $intMediaID){
					//Check Published
					if (!$this->pdh->get('mediacenter_media', 'published', array($intMediaID))) continue;
						
					$arrOut[] = $intMediaID;
				}
			
			}
			
			return $arrOut;
		}
		
		
		/**
		 * Get all data of Element with $strID
		 * @return multitype: Array with all data
		 */				
		public function get_data($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID];
			}
			return false;
		}
				
		/**
		 * Returns id for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype id
		 */
		 public function get_id($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['id'];
			}
			return false;
		}

		/**
		 * Returns name for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype name
		 */
		 public function get_name($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['name'];
			}
			return false;
		}
		
		public function get_html_name($intCategoryID, $strLink, $strSuffix, $intUseController=false, $blnWithIcon=false){
			if($blnWithIcon){
				$icon = (count($this->get_childs($intCategoryID))) ? '<i class="fa fa-folder-open-o"></i> ' : '<i class="fa fa-folder-o"></i> ';
			} else $icon = "";
			
			if($intUseController){
				return '<a href="'.$this->controller_path.$this->get_path($intCategoryID).'">'.$icon.$this->get_name($intCategoryID).'</a>';
			}
			return $this->get_name_prefix($intCategoryID).'<a href="'.$strLink.$this->SID.'&cid='.$intCategoryID.((strlen($strSuffix)) ? $strSuffix : '').'">'.$icon.$this->get_name($intCategoryID).'</a>';
		}

		/**
		 * Returns alias for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype alias
		 */
		 public function get_alias($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['alias'];
			}
			return false;
		}

		/**
		 * Returns description for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype description
		 */
		 public function get_description($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['description'];
			}
			return false;
		}

		/**
		 * Returns per_page for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype per_page
		 */
		 public function get_per_page($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				if ($this->mediacenter_categories[$intCategoryID]['per_page'] == 0){
					return (int)$this->config->get('per_page', 'mediacenter');
				}
				
				return $this->mediacenter_categories[$intCategoryID]['per_page'];
			}
			return false;
		}

		/**
		 * Returns permissions for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype permissions
		 */
		 public function get_permissions($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return unserialize($this->mediacenter_categories[$intCategoryID]['permissions']);
			}
			return false;
		}
		
		/**
		 * Returns allowed media types for $intCategoryID
		 * @param integer $intCategoryID
		 * @return multitype permissions
		 */
		public function get_types($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return unserialize($this->mediacenter_categories[$intCategoryID]['types']);
			}
			return false;
		}
		
		/**
		 * Returns layout for $intCategoryID
		 * @param integer $intCategoryID
		 * @return multitype permissions
		 */
		public function get_layout($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['layout'];
			}
			return false;
		}

		/**
		 * Returns published for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype published
		 */
		 public function get_published($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['published'];
			}
			return false;
		}
		
		public function get_html_published($intCategoryID){
			if ($this->get_published($intCategoryID)){
				$strImage = '<div><div class="eye eyeToggleTrigger"></div><input type="hidden" class="published_cb" name="published['.$intCategoryID.']" value="1"/></div>';
			} else {
				$strImage = '<div><div class="eye-gray eyeToggleTrigger"></div><input type="hidden" class="published_cb" name="published['.$intCategoryID.']" value="0"/></div>';
			}
			return $strImage;
		}

		/**
		 * Returns parent for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype parent
		 */
		 public function get_parent($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['parent'];
			}
			return false;
		}

		/**
		 * Returns sort_id for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype sort_id
		 */
		 public function get_sort_id($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->sortation[$intCategoryID];
			}
			return false;
		}
		
		public function get_html_sort_id($intCategoryID){
			return '<span class="ui-icon ui-icon-arrowthick-2-n-s" title="'.$this->user->lang('dragndrop').'"></span><input type="hidden" name="sortCategories[]" value="'.$intCategoryID.'"/>';
		}

		/**
		 * Returns notify_on_onpublished for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype notify_on_onpublished
		 */
		 public function get_notify_on_onpublished($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['notify_on_onpublished'];
			}
			return false;
		}

		/**
		 * Returns default_published_state for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype default_published_state
		 */
		 public function get_default_published_state($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['default_published_state'];
			}
			return false;
		}

		/**
		 * Returns allow_comments for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype allow_comments
		 */
		 public function get_allow_comments($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['allow_comments'];
			}
			return false;
		}

		/**
		 * Returns allow_voting for $intCategoryID				
		 * @param integer $intCategoryID
		 * @return multitype allow_voting
		 */
		 public function get_allow_voting($intCategoryID){
			if (isset($this->mediacenter_categories[$intCategoryID])){
				return $this->mediacenter_categories[$intCategoryID]['allow_voting'];
			}
			return false;
		}
		
		public function get_checkbox_check($intCategoryID){
			//if ($intCategoryID == 1) return false;
			return true;
		}
		
		public function get_editicon($intCategoryID){
			return '<a href="'.$this->root_path.'plugins/mediacenter/admin/manage_categories.php'.$this->SID.'&cid='.$intCategoryID.'"><i class="fa fa-pencil fa-lg" title="'.$this->user->lang('edit').'"></i></a>';
		}
		
		public function get_check_alias($strAlias, $blnCheckAlbums=false){
			$strAlias = utf8_strtolower($strAlias);
		
			foreach ($this->mediacenter_categories as $key => $val){
				if ($this->get_alias($key) == $strAlias) return false;
			}
				
			//Check static routes
			$arrRoutes = register('routing')->getRoutes();
			if (isset($arrRoutes[$strAlias])) return false;
				
			//No Category uses this alias, check articles
			if ($blnCheckAlbums){
				$blnResult = $this->pdh->get('mediacenter_albums', 'check_alias', array($strAlias, false));
				return $blnResult;
			}
			return true;
		}
		
		public function get_calculated_permissions($intCategoryID, $strPermission, $intUsergroupID, $myPermission=false, $intParentID=false, $intCall = 0){
			$arrPermissions = $this->get_permissions($intCategoryID);
			$myPermission = ($myPermission !== false && $intCall == 0) ? $myPermission : ((isset($arrPermissions[$strPermission][$intUsergroupID])) ? $arrPermissions[$strPermission][$intUsergroupID] : -1);
			if ($strPermission == 'rea'){
				switch($myPermission){
					case -1:
					case 1:
						//Do we have a parent?
						$result = $myPermission;
						
						if ($intParentID !== false){
							$result = $this->get_calculated_permissions($intParentID, $strPermission, $intUsergroupID, $myPermission,  false, $intCall+1);
						} else {
							if ($this->get_parent($intCategoryID)) $result = $this->get_calculated_permissions($this->get_parent($intCategoryID), $strPermission, $intUsergroupID, $myPermission,  false, $intCall+1);
						}
						if($intCall != 0) return $result;
						if ($result == -1){
							switch($myPermission){
								case 0:
								case -1: return 0;
								case 1: return 1;
							}
						}
						return $result;
						break;
					default: return 0;
				}
		
		
			} else {

				switch($myPermission){
					case 0:
					case 1: return $myPermission;
					case -1: //Do we have a parent?
						$result = $myPermission;
						
						if ($intParentID !== false){
							$result = $this->get_calculated_permissions($intParentID, $strPermission, $intUsergroupID, $myPermission, false, $intCall+1);			
						} else {
							if ($this->get_parent($intCategoryID)) $result = $this->get_calculated_permissions($this->get_parent($intCategoryID), $strPermission, $intUsergroupID, $myPermission, false, $intCall+1);
						}

						if($intCall != 0) return $result;
						if ($result == -1) return 0;
						return $result;
				}
		
			}
			return 0;
		}
		
		public function get_sortation(){
			$myChildArray = array();
			$myRootArray  = array();
			if (is_array($this->mediacenter_categories)){
				foreach($this->mediacenter_categories as $key => $val){
					if ($val['parent']) {
						$myChildArray[$val['parent']][] = $key;
					} else {
						$myRootArray[$key] = $key;
					}
				}
			}
				
			$outArray = array();
			foreach($myRootArray as $key => $val){
				$outArray[] = $key;
				$this->add_array($key, $outArray, $myChildArray);
			}
				
			return array_flip($outArray);
		}
		
		public function add_array($key, &$arrOut, $arrChildArray){
			if (isset($arrChildArray[$key])){
				foreach($arrChildArray[$key] as $val){
					$arrOut[] = $val;
					$this->add_array($val, $arrOut, $arrChildArray);
				}
			}
		}
		
		public function get_parent_count($intCategoryID, $intCount=0){
			if ($this->get_parent($intCategoryID)){
				$intCount = $this->get_parent_count($this->get_parent($intCategoryID), $intCount+1);
			}
			return $intCount;
		}
		
		public function get_name_prefix($intCategoryID){
			$intParentCount = $this->get_parent_count($intCategoryID);
			$strOut = '';
			for($i=0; $i < $intParentCount; $i++){
				$strOut .= '-- ';
			}
			return $strOut;
		}
		
		public function get_resolve_alias($strAlias){
			$strAlias = utf8_strtolower($strAlias);
				
			if (isset($this->alias[$strAlias])){
				return $this->alias[$strAlias];
			}
			return false;
		}
		
		public function get_path($intCategoryID,$blnWithSID=true){
			$strPath = $this->add_path($intCategoryID);
				
			switch((int)$this->config->get('seo_extension')){
				case 1:
					if(substr($strPath, -1) == "/") $strPath = substr($strPath, 0, -1);
					$strPath .= '.html';
					break;
				case 2: if(substr($strPath, -1) == "/") $strPath = substr($strPath, 0, -1);
				$strPath .= '.php';
				break;
				default: $strPath .= '/';
			}
				
			return 'MediaCenter/'.$strPath.(($blnWithSID) ? $this->SID : '');
		}
		
		private function add_path($intCategoryID, $strPath=''){
			$strAlias = ucfirst($this->get_alias($intCategoryID));
			if ($strAlias != ''){
				$strPath = $strAlias.'/'.$strPath;
			}
			if ($this->get_parent($intCategoryID)){
				$strPath = $this->add_path($this->get_parent($intCategoryID), $strPath);
			}
				
			return $strPath;
		}
		
		public function get_user_permissions($intCategoryID, $intUserID){
			$arrUsergroupMemberships = $this->acl->get_user_group_memberships($intUserID);
				
			if (isset($this->arrTempPermissions[$intCategoryID]) && isset($this->arrTempPermissions[$intCategoryID][$intUserID])){
				return $this->arrTempPermissions[$intCategoryID][$intUserID];
			} else {
				$arrPermissions = array('read' => false, 'create' => false, 'delete' => false, 'update' => false, 'change_state' => false, 'add_album' => false);
				foreach($arrUsergroupMemberships as $intGroupID => $intStatus){
					$blnReadPerm = $this->get_calculated_permissions($intCategoryID, 'rea', $intGroupID);
					if ($blnReadPerm) $arrPermissions['read'] = true;
					$blnCreatePerm = $this->get_calculated_permissions($intCategoryID, 'cre', $intGroupID);
					if ($blnCreatePerm) $arrPermissions['create'] = true;
					$blnUpdatePerm = $this->get_calculated_permissions($intCategoryID, 'upd', $intGroupID);
					if ($blnUpdatePerm) $arrPermissions['update'] = true;
					$blnDeletePerm = $this->get_calculated_permissions($intCategoryID, 'del', $intGroupID);
					if ($blnDeletePerm) $arrPermissions['delete'] = true;
					$blnChangeStatePerm = $this->get_calculated_permissions($intCategoryID, 'chs', $intGroupID);
					if ($blnChangeStatePerm) $arrPermissions['change_state'] = true;
					$blnAddAlbumPerm = $this->get_calculated_permissions($intCategoryID, 'ada', $intGroupID);
					if ($blnAddAlbumPerm) $arrPermissions['add_album'] = true;
				}
				$this->arrTempPermissions[$intCategoryID][$intUserID] = $arrPermissions;
				return $arrPermissions;
			}
		}
		
		public function get_breadcrumb($intCategoryID){
			$strBreadcrumb = ($this->get_parent($intCategoryID)) ? $this->add_breadcrumb($this->get_parent($intCategoryID)) : '';
			$strBreadcrumb .=  '<li class="current"><a href="'.$this->controller_path.$this->get_path($intCategoryID).'">'.$this->get_name($intCategoryID).'</a></li>';
			return $strBreadcrumb;
		}
		
		private function add_breadcrumb($intCategoryID, $strBreadcrumb=''){
			$strName = $this->get_name($intCategoryID);
			$strPath = $this->get_path($intCategoryID);
			$strBreadcrumb = '<li><a href="'.$this->controller_path.$strPath.'">'.$strName.'</a></li>'.$strBreadcrumb;
				
			if ($this->get_parent($intCategoryID)){
				$strBreadcrumb = $this->add_breadcrumb($this->get_parent($intCategoryID), $strBreadcrumb);
			}
				
			return $strBreadcrumb;
		}
		
		public function get_childs($intCategoryID){
			$arrChilds = array();
			foreach($this->mediacenter_categories as $catID => $val){
				if ($this->get_parent($catID) === $intCategoryID){
					$arrChilds[] = $catID;
				}
			}
			return $arrChilds;
		}
		
		public function get_media_count($intCategoryID){
			$arrMedia = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryID));
			$intCount = 0;
			foreach($arrMedia as $intAlbumID => $arrMedias){
				$intCount += count($arrMedias);
			}
			
			return $intCount;
		}
		
		public function get_album_count($intCategoryID){
			return count($this->pdh->get('mediacenter_albums', 'albums_for_category', array($intCategoryID)));
		}
		
		public function get_category_tree($blnPublishedOnly=false, $blnWithPrefix=true){
			$arrCategories = array();
			$arrCategoryIDs = $this->pdh->sort($this->get_id_list($blnPublishedOnly), 'mediacenter_categories', 'sort_id', 'asc');
			foreach($arrCategoryIDs as $intCategoryID){
				if ($blnPublishedOnly && !$this->get_published($intCategoryID)) continue;
				
				$catName = (($blnWithPrefix) ? $this->get_name_prefix($intCategoryID) : '').$this->get_name($intCategoryID);	
				$arrCategories[$intCategoryID] = $catName;
			}
			return $arrCategories;
		}
		
		public function get_unpublished_articles_notify(){
			$arrOut = array();
			foreach($this->mediacenter_categories as $intCategoryID => $val){
				if (!$val['notify_on_onpublished']) continue;
				$arrArticleIDs = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryID, false, true));
				foreach($arrArticleIDs as $intArticleID){
					if (!$this->pdh->get('mediacenter_media', 'published', array($intArticleID))){
						if (!isset($arrOut[$intCategoryID])) $arrOut[$intCategoryID] = 0;
						$arrOut[$intCategoryID]++;
					}
				}
			}
			return $arrOut;
		}

	}//end class
}//end if
?>