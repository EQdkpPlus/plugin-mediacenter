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

class editmedia_pageobject extends pageobject {
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array();
   	return array_merge(parent::__shortcuts(), $shortcuts);
  }  

  private $blnAdminMode = false;
  
  /**
   * Constructor
   */
  public function __construct()
  {
    // plugin installed?
    if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
      message_die($this->user->lang('mc_plugin_not_installed'));
    
    //Check Permissions
    if (!$this->user->check_auth('u_mediacenter_view', false) || !$this->user->is_signedin()){
    	$this->user->check_auth('u_mediacenter_something');
    }
    
    $this->blnAdminMode = $this->user->check_auth('a_mediacenter_manage', false) ? 1 : 0;
    
    $handler = array(
      'save' => array('process' => 'save', 'csrf' => true),
      'save_edit_image'=> array('process' => 'save_edit_image', 'csrf' => true),
      'reload_albums' => array('process' => 'ajax_reload_albums'),
      'media_types' => array('process' => 'ajax_media_types'),
      'upload' => array('process' => 'upload_file'),
      'chunkupload' => array('process' => 'upload_chunked_file'),
      'massupload' => array('process' => 'upload_massupload'),
      'imageedit' => array(
				array('process' => 'ajax_imageedit_rotate', 'value' => 'rotate'),
				array('process' => 'ajax_imageedit_resize', 'value' => 'resize'),
				array('process' => 'ajax_imageedit_restore', 'value' => 'restore'),
      			array('process' => 'ajax_imageedit_mirror', 'value' => 'mirror'),
	  ),
      'del_votes' => array('process' => 'delete_votes', 'csrf' => true),
      'del_comments' => array('process' => 'delete_comments', 'csrf' => true),
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  private $arrData = array();
  
  public function save_edit_image(){
  	$intMediaID = $this->url_id;
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  		$this->user->check_auth('u_mediacenter_something');
  	}
  	 
  	//Move File
  	$previewimage = $this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	$strExtension = pathinfo($previewimage, PATHINFO_EXTENSION);
  	$strFilename = pathinfo($previewimage, PATHINFO_FILENAME);
  	
  	$src = $this->pfh->FolderPath('thumbs', 'mediacenter').$strFilename.'_edited.'.$strExtension;
  	$dest = $this->pfh->FolderPath('thumbs', 'mediacenter').$this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	$this->pfh->copy($src, $dest);
  	
  	//Create new Thumbnails
  	$this->pfh->thumbnail($dest, $this->pfh->FolderPath('thumbs', 'mediacenter'), $strFilename.'.64.'.$strExtension, 64);
  	$this->pfh->thumbnail($dest, $this->pfh->FolderPath('thumbs', 'mediacenter'), $strFilename.'.240.'.$strExtension, 240);

  }
  
  private function create_edit_image($intMediaID, $blnForce=false){
  	$previewimage = $this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	
  	$strExtension = pathinfo($previewimage, PATHINFO_EXTENSION);
  	$strFilename = pathinfo($previewimage, PATHINFO_FILENAME);
  	
  	$dest = $this->pfh->FolderPath('thumbs', 'mediacenter').$strFilename.'_edited.'.$strExtension;
  	$src = $this->pfh->FolderPath('thumbs', 'mediacenter').$previewimage;
  	
  	if ($blnForce || !file_exists($dest)){
  		$this->pfh->copy($src, $dest);
   	}
  	
  	return $dest;
  }

  
  public function delete_comments(){
  	$intMediaID = $this->url_id;
  	if (!$this->user->check_auth('a_mediacenter_manage', false)) return false;
  	
  	if ($intMediaID) {
  		$this->pdh->put('comment', 'delete_attach_id', array('mediacenter', $intMediaID));
  		$this->pdh->process_hook_queue();
  		$this->logs->add('action_mediacenter_reset_comments', array(), $intMediaID, $this->pdh->get('mediacenter_media', 'name', array($intMediaID)), 1, 'mediacenter');
  		$this->core->message($this->user->lang('mc_f_delete_comments'), $this->user->lang('success'), 'green');
  	}

  }
  
  public function delete_votes(){
  	$intMediaID = $this->url_id;
  	if (!$this->user->check_auth('a_mediacenter_manage', false)) return false;
  	
  	
  	if ($intMediaID) {
  		$blnResult = $this->pdh->put('mediacenter_media', 'reset_votes', array($intMediaID));
  		if ($blnResult){
  			$this->core->message($this->user->lang('mc_f_delete_votes'), $this->user->lang('success'), 'green');
  			$this->pdh->process_hook_queue();
  		}
  	}

  }
 
  public function ajax_imageedit_rotate(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  		echo "error";
  		return false;
  	}
  	
  	$dir = $this->in->get('dir', 'r');
  	 
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->create_edit_image($intMediaID);
  	 
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	 
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	$rotang = ($dir == 'r') ? 270 : 90;
  	$rotation = imagerotate($imgOld, $rotang, imageColorAllocateAlpha($imgOld, 0, 0, 0, 127));
  	imagealphablending($rotation, false);
  	imagesavealpha($rotation, true);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($rotation,	$image);	break;	// GIF
  		case 2:	ImageJPEG($rotation,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($rotation,	$image, 0);	break;	// PNG
  	}
  	
  	imagedestroy($rotation);
  	imagedestroy($imgOld);	
  	
  	echo $image;
  	exit;
  }
  
  public function ajax_imageedit_restore(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  		echo "error";
  		return false;
  	}
  	
  	$this->create_edit_image($intMediaID, true);
  	
  	echo "true";
  	exit;
  }
  
  public function ajax_imageedit_resize(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  		echo "error";
  		return false;
  	}
  	
  	$x = $this->in->get('x', 0);
  	$y = $this->in->get('y', 0);
  	$w = $this->in->get('w', 0);
  	$h = $this->in->get('h', 0);
  	
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->create_edit_image($intMediaID);
  	
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	
  	$dst = ImageCreateTrueColor( $w, $h );
  	
  	imagecopyresampled($dst,$imgOld,0,0,$x,$y,
  	$w,$h,$w,$h);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($dst,	$image);	break;	// GIF
  		case 2:	ImageJPEG($dst,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($dst,	$image, 0);	break;	// PNG
  	}
  	imagedestroy($imgOld);
  	imagedestroy($dst);

  	echo $image;
  	exit;
  }
  
  public function ajax_imageedit_mirror(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  		echo "error";
  		return false;
  	}
  	
  	$dir = $this->in->get('dir', 'h');
  	
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->create_edit_image($intMediaID);
  	
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	
  	// Flip it
  	imageflip($imgOld, ($dir == 'h') ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($imgOld,	$image);	break;	// GIF
  		case 2:	ImageJPEG($imgOld,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($imgOld,	$image, 0);	break;	// PNG
  	}
  	imagedestroy($imgOld);
  	
  	echo $image;
  	exit;
  }
  
  public function ajax_reload_albums(){
  	header('content-type: text/html; charset=UTF-8');
  	
  	echo '<select onchange="load_mediatypes();" class="input" id="album_id" name="album_id" size="1">'.$this->pdh->geth('mediacenter_albums', 'album_tree', array()).'</select>',

  	exit;
  }
  
  public function ajax_media_types(){
  	header('content-type: text/html; charset=UTF-8');
  	$strAlbumID = $this->in->get('album');
  	$intMediaID = $this->in->get('media', 0);
  	$option = ($intMediaID) ? $this->pdh->get('mediacenter_media', 'type', array($intMediaID)) : 0;
  	
  	if(substr($strAlbumID, 0, 1) == 'c'){
  		$intCategoryID = (int)substr($strAlbumID, 1);
  		$intAlbumID = 0;
  	} else {
  		$intAlbumID = intval($strAlbumID);
  		$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
  	}
  	$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
  	$myArray = $this->user->lang('mc_types');

  	if (count($arrTypes) == 1){
  		$tmp = array();
  		$tmp[$arrTypes[0]] = $myArray[$arrTypes[0]];
  		$myArray = $tmp;
  	} elseif(count($arrTypes) > 1) {
		$tmp = array();
		foreach($arrTypes as $typeid){
			$tmp[$typeid] = $myArray[$typeid];
		}
		$myArray = $tmp;
  	}
  	echo new hdropdown('type', array('js' => 'onchange="handle_type(this.value)"', 'options' => $myArray, 'value' => $option));
  	exit;
  }
  
  public function upload_chunked_file(){
  	$strChunkDir = md5($this->user->csrfGetToken('mediacenter_chunkupload'));
  	
  	$chunkUploader = register('chunkedUploadHelper', array($strChunkDir, $this->extensions_video()));
  	$arrFields = $chunkUploader->getForm();
  	
  	$filename = $arrFields['filename'];
  	 
  	$arrAllowedExtensions = array_merge($this->extensions_file(), $this->extensions_image(), $this->extensions_video());
  	 
  	$fileEnding		= strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  	if (!in_array($fileEnding, $arrAllowedExtensions)) {
  		echo "error";
  		exit();
  	}
  	
  	$strResult = $chunkUploader->uploadChunk();
  	if(!$strResult){
  		header("HTTP/1.1 500 Internal Error");
  		exit;
  	}
  	exit;
  }
  
  public function upload_massupload(){  	
  	$strAlbumID = $this->in->get('album_id', '');

  	if(substr($strAlbumID, 0, 1) == 'c'){
  		$intCategoryID = (int)substr($strAlbumID, 1);
  		$intAlbumID = 0;
  	} else {
  		$intAlbumID = intval($strAlbumID);
  		$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
  	}
  	
  	if (!$intCategoryID) {
  		echo "error catid";
  		exit();
  	}
  	
  	//Check Permissions
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if (!$arrPermissions || !$arrPermissions['create']){
  		echo "error perm";
  		exit();
  	}
  	
  	//Check if Personal Album
  	if ($intAlbumID && $this->pdh->get('mediacenter_albums', 'personal_album', array($intAlbumID))){
  		if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array($intAlbumID))) {
  			echo "error personalalbum";
  			exit();
  		};
  	}
  	  	 
  	$folder = $this->pfh->FolderPath('files', 'mediacenter');

  	if(!is_file($folder.'index.html')){
  		$this->pfh->putContent($folder.'index.html', "");
  	}

  	$tempname		= $_FILES['file']['tmp_name'];
  	$filename		= $_FILES['file']['name'];
  	$filetype		= $_FILES['file']['type'];
  	if ($tempname == '') {
  		echo "error tempname";
  		exit();
  	}
  	
  	$strExtension = pathinfo($filename, PATHINFO_EXTENSION);
  	$new_filename = md5(rand().rand().rand().unique_id());
  	
  	if(in_array(utf8_strtolower($strExtension), $this->extensions_video())){
  		$new_filename .= '.'.utf8_strtolower($strExtension);
  	}
  	 
  	$this->pfh->FileMove($tempname, $folder.$new_filename, true);
  	
  	$result = $this->pdh->put('mediacenter_media', 'add_massupdate', array($strAlbumID, $filename, $new_filename));
  	if (!$result) {
  		echo "error result"; exit();
  	}
  	
  	$this->pdh->process_hook_queue();
  	die('ok');
  	exit;
  }
  
  public function upload_file(){  	
  	$folder = $this->pfh->FolderPath('files', 'mediacenter');

  	if(!is_file($folder.'index.html')){
  		$this->pfh->putContent($folder.'index.html', "");
  	}
  	
  	$tempname		= $_FILES['file']['tmp_name'];
  	$filename		= $_FILES['file']['name'];
  	$filetype		= $_FILES['file']['type'];
  	if ($tempname == '') {
  		echo "error";
  		exit();
  	}
  	
  	$arrAllowedExtensions = array_merge($this->extensions_file(), $this->extensions_image(), $this->extensions_video());
  	
  	$fileEnding		= strtolower(pathinfo($filename, PATHINFO_EXTENSION));		
  	if (!in_array($fileEnding, $arrAllowedExtensions)) {
  		echo "error";
  		exit();
  	}
  	
  	$new_filename = md5(rand().rand().rand().unique_id());
  	
  	if(in_array($fileEnding, $this->extensions_video())){
  		$new_filename .= '.'.$fileEnding;
  	}
  	
  	$this->pfh->FileMove($tempname, $folder.$new_filename, true);
  	
  	header('content-type: text/html; charset=UTF-8');
  	echo register('encrypt')->encrypt($new_filename);
  	  	
  	exit;
  }
  
  public function save(){
  	$objForm = register('form', array('editalbum'));
  	$objForm->langPrefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->add_fields($this->fields());
  	$arrValues = $objForm->return_values();
  	$mixResult = false;
	
  	
  	$arrValues['album_id'] = $this->in->get('album_id');
  	
  	//Check if Personal Album
  	if (is_numeric($arrValues['album_id']) && $this->pdh->get('mediacenter_albums', 'personal_album', array())){
  		if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array((int)$arrValues['album_id']))) {
  			$this->user->check_auth('u_mediacenter_something');
  		};
  	}
  	
  	if ($objForm->error){
  		$this->arrData = $arrValues;
  		$this->display();
  	} else {
  		if ($this->url_id) {
  			//Check Permissions
  			if(substr($arrValues['album_id'], 0, 1) == 'c'){
  				$intCategoryID = (int)substr($arrValues['album_id'], 1);
  			} else {
  				$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($arrValues['album_id']));
  			}
  			
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  			if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  				$this->user->check_auth('u_mediacenter_something');
  			}
			
			//If there is only one type, there is no incoming input for type
			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));		
			if (count($arrTypes) == 1) $arrValues['type'] = intval($arrTypes[0]);
  			
  			if ($this->blnAdminMode) {
  				//$intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename
  				//$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false, $intReported=false
	  			$mixResult = $this->pdh->put('mediacenter_media', 'update_media', array(
	  					$this->url_id, $arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename'),
	  					(int)$arrValues['published'], (int)$arrValues['featured'], (int)$arrValues['user_id'], (int)$arrValues['views'],(int)$arrValues['reported'], (int)$arrValues['downloads']
	  			));
  			}else {
  				$mixResult = $this->pdh->put('mediacenter_media', 'update_media', array(
  						$this->url_id, $arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename')
  				));
  			}
  			
  		} else {
  			//Check Permissions
  			if(substr($arrValues['album_id'], 0, 1) == 'c'){
  				$intCategoryID = (int)substr($arrValues['album_id'], 1);
  			} else {
  				$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($arrValues['album_id']));
  			}
  			
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  			if ((!$arrPermissions || !$arrPermissions['create']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  				$this->user->check_auth('u_mediacenter_something');
  			}
			
			//If there is only one type, there is no incoming input for type
			$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));	
			if (count($arrTypes) == 1) $arrValues['type'] = intval($arrTypes[0]);
			
  			if ($this->blnAdminMode){
	  			//$intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename
  				//$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false
	  			$mixResult = $this->pdh->put('mediacenter_media', 'insert_media', array(
	  				$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename'),
	  				(int)$arrValues['published'], (int)$arrValues['featured'], (int)$arrValues['user_id'], (int)$arrValues['views']
	  			));
  			
  			} else {  				
  				$mixResult = $this->pdh->put('mediacenter_media', 'insert_media', array(
  						$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename')
  				));
  			}
  		}
  		$this->pdh->process_hook_queue();
  	}

  	if ($mixResult !== false && is_numeric($mixResult)){
  		$this->core->message($this->user->lang('save_suc'), $this->user->lang('success'), 'green');
  		if ($this->in->get('simple_head')) $this->tpl->add_js('$.FrameDialog.closeDialog();', 'docready');
  	} else {
  		$strErrorMessage = (strpos($mixResult, 'error:') === 0) ? $this->user->lang('mc_editmedia_save_error_'.substr($mixResult, 6)) : $this->user->lang('save_nosuc');
  		$this->core->message($strErrorMessage, $this->user->lang('error'), 'red');
  		$this->arrData = $arrValues;
  	}

  }
  
  public function delete(){
  	$intMediaID = $this->url_id;
  	 
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['delete']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		return false;
  	}
  	
  	$blnResult = $this->pdh->put('mediacenter_media', 'delete', array($intMediaID));
  	if ($blnResult){
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('del_suc'), $this->user->lang('success'), 'green');
  	}
  }
  
  
  public function display(){
	$objForm = register('form', array('editalbum'));
	$objForm->langPrefix = 'mc_';
	$objForm->validate = true;
	$objForm->add_fields($this->fields());
	
	
	$arrValues = array();
  	if ($this->url_id) {
  		//Check Permission
  		$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  		$intUserID = $this->pdh->get('mediacenter_media', 'user_id', array($this->url_id));
  		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  		if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false) && ($this->user->id !== $intUserID)){
  			$this->user->check_auth('u_mediacenter_something');
  		}
  		
  		$arrValues = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  		$arrValues['tags'] = implode(", ", unserialize($arrValues['tags']));
  		$arrValues['previewimage'] = (strlen($arrValues['previewimage'])) ? $this->pfh->FolderPath('thumbs', 'mediacenter', 'absolute').$arrValues['previewimage'] : false;
  		
  		if ($arrValues['type'] == 2){
  			$editfile = str_replace($this->root_path, $this->server_path, $this->create_edit_image($this->url_id, true));
  		} else $editfile = "";
  		
  		$this->tpl->assign_vars(array(
  			'S_EDIT'		=> true,
  			'LOCALFILE'		=> $arrValues['filename'],
  			'S_TYPE_IMAGE'	=> ($arrValues['type'] == 2 && ($arrPermissions['update'] || $this->user->check_auth('a_mediacenter_manage', false))) ? true : false,
  			'LOCAL_IMAGE'	=> $arrValues['previewimage'],
  			'IMAGE_ID'		=> $this->url_id,
  			'EDIT_FILE'		=> $editfile,
  		));
  		
  	} else {
  		$arrValues['user_id'] = $this->user->id;
  	}

  	$this->jquery->Tab_header('editmedia_tab');
  	
  	$blnCheckPerms = ($this->blnAdminMode) ? false : true;
  	
  	$this->tpl->assign_vars(array(
  		'DD_ALBUMS' => $this->pdh->geth('mediacenter_albums', 'album_tree', array($this->in->get('aid', 0), $blnCheckPerms)),
  		'ADMINMODE'	=> $this->blnAdminMode,
  		'MAX_UPLOADSIZE' => human_filesize($this->detectMaxUploadFileSize()),
  	));
  	
	//Output, with Values
	if (count($this->arrData)) $arrValues = $this->arrData;
	
	//Set Album ID
	if ($this->in->get('aid')) $arrValues['album_id'] = $this->in->get('aid');
	
	$objForm->output($arrValues);
	
	$this->jquery->Dialog('addalbum', $this->user->lang('mc_new_album'), array('url'=> $this->controller_path.'AddAlbum/'.$this->SID.'&simple_head=1', 'width'=>'640', 'height'=>'520', 'onclosejs'=>'reload_albums();'));
	
    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array (
      'page_title'    => $this->user->lang('mc_edit_media'),
      'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
      'template_file' => 'media_edit.html',
      'header_format' => 'simple',
      'display'       => true
    ));	
  }
  
  
  //Get Fields for Form
  private function fields(){  	
  	$arrMediaTypes = array();
  	
  	if ($this->in->get('aid')){
  		$strSelected = $this->in->get('aid');
  	}elseif($this->url_id){
  		$intAlbum = $this->pdh->get('mediacenter_media', 'album_id', array($this->url_id));
  		$strSelected = $intAlbum;
  		if (!$intAlbum) {
  			$intCategory = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  			$strSelected = 'c'.$intCategory;
  		}
  	}

  	$blnCheckPerms = ($this->blnAdminMode) ? false : true;
  	
  	$arrFields = array(
  		'album_id' => array(
  			'text' => '<select onchange="load_mediatypes();" class="input" id="album_id" name="album_id" size="1">'.$this->pdh->geth('mediacenter_albums', 'album_tree', array($strSelected, $blnCheckPerms)).'</select> <button onclick="addalbum()" type="button"><i class="fa fa-plus"></i> '.$this->user->lang('mc_new_album').'</button>',	
  			'lang' => 'mc_f_album',
  		),
  		'name' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'required' => true,
  			'lang' => 'mc_f_name',
  		),
  		'type' => array(
  			'type' => 'dropdown',
  			'options' => $this->user->lang('mc_types'),
  			'lang' => 'mc_f_type',
  			'js'	=> 'onchange="handle_type(this.value)"'
  		),
  		'description' => array(
  			'type' => 'bbcodeeditor',
  			'lang' => 'mc_f_description',
  		),
  		'externalfile' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'lang' => 'mc_f_externalfile',
  		),
  		'previewimage' => array(
  			'type'	=> 'file',
  			'lang'	=> 'mc_f_previewimage',
  			'preview' => true,
  			'extensions'	=> array('jpg', 'png'),
			'mimetypes'		=> array(
					'image/jpeg',
					'image/png',
			),
			'folder'		=> $this->pfh->FolderPath('previewimages', 'mediacenter'),
			'numerate'		=> true,
  		),
  		'tags' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'lang' => 'mc_f_tags',
  		),
  	);
  	
  	if ($this->blnAdminMode){
  		$arrUser = $this->pdh->aget('user', 'name', 0, array($this->pdh->get('user', 'id_list')));
  		natcasesort($arrUser);
  		$arrFields['user_id'] = array(
  				'type'		=> 'dropdown',
  				'options'	=> $arrUser,
  				'lang'		=> 'user',
  		);
  		$arrFields['published'] = array(
  				'type'		=> 'radio',
  				'lang'		=> 'mc_f_published',
  				'default'	=> 1,
  		);
  		$arrFields['featured'] = array(
  				'type'		=> 'radio',
  				'lang'		=> 'mc_f_featured',
  		);
  		if ($this->url_id){
  			$arrFields['views'] = array(
  					'type'		=> 'int',
  					'lang'		=> 'mc_f_views',
  			);
  			$arrFields['downloads'] = array(
  					'type'		=> 'int',
  					'lang'		=> 'mc_f_downloads',
  			);
  			
	  		$arrFields['reported'] = array(
	  				'type'		=> 'radio',
	  				'lang'		=> 'mc_f_reported',
	  		);
	  		$arrFields['del_comments'] = array(
	  				'type'		=> 'button',
	  				'lang'		=> 'mc_f_delete_comments',
	  				'buttontype' => 'submit',
	  				'buttonvalue' => '<i class="fa fa-trash"></i> '.$this->user->lang('mc_f_delete_comments'),
	  		);
	  		$arrFields['del_votes'] = array(
	  				'type'		=> 'button',
	  				'lang'		=> 'mc_f_delete_votes',
	  				'buttontype' => 'submit',
	  				'buttonvalue' => '<i class="fa fa-trash"></i> '.$this->user->lang('mc_f_delete_votes'),	
	  		);
  		}
  	}
  	  	
  	return $arrFields;
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
  

  private function detectMaxUploadFileSize(){
		/**
		* Converts shorthands like “2M” or “512K” to bytes
		*
		* @param $size
		* @return mixed
		*/
		$normalize = function($size) {
		if (preg_match('/^([\d\.]+)([KMG])$/i', $size, $match)) {
			$pos = array_search($match[2], array("K", "M", "G"));
			if ($pos !== false) {
				$size = $match[1] * pow(1024, $pos + 1);
			}
		}
		return $size;
		};
		$max_upload = $normalize(ini_get('upload_max_filesize'));
		
		$max_post = (ini_get('post_max_size') == 0) ? 0 : $normalize(ini_get('post_max_size'));
		
		$memory_limit = (ini_get('memory_limit') == -1) ?
		$max_post : $normalize(ini_get('memory_limit'));
		
		if($memory_limit < $max_post || $memory_limit < $max_upload)
		return $memory_limit;
		
		if($max_post < $max_upload)
		return $max_post;
		
		$maxFileSize = min($max_upload, $max_post, $memory_limit);
		return $maxFileSize;
	}

}

