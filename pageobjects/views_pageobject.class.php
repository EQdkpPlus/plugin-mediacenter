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
    
    $this->user->check_auth('u_mediacenter_view');
    
    $handler = array(
    	'myalbums' 			=> array('process' => 'view_myalbums'),
    	'download'			=> array('process' => 'download'),
    	'image'				=> array('process' => 'image'),	
    	'report'			=> array('process' => 'report'),
    	'set_published'		=> array('process' => 'set_published', 'csrf' => true),
    	'set_unpublished'	=> array('process' => 'set_unpublished', 'csrf' => true),
    	'delete_media'		=> array('process' => 'delete', 'csrf' => true),
    	'a'					=> array('process' => 'view_album'),
    );
    parent::__construct(false, $handler, array('mediacenter_media', 'name'), null, 'selected_ids[]');
    
    $this->process();
  }
  

  public function set_unpublished(){
  	$intCategoryId = $this->in->get('category_id', 0);
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  	if (!$arrPermissions['change_state']) {
  		$this->user->check_auth('u_something');
  	}
  	
  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		$arrMedia = array();
  		foreach($this->in->getArray('selected_ids', 'int') as $intMediaID){
  			if($this->pdh->get('mediacenter_media', 'category_id', array($intMediaID)) === $intCategoryId){
  				$arrMedia[] = $intMediaID;
  			}
  		}
  		if(count($arrMedia) === 0) {
  			if($this->in->get('album_id', 0)){
  				$this->view_album();
  			}
  			return;
  		}
  
  		$this->pdh->put('mediacenter_media', 'set_unpublished', array($arrMedia));
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('mc_change_state_unpublish'), $this->user->lang('success'), 'green');
  	}

  	if($this->in->get('album_id', 0)){
  		$this->view_album();
  	}
  }
  
  public function set_published(){
  	$intCategoryId = $this->in->get('category_id', 0);
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  	if (!$arrPermissions['change_state']) $this->user->check_auth('u_something');
  	 
  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		$arrMedia = array();
  		foreach($this->in->getArray('selected_ids', 'int') as $intMediaID){
  		  	//Check if they are in the right category
  			if($this->pdh->get('mediacenter_media', 'category_id', array($intMediaID)) === $intCategoryId){
  				$arrMedia[] = $intMediaID;
  			}
  		}
  		if(count($arrMedia) === 0) {
  			if($this->in->get('album_id', 0)){
  				$this->view_album();
  			}
  			return;
  		}
  
  		$this->pdh->put('mediacenter_media', 'set_published', array($arrMedia));
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('mc_change_state_publish'), $this->user->lang('success'), 'green');
  	}
  	if($this->in->get('album_id', 0)){
  		$this->view_album();
  	}
  }
  
  public function delete(){
  	$retu = array();

  	$intCategoryId = $this->in->get('category_id', 0);
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  	if (!$arrPermissions['change_state']) $this->user->check_auth('u_something');

  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		foreach($this->in->getArray('selected_ids','int') as $id) {
  			//Check if they are in the right category
  			if($this->pdh->get('mediacenter_media', 'category_id', array($id)) !== $intCategoryId){
  				echo "continue";
  				continue;
  			}
  			
  			$pos[] = stripslashes($this->pdh->get('mediacenter_media', 'name', array($id)));
  			$retu[$id] = $this->pdh->put('mediacenter_media', 'delete', array($id));
  		}
  	}
  
  	if(!empty($pos)) {
  		$messages[] = array('title' => $this->user->lang('del_suc'), 'text' => implode(', ', $pos), 'color' => 'green');
  		$this->core->messages($messages);
  	}
  
  	$this->pdh->process_hook_queue();
  	
  	if($this->in->get('album_id', 0)){
  		$this->view_album();
  	}
  }
  
  public function download(){
  	$intMediaID = $this->url_id;
  	
  	$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  	if(count($arrMediaData)){
  		$intMediaID = $this->url_id;
  		$intCategoryId = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  		if(!$arrMediaData['published']) message_die($this->user->lang('article_unpublished'));
  		$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  	
  		$intPublished = $arrCategoryData['published'];
  		if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  	
  		//Check Permissions
  		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  		if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  		if(!$this->pdh->get('mediacenter_media', 'published', array($intMediaID))) message_die($this->user->lang('category_unpublished'));
  		
  		//It's a downloable file
  		if($arrMediaData['type'] === 0){
  			$strExternalFile = $arrMediaData['externalfile'];
  			//If there is an external file, redirect
  			if($strExternalFile != ""){
  				redirect($strExternalFile, false, true, false);
  				exit;
  			}
  			
  			$file = $this->pfh->FolderPath('files', 'mediacenter', 'relative').$arrMediaData['localfile'];
  			$this->pdh->put('mediacenter_media', 'update_download', array($intMediaID));
  			$this->pdh->process_hook_queue();
  			
  			if (file_exists($file)){
  				header('Content-Type: application/octet-stream');
  				header('Content-Length: '.$this->pfh->FileSize($file));
  				header('Content-Disposition: attachment; filename="'.sanitize($arrMediaData['filename']).'"');
  				header('Content-Transfer-Encoding: binary');
  				readfile($file);
  				exit;
  			}
  		}
  	}
  	
  	message_die($this->user->lang('article_unpublished'));
  }
  
  public function image(){
  	$intMediaID = $this->url_id;
  	$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  	if(count($arrMediaData)){
  		$intMediaID = $this->url_id;
  		$intCategoryId = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  		if(!$arrMediaData['published']) message_die($this->user->lang('article_unpublished'));
  		$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  		 
  		$intPublished = $arrCategoryData['published'];
  		if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  		 
  		//Check Permissions
  		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  		if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  		if(!$this->pdh->get('mediacenter_media', 'published', array($intMediaID))) message_die($this->user->lang('category_unpublished'));
  
  		//It's an image
  		if($arrMediaData['type'] === 2){
  			$strExtension = pathinfo($arrMediaData['filename'], PATHINFO_EXTENSION);
  			$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
  			
  			if($this->config->get('watermark_enabled', 'mediacenter') && $strExtension !== 'gif'){
  				$strWatermarkFile = $strThumbfolder.'wm_'.$arrMediaData['previewimage'];
  				if(file_exists($strWatermarkFile)){
	  				$strImage = $strWatermarkFile;
	  			} else {
	  				$this->create_watermark($strThumbfolder.$arrMediaData['previewimage'], $strWatermarkFile);
	  				$strImage = $strWatermarkFile;
	  			}
  			} else {
  				$strImage = $strThumbfolder.$arrMediaData['previewimage'];
  			}
  				
  			if (file_exists($strImage)){
  				switch($strExtension){
  					case 'jpg':
  					case 'jpeg':
  						header('Content-Type: image/jpeg');
  						break;
  					case 'png':
  						header('Content-Type: image/png');
  						break;
  					case 'gif':
  						header('Content-Type: image/gif');
  						break;
  					default: exit;
  				}
  				readfile($strImage);
  				exit;
  			}
  		}
  	}
  	 
  	message_die($this->user->lang('article_unpublished'));
  }
  
  public function report(){
  	if(!$this->user->is_signedin()) $this->user->check_auth('u_something');
  	
  	$intUserID = $this->user->id;
  	$strReason = $this->in->get('reason');
  	$intMediaID = $this->url_id;
  	
  	if(!$this->pdh->get('mediacenter_media', 'reported', array($intMediaID))){
  		$this->pdh->put('mediacenter_media', 'report', array($intMediaID, $strReason, $intUserID));
  		//Get all Admins to notify
  		$arrAdmins = $this->pdh->get('user', 'users_with_permission', array('a_mediacenter_manage'));
  		$intCategory = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  		$strName = $this->pdh->get('mediacenter_media', 'name', array($intMediaID));
  		$strLink = 'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&cid='.$intCategory.'&filter=reported';
  		foreach($arrAdmins as $intUserID){
  			$this->ntfy->add('mediacenter_media_reported', $intMediaID, $this->user->data['username'], $strLink, $intUserID, $strName);
  		}

  		$this->pdh->process_hook_queue();
  	}
  	$this->core->message($this->user->lang('mc_report_success'), $this->user->lang('success'), 'green');
  }

  
  //For URL: index.php/MediaCenter/Downloads/MyAlbumname-a1/
  public function view_album(){

  	$intAlbumID = $this->in->get('a', 0);
  	$arrAlbumData = $this->pdh->get('mediacenter_albums', 'data', array($intAlbumID));
  	$intCategoryId = $arrAlbumData['category_id'];
  	
  	$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  	$intPublished = $arrCategoryData['published'];
  	if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  		
  	//Check Permissions
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  	if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  	
  	
  	if($this->in->exists('map') && (int)$this->config->get('show_maps', 'mediacenter') == 1){
  		$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, (!$blnShowUnpublished)));
  	
  		$intCount = 0;
  		foreach($arrMediaInCategory as $intMediaID){
  			$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
  			if(isset($arrAdditionalData['Longitude']) && isset($arrAdditionalData['Latitude'])){
  				$this->tpl->assign_block_vars('mc_media_row', array(
  						'LNG'			=> $arrAdditionalData['Longitude'],
  						'LAT'			=> $arrAdditionalData['Latitude'],
  						'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  						'LINK'			=> $this->server_path.$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  						'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  				));
  	
  				$intCount++;
  			}
  		}

  		$this->tpl->assign_vars(array(
  				'MC_CATEGORY_NAME'	=> $arrAlbumData['name'],
  				'MC_CATEGORY_ID'	=> $intCategoryId,
  				'MC_BREADCRUMB'		=> $this->pdh->get('mediacenter_albums', 'breadcrumb', array($intAlbumID)),
  				'MC_CATEGORY_MEDIA_COUNT' => $intCount,
  		));
  			
  		// -- EQDKP ---------------------------------------------------------------
  		$this->core->set_vars(array (
  				'page_title'    => $arrCategoryData['name'].' - '.$this->user->lang('mediacenter'),
  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  				'template_file' => 'map.html',
  				'display'       => true
  		));
  	
  	} else {
  		$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, (!$blnShowUnpublished)));
  		$intMapCount = 0;
  		foreach($arrMediaInCategory as $intMediaID){
  			$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
  			if(isset($arrAdditionalData['Longitude']) && isset($arrAdditionalData['Latitude'])){
  				$intMapCount++;
  			}
  		}
  	}
  	
  	
  	
  	//Items per Page
  	$intPerPage = $arrCategoryData['per_page'];
  	//Grid or List
  	$intLayout = ($this->in->exists('layout')) ? $this->in->get('layout', 0) : (int)$arrCategoryData['layout'];
  	
  	$hptt_page_settings = array(
  			'name'				=> 'hptt_mc_categorylist',
  			'table_main_sub'	=> '%intMediaID%',
  			'table_subs'		=> array('%intCategoryID%', '%intMediaID%'),
  			'page_ref'			=> 'manage_media.php',
  			'show_numbers'		=> false,
  			//'show_select_boxes'	=> true,
  			'selectboxes_checkall'=>true,
  			'show_detail_twink'	=> false,
  			'table_sort_dir'	=> 'desc',
  			'table_sort_col'	=> 3,
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
  	
	  	if($arrPermissions['delete'] || $arrPermissions['change_state']){
	  		$hptt_page_settings['show_select_boxes'] = true;
	  	}
  				
  			$start		 = $this->in->get('start', 0);
  			$page_suffix = '&amp;layout='.$intLayout;
  			$sort_suffix = '&sort='.$this->in->get('sort', '3|desc');
  			$strBaseLayoutURL = $this->strPath.$this->SID.'&sort='.$this->in->get('sort', '3|desc').'&start='.$start.'&layout=';
  			$strBaseSortURL = $this->strPath.$this->SID.'&start='.$start.'&layout='.$intLayout.'&sort=';
  			$arrSortOptions = $this->user->lang('mc_sort_options');
  			
  			$blnShowUnpublished = ($arrPermissions['change_state'] || $this->user->check_auth('a_mediacenter_manage', false)) ? true : false;
  				
  			$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, (!$blnShowUnpublished)));
  				
  			if (count($arrMediaInCategory)){
  				$view_list = $arrMediaInCategory;
  				$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array(), 'album'.$intAlbumID);
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
  							'ID'			=> $intMediaID,
  							'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  							'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)),
  							'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  							'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  							'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  							'COMMENTS' 		=> $this->pdh->get('mediacenter_media', 'comment_count', array($intMediaID)),
  							'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  							'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  							'CATEGORY_AND_ALBUM' => ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  							'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  							'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  							'S_PUBLISHED'	=> ($this->pdh->get('mediacenter_media', 'published', array($intMediaID))) ? true : false,
  							'S_CHECKBOX'	=> ($arrPermissions['delete'] || $arrPermissions['change_state']),
  					));
  				}
  			}
 
  			$strPermalink = $this->user->removeSIDfromString($this->env->buildlink().$this->controller_path_plain.$this->pdh->get('mediacenter_albums', 'path', array($intAlbumID, false)));
  	
  			$arrToolbarItems = array();
  			if ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-plus',
  						'js'	=> 'onclick="editMedia(0)"',
  						'title'	=> $this->user->lang('mc_add_media'),
  				);
  					
  				$this->jquery->dialog('editMedia', $this->user->lang('mc_add_media'), array('url' => $this->controller_path."EditMedia/Media-'+id+'/".$this->SID."&aid=".$intAlbumID, 'withid' => 'id', 'width' => 920, 'height' => 740, 'onclose'=> $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));
  			}
  			
  			if ($this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-list',
  						'js'	=> 'onclick="window.location=\''.$this->server_path."plugins/mediacenter/admin/manage_media.php".$this->SID.'&cid='.$intCategoryId.'\';"',
  						'title'	=> $this->user->lang('mc_manage_media'),
  				);
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-pencil',
  						'js'	=> 'onclick="editAlbum('.$intAlbumID.')"',
  						'title'	=> $this->user->lang('mc_edit_album'),
  				);
  				
  				$this->jquery->Dialog('editAlbum', $this->user->lang('mc_edit_album'), array('withid' => 'albumid', 'url'=> $this->controller_path.'EditAlbum/Album-\'+albumid+\'/'.$this->SID.'&simple_head=1', 'width'=>'640', 'height'=>'520', 'onclose' => $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));
  				
  			}
  				
  			$jqToolbar = $this->jquery->toolbar('pages', $arrToolbarItems, array('position' => 'bottom'));
  				
  			$arrMenuItems = array();
  			if ($arrPermissions['delete']){
  				$arrMenuItems[] = array(
  						'name'	=> $this->user->lang('delete'),
  						'type'	=> 'button', //link, button, javascript
  						'icon'	=> 'fa-trash-o',
  						'perm'	=> true,
  						'link'	=> '#del_articles',
  				);
  			}
  				
  			if ($arrPermissions['change_state']){
  				$arrMenuItems[] =  array(
  						'name'	=> $this->user->lang('mc_change_state_publish'),
  						'type'	=> 'button', //link, button, javascript
  						'icon'	=> 'fa-eye',
  						'perm'	=> true,
  						'link'	=> '#set_published',
  				);
  				$arrMenuItems[] = array(
  						'name'	=> $this->user->lang('mc_change_state_unpublish'),
  						'type'	=> 'button', //link, button, javascript
  						'icon'	=> 'fa-eye-slash',
  						'perm'	=> true,
  						'link'	=> '#set_unpublished',
  				);
  			}
  				
  			$this->confirm_delete($this->user->lang('mc_confirm_delete_media'));
  			
  			$this->tpl->assign_vars(array(
  					'MC_CATEGORY_NAME'	=> $arrAlbumData['name'],
  					'MC_CATEGORY_ID'	=> $intCategoryId,
  					'MC_ALBUM_ID'		=> $intAlbumID,
  					'MC_BREADCRUMB'		=> $this->pdh->get('mediacenter_albums', 'breadcrumb', array($intAlbumID)),
  					'MC_CATEGORY_MEDIA_COUNT' => count($arrMediaInCategory),
  					'MC_LAYOUT_DD'		=> new hdropdown('selectlayout', array('options' => $this->user->lang('mc_layout_types'), 'value' => $intLayout, 'id' => 'selectlayout', 'class' => 'dropdown')),
  					'MC_SORT_DD'		=> new hdropdown('selectsort', array('options' => $arrSortOptions, 'value' => $this->in->get('sort', '3|desc'), 'id' => 'selectsort', 'class' => 'dropdown')),
  					'MC_BASEURL_LAYOUT' => $strBaseLayoutURL,
  					'MC_BASEURL_SORT'	=> $strBaseSortURL,
  					'MC_PERMALINK'		=> $strPermalink,
  					'MC_EMBEDD_HTML'	=> htmlspecialchars('<a href="'.$strPermalink.'">'.$this->pdh->get('mediacenter_albums', 'name', array($intAlbumID)).'</a>'),
  					'MC_EMBEDD_BBCODE'	=> htmlspecialchars("[url='".$strPermalink."']".$this->pdh->get('mediacenter_albums', 'name', array($intAlbumID))."[/url]"),
  					'S_MC_TOOLBAR'		=> ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)),
  					'MC_TOOLBAR'		=> $jqToolbar['id'],
  					'MC_BUTTON_MENU'	=> $this->jquery->ButtonDropDownMenu('manage_members_menu', $arrMenuItems, array("input[name=\"selected_ids[]\"]"), '', $this->user->lang('mc_selected_media').'...', ''),
  					'S_MC_BUTTON_MENU'  => (count($arrMenuItems) > 0) ? true : false,
  					'MC_S_PERSONAL_ALBUM' => ($this->pdh->get('mediacenter_albums', 'personal_album', array($intAlbumID)) ? true : false),
  					'L_ALBUM_OWNER'		=> sprintf($this->user->lang('mc_personal_album_info'), $this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_albums', 'user_id', array($intAlbumID)), '', '', true))),
  					'S_SHOW_MAP'		=> ($intMapCount && $this->config->get('show_maps', 'mediacenter')) ? true : false,	
  			));

  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $arrAlbumData['name'].' - '.$this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'album.html',
  			'display'       => true
  	));
  	
  }
  
  private function saveRating(){
  	$this->pdh->put('mediacenter_media', 'vote', array($this->in->get('name'), $this->in->get('score')));
  	$this->pdh->process_hook_queue();
  	die('done');
  }
  
  public function display(){
  	if ($this->in->exists('mcsavevote')){
  		$this->saveRating();
  	}
  	
  	$arrPathArray = registry::get_const('patharray');
  	
  	if (is_numeric($this->url_id)){
  		//For URL: index.php/MediaCenter/Downloads/MyFileName-17.html
  		
  		$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));

  		if($arrMediaData != false && count($arrMediaData)){
  			$strRef = ($this->in->get('ref') != "") ? '&ref='.$this->in->get('ref') : '';
  			$intMediaID = $this->url_id;
  			$intCategoryId = $this->pdh->get('mediacenter_media', 'category_id', array($this->url_id));
  			$intAlbumID = $this->pdh->get('mediacenter_media', 'album_id', array($this->url_id));
  			$blnShowUnpublished = ($arrPermissions['change_state'] || $this->user->check_auth('a_mediacenter_manage', false));
  			
  			if(!$arrMediaData['published'] && !$blnShowUnpublished) message_die($this->user->lang('article_unpublished'));
  			
  			$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  			$intPublished = $arrCategoryData['published'];
  			
  			if (!$intPublished && !$this->user->check_auth('a_mediacenter_manage', false)) message_die($this->user->lang('category_unpublished'));
  				
  			//Check Permissions
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  			if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  	
  			
  			$arrTags = $this->pdh->get('mediacenter_media', 'tags', array($intMediaID));
  			
  			//Create Maincontent
  			$intType = $this->pdh->get('mediacenter_media', 'type', array($intMediaID));
  			$strExtension = strtolower(pathinfo($arrMediaData['filename'], PATHINFO_EXTENSION));
  			$arrPlayableVideos = array('mp4', 'webm', 'ogg');
  			$arrAdditionalData = unserialize($arrMediaData['additionaldata']);
  			
  			$strPermalink = $this->user->removeSIDfromString($this->env->buildlink().$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID, false, array(), false)));
  			
  			if($intType === 0){
   				$this->tpl->assign_vars(array(
  						'MC_MEDIA_DOWNLOADS'=> $arrMediaData['downloads'],
  						'MC_MEDIA_FILENAME'	=> $arrMediaData['filename'],
   						'S_MC_EXTERNALFILEONLY' => ($arrMediaData['filename'] == "" && $arrMediaData['externalfile'] != "") ? true : false,
  						'MC_MEDIA_EXTENSION' => pathinfo($arrMediaData['filename'], PATHINFO_EXTENSION),
  						'MC_MEDIA_SIZE' => human_filesize($arrAdditionalData['size']),
  						'MC_EMBEDD_HTML' => htmlspecialchars('<a href="'.$strPermalink.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a>'),
  						'MC_EMBEDD_BBCODE' => htmlspecialchars("[url='".$strPermalink."']".$this->pdh->get('mediacenter_media', 'name', array($intMediaID))."[/url]"),
  				));

  			} elseif($intType === 1){
  				//Video
  				$blnIsEmbedly = false;
  				if(isset($arrAdditionalData['html'])){
  					//Is embedly Video
  					$strVideo = $arrAdditionalData['html'];
  					$blnIsEmbedly = true;
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
  				
  				$strEmbeddHTML = ($blnIsEmbedly) ? $arrAdditionalData['html'] : '<a href="'.$strPermalink.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a>';
  				$this->tpl->assign_vars(array(
  					'MC_VIDEO'	=> $strVideo,
  					'MC_EMBEDD_HTML' => htmlspecialchars($strEmbeddHTML),
  					'MC_EMBEDD_BBCODE' => htmlspecialchars("[url='".$strPermalink."']".$this->pdh->get('mediacenter_media', 'name', array($intMediaID))."[/url]"),	
  				));
  			} else {
  				//Image
  				$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
  				if(file_exists($strThumbfolder.$arrMediaData['localfile'].'.'.$strExtension)){
  					$strImage = $strThumbfolder.$arrMediaData['localfile'].'.'.$strExtension;
  				} else {
  					$strImage = $this->pfh->FolderPath('files', 'mediacenter', 'relative').$arrMediaData['localfile'];
  				}
  				$arrImageDimesions = getimagesize($strImage);
  				$strImage = str_replace($this->root_path, $this->server_path, $strImage);
  				
  				$strOtherImages = "";
  				$arrOtherFiles = $this->pdh->get('mediacenter_media', 'other_ids', array($intMediaID, (($strRef != "") ? true : false)));
  				$this->jquery->lightbox(md5($intMediaID), array('slideshow' => true, 'transition' => "elastic", 'slideshowSpeed' => 4500, 'slideshowAuto' => false, 'type' => 'photo', 'title_function' => "var url = $(this).data('url');
var title = $(this).attr('title');
if(url == undefined){ url = $(this).attr('href');}
var desc = $(this).data('desc');
if(desc == undefined) { desc = ''; } else { desc = '<br />'+desc;}
return '<a href=\"' + url + '\">'+title+'</a>'+desc;"));
  					
  				foreach($arrOtherFiles as $intFileID){
  					if($intFileID === $intMediaID) continue;
  					
  					if($this->pdh->get('mediacenter_media', 'type', array($intFileID)) === 2){
  						$strName = $this->pdh->get('mediacenter_media', 'name', array($intFileID));
  						$strOtherImage = $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intFileID));
  						$strDesc = $this->pdh->get('mediacenter_media', 'description', array($intFileID));
  						$strDesc = strip_tags($this->bbcode->remove_bbcode($strDesc));
  						
  						$strOtherImages .= '<a href="'.$strOtherImage.'&image" data-url="'.$strOtherImage.'" data-desc="'.$strDesc.'" class="lightbox_'.md5($intMediaID).'" rel="'.md5($intMediaID).'" title="'.sanitize($strName).'"><img src="" /></a>';
  					}
  				}

  				$this->tpl->assign_vars(array(
  					'MC_IMAGE'					=> $strImage,
  					'MC_MEDIA_FILENAME'			=> $arrMediaData['filename'],
  					'MC_MEDIA_IMAGEDIMENSIONS'	=> $arrImageDimesions[0].' x '.$arrImageDimesions[1],
  					'MC_LIGHTBOX'				=> md5($intMediaID),
  					'MC_OTHER_IMAGES'			=> $strOtherImages,
  					'MC_DESC_STRIPPED' 			=> strip_tags($this->bbcode->remove_bbcode($arrMediaData['description'])),
  					'MC_EMBEDD_HTML_BIG' 		=> htmlspecialchars('<a href="'.$strPermalink.'"><img src="'.$strPermalink.'?image" alt="" /></a>'),
  					'MC_EMBEDD_HTML_SMALL' 		=> htmlspecialchars('<a href="'.$strPermalink.'"><img src="'.$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)).'" alt="" /></a>'),
  					'MC_EMBEDD_BBCODE_SMALL' 	=> htmlspecialchars("[url='".$strPermalink."'][img]".$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true))."[/img][/url]"),
  					'MC_EMBEDD_BBCODE_BIG' 		=> htmlspecialchars("[url='".$strPermalink."'][img]".$strPermalink."?image[/img][/url]"),
  				));
  				
  				foreach($arrAdditionalData as $key => $val){
  					if($key === 'size'){
  						$val = human_filesize($val);
  					} elseif($key === 'CreationTime'){
  						if($val === 0) continue;
  						$val = $this->time->createTimeTag((int)$val, $this->time->user_date($val, true));
  					} elseif($key === 'FNumber'){
  						$val = 'f/'.$val;
  					}
  					
  					if($key == 'Longitude' || $key == 'Latitude' || $key == 'Orientation') continue;
  					
  					if(!strlen($val)) continue;
  					
  					$this->tpl->assign_block_vars('mc_more_image_details', array(
  						'LABEL' => $this->user->lang('mc_'.$key),
  						'VALUE'	=> (strlen($val)) ? sanitize($val) : '&nbsp;',	
  					));
  				}
  				
  				if(isset($arrAdditionalData['Longitude']) && isset($arrAdditionalData['Latitude']) && (int)$this->config->get('show_maps', 'mediacenter') == 1) {
  					$this->tpl->assign_vars(array(
  							'S_MC_COORDS' 			=> true,
  							'MC_MEDIA_LONGITUDE'	=> $arrAdditionalData['Longitude'],
  							'MC_MEDIA_LATITUDE'		=> $arrAdditionalData['Latitude'],
  					));
  				}

  				
  			}
  			
  			$nextID = $this->pdh->get('mediacenter_media', 'next_media', array($intMediaID, (($strRef != "") ? true : false)));
  			$prevID = $this->pdh->get('mediacenter_media', 'prev_media', array($intMediaID, (($strRef != "") ? true : false)));
  			
  			
  			$arrInvolvedUser = $this->pdh->get('comment', 'involved_users', array('mediacenter', $intMediaID));
  			$arrInvolvedUser[] = $this->pdh->get('mediacenter_media', 'user_id', array($intMediaID));
  			$arrInvolvedUser = array_unique($arrInvolvedUser);
  			
  			$this->comments->SetVars(array(
  					'attach_id'		=> $intMediaID,
  					'page'			=> 'mediacenter',
  					'auth'			=> 'a_mediacenter_manage',
  					'ntfy_type' 	=> 'mediacenter_media_comment_new',
  					'ntfy_title'	=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'ntfy_link' 	=> $this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  					'ntfy_user'		=> $arrInvolvedUser,
  			));
  				
  			$intCommentsCount = $this->comments->Count();
  			
  			//Reset Notifications
  			if($this->user->is_signedin()){
  				$arrCommentIDs = $this->pdh->get('comment', 'filtered_list', array('mediacenter', $intMediaID));
  				if(count($arrCommentIDs)) $this->ntfy->markAsRead('mediacenter_media_comment_new', $this->user->id, array_keys($arrCommentIDs));
  				
  				$this->ntfy->markAsRead('mediacenter_media_new', $this->user->id, $intMediaID);
  			}
  			
  			$arrToolbarItems = array();
  			if ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-plus',
  						'js'	=> 'onclick="editMedia(0)"',
  						'title'	=> $this->user->lang('mc_add_media'),
  				);
  			}
  			
  			if ($arrPermissions['update']  || $this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-pencil-square-o',
  						'js'	=> 'onclick="editMedia('.$intMediaID.')"',
  						'title'	=> $this->user->lang('mc_edit_media'),
  				);
  			}
  			
  			if ($arrPermissions['delete']) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-trash-o',
  						'js'	=> 'onclick="$(\'#del_articles\').click()"',
  						'title'	=> $this->user->lang('mc_delete_media'),
  				);
  			}
  			if ($arrPermissions['change_state']) {
  				if ($this->pdh->get('mediacenter_media', 'published', array($intMediaID))){
  					$arrToolbarItems[] = array(
  							'icon'	=> 'fa-eye-slash',
  							'js'	=> 'onclick="$(\'#set_unpublished\').click()"',
  							'title'	=> $this->user->lang('article_unpublish'),
  					);
  				} else {
  					$arrToolbarItems[] = array(
  							'icon'	=> 'fa-eye',
  							'js'	=> 'onclick="$(\'#set_published\').click()"',
  							'title'	=> $this->user->lang('article_publish'),
  					);
  				}
  			}
  			
  			$this->confirm_delete($this->user->lang('mc_confirm_delete_media'));
  			
  			$jqToolbar = $this->jquery->toolbar('pages', $arrToolbarItems, array('position' => 'bottom'));

  			$strAlbumEditID = ($this->pdh->get('mediacenter_media', 'album_id', array($intMediaID))) ? $this->pdh->get('mediacenter_media', 'album_id', array($intMediaID)) : 'c'.$intCategoryId;

  			$this->tpl->assign_vars(array(
  					'MC_MEDIA_PREVIEW_IMAGE' 		=> $this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, false, 'mcPreviewImageBig')),
  					'MC_MEDIA_PREVIEW_IMAGE_URL' 	=> $this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)),
  					'MC_MEDIA_NAME'					=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  					'MC_MEDIA_LINK'					=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  					'MC_MEDIA_VIEWS'				=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  					'MC_MEDIA_AUTHOR'				=> $this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  					'MC_MEDIA_DATE'					=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  					'MC_MEDIA_ALBUM'				=> ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? $this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  					'MC_MEDIA_DESCRIPTION'			=> $this->bbcode->toHTML($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  					'MC_MEDIA_TYPE'					=> $intType,
  					'MC_BREADCRUMB'					=> ($intAlbumID) ? str_replace('class="current"', '', $this->pdh->get('mediacenter_albums', 'breadcrumb', array($intAlbumID))) : str_replace('class="current"', '', $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryId))),
  					'S_MC_TAGS'						=> (count($arrTags)) ? true : false,
  					'S_NEXT_MEDIA'					=> ($nextID !== false) ? true : false,
  					'S_PREV_MEDIA'					=> ($prevID !== false) ? true : false,
  					'U_NEXT_MEDIA'					=> ($nextID) ? $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($nextID)).$strRef : '',
  					'U_PREV_MEDIA'					=> ($prevID) ? $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($prevID)).$strRef : '',
  					'MEDIA_NEXT_TITLE'				=> ($nextID) ? $this->pdh->get('mediacenter_media', 'name', array($nextID)) : '',
  					'MEDIA_PREV_TITLE'				=> ($prevID) ? $this->pdh->get('mediacenter_media', 'name', array($prevID)) : '',
  					'MC_MEDIA_SOCIAL_BUTTONS'		=> $this->social->createSocialButtons($this->env->link.$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)), strip_tags($this->pdh->get('mediacenter_media', 'name', array($intMediaID)))),
  					'MC_MEDIA_RATING'				=> ($arrCategoryData['allow_voting']) ? $this->jquery->starrating($intMediaID, $this->controller_path.'MediaCenter/'.$this->SID.'&mcsavevote&link_hash='.$this->CSRFGetToken('savevote'), array('score' => (($arrMediaData['votes_count']) ? round($arrMediaData['votes_sum'] / $arrMediaData['votes_count']): 0), 'number' => 10)) : '',
  					'MC_MEDIA_COMMENTS_COUNTER'		=> ($intCommentsCount == 1 ) ? $intCommentsCount.' '.$this->user->lang('comment') : $intCommentsCount.' '.$this->user->lang('comments'),
  					'S_MC_COMMENTS'					=> ($arrCategoryData['allow_comments']) ? true : false,
  					'MC_COMMENTS'					=> $this->comments->Show(),
  					'MC_TOOLBAR'					=> $jqToolbar['id'],
  					'S_MC_TOOLBAR'					=> ($arrPermissions['create'] || $arrPermissions['update'] || $arrPermissions['delete'] || $arrPermissions['change_state']),
  					'MC_MEDIA_ID'					=> $intMediaID,
  					'MC_CATEGORY_ID'				=> $intCategoryId,
  					'MC_PERMALINK'					=> $strPermalink,
  					'MC_S_PUBLISHED'				=> ($this->pdh->get('mediacenter_media', 'published', array($intMediaID)) ? true : false),
  			));
  			
  			$this->social->callSocialPlugins($this->pdh->get('mediacenter_media', 'name', array($intMediaID)), truncate($this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))), 200), $this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)));
  			
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
  				$this->jquery->dialog('editMedia', $this->user->lang('mc_edit_media'), array('url' => $this->controller_path."EditMedia/Media-'+id+'/".$this->SID."&aid=".$strAlbumEditID, 'withid' => 'id', 'width' => 920, 'height' => 740, 'onclose'=> $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));
  			}
  				
  			if ($arrPermissions['delete'] || $arrPermissions['change_state']){
  				$this->jquery->dialog('deleteMedia', $this->user->lang('mc_delete_media'), array('custom_js' => 'deleteMediaSubmit(aid);', 'confirm', 'withid' => 'aid', 'message' => $this->user->lang('mc_confirm_delete_media')), 'confirm');
  				$this->tpl->add_js(
  						"function deleteMediaSubmit(aid){
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
  			redirect($this->controller_path_plain.'MediaCenter/'.$this->SID);
  		}
  	} elseif(isset($arrPathArray[1]) && $arrPathArray[1] === 'tags'){
  		
  		$strTag = $this->url_id;

  		//Items per Page
  		$intPerPage = $this->config->get('per_page', 'mediacenter');
  		//Grid or List
  		$intLayout = ($this->in->exists('layout')) ? $this->in->get('layout', 0) : 1;
  		 
  		$hptt_page_settings = array(
  				'name'				=> 'hptt_mc_categorylist',
  				'table_main_sub'	=> '%intMediaID%',
  				'table_subs'		=> array('%intCategoryID%', '%intMediaID%'),
  				'page_ref'			=> 'manage_media.php',
  				'show_numbers'		=> false,
  				//'show_select_boxes'	=> true,
  				'selectboxes_checkall'=>true,
  				'show_detail_twink'	=> false,
  				'table_sort_dir'	=> 'desc',
  				'table_sort_col'	=> 3,
  				'table_presets'		=> array(
  				array('name' => 'mediacenter_media_previewimage',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
  				array('name' => 'mediacenter_media_frontendlist',		'sort' => true, 'th_add' => '', 'td_add' => ''),
  				array('name' => 'mediacenter_media_type','sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  				array('name' => 'mediacenter_media_date','sort' => true, 'th_add' => 'width="20"', 'td_add' => 'nowrap="nowrap"'),
  				array('name' => 'mediacenter_media_views',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
  		),
  		);
  		
  				$start		 = $this->in->get('start', 0);
  				$page_suffix = '&amp;layout='.$intLayout;
  				$sort_suffix = '&sort='.$this->in->get('sort', '3|desc');
  				$strBaseLayoutURL = $this->strPath.$this->SID.'&sort='.$this->in->get('sort', '3|desc').'&start='.$start.'&layout=';
  				$strBaseSortURL = $this->strPath.$this->SID.'&start='.$start.'&layout='.$intLayout.'&sort=';
  				$arrSortOptions = $this->user->lang('mc_sort_options');
  		
  				$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_tags', array($strTag));
  		
  				if (count($arrMediaInCategory)){
  					$view_list = $arrMediaInCategory;
  					$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array(), 'tag'.md5($strTag));
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
  								'COMMENTS' 		=> $this->pdh->get('mediacenter_media', 'comment_count', array($intMediaID)),
  								'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  								'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  								'CATEGORY_AND_ALBUM' => ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  								'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  								'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  						));
  					}
  				}
  		 
  				$this->tpl->assign_vars(array(
  						'MC_CATEGORY_NAME'			=> ucfirst(sanitize($strTag)),
  						'MC_CATEGORY_MEDIA_COUNT'	=> count($arrMediaInCategory),
  						'MC_LAYOUT_DD'				=> new hdropdown('selectlayout', array('options' => $this->user->lang('mc_layout_types'), 'value' => $intLayout, 'id' => 'selectlayout', 'class' => 'dropdown')),
  						'MC_SORT_DD'				=> new hdropdown('selectsort', array('options' => $arrSortOptions, 'value' => $this->in->get('sort', '3|desc'), 'id' => 'selectsort', 'class' => 'dropdown')),
  						'MC_BASEURL_LAYOUT' 		=> $strBaseLayoutURL,
  						'MC_BASEURL_SORT'			=> $strBaseSortURL,
  				));
  		
  		// -- EQDKP ---------------------------------------------------------------
  		$this->core->set_vars(array (
  				'page_title'    => ucfirst(sanitize($strTag)).' - '.$this->user->lang('mediacenter'),
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
  			
  			$intPublished = $arrCategoryData['published'];
  			if (!$intPublished) message_die($this->user->lang('category_unpublished'));
  			
  			//Check Permissions
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  			if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);  			
  			
  			if($this->in->exists('map') && (int)$this->config->get('show_maps', 'mediacenter') == 1){
  				$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, (($blnShowUnpublished) ? false : true), true));
  				
  				$intCount = 0;
  				foreach($arrMediaInCategory as $intMediaID){
  					$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
  					if(isset($arrAdditionalData['Longitude']) && isset($arrAdditionalData['Latitude'])){
  						$this->tpl->assign_block_vars('mc_media_row', array(
  								'LNG'			=> $arrAdditionalData['Longitude'],
  								'LAT'			=> $arrAdditionalData['Latitude'],
  								'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  								'LINK'			=> $this->server_path.$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)),
  								'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  						));
  						
  						$intCount++;
  					}
  				}
  				
  				$this->tpl->assign_vars(array(
  						'MC_CATEGORY_NAME'	=> $arrCategoryData['name'],
  						'MC_CATEGORY_ID'	=> $intCategoryId,
  						'MC_BREADCRUMB'		=> $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryId)),
  						'MC_CATEGORY_MEDIA_COUNT' => $intCount,
  				));	
  					
  				// -- EQDKP ---------------------------------------------------------------
  				$this->core->set_vars(array (
  						'page_title'    => $arrCategoryData['name'].' - '.$this->user->lang('mediacenter'),
  						'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  						'template_file' => 'map.html',
  						'display'       => true
  				));
  				
  			} else {
  				$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, (($blnShowUnpublished) ? false : true), true));
  				$intMapCount = 0;
  				foreach($arrMediaInCategory as $intMediaID){
  					$arrAdditionalData = $this->pdh->get('mediacenter_media', 'additionaldata', array($intMediaID));
  					if(isset($arrAdditionalData['Longitude']) && isset($arrAdditionalData['Latitude'])){
  						$intMapCount++;
  					}
  				}				
  			}
  			
  			
  			
  			$blnShowUnpublished = ($arrPermissions['change_state'] || $this->user->check_auth('a_mediacenter_manage', false));
  			
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
  			$intLayout = ($this->in->exists('layout')) ? $this->in->get('layout', 0) : (int)$arrCategoryData['layout'];
  			  			
  			$hptt_page_settings = array(
  					'name'				=> 'hptt_mc_categorylist',
  					'table_main_sub'	=> '%intMediaID%',
  					'table_subs'		=> array('%intCategoryID%', '%intMediaID%'),
  					'page_ref'			=> 'manage_media.php',
  					'show_numbers'		=> false,
  					//'show_select_boxes'	=> true,
  					'selectboxes_checkall'=>true,
  					'show_detail_twink'	=> false,
  					'table_sort_dir'	=> 'desc',
  					'table_sort_col'	=> 3,
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
  			
  			if($arrPermissions['delete'] || $arrPermissions['change_state']){
	  			$hptt_page_settings['show_select_boxes'] = true;
	  		}
  			
  			$start		 = $this->in->get('start', 0);
  			$page_suffix = '&amp;layout='.$intLayout;
  			$sort_suffix = '&sort='.$this->in->get('sort', '3|desc');
  			$strBaseLayoutURL = $this->strPath.$this->SID.'&sort='.$this->in->get('sort', '3|desc').'&start='.$start.'&layout=';
  			$strBaseSortURL = $this->strPath.$this->SID.'&start='.$start.'&layout='.$intLayout.'&sort=';
  			$arrSortOptions = $this->user->lang('mc_sort_options');
  			
  			$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, (($blnShowUnpublished) ? false : true), true));
  			
  			if (count($arrMediaInCategory)){
  				$view_list = $arrMediaInCategory;
  				$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url_suffix%' => '&amp;ref=cat'), 'cat_'.$intCategoryId.'.0');
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
  							'ID'			=> $intMediaID,
  							'PREVIEW_IMAGE' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)),
  							'PREVIEW_IMAGE_URL' => 	$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2, true)),
  							'NAME'			=> $this->pdh->get('mediacenter_media', 'name', array($intMediaID)),
  							'LINK'			=> $this->controller_path.$this->pdh->get('mediacenter_media', 'path', array($intMediaID)).'&ref=cat',
  							'VIEWS'			=> $this->pdh->get('mediacenter_media', 'views', array($intMediaID)),
  							'COMMENTS' 		=> $this->pdh->get('mediacenter_media', 'comment_count', array($intMediaID)),
  							'AUTHOR'		=> $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true)),
  							'DATE'			=> $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID))),
  							'CATEGORY_AND_ALBUM' => ((strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? ' &bull; '.$this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): ''),
  							'DESCRIPTION'	=> $this->bbcode->remove_bbcode($this->pdh->get('mediacenter_media', 'description', array($intMediaID))),
  							'TYPE'			=> $this->pdh->geth('mediacenter_media', 'type', array($intMediaID)),
  							'S_PUBLISHED'	=> ($this->pdh->get('mediacenter_media', 'published', array($intMediaID))) ? true : false,
  							'S_CHECKBOX'	=> ($arrPermissions['delete'] || $arrPermissions['change_state']),
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
  			$strPermalink = $this->user->removeSIDfromString($this->env->buildlink().$this->controller_path_plain.$this->pdh->get('mediacenter_categories', 'path', array($intCategoryId, false)));
  			
  			$arrToolbarItems = array();
  			if ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-plus',
  						'js'	=> 'onclick="editMedia(0)"',
  						'title'	=> $this->user->lang('mc_add_media'),
  				);
  					
  				$this->jquery->dialog('editMedia', $this->user->lang('mc_add_media'), array('url' => $this->controller_path."EditMedia/Media-'+id+'/".$this->SID."&aid=c".$intCategoryId, 'withid' => 'id', 'width' => 920, 'height' => 740, 'onclose'=> $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));
  			}
  				
  			if ($this->user->check_auth('a_mediacenter_manage', false)) {
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-list',
  						'js'	=> 'onclick="window.location=\''.$this->server_path."plugins/mediacenter/admin/manage_media.php".$this->SID.'&cid='.$intCategoryId.'\';"',
  						'title'	=> $this->user->lang('mc_manage_media'),
  				);
  				$arrToolbarItems[] = array(
  						'icon'	=> 'fa-pencil',
  						'js'	=> 'onclick="window.location=\''.$this->server_path."plugins/mediacenter/admin/manage_categories.php".$this->SID.'&cid='.$intCategoryId.'\';"',
  						'title'	=> $this->user->lang('mc_manage_category'),
  				);
  			}
  			
  			$jqToolbar = $this->jquery->toolbar('pages', $arrToolbarItems, array('position' => 'bottom'));
  			
  			$arrMenuItems = array();
  			if ($arrPermissions['delete']){
  				$arrMenuItems[] = array(
  					'name'	=> $this->user->lang('delete'),
  					'type'	=> 'button', //link, button, javascript
  					'icon'	=> 'fa-trash-o',
  					'perm'	=> true,
  					'link'	=> '#del_articles',
  				);
  			}
  			
  			if ($arrPermissions['change_state']){
  				$arrMenuItems[] =  array(
  					'name'	=> $this->user->lang('mc_change_state_publish'),
  					'type'	=> 'button', //link, button, javascript
  					'icon'	=> 'fa-eye',
  					'perm'	=> true,
  					'link'	=> '#set_published',
  				);
  				$arrMenuItems[] = array(
  					'name'	=> $this->user->lang('mc_change_state_unpublish'),
  					'type'	=> 'button', //link, button, javascript
  					'icon'	=> 'fa-eye-slash',
  					'perm'	=> true,
  					'link'	=> '#set_unpublished',
  				);
  			}
  			
  			$this->confirm_delete($this->user->lang('mc_confirm_delete_media'));

  			$this->tpl->assign_vars(array(
  					'MC_CATEGORY_NAME'	=> $arrCategoryData['name'],
  					'MC_CATEGORY_ID'	=> $intCategoryId,
  					'MC_BREADCRUMB'		=> $this->pdh->get('mediacenter_categories', 'breadcrumb', array($intCategoryId)),
  					'MC_CATEGORY_MEDIA_COUNT' => $this->pdh->get('mediacenter_categories', 'media_count', array($intCategoryId)),
  					'MC_CATEGORY_DESCRIPTION'	=> $this->bbcode->parse_shorttags(xhtml_entity_decode($arrCategoryData['description'])),
  					'MC_LAYOUT_DD'	=> new hdropdown('selectlayout', array('options' => $this->user->lang('mc_layout_types'), 'value' => $intLayout, 'id' => 'selectlayout', 'class' => 'dropdown')),
  					'MC_SORT_DD'	=> new hdropdown('selectsort', array('options' => $arrSortOptions, 'value' => $this->in->get('sort', '3|desc'), 'id' => 'selectsort', 'class' => 'dropdown')),
  					'MC_BASEURL_LAYOUT' => $strBaseLayoutURL,
  					'MC_BASEURL_SORT'	=> $strBaseSortURL,
  					'MC_PERMALINK'		=> $strPermalink,
  					'MC_EMBEDD_HTML' => htmlspecialchars('<a href="'.$strPermalink.'">'.$this->pdh->get('mediacenter_categories', 'name', array($intCategoryId)).'</a>'),
  					'MC_EMBEDD_BBCODE' => htmlspecialchars("[url='".$strPermalink."']".$this->pdh->get('mediacenter_categories', 'name', array($intCategoryId))."[/url]"),
  					'S_MC_TOOLBAR'		=> ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)),
  					'MC_TOOLBAR'		=> $jqToolbar['id'],
  					'MC_BUTTON_MENU'	=> $this->jquery->ButtonDropDownMenu('manage_members_menu', $arrMenuItems, array("input[name=\"selected_ids[]\"]"), '', $this->user->lang('mc_selected_media').'...', ''),
  					'S_MC_BUTTON_MENU'  => (count($arrMenuItems) > 0) ? true : false,
  					'S_SHOW_MAP'		=> ($intMapCount && $this->config->get('show_maps', 'mediacenter')) ? true : false,
  			));
  			
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
  					'S_MC_TOOLBAR'	=> ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)),
  					'MC_TOOLBAR'	=> $jqToolbar['id'],
  			));
  		}
  		
  		//Get Best rated files
  		$arrBestRatedMedia = $this->pdh->get('mediacenter_media', 'best_rated', array(6));

  		foreach($arrBestRatedMedia as $intMediaID){
  			$this->tpl->assign_block_vars('mc_bestrated_row', array(
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
  					'S_MC_TOOLBAR'	=> ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)),
  					'MC_TOOLBAR'	=> $jqToolbar['id'],
  			));
  		}
  		
  		$arrToolbarItems = array();
  		if ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)) {
  			$arrToolbarItems[] = array(
  					'icon'	=> 'fa-plus',
  					'js'	=> 'onclick="editMedia(0)"',
  					'title'	=> $this->user->lang('mc_add_media'),
  			);
  			
  			$this->jquery->dialog('editMedia', $this->user->lang('mc_add_media'), array('url' => $this->controller_path."EditMedia/Media-'+id+'/".$this->SID, 'withid' => 'id', 'width' => 920, 'height' => 740, 'onclose'=> $this->env->link.$this->controller_path_plain.$this->page_path.$this->SID));

  		}
  			
  		if ($this->user->check_auth('a_mediacenter_manage', false)) {
  			$arrToolbarItems[] = array(
					'icon'	=> 'fa-list',
					'js'	=> 'onclick="window.location=\''.$this->server_path."plugins/mediacenter/admin/manage_categories.php".$this->SID.'\';"',
					'title'	=> $this->user->lang('mc_manage_media'),
			);
  		}

  			
  		$jqToolbar = $this->jquery->toolbar('pages', $arrToolbarItems, array('position' => 'bottom'));
  		
  		$arrStats = $this->pdh->get('mediacenter_media', 'statistics');
  		foreach($arrStats as $key => $val){
  			if($key === 'size') $val = human_filesize($val);
  			$this->tpl->assign_vars(array(
  				'S_MC_STATS_'.strtoupper($key) => sprintf($this->user->lang('mc_statistics_'.$key), $val),
  			));
  		}
  		
  		$this->tpl->assign_vars(array(
  			'S_MC_SHOW_FEATURED'	=> intval($this->config->get('show_featured', 'mediacenter')) && count($arrFeaturedFiles),
  			'S_MC_SHOW_NEWEST'		=> intval($this->config->get('show_newest', 'mediacenter')) && count($arrNewestMedia),
  			'S_MC_SHOW_CATEGORIES'	=> intval($this->config->get('show_categories', 'mediacenter')),
  			'S_MC_SHOW_BESTRATED'	=> intval($this->config->get('show_bestrated', 'mediacenter')) && count($arrBestRatedMedia),
  			'S_MC_SHOW_MOSTVIEWED'	=> intval($this->config->get('show_mostviewed', 'mediacenter')) && count($arrMostViewedMedia),
  			'S_MC_SHOW_LATESTCOMMENTS' => intval($this->config->get('show_latestcomments', 'mediacenter')) && count($arrLatestCommentMedia),
  			'S_MC_TOOLBAR'			=> ($arrPermissions['create'] || $this->user->check_auth('a_mediacenter_manage', false)),
  			'MC_TOOLBAR'			=> $jqToolbar['id'],
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
  
  private function create_watermark($image, $dest){
  		
  	//Image
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		return false;
  	}
  		
  	switch($imageInfo[2]){
  		case 1:	return true;	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  		
  	//Watermark Logo
  	$strWatermarkImage = $this->root_path.$this->config->get('watermark_logo', 'mediacenter');
  	$logoInfo = getimagesize($strWatermarkImage);
  	if (!$logoInfo) return false;
  		
  	switch($logoInfo[2]){
  		case 1:	$imgLogo = ImageCreateFromGIF($strWatermarkImage);	break;	// GIF
  		case 2:	$imgLogo = ImageCreateFromJPEG($strWatermarkImage);	break;	// JPG
  		case 3:
  			$imgLogo = ImageCreateFromPNG($strWatermarkImage);
  			imageAlphaBlending($imgLogo, false);
  			imageSaveAlpha($imgLogo, true);
  			break;	// PNG
  	}
  		
  	$margin = 10;
  	$sx = imagesx($imgLogo);
  	$sy = imagesy($imgLogo);
  		
  	switch($this->config->get('watermark_position', 'mediacenter')){
  		case 'rt': $dst_x = imagesx($imgOld) - $sx - $margin; $dst_y = 10;
  		break;
  		case 'rb': $dst_x = imagesx($imgOld) - $sx - $margin; $dst_y = imagesy($imgOld) - $sy - $margin;
  		break;
  		case 'lb': $dst_x = 10; $dst_y = imagesy($imgOld) - $sy - $margin;
  		break;
  		case 'lt': $dst_x = $margin; $dst_y = $margin;
  		break;
  	}
  		
  	$intTransparency = (100 - $this->config->get('watermark_transparency', 'mediacenter'));
  	if ($intTransparency > 100 || $intTransparency < 0) $intTransparency = 100;
  	
  	$result = imagecopymerge($imgOld, $imgLogo, $dst_x, $dst_y, 0, 0, $sx, $sy, $intTransparency);
  
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($imgOld,	$dest);	break;	// GIF
  		case 2:	ImageJPEG($imgOld,	$dest, 100);	break;	// JPG
  		case 3:	ImagePNG($imgOld,	$dest, 0);	break;	// PNG
  	}
  		
  	imagedestroy($imgOld);
  	imagedestroy($imgLogo);
  		
  	return true;
  }
  
}
?>