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
			foreach($this->mediacenter_albums as $intAlbumID => $arrValue){
				if ($arrValue['category_id'] == $intCategoryID) $arrAlbums[] = $intAlbumID;
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

	}//end class
}//end if
?>