class chunkedUploadHelper extends gen_class {
	
	private $chunkDir = false;
	private $globalChunkDir = false;
	private $arrVideoExtensions = array();
	
	public function __construct($strChunkDir, $arrVideoExtensions) {
		$this->globalChunkDir = $this->pfh->FolderPath('tmp', 'mediacenter');
		$this->chunkDir = $this->globalChunkDir.$strChunkDir;
		$this->arrVideoExtensions = $arrVideoExtensions;
		$this->pfh->FolderPath($this->chunkDir);
	}
	
	/**
	 * Main Method that handles the whole Chunk Upload
	 */
	public function uploadChunk(){
		$arrFields = $this->getForm();
		
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($this->checkChunkExists($arrFields['identifier'], $arrFields['currentChunkNumber'])) {
				header("HTTP/1.1 200 Ok");
			} else {
				// The 204 response MUST NOT include a message-body, and thus is always terminated by the first empty line after the header fields.
				header("HTTP/1.1 204 No Content");
				exit;
			}
		} else {
			if ($this->validateChunk()) {
				$destPath = $this->getChunkPath($arrFields['identifier'], $arrFields['currentChunkNumber']);
				$this->pfh->FileMove($arrFields['file']['tmp_name'], $destPath, true);
				header("HTTP/1.1 200 Ok");
			} else {
				// error, invalid chunk upload request, retry
				header("HTTP/1.1 400 Bad Request");
				exit;
			}
		}
		$new_filename = md5(rand().rand().rand().unique_id());
		$fileEnding = utf8_strtolower(pathinfo($arrFields['filename'], PATHINFO_EXTENSION));

