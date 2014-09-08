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
				
if ( !class_exists( "pdh_w_mediacenter_media" ) ) {
	class pdh_w_mediacenter_media extends pdh_w_generic {
		
		private $arrLogLang = array(
				'id'				=> "{L_ID}",
				'album_id'			=> "{L_MC_F_ALBUM}",
				'name'				=> "{L_NAME}",
				'description'		=> "{L_DESCRIPTION}",
				'localfile'			=> "{L_MC_F_FILE}",
				'filename'			=> "{L_MC_F_FILENAME}",
				'externalfile'		=> "{L_MC_F_EXTERNALFILE}",
				'previewimage'		=> "{L_MC_F_PREVIEWIMAGE}",
				'type'				=> "{L_MC_F_TYPE}",
				'tags'				=> "{L_MC_F_TAGS}",
				'featured'			=> "{L_MC_F_FEATURED}",
				'reported'			=> "{L_MC_F_REPORTED}",
				'published'			=> "{L_MC_F_PUBLISHED}",
				'user_id'			=> "{L_USER}",
		);
		
		public function insert_media($intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename,
			$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false){
			$strLocalfile = "";
			$arrAdditionalData = array();
			$strLocalPreviewImage = "";
			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
			
			if ($intType == 0){
				//File
				if ($strFile == "" && $strExternalLink == ""){
					//No Local and External File
					return false;
				} elseif($strFile != "") {
					$strLocalfile = register('encrypt')->decrypt($strFile);
				}
				
				//If it's a image, we have a preview image
				if ($strPreviewimage == ""){
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif'))) return false;
					$filename = md5(rand().unique_id());
					$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
					$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
					
					$strLocalPreviewImage = $filename.'.'.$strExtension;
				}
				
			}elseif($intType == 1){
				//Video
				if ($strExternalLink != "" && $strFile == ""){
					//External File
					$objEmbedly = register('embedly');
					$arrEmbedlyDetails = $objEmbedly->getLinkDetails($strExternalLink);
					if (!$arrEmbedlyDetails) return false;
					
					$arrAdditionalData = array(
						'thumbnail_url' => $arrEmbedlyDetails[0]->thumbnail_url,
						'provider_name' => $arrEmbedlyDetails[0]->provider_name,
						'html' 			=> $arrEmbedlyDetails[0]->html,
						'url' 			=> $arrEmbedlyDetails[0]->url,
						'title' 		=> $arrEmbedlyDetails[0]->title,
					);
					
					$strLocalPreviewImage = "";
					//Download Previewimage
					if (isset($arrEmbedlyDetails[0]->thumbnail_url)){
						$binImage = register('urlfetcher')->fetch($arrEmbedlyDetails[0]->thumbnail_url);
						$strExtension = strtolower(pathinfo($arrEmbedlyDetails[0]->thumbnail_url, PATHINFO_EXTENSION));
						$filename = md5(rand().unique_id());
						$this->pfh->putContent($strThumbfolder.$filename, $binImage);
						
						$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
						$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
						$strLocalPreviewImage = $filename.'.'.$strExtension;
					}
					
				} elseif ($strFile != ""){
					//Internal File
					$strLocalfile = register('encrypt')->decrypt($strFile);
				} else return false;
				
				
			}else{
				//Image
				if ($strFile == "" || $strFilename == "") return false;
				$strLocalfile = register('encrypt')->decrypt($strFile);
				
				//Preview Image
				$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
				if (!in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif'))) return false;
				$filename = md5(rand().unique_id());
				$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
				$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
				
				$strLocalPreviewImage = $filename.'.'.$strExtension;
			}
			
			//Handle Previewimage
			if ($strLocalPreviewImage == "" && $strPreviewimage != ""){
				$strLocalPreviewImage = str_replace($this->pfh->FolderPath('previewimages', 'mediacenter', 'relative'), "", $strPreviewimage);
				$strLocalPreviewImage = str_replace($this->pfh->FolderPath('previewimages', 'mediacenter', 'plain'), "", $strPreviewimage);
				
				$filename = md5(rand().unique_id());
				$strExtension = strtolower(pathinfo($strLocalPreviewImage, PATHINFO_EXTENSION));
				
				$this->pfh->copy($this->pfh->FolderPath('previewimages', 'mediacenter').$strLocalPreviewImage, $strThumbfolder.$filename.'.'.$strExtension);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
				$this->pfh->Delete($this->pfh->FolderPath('previewimages', 'mediacenter').$strLocalPreviewImage);
				$strLocalPreviewImage = $filename.'.'.$strExtension;
			}		
			
			//Handle Tags
			$schluesselwoerter = preg_split("/[\s,]+/", $strTags);
			$arrTags = array();
			foreach($schluesselwoerter as $val){
				$arrTags[] = utf8_strtolower(str_replace("-", "", $val));
			}
			
			//Default Publish State
			$intCategoryID  = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			$blnDefaultPublishState = $this->pdh->get('mediacenter_categories', 'default_publish_state', array($intCategoryID));
			$intDefaultPublished = ($this->user->check_auth('a_mediacenter_manage', false)) ? 1 : $blnDefaultPublishState;
			
			//Admin Things
			$intPublished = ($intPublished !== false) ? $intPublished : $intDefaultPublished;
			$intFeatured = ($intFeatured !== false) ? $intFeatured : 0;
			$intViews = ($intViews !== false) ? $intViews : 0;
			$intUserID = ($intUserID !== false) ? $intUserID : $this->user->id;
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'name'			=> $strName,
					'description'	=> $strDescription,
					'type'			=> $intType,
					'tags'			=> serialize($arrTags),
					'filename'		=> $strFilename,
					'localfile'		=> $strLocalfile,
					'externalfile'	=> $strExternalLink,
					'previewimage'	=> $strLocalPreviewImage,
					'published'		=> $intPublished,
					'additionaldata'=> serialize($arrAdditionalData),
					'date'			=> $this->time->time,
					'user_id'		=> $intUserID,
					'featured'		=> $intFeatured,
					'views'			=> $intViews,
			);
			
			$objQuery = $this->db->prepare("INSERT INTO __mediacenter_media :p")->set($arrQuery)->execute();
			
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			if ($objQuery) {
				$id = $objQuery->insertId;
				$log_action = $this->logs->diff(false, $arrQuery, $this->arrLogLang);
				$this->log_insert("action_media_added", $log_action, $id, $arrQuery["name"], 1, 'mediacenter');
				
				return $id;
			}
			
			return false;
		}
		
		
		
		public function update_media($intMediaID, $intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename,
			$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false, $intReported=false){
			$strLocalfile = $this->pdh->get('mediacenter_media', 'localfile', array($intMediaID));
			$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
			$strLocalPreviewImage = $this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
			$strLocalfilename = $this->pdh->get('mediacenter_media', 'filename', array($intMediaID));
			
			if ($intType == 0){
				//File
				
				//New File?
				if ($strFile != "" && $strFilename != ""){
					$strLocalfile = register('encrypt')->decrypt($strFile);
					$strLocalfilename = $strFilename;
				}
				
				//If it's a image, we have a preview image
				if ($strFile != "" && $strPreviewimage == ""){
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif'))) return false;
					$filename = md5(rand().unique_id());
					$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
					$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
					
					$strLocalPreviewImage = $filename.'.'.$strExtension;
				}
				
			}elseif($intType == 1){
				//Video
				if ($strExternalLink != "" && $strFile == ""){
					//External File
					$objEmbedly = register('embedly');
					$arrEmbedlyDetails = $objEmbedly->getLinkDetails($strExternalLink);
					if (!$arrEmbedlyDetails) return false;
					
					$arrAdditionalData = array(
						'thumbnail_url' => $arrEmbedlyDetails[0]->thumbnail_url,
						'provider_name' => $arrEmbedlyDetails[0]->provider_name,
						'html' 			=> $arrEmbedlyDetails[0]->html,
						'url' 			=> $arrEmbedlyDetails[0]->url,
						'title' 		=> $arrEmbedlyDetails[0]->title,
					);
					
					$strLocalPreviewImage = "";
					//Download Previewimage
					if (isset($arrEmbedlyDetails[0]->thumbnail_url)){
						$binImage = register('urlfetcher')->fetch($arrEmbedlyDetails[0]->thumbnail_url);
						$strExtension = strtolower(pathinfo($arrEmbedlyDetails[0]->thumbnail_url, PATHINFO_EXTENSION));
						$filename = md5(rand().unique_id());
						$this->pfh->putContent($strThumbfolder.$filename, $binImage);
						
						$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
						$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
						$strLocalPreviewImage = $filename.'.'.$strExtension;
					}
					
				} elseif ($strFile != ""){
					//Internal File
					$strLocalfile = register('encrypt')->decrypt($strFile);
					$strLocalfilename = $strFilename;
				}
				
				
			}else{
				//Image
				
				//New Image
				if ($strFile != "" && $strFilename != ""){
					$strLocalfile = register('encrypt')->decrypt($strFile);
					$strLocalfilename = $strFilename;
					
					//Preview Image
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif'))) return false;
					$filename = md5(rand().unique_id());
					$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
					$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
					$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
					
					$strLocalPreviewImage = $filename.'.'.$strExtension;
				}				
			}
			
			//Handle Previewimage
			if ($strLocalPreviewImage == "" && $strPreviewimage != ""){
				$strLocalPreviewImage = str_replace($this->pfh->FolderPath('previewimages', 'mediacenter', 'relative'), "", $strPreviewimage);
				$strLocalPreviewImage = str_replace($this->pfh->FolderPath('previewimages', 'mediacenter', 'plain'), "", $strPreviewimage);
				
				$filename = md5(rand().unique_id());
				$strExtension = strtolower(pathinfo($strLocalPreviewImage, PATHINFO_EXTENSION));
				
				$this->pfh->copy($this->pfh->FolderPath('previewimages', 'mediacenter').$strLocalPreviewImage, $strThumbfolder.$filename.'.'.$strExtension);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
				$this->pfh->Delete($this->pfh->FolderPath('previewimages', 'mediacenter').$strLocalPreviewImage);
				$strLocalPreviewImage = $filename.'.'.$strExtension;
			}		
			
			//Handle Tags
			$schluesselwoerter = preg_split("/[\s,]+/", $strTags);
			$arrTags = array();
			foreach($schluesselwoerter as $val){
				$tag = utf8_strtolower(str_replace("-", "", $val));
				if ($tag != "") $arrTags[] = $tag;
			}
			
			//Default Publish State
			$intCategoryID  = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			$blnDefaultPublishState = $this->pdh->get('mediacenter_categories', 'default_publish_state', array($intCategoryID));
			$intPublishedState = ($this->user->check_auth('a_mediacenter_manage', false)) ? 1 : $blnDefaultPublishState;
			
			//Admin Things
			$intPublished = ($intPublished !== false) ? $intPublishedState : $intDefaultPublished;
			$intFeatured = ($intFeatured !== false) ? $intFeatured : $this->pdh->get('mediacenter_media', 'featured', array($intMediaID));
			$intViews = ($intViews !== false) ? $intViews : $this->pdh->get('mediacenter_media', 'views', array($intMediaID));
			$intUserID = ($intUserID !== false) ? $intUserID : $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
			$intReported = ($intReported !== false) ? $intReported : $this->pdh->get('mediacenter_media', 'reported', array($intMediaID));
			
			$arrOldData = $this->pdh->get('mediacenter_media', 'data', array($intMediaID));
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'name'			=> $strName,
					'description'	=> $strDescription,
					'type'			=> $intType,
					'tags'			=> serialize($arrTags),
					'filename'		=> $strLocalfilename,
					'localfile'		=> $strLocalfile,
					'externalfile'	=> $strExternalLink,
					'previewimage'	=> $strLocalPreviewImage,
					'published'		=> $intPublished,
					'additionaldata'=> serialize($arrAdditionalData),
					'date'			=> $this->time->time,
					'user_id'		=> $intUserID,
					'featured'		=> $intFeatured,
					'views'			=> $intViews,
					'reported'		=> $intReported,
			);
			
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media :p WHERE id=?")->set($arrQuery)->execute($intMediaID);
			
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			if ($objQuery) {

				$log_action = $this->logs->diff($arrOldData, $arrQuery, $this->arrLogLang, array('description' => 1), true);
				$this->log_insert("action_media_updated", $log_action, $intMediaID, $arrOldData["name"], 1, 'mediacenter');
				
				return $objQuery->insertId;
			}
			
			return false;
		}
		
		
		public function add_massupdate($intAlbumID, $strFilename, $strFile){
			$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));		
			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
			
			if (!$intCategoryID || !$arrTypes || (count($arrTypes) == 0)) return false;
			
			//Try to detect the Type
			$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
			$intType = $oldType = 0; //Default: file
			if (in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif'))){
				$intType = $oldType = 2;// Image
			}elseif(in_array($strExtension, array('mp4', 'm4v', 'f4v', 'flv', 'webm'))){
				$intType = $oldType = 1; //Video
			}
			//If Type is not allowed, make type file
			if (!in_array($intType, $arrTypes)){
				$intType = 0; //File
			}

			//If type file now allowed: wrong type
			if (!in_array($intType, $arrTypes)){
				echo "not allowed"; return false;
			}
				
			$strLocalfile = $strFile;
			$arrAdditionalData = array();
			$strLocalPreviewImage = "";
			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
			
			if ($intType == 2 || $oldType == 2){
				//Preview Image
				$filename = md5(rand().unique_id());
				$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
				$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
				
				$strLocalPreviewImage = $filename.'.'.$strExtension;
			}
				
			//Default Publish State
			$intCategoryID  = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			$blnDefaultPublishState = $this->pdh->get('mediacenter_categories', 'default_publish_state', array($intCategoryID));
			$intPublished = ($this->user->check_auth('a_mediacenter_manage', false)) ? 1 : $blnDefaultPublishState;
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'name'			=> str_replace('.'.$strExtension, '', $strFilename),
					'description'	=> "",
					'type'			=> $intType,
					'tags'			=> serialize(array()),
					'filename'		=> $strFilename,
					'localfile'		=> $strLocalfile,
					'externalfile'	=> "",
					'previewimage'	=> $strLocalPreviewImage,
					'published'		=> $intPublished,
					'additionaldata'=> serialize($arrAdditionalData),
					'date'			=> $this->time->time,
					'user_id'		=> $this->user->id,
			);
			
			$objQuery = $this->db->prepare("INSERT INTO __mediacenter_media :p")->set($arrQuery)->execute();
				
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			if ($objQuery) {
				$id = $objQuery->insertId;
				$log_action = $this->logs->diff(false, $arrQuery, $this->arrLogLang);
				$this->log_insert("action_media_added", $log_action, $id, $arrQuery["name"], 1, 'mediacenter');
				return $id;
			}
				
			return false;
		}
		
		public function set_published($arrIDs){
			
			foreach($arrIDs as $id){
				$arrOld = array(
						'published'=> $this->pdh->get('mediacenter_media', 'published', array($id))
				);
				$arrNew = array(
						'published'	=> 1,
				);
				$log_action = $this->logs->diff($arrOld, $arrNew, $this->arrLang);
				if ($log_action) $this->log_insert('action_media_updated', $log_action, $id, $this->pdh->get('mediacenter_media', 'name', array($id)), 1, 'mediacenter');
			}
			
				
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media :p WHERE id :in")->set(array(
					'published'		=> 1,
			))->in($arrIDs)->execute($id);
				
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
		}
		
		public function set_unpublished($arrIDs){
			
			foreach($arrIDs as $id){
				$arrOld = array(
						'published'=> $this->pdh->get('mediacenter_media', 'published', array($id))
				);
				$arrNew = array(
						'published'	=> 0,
				);
				$log_action = $this->logs->diff($arrOld, $arrNew, $this->arrLang);
				if ($log_action) $this->log_insert('action_media_updated', $log_action, $id, $this->pdh->get('mediacenter_media', 'name', array($id)), 1, 'mediacenter');
			}
			
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media :p WHERE id :in")->set(array(
					'published'		=> 0,
			))->in($arrIDs)->execute($id);
				
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
		}
		
		public function change_album($arrIDs, $intAlbumID){
			
			$arrNew = array(
					'album_id'	=> $intAlbumID,
			);
			foreach($arrIDs as $id){
				$arrOld = array(
					'album_id' => $this->pdh->get('mediacenter_media', 'album_id', array($id))
				);
				$log_action = $this->logs->diff($arrOld, $arrNew, $this->arrLang);
				if ($log_action) $this->log_insert('action_media_updated', $log_action, $id, $this->pdh->get('mediacenter_media', 'name', array($id)), 1, 'mediacenter');
			}
			
				
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media WHERE id :in")->set(array(
				'album_id' => $intAlbumID,
			))->in($arrIDs)->execute();
				
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
		}
		
		public function update_featuredandpublished($id, $intFeatured, $intPublished){
			
			$arrOld = array(
					'featured' => $this->pdh->get('mediacenter_media', 'featured', array($id)),
					'published'=> $this->pdh->get('mediacenter_media', 'published', array($id))
			);

				
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media :p WHERE id=?")->set(array(
					'featured'		=> $intFeatured,
					'published'		=> $intPublished,
			))->execute($id);
				
			if ($objQuery){
				
				$arrNew = array(
						'featured'	=> $intFeatured,
						'published'	=> $intPublished,
				);
				$log_action = $this->logs->diff($arrOld, $arrNew, $this->arrLang);
				if ($log_action) $this->log_insert('action_mediacenter_updated', $log_action, $id, $this->pdh->get('mediacenter_media', 'name', array($id)), 1, 'article');
				
		
				$this->pdh->enqueue_hook('mediacenter_media_update');
				$this->pdh->enqueue_hook('mediacenter_categories_update');
				return $id;
			}
			return false;
		}
		
		public function delete($id) {
			$arrOldData = $this->pdh->get('mediacenter_media', 'data', array($id));	
			
			if ($arrOldData['localfile'] != ""){
				$this->pfh->Delete('files/'.$arrOldData['localfile'], 'mediacenter');
			}
			if ($arrOldData['previewimage'] != ""){
				$this->pfh->Delete('thumbs/'.$arrOldData['previewimage'], 'mediacenter');
				$this->pfh->Delete('thumbs/'.str_replace('.', '.64.', $arrOldData['previewimage']), 'mediacenter');
				$this->pfh->Delete('thumbs/'.str_replace('.', '.240.', $arrOldData['previewimage']), 'mediacenter');
			}
			
			$objQuery = $this->db->prepare("DELETE FROM __mediacenter_media WHERE id =?")->execute($id);
							
			$this->pdh->put("comment", "delete_attach_id", array("mediacenter", $id));
		
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			
			//Logging
			$arrChanges = $this->logs->diff(false, $arrOld, $this->arrLang);
			
			if ($arrChanges){
				$this->log_insert('action_media_deleted', $arrChanges, $id, $arrOldData["name"], 1, 'mediacenter');
			}
			
			return true;
		}
		
		public function reset_votes($intMediaID){
			$objQuery = $this->db->prepare("UPDATE __mediacenter_media :p WHERE id=?")->set(array(
					'votes_count' 		=> 0,
					'votes_sum'			=> 0,
					'votes_users'		=> '',
			))->execute($intMediaID);
				
			if ($objQuery) {
				$this->log_insert('action_mediacenter_reset_votes', array(), $intMediaID, $this->pdh->get('mediacenter_media', 'name', array($intMediaID)), 1, 'mediacenter');
		
				$this->pdh->enqueue_hook('mediacenter_media_update');
				return true;
			}
				
			return false;
		}

	}//end class
}//end if
?>