<?php
/*
* Project:		EQdkp-Plus
* License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
* Link:			http://creativecommons.org/licenses/by-nc-sa/3.0/
* -----------------------------------------------------------------------
* Began:		2010
* Date:			$Date: 2013-01-29 17:35:08 +0100 (Di, 29 Jan 2013) $
* -----------------------------------------------------------------------
* @author		$Author: wallenium $
* @copyright	2006-2014 EQdkp-Plus Developer Team
* @link			http://eqdkp-plus.eu
* @package		eqdkpplus
* @version		$Rev: 12937 $
*
* $Id: pdh_r_articles.class.php 12937 2013-01-29 16:35:08Z wallenium $
*/

if ( !defined('EQDKP_INC') ){
	die('Do not access this file directly.');
}
				
if ( !class_exists( "pdh_r_mediacenter_media" ) ) {
	class pdh_r_mediacenter_media extends pdh_r_generic{
		public static function __shortcuts() {
		$shortcuts = array();
		return array_merge(parent::$shortcuts, $shortcuts);
	}				
	
	public $default_lang = 'english';
	public $mediacenter_media = null;

	public $hooks = array(
		'mediacenter_media_update',
	);		
			
	public $presets = array(
		'mediacenter_media_id' => array('id', array('%intMediaID%'), array()),
		'mediacenter_media_album_id' => array('album_id', array('%intMediaID%'), array()),
		'mediacenter_media_category_id' => array('category_id', array('%intMediaID%'), array()),
		'mediacenter_media_name' => array('name', array('%intMediaID%'), array()),
		'mediacenter_media_description' => array('description', array('%intMediaID%'), array()),
		'mediacenter_media_filename' => array('filename', array('%intMediaID%'), array()),
		'mediacenter_media_localfile' => array('localfile', array('%intMediaID%'), array()),
		'mediacenter_media_externalfile' => array('externalfile', array('%intMediaID%'), array()),
		'mediacenter_media_reported' => array('reported', array('%intMediaID%'), array()),
		'mediacenter_media_previewimage' => array('previewimage', array('%intMediaID%'), array()),
		'mediacenter_media_type' => array('type', array('%intMediaID%'), array()),
		'mediacenter_media_tags' => array('tags', array('%intMediaID%'), array()),
		'mediacenter_media_votes_count' => array('votes_count', array('%intMediaID%'), array()),
		'mediacenter_media_votes_sum' => array('votes_sum', array('%intMediaID%'), array()),
		'mediacenter_media_votes_users' => array('votes_users', array('%intMediaID%'), array()),
		'mediacenter_media_featured' => array('featured', array('%intMediaID%'), array()),
		'mediacenter_media_published' => array('published', array('%intMediaID%'), array()),
		'mediacenter_media_additionaldata' => array('additionaldata', array('%intMediaID%'), array()),
		'mediacenter_media_date' => array('date', array('%intMediaID%'), array()),
		'mediacenter_media_views' => array('views', array('%intMediaID%'), array()),
		'mediacenter_media_user_id' => array('user_id', array('%intMediaID%'), array()),
		'mediacenter_media_editicon' => array('editicon', array('%intMediaID%'), array()),
		'mediacenter_media_downloads' => array('downloads', array('%intMediaID%'), array()),		
	);
		
	public function reset(){
			$this->pdc->del('pdh_mediacenter_media_table');
			
			$this->mediacenter_media = NULL;
	}
					
	public function init(){
			$this->mediacenter_media	= $this->pdc->get('pdh_mediacenter_media_table');				
					
			if($this->mediacenter_media !== NULL){
				return true;
			}		

			$objQuery = $this->db->query('SELECT * FROM __mediacenter_media');
			if($objQuery){
				while($drow = $objQuery->fetchAssoc()){
					$this->mediacenter_media[(int)$drow['id']] = array(
						'id'				=> (int)$drow['id'],
						'album_id'			=> (int)$drow['album_id'],
						'category_id'		=> (int)$drow['category_id'],
						'name'				=> $drow['name'],
						'description'		=> $drow['description'],
						'filename'			=> $drow['filename'],
						'localfile'			=> $drow['localfile'],
						'externalfile'		=> $drow['externalfile'],
						'reported'			=> (int)$drow['reported'],
						'previewimage'		=> $drow['previewimage'],
						'type'				=> (int)$drow['type'],
						'tags'				=> $drow['tags'],
						'votes_count'		=> (int)$drow['votes_count'],
						'votes_sum'			=> (int)$drow['votes_sum'],
						'votes_users'		=> $drow['votes_users'],
						'featured'			=> (int)$drow['featured'],
						'published'			=> (int)$drow['published'],
						'additionaldata'	=> $drow['additionaldata'],
						'date'				=> (int)$drow['date'],
						'views'				=> (int)$drow['views'],
						'downloads'			=> (int)$drow['downloads'],
						'user_id'			=> (int)$drow['user_id'],			
					);
				}
				
				$this->pdc->put('pdh_mediacenter_media_table', $this->mediacenter_media, null);
			}

		}	//end init function

		/**
		 * @return multitype: List of all IDs
		 */				
		public function get_id_list($intAlbumID=false, $blnPublishedOnly=false){
			if ($this->mediacenter_media === null) return array();
			
			if ($intAlbumID){
				$arrKeys = array();
				foreach($this->mediacenter_media as $intMediaID => $val){
					if ($blnPublishedOnly && !$this->get_published($intMediaID)) continue;
					if ($val['album_id'] == $intAlbumID) $arrKeys[] = $intMediaID;
				}
				return $arrKeys;
			}else return array_keys($this->mediacenter_media);
		}
		
		/**
		 * @param integer $intCategoryID
		 * @param boolean $blnPublishedOnly
		 * @return array  Array: albumid => array with all Media in Category. Albumid 0 = only in category
		 */
		public function get_id_list_for_category($intCategoryID, $blnPublishedOnly=false){
			if ($this->mediacenter_media === null) return array();
			$arrOut = array();
			
			foreach($this->mediacenter_media as $intMediaID => $val){
				if ($blnPublishedOnly && !$this->get_published($intMediaID)) continue;
				
				if ($this->get_category_id($intMediaID) == $intCategoryID){
					if (!isset($arrOut[$this->get_album_id($intMediaID)])) $arrOut[$this->get_album_id($intMediaID)] = array();
					
					$arrOut[$this->get_album_id($intMediaID)][] = $intMediaID;
				}
			}
			
			return $arrOut;
		}
		
		/**
		 * Get all data of Element with $strID
		 * @return multitype: Array with all data
		 */				
		public function get_data($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID];
			}
			return false;
		}
				
		/**
		 * Returns id for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype id
		 */
		 public function get_id($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['id'];
			}
			return false;
		}

		/**
		 * Returns album_id for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype album_id
		 */
		 public function get_album_id($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['album_id'];
			}
			return false;
		}
		
		
		/**
		 * Returns category_id for $intMediaID
		 * @param integer $intMediaID
		 * @return multitype category_id
		 */
		public function get_category_id($intMediaID){
			if ($this->get_album_id($intMediaID) == 0){
				return $this->mediacenter_media[$intMediaID]['category_id'];
			} else {		
				$albumID = $this->get_album_id($intMediaID);
				if ($albumID){
					$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($albumID));
					return $intCategoryID;
				}
			}
			return false;
		}

		/**
		 * Returns name for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype name
		 */
		 public function get_name($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['name'];
			}
			return false;
		}
		
		public function get_html_name($intMediaID){
			return '<a href="javascript:editmedia('.$intMediaID.')">'.$this->get_name($intMediaID).'</a>';
		}

		/**
		 * Returns description for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype description
		 */
		 public function get_description($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['description'];
			}
			return false;
		}

		/**
		 * Returns filename for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype filename
		 */
		 public function get_filename($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['filename'];
			}
			return false;
		}

		/**
		 * Returns localfile for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype localfile
		 */
		 public function get_localfile($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['localfile'];
			}
			return false;
		}

		/**
		 * Returns externalfile for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype externalfile
		 */
		 public function get_externalfile($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['externalfile'];
			}
			return false;
		}

		/**
		 * Returns reported for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype reported
		 */
		 public function get_reported($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['reported'];
			}
			return false;
		}
		
		public function get_html_reported($intMediaID){
			if ($this->get_reported($intMediaID)){
				return $this->core->icon_font('fa fa-warning icon-red fa-lg');
			}
			return "";
		}

		/**
		 * Returns previewimage for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype previewimage
		 */
		 public function get_previewimage($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['previewimage'];
			}
			return false;
		}
		
		//Types: 1: small 64px, 2: big 240px, 3: orig
		public function get_html_previewimage($intMediaID, $intType=1){
			if ($this->get_previewimage($intMediaID) && strlen($this->get_previewimage($intMediaID))){
				$image = $this->get_previewimage($intMediaID);
				
				switch($intType){
					case 1: $icon = str_replace('.', '.64.', $image);
						break;
					case 2: $icon = str_replace('.', '.240.', $image);
						break;
					default: $icon = $image;
						
				}
				return '<img src="'.$this->pfh->FolderPath('thumbs', 'mediacenter', 'absolute').$icon.'?_t='.$this->time->time.'" />';
			}

			return '<img src="'.$this->server_path.'images/global/default-image.svg" height="40"/>';
		}

		/**
		 * Returns type for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype type
		 */
		 public function get_type($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['type'];
			}
			return false;
		}
		
		public function get_html_type($intMediaID){
			$intType = $this->get_type($intMediaID);
			$icon = "";
			switch($intType){
				case 0: $icon = $this->core->icon_font('fa fa-file');
					break;
				case 1: $icon = $this->core->icon_font('fa fa-video-camera');
					break;
				case 2: $icon = $this->core->icon_font('fa fa-camera');
					break;
				default: $icon = $this->core->icon_font('fa fa-file');
			}
			return $icon;
		}

		/**
		 * Returns tags for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype tags
		 */
		 public function get_tags($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return unserialize($this->mediacenter_media[$intMediaID]['tags']);
			}
			return false;
		}

		/**
		 * Returns votes_count for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype votes_count
		 */
		 public function get_votes_count($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['votes_count'];
			}
			return false;
		}

		/**
		 * Returns votes_sum for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype votes_sum
		 */
		 public function get_votes_sum($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['votes_sum'];
			}
			return false;
		}

		/**
		 * Returns votes_users for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype votes_users
		 */
		 public function get_votes_users($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['votes_users'];
			}
			return false;
		}

		/**
		 * Returns featured for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype featured
		 */
		 public function get_featured($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['featured'];
			}
			return false;
		}
		
		public function get_html_featured($intMediaID){
			if ($this->get_featured($intMediaID)){
				$strImage = '<div><div class="featured featuredToggleTrigger"></div><input type="hidden" class="featured_cb" name="featured['.$intMediaID.']" value="1"/></div>';
			} else {
				$strImage = '<div><div class="not-featured featuredToggleTrigger"></div><input type="hidden" class="featured_cb" name="featured['.$intMediaID.']" value="0"/></div>';
			}
			return $strImage;
		}

		/**
		 * Returns published for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype published
		 */
		 public function get_published($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['published'];
			}
			return false;
		}
		
		public function get_html_published($intMediaID){
			if ($this->get_published($intMediaID)){
				$strImage = '<div><div class="eye eyeToggleTrigger"></div><input type="hidden" class="published_cb" name="published['.$intMediaID.']" value="1"/></div>';
			} else {
				$strImage = '<div><div class="eye-gray eyeToggleTrigger"></div><input type="hidden" class="published_cb" name="published['.$intMediaID.']" value="0"/></div>';
			}
			return $strImage;
		}

		/**
		 * Returns additionaldata for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype additionaldata
		 */
		 public function get_additionaldata($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return unserialize($this->mediacenter_media[$intMediaID]['additionaldata']);
			}
			return false;
		}

		/**
		 * Returns date for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype date
		 */
		 public function get_date($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['date'];
			}
			return false;
		}
		
		public function get_html_date($intMediaID){
			return $this->time->user_date($this->get_date($intMediaID), true);
		}

		/**
		 * Returns views for $intMediaID				
		 * @param integer $intMediaID
		 * @return multitype views
		 */
		 public function get_views($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['views'];
			}
			return false;
		}
		
		/**
		 * Returns downloads for $intMediaID
		 * @param integer $intMediaID
		 * @return multitype views
		 */
		public function get_downloads($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['downloads'];
			}
			return false;
		}
		
		/**
		 * Returns user_id for $intMediaID
		 * @param integer $intMediaID
		 * @return multitype views
		 */
		public function get_user_id($intMediaID){
			if (isset($this->mediacenter_media[$intMediaID])){
				return $this->mediacenter_media[$intMediaID]['user_id'];
			}
			return false;
		}
		
		public function get_html_user_id($intMediaID){
			return $this->pdh->get('user', 'name', array($this->get_user_id($intMediaID)));
		}
		
		public function get_editicon($intMediaID){
			return '<a href="javascript:editmedia('.$intMediaID.')"><i class="fa fa-pencil fa-lg" title="'.$this->user->lang('edit').'"></i></a>';
		}
		
		public function get_checkbox_check($intMediaID){
			return true;
		}

	}//end class
}//end if
?>