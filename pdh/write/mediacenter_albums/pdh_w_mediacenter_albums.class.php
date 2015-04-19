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
			
			$objQuery = $this->db->prepare('INSERT INTO __mediacenter_albums :p')->set($arrQuery)->execute();
			
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