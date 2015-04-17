<?php
/*
 * Project:     EQdkp guildrequest
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-10-13 22:48:23 +0200 (Sa, 13. Okt 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     guildrequest
 * @version     $Rev: 12273 $
 *
 * $Id: archive.php 12273 2012-10-13 20:48:23Z godmod $
 */


class views_pageobject extends pageobject {
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array('social' => 'socialplugins');
   	return array_merge(parent::__shortcuts(), $shortcuts);
  }  
  
  /**
   * Constructor
   */
  public function __construct()
  {
    // plugin installed?
    if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
      message_die($this->user->lang('mc_plugin_not_installed'));
    
    $handler = array(
    	'mymedia'	=> array('process' => 'view_mymedia'),
    	'myalbums' 	=> array('process' => 'view_myalbums'),
    	'a'			=> array('process' => 'view_album'),
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  //For URL: index.php/MediaCenter/MyMedia/
  public function view_mymedia(){

  	
  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'mymedia.html',
  			'display'       => true
  	));
  }
  
  //For URL: index.php/MediaCenter/Downloads/MyAlbumname-a1/
  public function view_album(){

  	$intAlbumID = $this->in->get('a');
  	
  	//d($this->pdh->get('mediacenter_album', 'data', array($intAlbumID)));
  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'album.html',
  			'display'       => true
  	));
  	
  }
  
  public function saveRating(){
  	$this->pdh->put('mediacenter_media', 'vote', array($this->in->get('name'), $this->in->get('score')));
  	$this->pdh->process_hook_queue();
  	die('done');
  }
  
  public function display(){
  	if ($this->in->exists('mcsavevote')){
  		$this->saveRating();
  	}
  	
  	$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/js/modernizr.custom.17475.js');
  	$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/js/jquery.elastislide.js');
  	/*
  	d($this->url_id);
  	d($this->page);
  	d($this->page_path);
  	d(registry::get_const('patharray'));
  	*/
  	$arrPathArray = registry::get_const('patharray');
  	
  	if (is_numeric($this->url_id)){
  		//For URL: index.php/MediaCenter/Downloads/MyFileName-17.html
  		
  		$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  		if(count($arrMediaData)){
  			$intMediaID = $this->url_id;
  			$intCategoryId = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  			
  			$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));

  			$intPublished = $arrCategoryData['published'];
  			if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  				
  			//Check Permissions
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  			if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  			if(!$this->pdh->get('mediacenter_media', 'published', array($intMediaID))) message_die($this->user->lang('category_unpublished'));
  			
  			pd($arrMediaData);
  			$arrTags = $this->pdh->get('mediacenter_media', 'tags', array($intMediaID));
  			
  			//Create Maincontent
  			$intType = $this->pdh->get('mediacenter_media', 'type', array($intMediaID));
  			$strExtension = strtolower(pathinfo($arrMediaData['filename'], PATHINFO_EXTENSION));
  			$arrPlayableVideos = array('mp4', 'webm', 'ogg');
  			$arrAdditionalData = unserialize($arrMediaData['additionaldata']);
  			
  			if($intType === 0){
  				//File
  			} elseif($intType === 1){
  				//Video
  				if(isset($arrAdditionalData['html'])){
  					//Is embedly Video
  					$strVideo = $arrAdditionalData['html'];
  				} else{
  					$strExternalExtension = pathinfo($arrMediaData['externalfile'], PATHINFO_EXTENSION);
  					if(strlen($arrMediaData['externalfile']) && in_array($strExternalExtension, $arrPlayableVideos)){
  						$this->tpl->css_file($this->root_path.'plugins/mediacenter/includes/videojs/video-js.min.css');
  						$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/videojs/video.js');
  						$this->tpl->add_js('videojs.options.flash.swf = "'.$this->server_path.'plugins/mediacenter/includes/videojs/video-js.swf"; ', 'docready');
  							
  						switch($strExtension){
  							case 'mp4': $strSource =  '  <source src="'.$arrMediaData['externalfile'].'" type=\'video/mp4\' />'; break;
  							case 'webm': $strSource =  '  <source src="'.$arrMediaData['externalfile'].'" type=\'video/webm\' />'; break;
  							case 'ogg': $strSource =  '   <source src="'.$arrMediaData['externalfile'].'" type=\'video/ogg\' />'; break;
  						}
  							
  						$strVideo = '  <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="640" height="264"
						      poster="" data-setup="{}">
						    '.$strSource.'
						    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
						  </video>';
  						
  						
  					} elseif(in_array($strExtension, $arrPlayableVideos)){
  						$this->tpl->css_file($this->root_path.'plugins/mediacenter/includes/videojs/video-js.min.css');
  						$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/videojs/video.js');
  						$this->tpl->add_js('videojs.options.flash.swf = "'.$this->server_path.'plugins/mediacenter/includes/videojs/video-js.swf"; ', 'docready');
  						
  						$strLocalFile = $this->pfh->FolderPath('files', 'mediacenter', 'absolute').$arrMediaData['localfile'];
  						
  						switch($strExtension){
  							case 'mp4': $strSource =  '  <source src="'.$strLocalFile.'" type=\'video/mp4\' />'; break;
  							case 'webm': $strSource =  '  <source src="'.$strLocalFile.'" type=\'video/webm\' />'; break;
  							case 'ogg': $strSource =  '   <source src="'.$strLocalFile.'" type=\'video/ogg\' />'; break;
  						}
  							
  						$strVideo = '  <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="640" height="264"
						      poster="" data-setup="{}">
						    '.$strSource.'
						    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
						  </video>';
  					} else {
  						$strVideo = 'Cannot play this video type.';
  					}
  				}
  				
  				$this->tpl->assign_vars(array(
  					'MC_VIDEO'	=> $strVideo,
  				));
  			} else {
  				//Image
  				
  				//Check if there is a watermark image
  				$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
  				if(file_exists($strThumbfolder.$arrMediaData['localfile'].'.'.$strExtension)){
  					$strImage = $strThumbfolder.$arrMediaData['localfile'].'.'.$strExtension;
  				} else {
  					$strImage = $this->pfh->FolderPath('files', 'mediacenter', 'absolute').$arrMediaData['localfile'];
  				}
  				
  				$arrImageDimesions = getimagesize($strImage);
  				
  				$this->tpl->assign_vars(array(
  					'MC_IMAGE'			=> $strImage,
  					'MC_MEDIA_FILENAME'	=> $arrMediaData['filename'],
  					'MC_MEDIA_IMAGEDIMENSIONS' => $arrImageDimesions[0].' x '.$arrImageDimesions[1],
  				));
  				
  				foreach($arrAdditionalData as $key => $val){
  					if($key === 'size'){
  						$val = human_filesize($val);
  					} elseif($key === 'CreationTime'){
  						$val = $this->time->createTimeTag((int)$val, $this->time->user_date($val, true));
  					} elseif($key === 'FNumber'){
  						$val = 'f/'.$val;
  					}
  					
  					$this->tpl->assign_block_vars('mc_more_image_details', array(
  						'LABEL' => $this->user->lang('mc_'.$key),
  						'VALUE'	=> sanitize($val),	
  					));
  				}
  				
  			}
  			
  			$nextID = $this->pdh->get('mediacenter_media', 'next_media', array($intMediaID));
  			$prevID = $this->pdh->get('mediacenter_media', 'prev_media', array($intMediaID));
  			
  			$this->comments->SetVars(array(
  					'attach_id'		=> $intMediaID,
  					'page'			=> 'mediacenter',
  					'auth'			=> 'a_mediacenter_man',
  					'ntfy_type' 	=> 'comment_new_mediacenter',
  					'ntfy_title'	=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'ntfy_link' 	=> $this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  			));
  				
  			$intCommentsCount = $this->comments->Count();
  			
  			$arrToolbarItems = array();
  			if ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_man', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-plus',
  						'js'	=> 'onclick="editMedia(0)"',
  						'title'	=> $this->user->lang('mc_add_media'),
  				);
  			}
  			
  			if ($arrPermissions['update']  || $this->user->check_auth('a_mediacenter_man', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-pencil-square-o',
  						'js'	=> 'onclick="editMedia('.$intMediaID.')"',
  						'title'	=> $this->user->lang('mc_edit_media'),
  				);
  			}
  			
  			if ($arrPermissions['delete']) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-trash-o',
  						'js'	=> 'onclick="deleteMedia('.$intMediaID.')"',
  						'title'	=> $this->user->lang('mc_delete_media'),
  				);
  			}
  			if ($arrPermissions['change_state']) {
  				if ($intPublished){
  					$arrToolbarItems[] = array(
  							'icon'	=> 'fa-eye-slash',
  							'js'	=> 'onclick="window.location=\''.$this->env->link.$this->controller_path_plain.$this->page_path.$this->SID.'&mcunpublish&link_hash='.$this->CSRFGetToken('unpublish').'&mid='.$intMediaID.'\'"',
  							'title'	=> $this->user->lang('article_unpublish'),
  					);
  				} else {
  					$arrToolbarItems[] = array(
  							'icon'	=> 'fa-eye',
  							'js'	=> 'onclick="window.location=\''.$this->env->link.$this->controller_path_plain.$this->page_path.$this->SID.'&mcpublish&link_hash='.$this->CSRFGetToken('publish').'&mid='.$intMediaID.'\'"',
  							'title'	=> $this->user->lang('article_publish'),
  					);
  				}
  			}
  			
  			$jqToolbar = $this->jquery->toolbar('pages', $arrToolbarItems, array('position' => 'bottom'));

  			$this->tpl->assign_vars(array(
  					'MC_MEDIA_PREVIEW_IMAGE' 		=> $this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  					'MC_MEDIA_PREVIEW_IMAGE_URL' 	=> $this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)),
  					'MC_MEDIA_NAME'					=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'MC_MEDIA_LINK'					=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  					'MC_MEDIA_VIEWS'				=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  					'MC_MEDIA_AUTHOR'				=> $this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  					'MC_MEDIA_DATE'					=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  					'MC_MEDIA_ALBUM'				=> ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? $this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  					'MC_MEDIA_DESCRIPTION'			=> $this->bbcode->toHTML($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  					'MC_MEDIA_TYPE'					=> $intType,
  					'MC_BREADCRUMB'					=> $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryId)),
  					'S_MC_TAGS'						=> (count($arrTags)) ? true : false,
  					'S_NEXT_MEDIA'					=> ($nextID !== false) ? true : false,
  					'S_PREV_MEDIA'					=> ($prevID !== false) ? true : false,
  					'U_NEXT_MEDIA'					=> ($nextID) ? $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($nextID)) : '',
  					'U_PREV_MEDIA'					=> ($prevID) ? $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($prevID)) : '',
  					'MEDIA_NEXT_TITLE'				=> ($nextID) ? $this->pdh->get('mediacenter_media', 'name', array($nextID)) : '',
  					'MEDIA_PREV_TITLE'				=> ($prevID) ? $this->pdh->get('mediacenter_media', 'name', array($prevID)) : '',
  					'MC_MEDIA_SOCIAL_BUTTONS'		=> $this->social->createSocialButtons($this->env->link.$strPath, strip_tags($this->pdh->get('mediacenter_media', 'name', array($intMediaID)))),
  					'MC_MEDIA_RATING'				=> ($arrCategoryData['allow_voting']) ? $this->jquery->starrating($intMediaID, $this->controller_path.'MediaCenter/'.$this->SID.'&mcsavevote&link_hash='.$this->CSRFGetToken('savevote'), array('score' => (($arrArticle['votes_count']) ? round($arrMediaData['votes_sum'] / $arrMediaData['votes_count']): 0), 'number' => 10)) : '',
  					'MC_MEDIA_COMMENTS_COUNTER'		=> ($intCommentsCount == 1 ) ? $intCommentsCount.' '.$this->user->lang('comment') : $intCommentsCount.' '.$this->user->lang('comments'),
  					'S_MC_COMMENTS'					=> ($arrCategoryData['allow_comments']) ? true : false,
  					'MC_COMMENTS'					=> $this->comments->Show(),
  					'MC_TOOLBAR'					=> $jqToolbar['id'],
  					'S_MC_TOOLBAR'					=> ($arrPermissions['create'] || $arrPermissions['update'] || $arrPermissions['delete'] || $arrPermissions['change_state']),
  					
  			));
  			
  			
  			if (count($arrTags) && $arrTags[0] != ""){
  				foreach($arrTags as $tag){
  					$this->tpl->assign_block_vars('tag_row', array(
  							'TAG'	=> $tag,
  							'U_TAG'	=> $this->controller_path.'MediaCenter/Tags/'.$tag,
  					));
  				}
  			}
  			
  			//Update Views
  			if(!$this->env->is_bot($this->user->data['session_browser'])){
  				$this->pdh->put('mediacenter_media', 'update_view', array($intMediaID));
  			}
  			
  			if ($arrPermissions['create'] || $arrPermissions['update']) {
  				$this->jquery->dialog('editMedia', $this->user->lang('mc_edit_media'), array('url' => $this->controller_path."EditMedia/".$this->SID."&aid='+id+'&cid=".$intCategoryID, 'withid' => 'id', 'width' => 920, 'height' => 740, 'onclose'=> $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));
  			}
  				
  			if ($arrPermissions['delete'] || $arrPermissions['change_state']){
  				$this->jquery->dialog('deleteMedia', $this->user->lang('mc_delete_media'), array('custom_js' => 'deleteMediaSubmit(aid);', 'confirm', 'withid' => 'aid', 'message' => $this->user->lang('mc_confirm_delete_media')), 'confirm');
  				$this->tpl->add_js(
  						"function deleteArticleSubmit(aid){
					window.location='".$this->controller_path.$this->page_path.$this->SID.'&mcdelete&link_hash='.$this->CSRFGetToken('delete')."&aid='+aid;
				}"
  				);
  			}
  			
	  		// -- EQDKP ---------------------------------------------------------------
	  		$this->core->set_vars(array (
	  				'page_title'    => $this->pdh->get('mediacenter_media', 'name', array($intMediaID)).' - '.$this->user->lang('mediacenter'),
	  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
	  				'template_file' => 'media.html',
	  				'display'       => true
	  		));
  		} else {
  			message_die($this->user->lang('article_not_found'));
  		}
  	} elseif(isset($arrPathArray[1]) && $arrPathArray[1] === 'tags'){
  		
  		
  		
  		
  		// -- EQDKP ---------------------------------------------------------------
  		$this->core->set_vars(array (
  				'page_title'    => $this->user->lang('mediacenter'),
  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  				'template_file' => 'tags.html',
  				'display'       => true
  		));
  	} elseif (strlen($this->url_id)) {
  		//For Category-View: index.php/MediaCenter/Downloads/
  		//Also Subcategories possible:
  		// index.php/MediaCenter/Blablupp/Sowieso/Downloads/

  		
  		$arrPathParts = registry::get_const('patharray');
  	  		$strCategoryAlias = $this->url_id;
  		if ($strCategoryAlias != $arrPathParts[0]){
  			$strCategoryAlias = $this->url_id = $arrPathParts[0];
  		}
  		
  		$intCategoryId = $this->pdh->get('mediacenter_categories', 'resolve_alias', array($strCategoryAlias));
  		if ($intCategoryId){
  			$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  			pd($arrCategoryData);
  			
  			$intPublished = $arrCategoryData['published'];
  			if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  			
  			//Check Permissions
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  			if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);  			
  			
  			$this->tpl->assign_vars(array(
  				'MC_CATEGORY_NAME'	=> $arrCategoryData['name'],
  				'MC_CATEGORY_ID'	=> $intCategoryId,
  				'MC_BREADCRUMB'		=> $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryId)),
  				'MC_CATEGORY_MEDIA_COUNT' => $this->pdh->get('mediacenter_categories', 'media_count', array($intCategoryId)),
  				'MC_CATEGORY_DESCRIPTION'	=> $this->bbcode->parse_shorttags(xhtml_entity_decode($arrCategoryData['description'])),
  			));
  			
  			$arrChilds = $this->pdh->get('mediacenter_categories', 'childs', array($intCategoryId));
  			foreach($arrChilds as $intChildID){
  				$this->tpl->assign_block_vars('child_row', array(
  						'CATEGORY_NAME' => 	$this->pdh->get('mediacenter_categories', 'name', array($intChildID)),
  						'CATEGORY_ID' => 	$intChildID,
  						'CATEGORY_LINK' => 	$this->controller_path.$this->pdh->get('mediacenter_categories', 'path', array($intChildID)),
  						'MEDIA_COUNT' => 	$this->pdh->get('mediacenter_categories', 'media_count', array($intChildID)),
  						'S_HAS_CHILDS'	=> (count($this->pdh->get('mediacenter_categories', 'childs', array($intChildID))) > 0) ? true : false,
  				));
  			}
  			//Items per Page
  			$intPerPage = $arrCategoryData['per_page'];
  			//Grid or List
  			$intLayout = (int)$arrCategoryData['layout'];
  			  			
  			$hptt_page_settings = array(
  					'name'				=> 'hptt_mc_categorylist',
  					'table_main_sub'	=> '%intMediaID%',
  					'table_subs'		=> array('%intCategoryID%', '%intMediaID%'),
  					'page_ref'			=> 'manage_media.php',
  					'show_numbers'		=> false,
  					//'show_select_boxes'	=> true,
  					'selectboxes_checkall'=>true,
  					'show_detail_twink'	=> false,
  					'table_sort_dir'	=> 'asc',
  					'table_sort_col'	=> 0,
  					'table_presets'		=> array(
  							//array('name' => 'mediacenter_media_editicon',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
  							//array('name' => 'mediacenter_media_published',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  							//array('name' => 'mediacenter_media_featured',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  							array('name' => 'mediacenter_media_previewimage',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
  							array('name' => 'mediacenter_media_frontendlist',		'sort' => true, 'th_add' => '', 'td_add' => ''),
  							//array('name' => 'mediacenter_media_user_id',		'sort' => true, 'th_add' => '', 'td_add' => ''),
  							array('name' => 'mediacenter_media_type','sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  							array('name' => 'mediacenter_media_date','sort' => true, 'th_add' => 'width="20"', 'td_add' => 'nowrap="nowrap"'),
  							//array('name' => 'mediacenter_media_reported',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  							array('name' => 'mediacenter_media_views',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  					),
  			);
  			
  			$start		 = $this->in->get('start', 0);
  			$page_suffix = '&amp;start='.$start;
  			$sort_suffix = '?sort='.$this->in->get('sort');
  			
  			$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, true, true));
  			
  			if (count($arrMediaInCategory)){
  				$view_list = $arrMediaInCategory;
  				$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url%' => 'manage_media.php', '%link_url_suffix%' => '&amp;upd=true'), $intCategoryId.'.0');
  				$hptt->setPageRef($this->strPath);
  				
  				$this->tpl->assign_vars(array(
  					'S_IN_CATEGORY' => true,
  					'S_LAYOUT_LIST' => ($intLayout == 1) ? true : false,
  					'MEDIA_LIST'	=> $hptt->get_html_table($this->in->get('sort'), $page_suffix, $start, $intPerPage, null, false, array('mediacenter_media', 'checkbox_check')),
  					'PAGINATION'	=> generate_pagination($this->strPath.$this->SID.$sort_suffix.$page_suffix, count($view_list), $intPerPage, $start),
  				));
  				
  				$arrRealViewList = $hptt->get_view_list();
  				foreach($arrRealViewList as $intMediaID){
  					$this->tpl->assign_block_vars('mc_media_row', array(
  							'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  							'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)),
  							'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  							'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  							'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  							'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  							'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  							'CATEGORY_AND_ALBUM' => ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  							'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  							'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),	
  					));
  				}
  			}
  			
  			$arrAlbums = $this->pdh->get('mediacenter_albums', 'albums_for_category', array($intCategoryId));
  			
  			foreach($arrAlbums as $intAlbumID){
  				$view_list = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, true));

  				$this->tpl->assign_block_vars('album_list', array(
  						'NAME'				=> $this->pdh->get('mediacenter_albums', 'name', array($intAlbumID)),
  						'LINK'				=> $this->controller_path.$this->pdh->get('mediacenter_albums', 'path', array($intAlbumID)),
  						'S_PERSONAL'		=> $this->pdh->get('mediacenter_albums', 'personal_album', array($intAlbumID)) ? true : false,
  						'S_ALBUM'			=> true,
  						'MEDIA_COUNT'		=> count($view_list),
  						'USER'				=> $this->pdh->get('user', 'name', array($this->pdh->get('mediacenter_albums', 'user_id', array($intAlbumID)))),
  						'ID'				=> $intAlbumID,
  				));
  			}
  			
  			
	  		// -- EQDKP ---------------------------------------------------------------
	  		$this->core->set_vars(array (
	  				'page_title'    => $arrCategoryData['name'].' - '.$this->user->lang('mediacenter'),
	  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
	  				'template_file' => 'category.html',
	  				'display'       => true
	  		));
  			
  			
  		} else {
  			message_die($this->user->lang('article_not_found'));
  		}
  		
  	} else {
  		//-- Index Page of MediaCenter --------------------------------------------
  		$this->tpl->js_file($this->root_path.'plugins/mediacenter/includes/js/responsiveslides.min.js');
  		
  		$this->tpl->add_js('
		$("#slider_mc_featured").responsiveSlides({
	        auto: true,
	        pager: true,
	        nav: true,
	        speed: 3000,
			timeout: 5000,
			pause: true,
			namespace: "mc_featured",
	      });
		', 'docready');
  		
  		
  		//Get Categorys
  		$arrCategories = $this->pdh->get('mediacenter_categories', 'published_id_list', array($this->user->id));
  		foreach($arrCategories as $intCategoryId){
  			if($this->pdh->get('mediacenter_categories', 'parent', array($intCategoryId)) == 0){
  				$this->tpl->assign_block_vars('category_row', array(
  					'CATEGORY_NAME' => 	$this->pdh->get('mediacenter_categories', 'name', array($intCategoryId)),
  					'CATEGORY_ID' => 	$intCategoryId,
  					'CATEGORY_LINK' => 	$this->controller_path.$this->pdh->get('mediacenter_categories', 'path', array($intCategoryId)),
  					'MEDIA_COUNT' => 	$this->pdh->get('mediacenter_categories', 'media_count', array($intCategoryId)),
  					'S_HAS_CHILDS'	=> (count($this->pdh->get('mediacenter_categories', 'childs', array($intCategoryId))) > 0) ? true : false,
  				));
  				
  				$arrChilds = $this->pdh->get('mediacenter_categories', 'childs', array($intCategoryId));
  				foreach($arrChilds as $intChildID){
  					$this->tpl->assign_block_vars('category_row.child_row', array(
  						'CATEGORY_NAME' => 	$this->pdh->get('mediacenter_categories', 'name', array($intChildID)),
  						'CATEGORY_ID' => 	$intChildID,
  						'CATEGORY_LINK' => 	$this->controller_path.$this->pdh->get('mediacenter_categories', 'path', array($intChildID)),
  						'MEDIA_COUNT' => 	$this->pdh->get('mediacenter_categories', 'media_count', array($intChildID)),
  						'S_HAS_CHILDS'	=> (count($this->pdh->get('mediacenter_categories', 'childs', array($intChildID))) > 0) ? true : false,
  					));
  				}
  			}
  		}
  		
  		//Get featured files
  		$arrFeaturedFiles = $this->pdh->get('mediacenter_media', 'featured_media', array());
  		$arrFeaturedFiles = $this->pdh->sort($arrFeaturedFiles, 'mediacenter_media', 'date', 'desc');
  		$arrFeaturedFiles = $this->pdh->limit($arrFeaturedFiles, 0, 5);
  		foreach($arrFeaturedFiles as $intMediaID){
  			$this->tpl->assign_block_vars('mc_featured_row', array(
  				'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  				'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  				'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  				'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  				'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  				'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  				'CATEGORY_AND_ALBUM' => $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true)).((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  				'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  				'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  					
  			));
  		}
  		
  		//Get newest files
  		$arrNewestMedia = $this->pdh->get('mediacenter_media', 'newest_media', array(6));
  		foreach($arrNewestMedia as $intMediaID){
  			$this->tpl->assign_block_vars('mc_newest_row', array(
  				'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1)),
  				'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1, true)),
  				'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  				'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  				'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  				'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  				'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  				'CATEGORY_AND_ALBUM' => $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true)).((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  				'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  				'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  			));
  		}
  		
  		//Get most viewed files
  		$arrMostViewedMedia = $this->pdh->get('mediacenter_media', 'most_viewed', array(6));
  		foreach($arrMostViewedMedia as $intMediaID){
  			$this->tpl->assign_block_vars('mc_mostviewed_row', array(
  					'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1)),
  					'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1, true)),
  					'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  					'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  					'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  					'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  					'CATEGORY_AND_ALBUM' => $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true)).((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  					'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  					'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  			));
  		}
  		
  		//Get last commented files
  		$arrLatestCommentMedia = $this->pdh->get('mediacenter_media', 'last_comments', array(6));
  		foreach($arrLatestCommentMedia as $intMediaID){
  			$this->tpl->assign_block_vars('mc_lastcomments_row', array(
  					'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1)),
  					'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1, true)),
  					'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  					'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  					'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  					'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  					'CATEGORY_AND_ALBUM' => $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true)).((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  					'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  					'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  			));
  		}
  		
  		$this->tpl->assign_vars(array(
  			'S_MC_SHOW_FEATURED' => intval($this->config->get('show_featured', 'mediacenter')) && count($arrFeaturedFiles),
  			'S_MC_SHOW_NEWEST' => intval($this->config->get('show_newest', 'mediacenter')) && count($arrNewestMedia),
  			'S_MC_SHOW_CATEGORIES' => intval($this->config->get('show_featured', 'mediacenter')),
  			'S_MC_SHOW_MOSTVIEWED' => intval($this->config->get('show_mostviewed', 'mediacenter')) && count($arrMostViewedMedia),
  			'S_MC_SHOW_LATESTCOMMENTS' => intval($this->config->get('show_latestcomments', 'mediacenter')) && count($arrLatestCommentMedia),
  		));
  		
  		// -- EQDKP ---------------------------------------------------------------
	  	$this->core->set_vars(array (
	  			'page_title'    => $this->user->lang('mediacenter'),
	  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
	  			'template_file' => 'mediacenter_index.html',
	  			'display'       => true
	  	));
  	}

  }
}
?>