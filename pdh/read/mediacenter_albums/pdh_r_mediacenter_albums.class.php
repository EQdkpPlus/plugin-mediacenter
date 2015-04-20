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
				
if ( !class_exists( "pdh_r_mediacenter_albums" ) ) {
	class pdh_r_mediacenter_albums extends pdh_r_generic{
		public static function __shortcuts() {
		$shortcuts = array();
		return array_merge(parent::$shortcuts, $shortcuts);
	}				
	
	public $default_lang = 'english';
	public $mediacenter_albums = null;

	public $hooks = array(
		'mediacenter_albums_update',
	);		
			
	public $presets = array(
		'mediacenter_albums_id' => array('id', array('%intAlbumID%'), array()),
		'mediacenter_albums_name' => array('name', array('%intAlbumID%'), array()),
		'mediacenter_albums_description' => array('description', array('%intAlbumID%'), array()),
		'mediacenter_albums_personal_album' => array('personal_album', array('%intAlbumID%'), array()),
		'mediacenter_albums_user_id' => array('user_id', array('%intAlbumID%'), array()),
		'mediacenter_albums_date' => array('date', array('%intAlbumID%'), array()),
		'mediacenter_albums_category_id' => array('category_id', array('%intAlbumID%'), array()),
	);
			
	public function reset(){
			$this->pdc->del('pdh_mediacenter_albums_table');
			
			$this->mediacenter_albums = NULL;
	}
					
	public function init(){
			$this->mediacenter_albums	= $this->pdc->get('pdh_mediacenter_albums_table');				
					
			if($this->mediacenter_albums !== NULL){
				return true;
			}		

			$objQuery = $this->db->query('SELECT * FROM __mediacenter_albums');
			if($objQuery){
				while($drow = $objQuery->fetchAssoc()){
					$this->mediacenter_albums[(int)$drow['id']] = array(
						'id'				=> (int)$drow['id'],
						'name'				=> $drow['name'],
						'description'		=> $drow['description'],
						'personal_album'	=> (int)$drow['personal_album'],
						'user_id'			=> (int)$drow['user_id'],
						'date'				=> (int)$drow['date'],
						'category_id'		=> (int)$drow['category_id'],
						'type'				=> $this->calc_type((int)$drow['id']),
					);
				}
				
				$this->pdc->put('pdh_mediacenter_albums_table', $this->mediacenter_albums, null);
			}

		}	//end init function

		/**
		 * @return multitype: List of all IDs
		 */				
		public function get_id_list(){
			if ($this->mediacenter_albums === null) return array();
			return array_keys($this->mediacenter_albums);
		}
		
		/**
		 * Calculated the Type on an Album based on Media Types
		 * 
		 * @param integer $intAlbumID
		 * @return mixed
		 */
		private function calc_type($intAlbumID){
			$arrMedia = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID));
			$intType = false;
			foreach ($arrMedia as $intMediaID){
				if ($intType === false) {
					//First one
					$intType = (int)$this->pdh->get('mediacenter_media', 'type', array($intMediaID));
				} else {
					if ($intType != (int)$this->pdh->get('mediacenter_media', 'type', array($intMediaID))) return false;
				}
				
			}
			
			return $intType;
		}
		
		/**
		 * Returns the calculated Type of an album, based on media types
		 * Does not check the Category Type
		 * Returns false is the type is mixed. Returns int with the Type is album has only one type
		 * 
		 * @param integer $intAlbumID
		 * @return mixed
		 */
		public function get_calculated_type($intAlbumID){
		if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['type'];
			}
			return false;
		}
		
		/**
		 * Get all data of Element with $strID
		 * @return multitype: Array with all data
		 */				
		public function get_data($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID];
			}
			return false;
		}
				
		/**
		 * Returns id for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype id
		 */
		 public function get_id($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['id'];
			}
			return false;
		}

		/**
		 * Returns name for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype name
		 */
		 public function get_name($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['name'];
			}
			return false;
		}

		/**
		 * Returns description for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype description
		 */
		 public function get_description($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['description'];
			}
			return false;
		}

		/**
		 * Returns personal_album for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype personal_album
		 */
		 public function get_personal_album($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['personal_album'];
			}
			return false;
		}

		/**
		 * Returns user_id for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype user_id
		 */
		 public function get_user_id($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['user_id'];
			}
			return false;
		}

		/**
		 * Returns date for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype date
		 */
		 public function get_date($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['date'];
			}
			return false;
		}

		/**
		 * Returns category_id for $intAlbumID				
		 * @param integer $intAlbumID
		 * @return multitype category_id
		 */
		 public function get_category_id($intAlbumID){
			if (isset($this->mediacenter_albums[$intAlbumID])){
				return $this->mediacenter_albums[$intAlbumID]['category_id'];
			}
			return false;
		}
		
		public function get_check_alias(){
			return true;
		}
		
		public function get_albums_for_category($intCategoryID){
			$arrAlbums = array();
			if (is_array($this->mediacenter_albums)){
				foreach($this->mediacenter_albums as $intAlbumID => $arrValue){
					if ($arrValue['category_id'] == $intCategoryID) $arrAlbums[] = $intAlbumID;
				}
			}
			return $arrAlbums;
		}
		
		public function get_category_tree(){
			$arrCategories = array();
			$arrCategoryIDs = $this->pdh->sort($this->pdh->get('mediacenter_categories', 'id_list', array()), 'mediacenter_categories', 'sort_id', 'asc');
		  	foreach($arrCategoryIDs as $intCategoryID){
		  		$catName = $this->pdh->get('mediacenter_categories', 'name_prefix', array($intCategoryID)).$this->pdh->get('mediacenter_categories', 'name', array($intCategoryID));
		  		$arrAlbums = $this->get_albums_for_category($intCategoryID);
		  		$arrAlbumOut = array();
		  		foreach($arrAlbums as $albumID){
		  			$arrAlbumOut[$albumID] = $this->pdh->get('mediacenter_albums', 'name', array($albumID));
		  		}
		  		
		  		$arrCategories[$catName] = $arrAlbumOut;
		  	}
		  	return $arrCategories;
		}
		
		public function get_html_album_tree($strValue = false, $blnCheckPermissions = false){
			$strOut = "";
			
			
			$arrCategoryIDs = $this->pdh->sort($this->pdh->get('mediacenter_categories', 'id_list', array()), 'mediacenter_categories', 'sort_id', 'asc');
			foreach($arrCategoryIDs as $intCategoryID){
				if($blnCheckPermissions){
					$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
					if ((!$arrPermissions || !$arrPermissions['create'])) continue;
				}
				
				
				$strPrefix = $this->pdh->get('mediacenter_categories', 'name_prefix', array($intCategoryID));
				$catName = $strPrefix.$this->pdh->get('mediacenter_categories', 'name', array($intCategoryID));
				
				$selected = ($strValue !== false && $strValue == 'c'.$intCategoryID) ? 'selected="selected"' : '';
					
				$arrAlbums = $this->get_albums_for_category($intCategoryID);
				$class = ' ';
				$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
				foreach($arrTypes as $typeid){
					switch($typeid){
						case 0: $class .= 'file';
						break;
						case 1: $class .= 'video';
						break;
						case 2: $class .= 'image';
					}
				}
				
				$strOut .= '<option class="category'.$class.'"'.$selected.' value="c'.$intCategoryID.'">'.$catName.'</option>';
				
				foreach($arrAlbums as $albumID){
					$selected = ($strValue !== false && $strValue == $albumID) ? 'selected="selected"' : '';		
					$strOut .= '<option class="'.$class.'"'.$selected.' value="'.$albumID.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strPrefix.$this->pdh->get('mediacenter_albums', 'name', array($albumID)).'</option>';
				}
			}
			return $strOut;
		}
		
		public function get_path($intAlbumID, $url_id=false, $arrPath=array()){
			$strPath = "";
			$strPath .= $this->add_path($this->get_category_id($intAlbumID));
		
			$strAlias = ucfirst($this->get_name($intAlbumID)).'-a'.$intAlbumID;
			$strPath .= $strAlias;
				
			if(substr($strPath, -1) == "/") $strPath = substr($strPath, 0, -1);
			$strPath .= $this->routing->getSeoExtension();
		
			return 'MediaCenter/'.$strPath.(($this->SID == "?s=") ? '?' : $this->SID);
		}
		
		private function add_path($intCategoryID, $strPath=''){
			$strAlias = ucfirst($this->pdh->get('mediacenter_categories', 'alias', array($intCategoryID)));
			if ($strAlias != ''){
				$strPath = $strAlias.'/'.$strPath;
			}
			if ($this->pdh->get('mediacenter_categories', 'parent', array($intCategoryID))){
				$strPath = $this->add_path($this->pdh->get('mediacenter_categories', 'parent', array($intCategoryID)), $strPath);
			}
		
			return $strPath;
		}
		
		public function get_breadcrumb($intAlbumID){
			$intCategoryID = $this->get_category_id($intAlbumID);
			
			$strBreadcrumb = str_replace('class="current"', '', $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryID)));
			$strBreadcrumb .=  '<li class="current"><a href="'.$this->controller_path.$this->get_path($intAlbumID).'">'.$this->get_name($intAlbumID).'</a></li>';
			return $strBreadcrumb;
		}

		public function get_my_albums($intUserID){
			$arrOut = array();
			foreach($this->mediacenter_albums as $intAlbumID => $arrAlbumData){
				if($arrAlbumData['user_id'] === $intUserID){
					$arrOut[] = $intAlbumID;
				}
			}
			return $arrOut;
		}

	}//end class
}//end if
?>