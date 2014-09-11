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
				'category_id'		=> "{L_MC_F_CATEGORY}",
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
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
			} else {
				$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			}
			
			//Check Type
			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));			
			if (!$intCategoryID || !$arrTypes || (count($arrTypes) == 0)) return false;
			
			//If Type is not allowed, make type file
			if (!in_array($intType, $arrTypes)){
				$intType = 0; //File
			}
			
			//If type file now allowed: wrong type
			if (!in_array($intType, $arrTypes)){
				return false;
			}
			
			
			if ($intType == 0){
				//File
				if ($strFile == "" && $strExternalLink == ""){
					//No Local and External File
					return false;
				} elseif($strFile != "") {
					$strLocalfile = register('encrypt')->decrypt($strFile);
					if(file_exists($strFileFolder.$strLocalfile)) $arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
					
					//Check Extension
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, $this->extensions_file())) return false;
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
					if(file_exists($strFileFolder.$strLocalfile)) $arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
					//Check Extension
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, $this->extensions_video())) return false;
				
				} else return false;
				
				
			}else{
				//Image
				if ($strFile == "" || $strFilename == "") return false;
				$strLocalfile = register('encrypt')->decrypt($strFile);
				
				$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
				//Check Extension
				if (!in_array($strExtension, $this->extensions_image())) return false;
				
				//Exif Data
				if ($strExtension == 'jpg'){
					$arrExif = $this->exif_data($strFileFolder.$strLocalfile);
					if ($arrExif) $arrAdditionalData = array_merge($arrAdditionalData, $arrExif);
				}
				
				//Preview Image
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
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
				$intAlbumID = 0;
			} else $intCategoryID = 0;
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'category_id'	=> $intCategoryID,
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
			$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false, $intReported=false, $intDownloads=false){
			$strLocalfile = $this->pdh->get('mediacenter_media', 'localfile', array($intMediaID));
			$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
			$strLocalPreviewImage = $this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
			$strLocalfilename = $this->pdh->get('mediacenter_media', 'filename', array($intMediaID));
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
			} else {
				$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			}
				
			//Check Type
			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
			if (!$intCategoryID || !$arrTypes || (count($arrTypes) == 0)) return false;
				
			//If Type is not allowed, make type file
			if (!in_array($intType, $arrTypes)){
				$intType = 0; //File
			}
				
			//If type file now allowed: wrong type
			if (!in_array($intType, $arrTypes)){
				return false;
			}
			
			if ($intType == 0){
				//File
				
				//New File?
				if ($strFile != "" && $strFilename != ""){
					$strLocalfile = register('encrypt')->decrypt($strFile);
					$strLocalfilename = $strFilename;
					if(file_exists($strFileFolder.$strLocalfile)) $arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
				
					//Check Extension
					$strExtension = strtolower(pathinfo($strLocalfilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, $this->extensions_file())) return false;
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
					if(file_exists($strFileFolder.$strLocalfile)) $arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
					//Check Extension
					$strExtension = strtolower(pathinfo($strLocalfilename, PATHINFO_EXTENSION));
					if (!in_array($strExtension, $this->extensions_video())) return false;
				}
				
				
			}else{
				//Image
				
				//New Image
				if ($strFile != "" && $strFilename != ""){
					$strLocalfile = register('encrypt')->decrypt($strFile);
					$strLocalfilename = $strFilename;
					if(!file_exists($strFileFolder.$strLocalfile)) return false;
					
					$arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
					
					$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
					//Check Extension
					if (!in_array($strExtension, $this->extensions_image())) return false;
					
					//Exif Data
					if ($strExtension == 'jpg'){
						$arrExif = $this->exif_data($strFileFolder.$strLocalfile);
						if ($arrExif) $arrAdditionalData = array_merge($arrAdditionalData, $arrExif);
					}
					
					//Preview Image
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
			$intDownloads = ($intDownloads !== false) ? $intDownloads : $this->pdh->get('mediacenter_media', 'downloads', array($intMediaID));
			
			$arrOldData = $this->pdh->get('mediacenter_media', 'data', array($intMediaID));
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
				$intAlbumID = 0;
			} else $intCategoryID = 0;
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'category_id'	=> $intCategoryID,
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
					'downloads'		=> $intDownloads,
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
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
			} else {
				$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			}

			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
			
			if (!$intCategoryID || !$arrTypes || (count($arrTypes) == 0)) return false;
			
			//Try to detect the Type
			$strExtension = strtolower(pathinfo($strFilename, PATHINFO_EXTENSION));
			$intType = $oldType = 0; //Default: file			
			
			if (in_array($strExtension, $this->extensions_image())){
				$intType = $oldType = 2;// Image
			}elseif(in_array($strExtension, $this->extensions_video())){
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
			
			//Check Extension
			switch($intType){
				case 0: if (!in_array($strExtension, $this->extensions_file())) return false;
				break;
				case 1: if (!in_array($strExtension, $this->extensions_video())) return false;
				break;
				case 2: if (!in_array($strExtension, $this->extensions_image())) return false;
				break;
				default: return false;
			}
			
			
			$arrAdditionalData = array();
			
			$strLocalfile = $strFile;
			$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
			if(file_exists($strFileFolder.$strLocalfile)) $arrAdditionalData['size'] = filesize($strFileFolder.$strLocalfile);
			$strLocalPreviewImage = "";
			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');

			if ($intType == 2 || $oldType == 2){
				//Preview Image
				$filename = md5(rand().unique_id());
				$this->pfh->copy($strFileFolder.$strLocalfile, $strThumbfolder.$filename.'.'.$strExtension);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.64.'.$strExtension, 64);
				$this->pfh->thumbnail($strThumbfolder.$filename.'.'.$strExtension, $strThumbfolder, $filename.'.240.'.$strExtension, 240);
				
				$strLocalPreviewImage = $filename.'.'.$strExtension;
			}
			
			if ($intType == 2){
				//Exif Data
				if ($strExtension == 'jpg'){
					$arrExif = $this->exif_data($strFileFolder.$strLocalfile);
					if($arrExif) $arrAdditionalData = array_merge($arrAdditionalData, $arrExif);
				}
			}

			//Default Publish State
			$intCategoryID  = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			$blnDefaultPublishState = $this->pdh->get('mediacenter_categories', 'default_publish_state', array($intCategoryID));
			$intPublished = ($this->user->check_auth('a_mediacenter_manage', false)) ? 1 : $blnDefaultPublishState;
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
				$intAlbumID = 0;
			} else $intCategoryID = 0;
			
			$arrQuery = array(
					'album_id'		=> $intAlbumID,
					'category_id'	=> $intCategoryID,
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
			
			if(substr($intAlbumID, 0, 1) == 'c'){
				$intCategoryID = (int)substr($intAlbumID, 1);
				$intAlbumID = 0;
			} else $intCategoryID = 0;
			
			$arrNew = array(
				'album_id'		=> $intAlbumID,
				'category_id'	=> $intCategoryID,
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
		
		private function exif_data($strFilename){
			$arrOut = array();
			if (function_exists('exif_read_data')) {
				$arrExifData = exif_read_data($strFilename, 0, true);
				if (!$arrExifData) return false;
				
				//Camera
				if (isset($arrExifData['IFD0'])) {
					if (!empty($arrExifData['IFD0']['Make'])) {
						$strMake = $arrExifData['IFD0']['Make'];
					}
					
					if (!empty($arrExifData['IFD0']['Model'])) {
						$strModel = $arrExifData['IFD0']['Model'];
					}
					
					$arrOut['Camera'] = $strMake.((strlen($strMake)) ? ' ': '').$strModel;
				}
				

				if (isset($arrExifData['EXIF'])) {
					//CreationTime
					if (isset($arrExifData['EXIF']['DateTimeOriginal'])) {
						$creationTime = @intval(strtotime($arrExifData['EXIF']['DateTimeOriginal']));
					} else if (isset($arrExifData['EXIF']['DateTimeDigitized'])) {
						$creationTime = @intval(strtotime($arrExifData['EXIF']['DateTimeDigitized']));
					} else if (!empty($arrExifData['EXIF']['DateTime'])) {
						$creationTime = @intval(strtotime($arrExifData['EXIF']['DateTime']));
					} else {
						$creationTime = 0;
					}
					if ($creationTime < 0 || $creationTime > 2147483647) $creationTime = 0;
					$arrOut['CreationTime'] = $creationTime;
					
					//Camera Settings
					if (isset($arrExifData['EXIF']['ExposureTime'])) {
						$arrOut['ExposureTime'] = $arrExifData['EXIF']['ExposureTime'];
					}
					if (isset($arrExifData['EXIF']['FNumber'])) {
						$arrOut['FNumber'] = $this->exif_get_float($arrExifData['EXIF']['FNumber']);
					}
					if (isset($arrExifData['EXIF']['FocalLength'])) {
						$arrOut['FocalLength'] = $this->exif_get_float($arrExifData['EXIF']['FocalLength']);
					}
					if (isset($arrExifData['EXIF']['ISOSpeedRatings'])) {
						$arrOut['ISOSpeedRatings'] = intval($arrExifData['EXIF']['ISOSpeedRatings']);
					}		
					if(isset($arrExifData['EXIF']['ShutterSpeedValue'])){
						$arrOut['ShutterSpeedValue'] = $this->exif_get_shutter($arrExifData['EXIF']['ShutterSpeedValue']);
					}
					if (isset($arrExifData['EXIF']['ApertureValue'])){
						$arrOut['ApertureValue'] = $this->exif_get_fstop($arrExifData['EXIF']['ApertureValue']);
					}elseif(isset($arrExifData['COMPUTED']['ApertureFNumber'])){
						$arrOut['ApertureValue'] = $arrExifData['COMPUTED']['ApertureFNumber'];
					}
				}
				
				//Coordinates
				if (isset($arrExifData['GPS']) && isset($arrExifData['GPS']['GPSLongitudeRef']) && isset($arrExifData['GPS']['GPSLongitude'])) {
					$longitude = 0;
					$degrees = (isset($arrExifData['GPS']['GPSLongitude'][0]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLongitude'][0]) : 0.0);
					$minutes = (isset($arrExifData['GPS']['GPSLongitude'][1]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLongitude'][1]) : 0.0);
					$seconds = (isset($arrExifData['GPS']['GPSLongitude'][2]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLongitude'][2]) : 0.0);
					$longitude = ($degrees * 60.0 + (($minutes * 60.0 + $seconds) / 60.0)) / 60.0;
					if ($arrExifData['GPS']['GPSLongitudeRef'] == 'W') $longitude *= -1;
					$arrOut['Longitude'] = $longitude;
				}
				
				if (isset($arrExifData['GPS']) && isset($arrExifData['GPS']['GPSLatitudeRef']) && isset($arrExifData['GPS']['GPSLatitude'])) {
					$latitude = 0;
					$degrees = isset($arrExifData['GPS']['GPSLatitude'][0]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLatitude'][0]) : 0.0;
					$minutes = isset($arrExifData['GPS']['GPSLatitude'][1]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLatitude'][1]) : 0.0;
					$seconds = isset($arrExifData['GPS']['GPSLatitude'][2]) ? $this->coordinate_to_decimal($arrExifData['GPS']['GPSLatitude'][2]) : 0.0;
					$latitude = ($degrees * 60.0 + (($minutes * 60.0 + $seconds) / 60.0)) / 60.0;
					if ($arrExifData['GPS']['GPSLatitudeRef'] == 'S') $latitude *= -1;
					$arrOut['Latitude'] = $latitude;
				}

				return $arrOut;
			}
			return array();
		}
		
		//----------------------------------------
		// Helper Functions
		
		private function exif_get_float($value) {
		  $pos = strpos($value, '/');
		  if ($pos === false) return (float) $value;
		  $a = (float) substr($value, 0, $pos);
		  $b = (float) substr($value, $pos+1);
		  return ($b == 0) ? ($a) : ($a / $b);
		}
		
		private function exif_get_shutter($shutterspeed) {

		  $apex    = $this->exif_get_float($shutterspeed);
		  $shutter = pow(2, -$apex);
		  if ($shutter == 0) return false;
		  if ($shutter >= 1) return round($shutter) . 's';
		  return '1/' . round(1 / $shutter) . 's';
		}
		
		private function exif_get_fstop($aperturevalue) {
		  $apex  = $this->exif_get_float($aperturevalue);
		  $fstop = pow(2, $apex/2);
		  if ($fstop == 0) return false;
		  return 'f/' . round($fstop,1);
		}
		
		private function coordinate_to_decimal($coordinate) {
			$result = 0.0;
			$coordinateData = explode('/', $coordinate);
			for ($i = 0, $j = count($coordinateData); $i < $j; $i++) {
				if ($i == 0) $result = (float) $coordinateData[0];
				else if ($coordinateData[$i]) $result /= (float) $coordinateData[$i];
			}
		
			return $result;
		}
		
		private function extensions_file(){
			$arrExtensionsFilePlain = preg_split("/[\s,]+/", $this->config->get('extensions_file', 'mediacenter'));
			$arrExtensionsFile = array();
			foreach($arrExtensionsFilePlain as $val){
				$arrExtensionsFile[] = utf8_strtolower(str_replace(".", "", $val));
			}
			return $arrExtensionsFile;
		}
		
		private function extensions_image(){
			$arrExtensionsFilePlain = preg_split("/[\s,]+/", $this->config->get('extensions_image', 'mediacenter'));
			$arrExtensionsFile = array();
			foreach($arrExtensionsFilePlain as $val){
				$arrExtensionsFile[] = utf8_strtolower(str_replace(".", "", $val));
			}
			return $arrExtensionsFile;
		}
		
		private function extensions_video(){
			$arrExtensionsFilePlain = preg_split("/[\s,]+/", $this->config->get('extensions_video', 'mediacenter'));
			$arrExtensionsFile = array();
			foreach($arrExtensionsFilePlain as $val){
				$arrExtensionsFile[] = utf8_strtolower(str_replace(".", "", $val));
			}
			return $arrExtensionsFile;
		}

	}//end class
}//end if
?>