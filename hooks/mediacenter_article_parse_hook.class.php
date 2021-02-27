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
if (!defined('EQDKP_INC'))
{
  header('HTTP/1.0 404 Not Found');exit;
}


/*+----------------------------------------------------------------------------
  | shoutbox_search_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('mediacenter_article_parse_hook'))
{
  class mediacenter_article_parse_hook extends gen_class
  {

  	private $videojs_included = false;
  	
	/**
    * hook_search
    * Do the hook 'search'
    *
    * @return array
    */
	public function article_parse($arrOptions)
	{
		
		$strContent = $arrOptions['content'];
		
		if(!$this->user->check_auth('u_mediacenter_view', false)){
			return $arrOptions;
		}
		
		//Parse all links
		$arrLinks = array();
		if(stripos($strContent, 'mediacenter') !== false){
			$intLinks = preg_match_all('@((("|:)?)https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\-\.]*(\?[^<\s]+|\?)?)?)?)@', $strContent, $arrLinks);
		} else $intLinks = 0;
		$arrDecodedLinks = array();
		if ($intLinks){
			$key = 0;
			foreach ($arrLinks[0] as $link){
				$orig_link = $link;

				if (substr($link, 0, 1) != '"' && substr($link, 0, 1) != ':') {
					$embedlyUrls[$key] = strip_tags($link);
					$arrDecodedLinks[$key] = $orig_link;
					$strOut = "";
					if(stripos($link, 'mediacenter/') !== false){
						$link = str_replace(array('.php', '.html'), '', strip_tags($link));
						$arrPath = array_filter(explode('/', $link));
						$arrPath = array_reverse($arrPath);
						
						$strMyPath = $arrPath[0];
						
						if(preg_match('#(.*)\-([a]?[0-9]*)#', $strMyPath, $arrMatches)){							
							if(is_numeric($arrMatches[2])){
								//Media
								$intMediaID = intval($arrMatches[2]);
								$strOut = $this->createMedia($intMediaID);
							} else {
								//Album
								$intAlbumID = intval(substr($arrMatches[2], 1));
								$strOut = $this->createAlbum($intAlbumID);
							}
						} else {
							//Must be a category
							$arrmyurl = parse_url($strMyPath);
							$intCategoryId = $this->pdh->get('mediacenter_categories', 'resolve_alias', array($arrmyurl['path']));
							if($intCategoryId){
								$strOut = $this->createCategory($intCategoryId);
							}
						}
						
						if($strOut != ""){
							$strContent = str_replace($orig_link, $strOut, $strContent);
						}
					}
					
					$key++;
				}
			}
		}

		//Replace Album
		if(stripos($strContent, 'mediacenter-album') !== false){
			$arrAlbumObjects = array();
			preg_match_all('#<p(.*)class="system-block mediacenter-album"(.*) data-album-id="(.*)">(.*)</p>#iU', $strContent, $arrAlbumObjects, PREG_PATTERN_ORDER);
			if (count($arrAlbumObjects[0])){
				foreach($arrAlbumObjects[3] as $key => $val){
					$intAlbumID = intval($val);
					
					$strMediaContent = $this->createAlbum($intAlbumID);
					$strContent = str_replace($arrAlbumObjects[0][$key], $strMediaContent, $strContent);
				}
			}
		}
		
		//Replace Media
		if(stripos($strContent, 'mediacenter-media') !== false){
			$arrMediaObjects = array();
			preg_match_all('#<p(.*)class="system-block mediacenter-media"(.*) data-media-id="(.*)">(.*)</p>#iU', $strContent, $arrMediaObjects, PREG_PATTERN_ORDER);
			if (count($arrMediaObjects[0])){
				foreach($arrMediaObjects[3] as $key => $val){
	
	
					$intMediaID = intval($val);
					$strMediaContent = $this->createMedia($intMediaID);
					$strContent = str_replace($arrMediaObjects[0][$key], $strMediaContent, $strContent);
				}
			}
		}
		
		//Replace Media BBCode
		if(stripos($strContent, '[media') !== false){
			$arrMediaObjects = array();
			preg_match_all('#\[media\](.*)\[\/media\]#iU', $strContent, $arrMediaObjects, PREG_PATTERN_ORDER);
			if (count($arrMediaObjects[0])){
				foreach($arrMediaObjects[1] as $key => $val){
			
					$intMediaID = intval($val);
					$strMediaContent = $this->createMedia($intMediaID);
					$strContent = str_replace($arrMediaObjects[0][$key], $strMediaContent, $strContent);
				}
			}
		}
		
		//Replace Album BBCode
		$arrMediaObjects = array();
		if(stripos($strContent, '[album') !== false){
			preg_match_all('#\[album\](.*)\[\/album\]#iU', $strContent, $arrMediaObjects, PREG_PATTERN_ORDER);
			if (count($arrMediaObjects[0])){
				foreach($arrMediaObjects[1] as $key => $val){
			
					$intAlbumID = intval($val);
					$strMediaContent = $this->createAlbum($intAlbumID);
					$strContent = str_replace($arrMediaObjects[0][$key], $strMediaContent, $strContent);
				}
			}
		}
		//Replace Category BBCode
		$arrMediaObjects = array();
		if(stripos($strContent, '[category') !== false){
			preg_match_all('#\[category\](.*)\[\/category\]#iU', $strContent, $arrMediaObjects, PREG_PATTERN_ORDER);
			if (count($arrMediaObjects[0])){
				foreach($arrMediaObjects[1] as $key => $val){
			
					$intCategoryID = intval($val);
					$strMediaContent = $this->createCategory($intCategoryID);
					$strContent = str_replace($arrMediaObjects[0][$key], $strMediaContent, $strContent);
				}
			}
		}

		$arrOptions['content'] = $strContent;
		
		return $arrOptions;
	}
	
	private function createMedia($intMediaID){
		$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($intMediaID));

		$intCategoryId = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
		if(!(int)$arrMediaData['published']) return "";
		$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
		
		$intPublished = $arrCategoryData['published'];
		if (!$intPublished) return "";
		 
		//Check Permissions
		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
		if (!$arrPermissions['read'] || !$this->user->check_auth('u_mediacenter_view', false)) return "";
		if(!$this->pdh->get('mediacenter_media', 'published', array($intMediaID))) return "";

		
		$strPath = $this->server_path.$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID));
		$strOut = "";
		
		if((int)$arrMediaData['type'] === 0){
			$strAuthor = $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
			$strDate =  $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
			
			
			$strOut = '<div class="mcArticleBoxFile">';
			$strOut .= '<div class="mcPreviewImageSmall"><a href="'.$strPath.'">'.$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1)).'</a></div>';
			$strOut .= '<div class="mcBoxContent"><h3><a href="'.$strPath.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a></h3>
					'.$this->pdh->geth('mediacenter_media', 'type', array($intMediaID)).' - '.$strDate.' - '.$strAuthor.'
					</div>';
			$strOut .= '<div class="clear"></div></div>';

		}elseif($arrMediaData['type'] === 1){
			//Video
			$arrAdditionalData = unserialize($arrMediaData['additionaldata']);
			if(isset($arrAdditionalData['html'])){
				//Is embedly Video
				$strOut = $arrAdditionalData['html'];
			} else {
				if(strlen($arrMediaData['externalfile'])){
					$strExtension = pathinfo($arrMediaData['externalfile'], PATHINFO_EXTENSION);
					$strFile = $arrMediaData['externalfile'];
				} else {
					$strExtension = pathinfo($arrMediaData['localfile'], PATHINFO_EXTENSION);
					$strFile = $this->pfh->FolderPath('files', 'mediacenter', 'absolute').$arrMediaData['localfile'];
				}
				
				$arrPlayableVideos = array('mp4', 'webm', 'ogg');
				if(in_array($strExtension, $arrPlayableVideos)){
					if(!$this->videojs_included){
						$this->tpl->css_file($this->root_path.'plugins/mediacenter/includes/videojs/video-js.min.css');
						$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/videojs/video.js');
						$this->tpl->add_js('videojs.options.flash.swf = "'.$this->server_path.'plugins/mediacenter/includes/videojs/video-js.swf"; ', 'docready');
						$this->videojs_included = true;	
					}
					switch($strExtension){
						case 'mp4': $strSource =  '  <source src="'.$strFile.'" type=\'video/mp4\' />'; break;
						case 'webm': $strSource =  '  <source src="'.$strFile.'" type=\'video/webm\' />'; break;
						case 'ogg': $strSource =  '   <source src="'.$strFile.'" type=\'video/ogg\' />'; break;
					}
						
					$strVideo = '  <video id="mc_video_'.$intMediaID.'" class="mcArticleBoxVideo video-js vjs-default-skin" controls preload="none" width="640" height="264"
						      poster="" data-setup="{}">
						    '.$strSource.'
						    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
						  </video>';
					
					$strOut = $strVideo;
				}
			}
			
		} else {
			//Image
			$strAuthor = $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
			$strDate =  $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
			$strCategory = $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true)).((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): '');

			$strOut = '<div class="mcArticleBoxImage"><a href="'.$strPath.'"><img src="'.$strPath.'&image" /></a><div><div>
					<h3><a href="'.$strPath.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a></h3>
					'.$strDate.' - '.$strAuthor.' - '.$strCategory.'
					</div></div></div>';
		}
		
		return $strOut;
	}
	
	private function createAlbum($intAlbumID){
		$strOut = "";
		$arrAlbumData = $this->pdh->get('mediacenter_albums', 'data', array($intAlbumID));
		$intCategoryId = $arrAlbumData['category_id'];
		 
		$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
		$intPublished = $arrCategoryData['published'];
		if (!$intPublished) return "";
		
		//Check Permissions
		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
		if (!$arrPermissions['read']) return "";
		
		$strOut = '<div class="mcArticleBoxAlbum"><ul class="mcGridList">';
		
		$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, true));
		
		$lightboxID = md5('article_'.$intAlbumID.rand());
		
		$this->jquery->lightbox($lightboxID, array('slideshow' => true, 'transition' => "elastic", 'slideshowSpeed' => 4500, 'slideshowAuto' => false, 'type' => 'photo', 'oncomplete' => "var url = $(this).data('url');
var title = $(this).attr('title');
if(url == undefined){ url = $(this).attr('href');}
var desc = $(this).data('desc');
if(desc == undefined) { desc = ''; } else { desc = '<br />'+desc;}
$('#cboxTitle').html('<a href=\"' + url + '\">'+title+'</a>'+desc);"));
		
		foreach($arrMediaInCategory as $intMediaID){
			$strPreviewImage = 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2));
			$strPreviewImageURL = 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true));
			$strName 		= $this->pdh->get('mediacenter_media', 'name', array($intMediaID));
			$strLink		= $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID));
			$intViews		= $this->pdh->get('mediacenter_media', 'views', array($intMediaID));
			$intComments 	= $this->pdh->get('mediacenter_media', 'comment_count', array($intMediaID));
			$strAuthor		= $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
			$strDate		= $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
			$strCategory 	= ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): '');
			$strDescription	= $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID)));
			$strType		= $this->pdh->geth('mediacenter_media', 'type', array($intMediaID));
				
			$strOut .= '<li style="background-image:url(\''.$strPreviewImageURL.'\');">';
			if($this->pdh->get('mediacenter_media', 'type', array($intMediaID)) === 2){
				$strOut .= '<a href="'.$strLink.'&image" data-url="'.$strLink.'" data-desc="'.$strDescription.'" class="lightbox_'.$lightboxID.'" rel="'.$lightboxID.'" title="'.sanitize($strName).'"></a>';
			} else {
				$strOut .= '<a href="'.$strLink.'" title="'.sanitize($strName).'"></a>';
			}
				$strOut .= '<div>
					<p><a href="'.$strLink.'">'.$strName.'</a></p>
					<div>'.$strType.' &nbsp; '.$strDate.' &bull; '.$strAuthor.' '.$strCategory.'<br />
					</div>
				</div>
			</li>';
			
		}
		 
		$strOut .= '</ul><div class="clear"></div></div>';
		
		return $strOut;
	}
	
	private function createCategory($intCategoryId){
		$strOut = "";

		$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
		$intPublished = $arrCategoryData['published'];
		if (!$intPublished) return "";
		
		//Check Permissions
		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
		if (!$arrPermissions['read']) return "";
		
		$strOut = '<div class="mcArticleBoxCategory"><ul class="mcGridList">';
		
		$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, true, true));
		
		$lightboxID = md5('category_'.$intCategoryId.rand());
		
		$this->jquery->lightbox($lightboxID, array('slideshow' => true, 'transition' => "elastic", 'slideshowSpeed' => 4500, 'slideshowAuto' => false, 'type' => 'photo', 'oncomplete' => "var url = $(this).data('url');
var title = $(this).attr('title');
if(url == undefined){ url = $(this).attr('href');}
var desc = $(this).data('desc');
if(desc == undefined) { desc = ''; } else { desc = '<br />'+desc;}
$('#cboxTitle').html('<a href=\"' + url + '\">'+title+'</a>'+desc);"));
		
		foreach($arrMediaInCategory as $intMediaID){
			$strPreviewImage = 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2));
			$strPreviewImageURL = 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true));
			$strName 		= $this->pdh->get('mediacenter_media', 'name', array($intMediaID));
			$strLink		= $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID));
			$intViews		= $this->pdh->get('mediacenter_media', 'views', array($intMediaID));
			$intComments 	= $this->pdh->get('mediacenter_media', 'comment_count', array($intMediaID));
			$strAuthor		= $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
			$strDate		= $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
			$strCategory 	= ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): '');
			$strDescription	= $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID)));
			$strType		= $this->pdh->geth('mediacenter_media', 'type', array($intMediaID));
				
			$strOut .= '<li style="background-image:url(\''.$strPreviewImageURL.'\');">';
			if($this->pdh->get('mediacenter_media', 'type', array($intMediaID)) === 2){
				$strOut .= '<a href="'.$strLink.'&image" data-url="'.$strLink.'" data-desc="'.$strDescription.'" class="lightbox_'.$lightboxID.'" rel="'.$lightboxID.'" title="'.sanitize($strName).'"></a>';
			} else {
				$strOut .= '<a href="'.$strLink.'" title="'.sanitize($strName).'"></a>';
			}
				$strOut .= '<div>
					<p><a href="'.$strLink.'">'.$strName.'</a></p>
					<div>'.$strType.' &nbsp; '.$strDate.' &bull; '.$strAuthor.' '.$strCategory.'<br />
					</div>
				</div>
			</li>';
			
		}
		 
		$strOut .= '</ul><div class="clear"></div></div>';
		
		return $strOut;
	}
	
	
  }
}
?>