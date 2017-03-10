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
				
				//Try to find an image extension
				$strExtension = strtolower(pathinfo($strFile, PATHINFO_EXTENSION));
				if(!$strExtension || $strExtension == ""){
					$strExtension = $this->mimetype_to_extension($strMime);
				}
				

				//If type file now allowed: wrong type
				if (!in_array($intType, $arrTypes)){
					$arrError[$strFile] = "filetype_not_allowed";
						
					$this->tpl->assign_block_vars('mc_media_row', array(
							'ICON' => $this->core->icon_font('fa-times'),
							'NAME' => $strFile,
							'STATUS' => "filetype_not_allowed",
							'EXTENSION' => $strExtension,
					));
						
					continue;
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
							'EXTENSION' => $strExtension,
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
			'DD_CATEGORIES' => 	(new hdropdown('album', array('value'=>4, 'options' => $this->pdh->geth('mediacenter_albums', 'album_tree', array(false, false, true)))))->output(),
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
	
	private function mimetype_to_extension($strMime){
		$definitive = array (
				'application/x-authorware-bin' => '.aab',
				'application/x-authorware-map' => '.aam',
				'application/x-authorware-seg' => '.aas',
				'text/vnd.abc' => '.abc',
				'video/animaflex' => '.afl',
				'application/x-aim' => '.aim',
				'text/x-audiosoft-intra' => '.aip',
				'application/x-navi-animation' => '.ani',
				'application/x-nokia-9000-communicator-add-on-software' => '.aos',
				'application/mime' => '.aps',
				'application/arj' => '.arj',
				'image/x-jg' => '.art',
				'text/asp' => '.asp',
				'application/x-mplayer2' => '.asx',
				'video/x-ms-asf-plugin' => '.asx',
				'audio/x-au' => '.au',
				'application/x-troff-msvideo' => '.avi',
				'video/avi' => '.avi',
				'video/msvideo' => '.avi',
				'video/x-msvideo' => '.avi',
				'video/avs-video' => '.avs',
				'application/x-bcpio' => '.bcpio',
				'application/mac-binary' => '.bin',
				'application/macbinary' => '.bin',
				'application/x-binary' => '.bin',
				'application/x-macbinary' => '.bin',
				'image/x-windows-bmp' => '.bmp',
				'application/x-bzip' => '.bz',
				'application/vnd.ms-pki.seccat' => '.cat',
				'application/clariscad' => '.ccad',
				'application/x-cocoa' => '.cco',
				'application/cdf' => '.cdf',
				'application/x-cdf' => '.cdf',
				'application/java' => '.class',
				'application/java-byte-code' => '.class',
				'application/x-java-class' => '.class',
				'application/x-cpio' => '.cpio',
				'application/mac-compactpro' => '.cpt',
				'application/x-compactpro' => '.cpt',
				'application/x-cpt' => '.cpt',
				'application/pkcs-crl' => '.crl',
				'application/pkix-crl' => '.crl',
				'application/x-x509-user-cert' => '.crt',
				'application/x-csh' => '.csh',
				'text/x-script.csh' => '.csh',
				'application/x-pointplus' => '.css',
				'text/css' => '.css',
				'application/x-deepv' => '.deepv',
				'video/dl' => '.dl',
				'video/x-dl' => '.dl',
				'application/commonground' => '.dp',
				'application/drafting' => '.drw',
				'application/x-dvi' => '.dvi',
				'drawing/x-dwf (old)' => '.dwf',
				'model/vnd.dwf' => '.dwf',
				'application/acad' => '.dwg',
				'application/dxf' => '.dxf',
				'text/x-script.elisp' => '.el',
				'application/x-bytecode.elisp (compiled elisp)' => '.elc',
				'application/x-elc' => '.elc',
				'application/x-esrehber' => '.es',
				'text/x-setext' => '.etx',
				'application/envoy' => '.evy',
				'application/vnd.fdf' => '.fdf',
				'application/fractals' => '.fif',
				'image/fif' => '.fif',
				'video/fli' => '.fli',
				'video/x-fli' => '.fli',
				'text/vnd.fmi.flexstor' => '.flx',
				'video/x-atomic3d-feature' => '.fmf',
				'image/vnd.fpx' => '.fpx',
				'image/vnd.net-fpx' => '.fpx',
				'application/freeloader' => '.frl',
				'image/g3fax' => '.g3',
				'image/gif' => '.gif',
				'video/gl' => '.gl',
				'video/x-gl' => '.gl',
				'application/x-gsp' => '.gsp',
				'application/x-gss' => '.gss',
				'application/x-gtar' => '.gtar',
				'multipart/x-gzip' => '.gzip',
				'application/x-hdf' => '.hdf',
				'text/x-script' => '.hlb',
				'application/hlp' => '.hlp',
				'application/x-winhelp' => '.hlp',
				'application/binhex' => '.hqx',
				'application/binhex4' => '.hqx',
				'application/mac-binhex' => '.hqx',
				'application/mac-binhex40' => '.hqx',
				'application/x-binhex40' => '.hqx',
				'application/x-mac-binhex40' => '.hqx',
				'application/hta' => '.hta',
				'text/x-component' => '.htc',
				'text/webviewhtml' => '.htt',
				'x-conference/x-cooltalk' => '.ice ',
				'image/x-icon' => '.ico',
				'application/x-ima' => '.ima',
				'application/x-httpd-imap' => '.imap',
				'application/inf' => '.inf ',
				'application/x-internett-signup' => '.ins',
				'application/x-ip2' => '.ip ',
				'video/x-isvideo' => '.isu',
				'audio/it' => '.it',
				'application/x-inventor' => '.iv',
				'i-world/i-vrml' => '.ivr',
				'application/x-livescreen' => '.ivy',
				'audio/x-jam' => '.jam ',
				'application/x-java-commerce' => '.jcm ',
				'image/x-jps' => '.jps',
				'application/x-javascript' => '.js ',
				'image/jutvision' => '.jut',
				'music/x-karaoke' => '.kar',
				'application/x-ksh' => '.ksh',
				'text/x-script.ksh' => '.ksh',
				'audio/x-liveaudio' => '.lam',
				'application/lha' => '.lha',
				'application/x-lha' => '.lha',
				'application/x-lisp' => '.lsp ',
				'text/x-script.lisp' => '.lsp ',
				'text/x-la-asf' => '.lsx',
				'application/x-lzh' => '.lzh',
				'application/lzx' => '.lzx',
				'application/x-lzx' => '.lzx',
				'text/x-m' => '.m',
				'audio/x-mpequrl' => '.m3u ',
				'application/x-troff-man' => '.man',
				'application/x-navimap' => '.map',
				'application/mbedlet' => '.mbd',
				'application/x-magic-cap-package-1.0' => '.mc$',
				'application/mcad' => '.mcd',
				'application/x-mathcad' => '.mcd',
				'image/vasa' => '.mcf',
				'text/mcf' => '.mcf',
				'application/netmc' => '.mcp',
				'application/x-troff-me' => '.me ',
				'application/x-frame' => '.mif',
				'application/x-mif' => '.mif',
				'www/mime' => '.mime ',
				'audio/x-vnd.audioexplosion.mjuicemediafile' => '.mjf',
				'video/x-motion-jpeg' => '.mjpg ',
				'application/x-meme' => '.mm',
				'audio/mod' => '.mod',
				'audio/x-mod' => '.mod',
				'audio/x-mpeg' => '.mp2',
				'video/x-mpeq2a' => '.mp2',
				'audio/mpeg3' => '.mp3',
				'audio/x-mpeg-3' => '.mp3',
				'application/vnd.ms-project' => '.mpp',
				'application/marc' => '.mrc',
				'application/x-troff-ms' => '.ms',
				'application/x-vnd.audioexplosion.mzz' => '.mzz',
				'application/vnd.nokia.configuration-message' => '.ncm',
				'application/x-mix-transfer' => '.nix',
				'application/x-conference' => '.nsc',
				'application/x-navidoc' => '.nvd',
				'application/oda' => '.oda',
				'application/x-omc' => '.omc',
				'application/x-omcdatamaker' => '.omcd',
				'application/x-omcregerator' => '.omcr',
				'text/x-pascal' => '.p',
				'application/pkcs10' => '.p10',
				'application/x-pkcs10' => '.p10',
				'application/pkcs-12' => '.p12',
				'application/x-pkcs12' => '.p12',
				'application/x-pkcs7-signature' => '.p7a',
				'application/x-pkcs7-certreqresp' => '.p7r',
				'application/pkcs7-signature' => '.p7s',
				'text/pascal' => '.pas',
				'image/x-portable-bitmap' => '.pbm ',
				'application/vnd.hp-pcl' => '.pcl',
				'application/x-pcl' => '.pcl',
				'image/x-pict' => '.pct',
				'image/x-pcx' => '.pcx',
				'application/pdf' => '.pdf',
				'audio/make.my.funk' => '.pfunk',
				'image/x-portable-graymap' => '.pgm',
				'image/x-portable-greymap' => '.pgm',
				'application/x-newton-compatible-pkg' => '.pkg',
				'application/vnd.ms-pki.pko' => '.pko',
				'text/x-script.perl' => '.pl',
				'application/x-pixclscript' => '.plx',
				'text/x-script.perl-module' => '.pm',
				'application/x-portable-anymap' => '.pnm',
				'image/x-portable-anymap' => '.pnm',
				'model/x-pov' => '.pov',
				'image/x-portable-pixmap' => '.ppm',
				'application/powerpoint' => '.ppt',
				'application/x-mspowerpoint' => '.ppt',
				'application/x-freelance' => '.pre',
				'paleovu/x-pv' => '.pvu',
				'text/x-script.phyton' => '.py ',
				'applicaiton/x-bytecode.python' => '.pyc ',
				'audio/vnd.qcelp' => '.qcp ',
				'video/x-qtc' => '.qtc',
				'audio/x-realaudio' => '.ra',
				'application/x-cmu-raster' => '.ras',
				'image/x-cmu-raster' => '.ras',
				'text/x-script.rexx' => '.rexx ',
				'image/vnd.rn-realflash' => '.rf',
				'image/x-rgb' => '.rgb ',
				'application/vnd.rn-realmedia' => '.rm',
				'audio/mid' => '.rmi',
				'application/ringing-tones' => '.rng',
				'application/vnd.nokia.ringing-tone' => '.rng',
				'application/vnd.rn-realplayer' => '.rnx ',
				'image/vnd.rn-realpix' => '.rp ',
				'text/vnd.rn-realtext' => '.rt',
				'application/x-rtf' => '.rtf',
				'video/vnd.rn-realvideo' => '.rv',
				'audio/s3m' => '.s3m ',
				'application/x-lotusscreencam' => '.scm',
				'text/x-script.guile' => '.scm',
				'text/x-script.scheme' => '.scm',
				'video/x-scm' => '.scm',
				'application/sdp' => '.sdp ',
				'application/x-sdp' => '.sdp ',
				'application/sounder' => '.sdr',
				'application/sea' => '.sea',
				'application/x-sea' => '.sea',
				'application/set' => '.set',
				'application/x-sh' => '.sh',
				'text/x-script.sh' => '.sh',
				'audio/x-psid' => '.sid',
				'application/x-sit' => '.sit',
				'application/x-stuffit' => '.sit',
				'application/x-seelogo' => '.sl ',
				'audio/x-adpcm' => '.snd',
				'application/solids' => '.sol',
				'application/x-pkcs7-certificates' => '.spc ',
				'application/futuresplash' => '.spl',
				'application/streamingmedia' => '.ssm ',
				'application/vnd.ms-pki.certstore' => '.sst',
				'application/sla' => '.stl',
				'application/vnd.ms-pki.stl' => '.stl',
				'application/x-navistyle' => '.stl',
				'application/x-sv4cpio' => '.sv4cpio',
				'application/x-sv4crc' => '.sv4crc',
				'x-world/x-svr' => '.svr',
				'application/x-shockwave-flash' => '.swf',
				'application/x-tar' => '.tar',
				'application/toolbook' => '.tbk',
				'application/x-tcl' => '.tcl',
				'text/x-script.tcl' => '.tcl',
				'text/x-script.tcsh' => '.tcsh',
				'application/x-tex' => '.tex',
				'application/plain' => '.text',
				'application/gnutar' => '.tgz',
				'audio/tsp-audio' => '.tsi',
				'application/dsptype' => '.tsp',
				'audio/tsplayer' => '.tsp',
				'text/tab-separated-values' => '.tsv',
				'text/x-uil' => '.uil',
				'application/i-deas' => '.unv',
				'application/x-ustar' => '.ustar',
				'multipart/x-ustar' => '.ustar',
				'application/x-cdlink' => '.vcd',
				'text/x-vcalendar' => '.vcs',
				'application/vda' => '.vda',
				'video/vdo' => '.vdo',
				'application/groupwise' => '.vew ',
				'application/vocaltec-media-desc' => '.vmd ',
				'application/vocaltec-media-file' => '.vmf',
				'audio/voc' => '.voc',
				'audio/x-voc' => '.voc',
				'video/vosaic' => '.vos',
				'audio/voxware' => '.vox',
				'audio/x-twinvq' => '.vqf',
				'application/x-vrml' => '.vrml',
				'x-world/x-vrt' => '.vrt',
				'application/wordperfect6.1' => '.w61',
				'audio/wav' => '.wav',
				'audio/x-wav' => '.wav',
				'application/x-qpro' => '.wb1',
				'image/vnd.wap.wbmp' => '.wbmp',
				'application/vnd.xara' => '.web',
				'application/x-123' => '.wk1',
				'windows/metafile' => '.wmf',
				'text/vnd.wap.wml' => '.wml',
				'application/vnd.wap.wmlc' => '.wmlc ',
				'text/vnd.wap.wmlscript' => '.wmls',
				'application/vnd.wap.wmlscriptc' => '.wmlsc ',
				'application/x-wpwin' => '.wpd',
				'application/x-lotus' => '.wq1',
				'application/mswrite' => '.wri',
				'application/x-wri' => '.wri',
				'text/scriplet' => '.wsc',
				'application/x-wintalk' => '.wtk ',
				'image/x-xbitmap' => '.xbm',
				'image/x-xbm' => '.xbm',
				'image/xbm' => '.xbm',
				'video/x-amt-demorun' => '.xdr',
				'xgl/drawing' => '.xgz',
				'image/vnd.xiff' => '.xif',
				'audio/xm' => '.xm',
				'application/xml' => '.xml',
				'text/xml' => '.xml',
				'xgl/movie' => '.xmz',
				'application/x-vnd.ls-xpix' => '.xpix',
				'image/xpm' => '.xpm',
				'video/x-amt-showrun' => '.xsr',
				'image/x-xwd' => '.xwd',
				'image/x-xwindowdump' => '.xwd',
				'application/x-compress' => '.z',
				'application/x-zip-compressed' => '.zip',
				'application/zip' => '.zip',
				'multipart/x-zip' => '.zip',
				'text/x-script.zsh' => '.zsh',
				'video/mp4' => '.mp4',
				'video/ogg' => '.ogg',
				'video/webm' => '.webm'
		);
		
		if(isset($definitive[$strMime])){
			return str_replace(".", "", trim($definitive[$strMime]));
		}
		
		$ambiguous = array (
				'x-world/x-3dmf' => '.3dm',
				'application/octet-stream' => '.exe',
				'text/html' => '.html',
				'application/postscript' => '.ps',
				'audio/aiff' => '.aif',
				'audio/x-aiff' => '.aif',
				'video/x-ms-asf' => '.asf',
				'text/x-asm' => '.asm',
				'audio/basic' => '.au',
				'image/bmp' => '.bmp',
				'application/book' => '.book',
				'application/x-bzip2' => '.bz2',
				'application/x-bsh' => '.sh',
				'text/plain' => '.txt',
				'text/x-c' => '.c',

				'application/x-netcdf' => '.cdf',
				'application/pkix-cert' => '.crt',
				'application/x-x509-ca-cert' => '.der',
				'application/x-chat' => '.chat',
				'application/x-director' => '.dcr',
				'video/x-dv' => '.dv',
				'application/msword' => '.doc',
				'image/vnd.dwg' => '.dwg',
				'image/x-dwg' => '.dwg',
				'application/x-envoy' =>  '.env',
				'text/x-fortran' => '.f',
				'image/florian' => '.flo',
				'audio/make' => '.funk',
				'audio/x-gsm' => '.gsm',
				'application/x-compressed' => '.zip',
				'application/x-gzip' => '.gz',
				'text/x-h' => '.h',
				'application/x-helpfile' => '.help',
				'application/vnd.hp-hpgl' => '.hgl',
				'image/ief' => '.ief',
				'application/iges' => '.igs',
				'model/iges' => '.igs',
				'text/x-java-source' => '.java',
				'image/jpeg' => '.jpg',
				'image/pjpeg' => '.jpg ',
				'audio/midi' => '.midi',
				'audio/nspaudio' => '.lma',
				'audio/x-nspaudio' => '.lma',
				'application/x-latex' => '.latex ',
				'video/mpeg' => '.mp4',
				'audio/mpeg' => '.mpg',
				'message/rfc822' => '.mhtml',
				'application/x-midi' => '.midi',
				'audio/x-mid' => '.mid',
				'audio/x-midi' => '.midi',
				'music/crescendo' => '.midi',
				'x-music/x-midi' => '.midi',
				'application/base64' => '.mme',
				'video/quicktime' => '.mov',
				'video/x-sgi-movie' => '.mv',
				'video/x-mpeg' => '.mp3',
				'application/x-project' => '.mpx',
				'image/naplps' => '.nap',
				'image/x-niff' => '.nif',
				'application/pkcs7-mime' => '.p7c',
				'application/x-pkcs7-mime' => '.p7c',
				'application/pro_eng' => '.part ',
				'chemical/x-pdb' => '.pdb',
				'image/pict' => '.pic',
				'image/x-xpixmap' => '.xpm',
				'application/x-pagemaker' => '.pm5',
				'image/png' => '.png',
				'application/mspowerpoint' => '.ppt',
				'application/vnd.ms-powerpoint' => '.ppt',
				'image/x-quicktime' => '.qtif',
				'audio/x-pn-realaudio' => '.ram',
				'audio/x-pn-realaudio-plugin' => '.rpm',
				'image/cmu-raster' => '.ras',
				'application/x-troff' => '.tr',
				'text/richtext' => '.rtf',
				'application/rtf' => '.rtf',
				'application/x-tbook' => '.tbk',
				'text/sgml' => '.sgm ',
				'text/x-sgml' => '.sgm ',
				'application/x-shar' => '.sh',
				'text/x-server-parsed-html' => '.shtml',
				'application/x-koan' => '.skt ',
				'application/smil' => '.smi ',
				'text/x-speech' => '.talk',
				'application/x-sprite' => '.spr',
				'application/x-wais-source' => '.src',
				'application/step' => '.step',
				'application/x-world' => '.wrl',
				'application/x-texinfo' => '.texi',
				'image/tiff' => '.tif',
				'image/x-tiff' => '.tif',
				'text/uri-list' => '.uri',
				'text/x-uuencode' => '.uu',
				'video/vivo' => '.viv',
				'video/vnd.vivo' => '.viv',
				'audio/x-twinvq-plugin' => '.vql',
				'model/vrml' => '.vrml',
				'x-world/x-vrml' => '.vrml',
				'application/x-visio' => '.vsd',
				'application/wordperfect6.0' => '.wp5',
				'application/wordperfect' => '.wpd',
				'application/excel' => '.xls',
				'application/x-excel' =>'.xls',
				'application/x-msexcel' => '.xls',
				'application/vnd.ms-excel' => '.xls',
		);
		
		if(isset($ambiguous[$strMime])){
			return str_replace(".", "", trim($ambiguous[$strMime]));
		}
		
		return 'unknown';
	}
	
}
registry::register('IndexMediaFiles');
?>