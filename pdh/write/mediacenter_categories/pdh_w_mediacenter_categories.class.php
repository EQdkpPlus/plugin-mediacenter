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

if(!defined('EQDKP_INC')) {
	die('Do not access this file directly.');
}

if(!class_exists('pdh_w_mediacenter_categories')) {
	class pdh_w_mediacenter_categories extends pdh_w_generic {
		
		private $arrLogLang = array(
				'id'				=> "{L_ID}",
				'name'				=> "{L_NAME}",
				'alias'				=> "{L_ALIAS}",
				'description'		=> "{L_DESCRIPTION}",
				'permissions'		=> "{L_PERMISSIONS}",
				'published'			=> "{L_PUBLISHED}",
				'parent'			=> "{L_PARENT_CATEGORY}",
				'sort_id'			=> "{L_SORTATION}",
				'types'				=> "{L_MC_MEDIA_TYPE}",
				'layout'			=> "{L_MC_DEFAULT_LAYOUT}",
				'notify_on_onpublished' => "{L_NOTIFY_ON_UNPUBLISHED_ARTICLES}",
				'default_published_state' => "{L_ARTICLE_PUBLISHED_STATE}",
				'allow_comments'	=> "{L_MC_ALLOW_COMMENTS}",
				'allow_votings'		=> "{L_MC_ALLOW_VOTING}",
				'per_page'			=> "{L_MC_F_PER_PAGE}",
				
		);

		public function delete($id) {
			$arrMedia = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($id));
			if (isset($arrMedia[0]) && count($arrMedia)){
				foreach($arrMedia[0] as $intMediaID){
					$this->pdh->put('mediacenter_media', 'delete', array($intMediaID));
				}
			}
			
			$this->delete_recursiv(intval($id));
			
			$this->pdh->enqueue_hook('articles_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			return true;
		}
		
		private function delete_recursiv($intCategoryID){
			if ($this->pdh->get('mediacenter_categories', 'childs', array($intCategoryID))){
				foreach($this->pdh->get('mediacenter_categories', 'childs', array($intCategoryID)) as $intChildID){
					$this->delete_recursiv($intChildID);
					$arrOldData = $this->pdh->get('mediacenter_categories', 'data', array($intChildID));
					
					$arrAlbums = $this->pdh->get('mediacenter_albums', 'albums_for_category', array($intChildID));
					foreach($arrAlbums as $intAlbumID){
						$this->pdh->put('mediacenter_albums', 'delete_album', array($intAlbumID));
					}					
					$log_action = $this->logs->diff(false, $arrOldData, $this->arrLogLang);
					$this->log_insert("action_category_deleted", $log_action, $intChildID, $arrOldData['name'], 1, 'mediacenter');
				}
			}
			$arrOldData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryID));
			$this->db->prepare("DELETE FROM __mediacenter_categories WHERE id =?")->execute($intCategoryID);
			$log_action = $this->logs->diff(false, $arrOldData, $this->arrLogLang);
			$this->log_insert("action_category_deleted", $log_action, $intCategoryID, $arrOldData["name"],  1, 'mediacenter');
			$this->ntfy->deleteNotification('mediacenter_media_new', $intCategoryID);
			
			return true;
		}
		
		
		
		public function add($strName, $strDescription, $strAlias, $intPublished, $intParentCategory, $intArticlePublishedState, 
				$arrPermissions, $intNotifyUnpublishedArticles, $intAllowComments, $intDefaultLayout, $arrTypes, $intAllowVoting, $intPerPage){
			if ($strAlias == ""){
				$strAlias = $this->create_alias($strName);
			} else {
				$strAlias = $this->create_alias($strAlias);
			}
			
			//Check Alias
			$blnAliasResult = $this->check_alias(0, $strAlias);
			if (!$blnAliasResult) return false;
			
			$strDescription = $this->bbcode->replace_shorttags($strDescription);
			$strDescription = $this->embedly->parseString($strDescription);
			
			$arrQuery  = array(
				'name' 			=> $strName,
				'description'	=> $strDescription,
				'alias' 		=> $strAlias,
				'published'		=> $intPublished,
				'parent'		=> $intParentCategory,
				'default_published_state' => $intArticlePublishedState,
				'permissions'	=> serialize($arrPermissions),
				'notify_on_onpublished' => $intNotifyUnpublishedArticles,
				'allow_comments'=> $intAllowComments,
				'layout'		=> $intDefaultLayout,
				'types'			=> serialize($arrTypes),
				'sort_id'		=> 99999999,
				'allow_voting'  => $intAllowVoting,
				'per_page'		=> $intPerPage,
			);
			
			$objQuery = $this->db->prepare("INSERT INTO __mediacenter_categories :p")->set($arrQuery)->execute();
			
			if ($objQuery){
				$id = $objQuery->insertId;
				$log_action = $this->logs->diff(false, $arrQuery, $this->arrLogLang);
				$this->log_insert("action_category_added", $log_action, $id, $arrQuery["name"], 1, 'mediacenter');
				
				$this->pdh->enqueue_hook('mediacenter_categories_update');
				return $id;
			}
			
			return false;
		}
		
		public function update($id, $strName, $strDescription, $strAlias, $intPublished, $intParentCategory, $intArticlePublishedState, 
				$arrPermissions, $intNotifyUnpublishedArticles, $intAllowComments, $intDefaultLayout, $arrTypes, $intAllowVoting, $intPerPage){
			
			if ($strAlias == "" || $strAlias != $this->pdh->get('mediacenter_categories', 'alias', array($id))){
				$strAlias = $this->create_alias($strName);
			} else {
				$strAlias = $this->create_alias($strAlias);
			}
			
			//Check Alias
			$blnAliasResult = $this->check_alias($id, $strAlias);
			if (!$blnAliasResult) return false;
			
			$strDescription = $this->bbcode->replace_shorttags($strDescription);
			$strDescription = $this->embedly->parseString($strDescription);
			
			$arrQuery = array(
				'name' 			=> $strName,
				'description'	=> $strDescription,
				'alias' 		=> $strAlias,
				'published'		=> $intPublished,
				'parent'		=> $intParentCategory,
				'default_published_state' => $intArticlePublishedState,
				'permissions'	=> serialize($arrPermissions),
				'notify_on_onpublished' => $intNotifyUnpublishedArticles,
				'allow_comments'=> $intAllowComments,
				'layout'		=> $intDefaultLayout,
				'types'			=> serialize($arrTypes),
				'sort_id'		=> 99999999,
				'allow_voting'  => $intAllowVoting,
				'per_page'		=> $intPerPage,
			);
			
			$arrOldData = $this->pdh->get('mediacenter_categories', 'data', array($id));
			
			$objQuery = $this->db->prepare("UPDATE __mediacenter_categories :p WHERE id=?")->set($arrQuery)->execute($id);
						
			if ($objQuery){
				$this->pdh->enqueue_hook('mediacenter_categories_update');
				
				$log_action = $this->logs->diff($arrOldData, $arrQuery, $this->arrLogLang, array('description' => 1), true);
				$this->log_insert("action_category_updated", $log_action, $id, $arrOldData["name"], 1, 'mediacenter');
				
				return $id;
			}
			
			return false;
		}
		
		public function update_sortandpublished($id, $intSortID, $intPublished){
			$arrOldData = array(
				'published' => $this->pdh->get('mediacenter_categories', 'published', array($id)),
			);
			
			$objQuery = $this->db->prepare("UPDATE __mediacenter_categories :p WHERE id=?")->set(array(
				'sort_id'		=> $intSortID,
				'published'		=> $intPublished,
			))->execute($id);
			
			if ($objQuery){
				$arrNewData = array(
					'published' => $intPublished,	
				);
				$log_action = $this->logs->diff($arrOldData, $arrNewData, $this->arrLogLang, array());
				if ($log_action) $this->log_insert("action_category_updated", $log_action, $id, $this->pdh->get('mediacenter_categories', 'name', array($id)), 1, 'mediacenter');
				
				$this->pdh->enqueue_hook('mediacenter_categories_update');
				return $id;
			}
			return false;
		}
		
		private function check_alias($id, $strAlias){
			if (is_numeric($strAlias)) return false;
			
			if ($id){
				$strMyAlias = $this->pdh->get('mediacenter_categories', 'alias', array($id));
				if ($strMyAlias == $strAlias) return true;		
				$blnResult = $this->pdh->get('mediacenter_categories', 'check_alias', array($strAlias));
				return $blnResult;
				
			} else {
				$blnResult = $this->pdh->get('mediacenter_categories', 'check_alias', array($strAlias));
				return $blnResult;
				
			}
			return false;
		}
		
		private function create_alias($strName){
			$strAlias = utf8_strtolower($strName);
			$strAlias = str_replace(' ', '-', $strAlias);
			$a_satzzeichen = array("\"",",",";",".",":","!","?", "&", "=", "/", "|", "#", "*", "+", "(", ")", "%", "$");
			$strAlias = str_replace($a_satzzeichen, "", $strAlias);
			return $strAlias;
		}
		
	}
}
?>