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
				
if ( !class_exists( "pdh_w_mediacenter_albums" ) ) {
	class pdh_w_mediacenter_albums extends pdh_w_generic {
		
		private $arrLogLang = array(
				'id'				=> "{L_ID}",
				'name'				=> "{L_NAME}",
				'description'		=> "{L_DESCRIPTION}",
				'personal_album'	=> "{L_MC_F_PERSONAL_ALBUM}",
				'user_id'			=> "{L_USER}",
				'category_id'		=> "{L_MC_F_CATEGORY}",
		);
		
		public function insert_album($strName, $strDescription, $intCategoryID, $blnIsPersonalAlbum=false, $intUserID=false){
			$arrQuery = array(
					'name'			=> $strName,
					'description'	=> $strDescription,
					'personal_album'=> $blnIsPersonalAlbum,
					'user_id'		=> ($intUserID === false) ? $this->user->id : $intUserID,
 					'date'			=> $this->time->time,
					'category_id'	=> $intCategoryID,
			);
			
			$objQuery = $this->db->prepare($arrQuery)->execute();
			
			$this->pdh->enqueue_hook('mediacenter_albums_update');
			if ($objQuery) {
				$intInsertID = $objQuery->insertId;
				$log_action = $this->logs->diff(false, $arrQuery, $this->arrLogLang);
				$this->log_insert("action_album_added", $log_action, $intInsertID, $arrQuery["name"], 1, 'mediacenter');
				
				return $intInsertID;
			}
			
			return false;
		}
		
		public function update_album($intID, $strName, $strDescription, $intCategoryID, $blnIsPersonalAlbum=false, $intUserID=false){
			$arrOldData = $this->pdh->get('mediacenter_albums', 'data', array($intID));
			
			$arrQuery = array(
					'name'			=> $strName,
					'description'	=> $strDescription,
					'personal_album'=> $blnIsPersonalAlbum,
					'category_id'	=> $intCategoryID,
					'user_id'		=> ($intUserID === false) ? $$this->pdh->get('mediacenter_albums', 'user_id', array($intID)) : $intUserID,
			);
			
			$objQuery = $this->db->prepare("UPDATE __mediacenter_albums :p WHERE id=?")->set($arrQuery)->execute($intID);
				
			$this->pdh->enqueue_hook('mediacenter_albums_update');
			if ($objQuery) {
				
				$log_action = $this->logs->diff($arrOldData, $arrQuery, $this->arrLogLang, array('description' => 1), true);
				$this->log_insert("action_album_updated", $log_action, $intID, $arrOldData["name"], 1, 'mediacenter');
				
				return $intID;
			}
				
			return false;
		}
	
		public function delete_album($intID){
			$arrOldData = $this->pdh->get('mediacenter_albums', 'data', array($intID));
			$objQuery = $this->db->prepare("DELETE FROM __mediacenter_albums WHERE id=?")->execute($intID);
			
			$arrMedia = $this->pdh->get('mediacenter_media', 'id_list', array($intID));
			foreach($arrMedia as $intMediaID){
				$this->pdh->put('mediacenter_media', 'delete', array($intMediaID));
			}
			
			$this->pdh->enqueue_hook('mediacenter_albums_update');
			if ($objQuery) {
				$log_action = $this->logs->diff(false, $arrOldData, $this->arrLogLang);
				$this->log_insert("action_album_deleted", $log_action, $intID, $arrOldData["name"],  1, 'mediacenter');
				
				return true;
			}
			
			return false;
		}

	}//end class
}//end if
?>