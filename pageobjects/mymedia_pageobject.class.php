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


class mymedia_pageobject extends pageobject {
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
    if(!$this->user->is_signedin()) $this->user->check_auth('u_something');
    
    $handler = array(
    		'set_published'		=> array('process' => 'set_published', 'csrf' => true),
    		'set_unpublished'	=> array('process' => 'set_unpublished', 'csrf' => true),
    		'delete_media'		=> array('process' => 'delete', 'csrf' => true),
    );
    parent::__construct(false, $handler, array('mediacenter_media', 'name'), null, 'selected_ids[]');

    $this->process();
  }
  
  public function set_unpublished(){
  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		$arrMedia = array();
  		foreach($this->in->getArray('selected_ids', 'int') as $intMediaID){
  			if($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)) === $this->user->id){
  				$arrMedia[] = $intMediaID;
  			}
  		}
  		if(count($arrMedia) === 0) return;
  		
  		$this->pdh->put('mediacenter_media', 'set_unpublished', array($arrMedia));
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('mc_change_state_unpublish'), $this->user->lang('success'), 'green');
  	}
  }
  
  public function set_published(){
  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		$arrMedia = array();
  		foreach($this->in->getArray('selected_ids', 'int') as $intMediaID){
  			if($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)) === $this->user->id){
  				$arrMedia[] = $intMediaID;
  			}
  		}
  		if(count($arrMedia) === 0) return;
  		
  		$this->pdh->put('mediacenter_media', 'set_published', array($arrMedia));
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('mc_change_state_publish'), $this->user->lang('success'), 'green');
  	}
  }
  
  public function delete(){
  	$retu = array();
  
  	if(count($this->in->getArray('selected_ids', 'int')) > 0) {
  		foreach($this->in->getArray('selected_ids','int') as $id) {
  			if($this->pdh->get('mediacenter_media', 'user_id', array($id)) !== $this->user->id) continue;
  			
  			$pos[] = stripslashes($this->pdh->get('mediacenter_media', 'name', array($id)));
  			$retu[$id] = $this->pdh->put('mediacenter_media', 'delete', array($id));
  		}
  	}
  
  	if(!empty($pos)) {
  		$messages[] = array('title' => $this->user->lang('del_suc'), 'text' => implode(', ', $pos), 'color' => 'green');
  		$this->core->messages($messages);
  	}
  
  	$this->pdh->process_hook_queue();
  }

  public function display(){
  	
  	$arrAlbums = $this->pdh->get('mediacenter_albums', 'my_albums', array($this->user->id));
  	foreach($arrAlbums as $intAlbumID){
  		$intCategoryId = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
  		
  		//Check Permissions
  		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
  		if (!$arrPermissions['read']) message_die($this->user->lang('category_noauth'), $this->user->lang('noauth_default_title'), 'access_denied', true);
  		
  		$view_list = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID, false));
  		
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
  			'show_select_boxes'	=> true,
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
  			array('name' => 'mediacenter_media_editicon',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
  	));
  				
  			$start		 = $this->in->get('start', 0);
  			$page_suffix = '&amp;layout='.$intLayout;
  			$sort_suffix = '&sort='.$this->in->get('sort', '3|desc');
  			$strBaseLayoutURL = $this->strPath.$this->SID.'&sort='.$this->in->get('sort', '3|desc').'&start='.$start.'&layout=';
  			$strBaseSortURL = $this->strPath.$this->SID.'&start='.$start.'&layout='.$intLayout.'&sort=';
  			$arrSortOptions = $this->user->lang('mc_sort_options');
  				
  			$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'my_media', array($this->user->id));
  				
  			if (count($arrMediaInCategory)){
  				$view_list = $arrMediaInCategory;
  				$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url%' => 'manage_media.php', '%link_url_suffix%' => '&amp;upd=true'), 'album'.$intAlbumID);
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
  					));
  				}
  			}
  			
  			$arrMenuItems = array(
  					0 => array(
  							'name'	=> $this->user->lang('delete'),
  							'type'	=> 'button', //link, button, javascript
  							'icon'	=> 'fa-trash-o',
  							'perm'	=> true,
  							'link'	=> '#del_articles',
  					),
  					1 => array(
  							'name'	=> $this->user->lang('mc_change_state_publish'),
  							'type'	=> 'button', //link, button, javascript
  							'icon'	=> 'fa-eye',
  							'perm'	=> true,
  							'link'	=> '#set_published',
  					),
  					2 => array(
  							'name'	=> $this->user->lang('mc_change_state_unpublish'),
  							'type'	=> 'button', //link, button, javascript
  							'icon'	=> 'fa-eye-slash',
  							'perm'	=> true,
  							'link'	=> '#set_unpublished',
  					),
  			);
  			
  			$this->jquery->Dialog('addmedia', $this->user->lang('mc_add_media'), array('withid' => 'albumid', 'url'=> $this->controller_path.'AddMedia/'.$this->SID.'&simple_head=1&aid=\'+albumid+\'', 'width'=>'900', 'height'=>'780', 'onclose' => $this->env->buildlink().$this->routing->build('MyMedia', '', '', true, true)));
  			$this->jquery->Dialog('editmedia', $this->user->lang('mc_edit_media'), array('withid' => 'mediaid', 'url'=> $this->controller_path.'EditMedia/Media-\'+mediaid+\'/'.$this->SID.'&simple_head=1', 'width'=>'900', 'height'=>'780', 'onclose' => $this->env->buildlink().$this->routing->build('MyMedia', '', '', true, true)));
  			$this->jquery->Dialog('addalbum', $this->user->lang('mc_new_album'), array('url'=> $this->controller_path.'AddAlbum/'.$this->SID.'&simple_head=1&cid=0', 'width'=>'640', 'height'=>'520', 'onclose' => $this->env->buildlink().$this->routing->build('MyMedia', '', '', true, true)));
  			$this->confirm_delete($this->user->lang('mc_confirm_delete_media'));
 	
  			$this->tpl->assign_vars(array(
  					'MC_CATEGORY_NAME'	=> $this->user->lang('mc_mymedia'),
  					'MC_CATEGORY_MEDIA_COUNT' => count($arrMediaInCategory),
  					'MC_LAYOUT_DD'		=> new hdropdown('selectlayout', array('options' => $this->user->lang('mc_layout_types'), 'value' => $intLayout, 'id' => 'selectlayout', 'class' => 'dropdown')),
  					'MC_SORT_DD'		=> new hdropdown('selectsort', array('options' => $arrSortOptions, 'value' => $this->in->get('sort', '3|desc'), 'id' => 'selectsort', 'class' => 'dropdown')),
  					'MC_BASEURL_LAYOUT' => $strBaseLayoutURL,
  					'MC_BASEURL_SORT'	=> $strBaseSortURL,
  					'MC_BUTTON_MENU'	=> $this->jquery->ButtonDropDownMenu('manage_members_menu', $arrMenuItems, array("input[name=\"selected_ids[]\"]"), $this->user->lang('mc_selected_media').'...', ''),
  			));


  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mc_mymedia').' - '.$this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'mymedia.html',
  			'display'       => true
  	));

  }
}
?>