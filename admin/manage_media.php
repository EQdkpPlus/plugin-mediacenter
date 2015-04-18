<?php
/*
* Project:		EQdkp-Plus
* License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
* Link:			http://creativecommons.org/licenses/by-nc-sa/3.0/
* -----------------------------------------------------------------------
* Began:		2009
* Date:			$Date: 2013-03-23 18:01:39 +0100 (Sa, 23 Mrz 2013) $
* -----------------------------------------------------------------------
* @author		$Author: godmod $
* @copyright	2006-2011 EQdkp-Plus Developer Team
* @link			http://eqdkp-plus.com
* @package		eqdkpplus
* @version		$Rev: 13242 $
*
* $Id: Manage_Article_Categories.php 13242 2013-03-23 17:01:39Z godmod $
*/

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path . 'common.php');

class Manage_Media extends page_generic {

	public function __construct(){
		// plugin installed?
		if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
			message_die($this->user->lang('mc_plugin_not_installed'));
		
		$this->user->check_auth('a_mediacenter_manage');
		
		$handler = array(
			'save' 				=> array('process' => 'save', 'csrf' => true),
			'change_album'		=> array('process' => 'change_album', 'csrf' => true),
			'set_published'		=> array('process' => 'set_published', 'csrf' => true),
			'set_unpublished'	=> array('process' => 'set_unpublished', 'csrf' => true),
			'delete_album'		=> array('process' => 'delete_album', 'csrf' => true),
			'unreport'			=> array('process' => 'unreport', 'csrf' => true),
		);
		parent::__construct(false, $handler, array('mediacenter_media', 'name'), null, 'selected_ids[]');
		$this->process();
	}
	
	public function unreport(){
		$this->pdh->put('mediacenter_media', 'unreport', array($this->in->get('id', 0)));
		$this->pdh->process_hook_queue();
	}
	
	public function set_unpublished(){
		if(count($this->in->getArray('selected_ids', 'int')) > 0) {
			$this->pdh->put('mediacenter_media', 'set_unpublished', array($this->in->getArray('selected_ids', 'int')));
			$this->pdh->process_hook_queue();
			$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
		}
	}
	
	public function set_published(){
		if(count($this->in->getArray('selected_ids', 'int')) > 0) {
			$this->pdh->put('mediacenter_media', 'set_published', array($this->in->getArray('selected_ids', 'int')));
			$this->pdh->process_hook_queue();
			$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
		}
	}
	
	public function change_album(){
		if(count($this->in->getArray('selected_ids', 'int')) > 0) {
			$intCategory = $this->in->get('new_category',0);
			$this->pdh->put('mediacenter_media', 'change_album', array($this->in->getArray('selected_ids', 'int'), $intCategory));
			$this->pdh->process_hook_queue();
			$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
		}
	}
	
	public function save(){
		$arrPublished = $this->in->getArray('published', 'int');
		$arrFeatured = $this->in->getArray('featured', 'int');
		foreach($arrPublished as $key => $val){
			$this->pdh->put('mediacenter_media', 'update_featuredandpublished', array($key, $arrFeatured[$key], $val));
		}
		$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
		$this->pdh->process_hook_queue();
	}
	