	  	if(in_array($fileEnding, $this->arrVideoExtensions)){
	  		$new_filename .= '.'.$fileEnding;
	  	}
		
		$folder = $this->pfh->FolderPath('files', 'mediacenter');
		$strDestination = $folder.$new_filename;
		
		if ($this->validateUploadedFile() && $this->combineChunks($strDestination)) {
			header('content-type: text/html; charset=UTF-8');
			echo register('encrypt')->encrypt($new_filename);
			$this->pruneChunkFolders();
			return true;
		}

		return true;
	}
	
	/**
	 * Checks if Chunk for File already exists. True if exists, false if not exists.
	 * 
	 * @param string $strChunkIdentifier
	 * @param string $strChunkNumber
	 * @return boolean
	 */
	private function checkChunkExists($strChunkIdentifier, $strChunkNumber){
		if(file_exists($this->getChunkPath($strChunkIdentifier, $strChunkNumber))){
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the Path for a specific chunk
	 * 
	 * @param string $strChunkIdentifier
	 * @param string $strChunkNumber
	 * @return string
	 */
	private function getChunkPath($strChunkIdentifier, $strChunkNumber){
		$strPath = $this->chunkDir.DIRECTORY_SEPARATOR.md5($strChunkIdentifier).DIRECTORY_SEPARATOR.'chunk_'.$strChunkNumber;
		$this->pfh->FolderPath($this->chunkDir.DIRECTORY_SEPARATOR.md5($strChunkIdentifier));
		return $strPath;
	}
	
	
	
	/**
	 * Prune Chunk Folders after 1 hour
	 */
	private function pruneChunkFolders(){
		$expirationTime = 3600; //1 hour
		
		$arrFiles = scandir($this->chunkDir);
		if($arrFiles){
			foreach($arrFiles as $file){
				
				$filepath = $this->chunkDir.DIRECTORY_SEPARATOR.$file;
				
				if($file == "." || $file == "..") continue;
				
				if (is_dir($filepath)) {
					if (time() - filemtime($filepath.DIRECTORY_SEPARATOR.'.') > $expirationTime) {
						unlink($path);
					}	
				}
			}
		}
	}
	
	
	/**
	 * Combines all Chunks to one file
	 * 
	 * @param string $strDestination
	 */
	private function combineChunks($strDestination){
		//Touch file
		$this->pfh->putContent($strDestination, "");
		
		$arrFields = $this->getForm();
		$totalChunks = $arrFields['totalChunks'];
		
		//check lock
		if(file_exists($this->getChunkPath($arrFields['identifier'], 'lock'))) {
			echo "lock exists";
			return false;
		}
		
		//Create lock file
		$this->pfh->putContent($this->getChunkPath($arrFields['identifier'], 'lock'), 'lock');
		
		//Create Dest File from all Chunks
		for ($i = 1; $i <= $totalChunks; $i++) {
			$file = $this->getChunkPath($arrFields['identifier'], $i);
			if(!file_exists($file)) return false;
			
			$data = file_get_contents($file);
			$this->pfh->addContent($strDestination, $data);
			
			$this->pfh->Delete($file);
		}
		
		//Delete Chunk Folder
		$this->pfh->Delete($this->getChunkPath($arrFields['identifier'], 'lock'));
		$this->pfh->Delete($this->chunkDir.DIRECTORY_SEPARATOR.md5($arrFields['identifier']).DIRECTORY_SEPARATOR);
		
		return true;
	}

	
	/**
	 * Validates if the File Upload is complete
	 * 
	 * @return boolean
	 */
	private function validateUploadedFile(){
		$arrFields = $this->getForm();
		$totalChunks = $arrFields['totalChunks'];
		$totalChunksSize = 0;
		$totalFileSize = $arrFields['totalSize'];
		
		for ($i = 1; $i <= $totalChunks; $i++) {
			$file = $this->getChunkPath($arrFields['identifier'], $i);
			if (!file_exists($file)) {
				return false;
			}
			$totalChunksSize += filesize($file);
		}
		return $totalFileSize == $totalChunksSize;
	}
	
	/**
	 * Validates the current uploaded chunk
	 * 
	 * @return boolean
	 */
	private function validateChunk(){
		$arrFields = $this->getForm();
		$file = $arrFields['file'];
		
		if (!$file) {
			return false;
		}
		if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
			return false;
		}
		if ($arrFields['currentChunkSize'] != $file['size']) {
			return false;
		}
		if ($file['error'] !== UPLOAD_ERR_OK) {
			return false;
		}
		return true;
	}
	
	/**
	 * Returns all Fields from the Uploaded Chunk, including the chunk data
	 * 
	 * @return array
	 */
	public function getForm(){
		$arrOut = array(
			'file' 				=> (isset($_FILES['file'])) ? $_FILES['file'] : false,
			'filename' 			=> $this->in->get('flowFilename'),
			'totalSize' 		=> $this->in->get('flowTotalSize', 0),
			'identifier' 		=> $this->in->get('flowIdentifier'),
			'relativePath' 		=> $this->in->get('flowRelativePath'),
			'totalChunks' 		=> $this->in->get('flowTotalChunks', 0),
			'defaultChunkSize'	=> $this->in->get('flowChunkSize', 0),
			'currentChunkNumber' => $this->in->get('flowChunkNumber', 0),
			'currentChunkSize'	=> $this->in->get('flowCurrentChunkSize', 0),
		);
		
		return $arrOut;
	}
}
?>