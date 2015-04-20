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

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path . 'common.php');

class IndexMediaFiles extends page_generic {

	public function __construct(){
		// plugin installed?
		if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
			message_die($this->user->lang('mc_plugin_not_installed'));
		
		$this->user->check_auth('a_mediacenter_manage');
		
		$handler = array(
			'save' 				=> array('process' => 'save', 'csrf' => true),
		);
		parent::__construct(false, $handler, array('mediacenter_media', 'name'), null, 'selected_ids[]');
		$this->process();
	}
	
	public function save(){
		$strFileFolder = $this->pfh->FolderPath('files', 'mediacenter');
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$arrFiles = scandir($strFileFolder);
		
		$arrLocalFilenames = $this->pdh->aget('mediacenter_media', 'localfile', 0, array($this->pdh->get('mediacenter_media', 'id_list', array())));
		

		$intAlbumID = $this->in->get('album');
		
		if(substr($intAlbumID, 0, 1) == 'c'){
			$intCategoryID = (int)substr($intAlbumID, 1);
			$intAlbumID = 0;
		} else {
			$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
			$intAlbumID = intval($intAlbumID);
		}
		
		$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
			
		if (!$intCategoryID || !$arrTypes || (count($arrTypes) == 0)) return false;
		
		foreach($arrFiles as $strFile){
			if(valid_folder($strFile)){
				if(in_array($strFile, $arrLocalFilenames)) continue;
				
				$strMime = $finfo->file($strFileFolder.$strFile);
				
				$strName = $strFile;
				if(strpos($strMime, 'image/') === 0){
					$intType = 2;
				} elseif(strpos($strMime, 'video/') === 0){
					$intType = 1;
				} else {
					$intType = 0;
				}
				
				//If Type is not allowed, make type file
				if (!in_array($intType, $arrTypes)){
					$intType = 0; //File
				}
				
				//If type file now allowed: wrong type
				if (!in_array($intType, $arrTypes)){
					$arrError[$strFile] = "filetype_not_allowed";
					
					$this->tpl->assign_block_vars('mc_media_row', array(
						'ICON' => $this->core->icon_font('fa-times'),
						'NAME' => $strFile,
						'STATUS' => "filetype_not_allowed",	
					));
					
					continue;
				}
				
				//Try to find an image extension
				$strExtension = pathinfo($strFile, pathinfo($path));
				if(!$strExtension || $strExtension == ""){
					switch ($strMime){
						case 'image/jpeg': $strExtension = 'jpg';
							break;
						case 'image/png': $strExtension = 'png';
							break;
						case 'image/gif': $strExtension = 'gif';
					}
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
				
					//Watermark
					if ((int)$this->config->get('watermark_enabled', 'mediacenter')){
						$this->create_watermark($strThumbfolder.$filename.'.'.$strExtension);
					}
				
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
				$blnDefaultPublishState = $this->pdh->get('mediacenter_categories', 'default_published_state', array($intCategoryID));
				$intPublished = ($this->user->check_auth('a_mediacenter_manage', false)) ? 1 : $blnDefaultPublishState;
	
					
				if($strExtension != ""){
					$strFilename = $strFile.'.'.$strExtension;
				} else {
					$strFilename = $strFile;
				}
				
				$arrQuery = array(
						'album_id'		=> $intAlbumID,
						'category_id'	=> $intCategoryID,
						'name'			=> $strName,
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
				if($objQuery){
					$id = $objQuery->insertId;
					$arrSucces[] = $strFile;
					
					$this->tpl->assign_block_vars('mc_media_row', array(
							'ICON' => $this->core->icon_font('fa-check'),
							'NAME' => $strFile,
							'STATUS' => "OK",
					));
				}
		
			}
		}
		$this->pdh->enqueue_hook('mediacenter_media_update');
		$this->pdh->enqueue_hook('mediacenter_categories_update');
		$this->pdh->process_hook_queue();

		$this->display(false);
	}
	
	public function display($start=true){
		$this->tpl->assign_vars(array(
			'S_MC_INDEX'	=> (function_exists("finfo_file")) ? true : false,
			'S_START'		=> $start,
			'DD_CATEGORIES' => 	new hdropdown('album', array('value'=>4, 'options' => $this->pdh->geth('mediacenter_albums', 'album_tree', array(false, false, true)),))
		));
				
		$this->core->set_vars(array(
				'page_title'		=> $this->user->lang('mc_index_files').': '.$this->pdh->get('mediacenter_categories', 'name', array($intCategoryID)),
				'template_path'		=> $this->pm->get_data('mediacenter', 'template_path'),
				'template_file'		=> 'admin/index_files.html',
				'display'			=> true)
		);
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
	
}
registry::register('IndexMediaFiles');
?>