	public function delete(){
		$retu = array();
	
		if(count($this->in->getArray('selected_ids', 'int')) > 0) {
			foreach($this->in->getArray('selected_ids','int') as $id) {
	
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
	
	public function delete_album(){
		$intAlbumID = $this->in->get('a', 0);
		if ($intAlbumID){
			$albumName = $this->pdh->get('mediacenter_albums', 'name', array($intAlbumID));
			$this->pdh->put('mediacenter_albums', 'delete_album', array($intAlbumID));
			$messages[] = array('title' => $this->user->lang('del_suc'), 'text' => $albumName, 'color' => 'green');
			$this->core->messages($messages);
			$this->pdh->process_hook_queue();
		}
	}

	
	public function display(){
		$intCategoryID = $this->in->get('cid', 0);
		if (!$intCategoryID) redirect('plugins/mediacenter/admin/manage_categories.php'.$this->SID);
				
		$arrAlbums = $this->pdh->get('mediacenter_albums', 'albums_for_category', array($intCategoryID));
		$view_list = $this->pdh->get('mediacenter_categories', 'id_list', array());
		
		$hptt_page_settings = array(
			'name'				=> 'hptt_mc_admin_manage_media_categorylist',
			'table_main_sub'	=> '%intMediaID%',
			'table_subs'		=> array('%intCategoryID%', '%intMediaID%'),
			'page_ref'			=> 'manage_media.php',
			'show_numbers'		=> false,
			'show_select_boxes'	=> true,
			'selectboxes_checkall'=>true,
			'show_detail_twink'	=> false,
			'table_sort_dir'	=> 'asc',
			'table_sort_col'	=> 4,
			'table_presets'		=> array(
					array('name' => 'mediacenter_media_editicon',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_media_published',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_media_featured',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_media_previewimage',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_media_name',		'sort' => true, 'th_add' => '', 'td_add' => ''),
					array('name' => 'mediacenter_media_user_id',		'sort' => true, 'th_add' => '', 'td_add' => ''),
					array('name' => 'mediacenter_media_type','sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_media_date','sort' => true, 'th_add' => 'width="20"', 'td_add' => 'nowrap="nowrap"'),
					array('name' => 'mediacenter_media_reported',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
			),
		);
		
		$page_suffix = '&amp;start='.$this->in->get('start', 0);
		$sort_suffix = '?sort='.$this->in->get('sort');
		
		$this->confirm_delete($this->user->lang('mc_confirm_delete_category'));
		$this->jquery->Dialog('confirm_album_delete', $this->user->lang('confirm_deletion'), array(
			'url' 		=> $this->SID.'&cid='.$intCategoryID.'&delete_album=true&link_hash='.$this->CSRFGetToken('delete_album').'&a=\'+albumid+\'',
			'withid'	=> 'albumid',
			'message'	=> $this->user->lang('mc_confirm_delete_album'),
		), 'confirm');
		
		$arrMediaInCategory = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryID));
		if (isset($arrMediaInCategory[0]) && count($arrMediaInCategory[0])){
			$view_list = $arrMediaInCategory[0];
			$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url%' => 'manage_media.php', '%link_url_suffix%' => '&amp;upd=true'), $intCategoryID.'.0');
			
			$this->tpl->assign_vars(array(
				'S_IN_CATEGORY' => true,
				'MEDIA_LIST'	=> $hptt->get_html_table($this->in->get('sort'), $page_suffix,null,1,null,false, array('mediacenter_media', 'checkbox_check')),
			));
		}
		
		
		foreach($arrAlbums as $intAlbumID){
			$view_list = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumID));
			$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url%' => 'manage_media.php', '%link_url_suffix%' => '&amp;upd=true'), $intCategoryID.'.'.$intAlbumID);
		
			$this->tpl->assign_block_vars('album_list', array(
				'NAME'				=> $this->pdh->get('mediacenter_albums', 'name', array($intAlbumID)),
				'S_PERSONAL'		=> $this->pdh->get('mediacenter_albums', 'personal_album', array($intAlbumID)) ? true : false,
				'S_ALBUM'			=> true,
				'USER'				=> $this->pdh->get('user', 'name', array($this->pdh->get('mediacenter_albums', 'user_id', array($intAlbumID)))),
				'ID'				=> $intAlbumID,
				'HPTT_COLUMN_COUNT'	=> $hptt->get_column_count(),
				'MEDIA_LIST'		=> $hptt->get_html_table($this->in->get('sort'), $page_suffix,null,1,null,false, array('mediacenter_media', 'checkbox_check')),
			));
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
						'name'	=> $this->user->lang('mass_stat_change').': '.$this->user->lang('published'),
						'type'	=> 'button', //link, button, javascript
						'icon'	=> 'fa-eye',
						'perm'	=> true,
						'link'	=> '#set_published',
				),
				2 => array(
						'name'	=> $this->user->lang('mass_stat_change').': '.$this->user->lang('not_published'),
						'type'	=> 'button', //link, button, javascript
						'icon'	=> 'fa-eye-slash',
						'perm'	=> true,
						'link'	=> '#set_unpublished',
				),
				3 => array(
						'name'	=> $this->user->lang('mc_move_album').':',
						'type'	=> 'button', //link, button, javascript
						'icon'	=> 'fa-refresh',
						'perm'	=> true,
						'link'	=> '#change_category',
						'append' => new hdropdown('new_category', array('options' => $this->pdh->get('mediacenter_albums', 'category_tree'))),
				),
		);
		
		$this->confirm_delete($this->user->lang('mc_confirm_delete_media'));

		$this->jquery->Dialog('addmedia', $this->user->lang('mc_add_media'), array('withid' => 'albumid', 'url'=> $this->controller_path.'AddMedia/'.$this->SID.'&admin=1&simple_head=1&aid=\'+albumid+\'', 'width'=>'900', 'height'=>'780', 'onclose' => $this->env->buildlink().'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&cid='.$intCategoryID));
		$this->jquery->Dialog('editmedia', $this->user->lang('mc_edit_media'), array('withid' => 'mediaid', 'url'=> $this->controller_path.'EditMedia/Media-\'+mediaid+\'/'.$this->SID.'&simple_head=1&admin=1', 'width'=>'900', 'height'=>'780', 'onclose' => $this->env->buildlink().'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&cid='.$intCategoryID));
		$this->jquery->Dialog('editalbum', $this->user->lang('mc_edit_album'), array('withid' => 'albumid', 'url'=> $this->controller_path.'EditAlbum/Album-\'+albumid+\'/'.$this->SID.'&simple_head=1&admin=1', 'width'=>'640', 'height'=>'520', 'onclose' => $this->env->buildlink().'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&cid='.$intCategoryID));
		$this->jquery->Dialog('addalbum', $this->user->lang('mc_new_album'), array('url'=> $this->controller_path.'AddAlbum/'.$this->SID.'&simple_head=1&admin=1&cid='.$intCategoryID, 'width'=>'640', 'height'=>'520', 'onclose' => $this->env->buildlink().'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&cid='.$intCategoryID));
		
		$this->tpl->assign_vars(array(
			'CID' 			=> $intCategoryID,
			'CATEGORY_NAME' => $this->pdh->get('mediacenter_categories', 'name', array($intCategoryID)),
			'BUTTON_MENU'	=> $this->jquery->ButtonDropDownMenu('manage_members_menu', $arrMenuItems, array("input[name=\"selected_ids[]\"]"), '', $this->user->lang('mc_selected_media').'...', ''),
		));
				
		$this->core->set_vars(array(
				'page_title'		=> $this->user->lang('mc_manage_media').': '.$this->pdh->get('mediacenter_categories', 'name', array($intCategoryID)),
				'template_path'		=> $this->pm->get_data('mediacenter', 'template_path'),
				'template_file'		=> 'admin/manage_media.html',
				'display'			=> true)
		);
	}
	
}
registry::register('Manage_Media');
?>