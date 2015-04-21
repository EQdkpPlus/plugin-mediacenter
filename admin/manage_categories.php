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

class Manage_Categories extends page_generic {

	public function __construct(){
		// plugin installed?
		if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
			message_die($this->user->lang('mc_plugin_not_installed'));
		
		$this->user->check_auth('a_mediacenter_manage');
		
		$handler = array(
			'save' 		=> array('process' => 'save', 'csrf' => true),
			'update'	=> array('process' => 'update', 'csrf' => true),
			'checkalias'=> array('process' => 'ajax_checkalias'),
			'calcperm'	=> array('process' => 'ajax_calculate_permission'),
			'cid'		=> array('process' => 'edit'),
				
		);
		parent::__construct(false, $handler, array('mediacenter_categories', 'name'), null, 'selected_ids[]');
		$this->process();
	}
	
	public function ajax_checkalias(){
		$strAlias = $this->in->get('alias');
		$intCID = $this->in->get('cid', 0);
		
		$blnResult = $this->pdh->get('mediacenter_categories', 'check_alias', array($strAlias, true));
		if (!$blnResult && $this->pdh->get('mediacenter_categories', 'alias', array($intCID)) === $strAlias) $blnResult = true;
		if (is_numeric($strAlias)) $blnResult = false;
		
		header('content-type: text/html; charset=UTF-8');
		if ($blnResult){
			echo 'true';
		} else {
			echo 'false';
		}
		exit;
	}
	
	public function ajax_calculate_permission(){
		$intCID = $this->in->get('cid', 0);
		$strPermission = $this->in->get('perm');
		$strPermissionValue = $this->in->get('value', 0);
		$intGroupID = $this->in->get('gid', 0);
		$intParentID = $this->in->get('parent', 0);
		$blnResult = $this->pdh->get('mediacenter_categories', 'calculated_permissions', array($intCID, $strPermission, $intGroupID, $strPermissionValue, $intParentID));
	
		header('content-type: text/html; charset=UTF-8');
		if ($blnResult){
			echo '<span class="positive">'.$this->user->lang('allowed').'</span>';
		} else {
			echo '<span class="negative">'.$this->user->lang('disallowed').'</span>';
		}
		exit;
	}
	
	public function update(){
		$id = $this->in->get('cid', 0);
		$strName = $this->in->get('name');
		$strDescription = $this->in->get('description', '', 'raw');
		$strAlias = $this->in->get('alias');
		$intPublished = $this->in->get('published', 0);
		$intParentCategory = (($id == 1) ? 0 : $this->in->get('parent',0));
		$intArticlePublishedState = $this->in->get('article_published_state', 0);
		$arrPermissions = $this->in->getArray('perm', 'int');
		$intNotifyUnpublishedArticles = $this->in->get('notify_unpublished', 0);
		$intAllowComments = $this->in->get('allow_comments', 0);
		$intAllowVoting = $this->in->get('allow_voting', 0);
		$intDefaultLayout = $this->in->get('layout', 0);
		$arrTypes = $this->in->getArray('types', 'int');
		$intPerPage = $this->in->get('per_page', (int)$this->config->get('per_page', 'mediacenter'));
		
		if ($strName == "" ) {
			$this->core->message($this->user->lang('name'), $this->user->lang('missing_values'), 'red');
			$this->edit();
			return;
		}
		
		if ($id){
			$blnResult = $this->pdh->put('mediacenter_categories', 'update', array(
				$id, $strName, $strDescription, $strAlias, $intPublished, $intParentCategory, $intArticlePublishedState, $arrPermissions, $intNotifyUnpublishedArticles, $intAllowComments, $intDefaultLayout, $arrTypes, $intAllowVoting, $intPerPage
			));
		} else {
			$blnResult = $this->pdh->put('mediacenter_categories', 'add', array(
				$strName, $strDescription, $strAlias, $intPublished, $intParentCategory, $intArticlePublishedState, $arrPermissions, $intNotifyUnpublishedArticles, $intAllowComments, $intDefaultLayout, $arrTypes, $intAllowVoting, $intPerPage
			));
		}
		
		if ($blnResult){
			$this->pdh->process_hook_queue();
			$this->core->message($this->user->lang('mc_save_category'), $this->user->lang('success'), 'green');
		} else {
			$this->core->message($this->user->lang('mc_save_category'), $this->user->lang('error'), 'red');
		}

		$this->display();
	}
	
	public function save(){
		$arrSortables = $this->in->getArray('sortCategories', 'int');
		$arrSortablesFlipped = array_flip($arrSortables);
	
		$arrPublished = $this->in->getArray('published', 'int');
		foreach($arrPublished as $key => $val){
			$this->pdh->put('mediacenter_categories', 'update_sortandpublished', array($key, (int)$arrSortablesFlipped[$key], (int)$val));
		}
		$this->pdh->process_hook_queue();
		$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
	}
	
	public function delete(){
		$retu = array();
		if(count($this->in->getArray('selected_ids', 'int')) > 0) {
			foreach($this->in->getArray('selected_ids','int') as $id) {
				$pos[] = stripslashes($this->pdh->get('mediacenter_categories', 'name', array($id)));
				$retu[$id] = $this->pdh->put('mediacenter_categories', 'delete', array($id));
			}
		}

		if(!empty($pos)) {
			$messages[] = array('title' => $this->user->lang('del_suc'), 'text' => implode(', ', $pos), 'color' => 'green');
			$this->core->messages($messages);
		}
		
		$this->pdh->process_hook_queue();
	}
	
	public function edit(){
		$id = $this->in->get('cid', 0);
		
		$arrPermissionDropdown = array(
			-1 => $this->user->lang('inherited'),
			1 => $this->user->lang('allowed'),
			0 => $this->user->lang('disallowed')
		);
						
		$arrGroups = $this->pdh->get('user_groups', 'id_list', array());		
		$arrPermissions = $this->pdh->get('mediacenter_categories', 'permissions', array($id));

		foreach($arrGroups as $gid){
			$this->tpl->assign_block_vars('group_row', array(
				'ID' 				=> $gid,
				'NAME' 				=> $this->pdh->get('user_groups', 'name', array($gid)),
				'DD_CREATE' 		=>new hdropdown('perm[cre]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['cre'][$gid]) ? $arrPermissions['cre'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'cre\', '.$gid.', this)"')),
				'DD_UPDATE' 		=> new hdropdown('perm[upd]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['upd'][$gid]) ? $arrPermissions['upd'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'upd\', '.$gid.', this)"')),
				'DD_DELETE' 		=> new hdropdown('perm[del]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['del'][$gid]) ? $arrPermissions['del'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'del\', '.$gid.', this)"')),
				'DD_READ' 			=> new hdropdown('perm[rea]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['rea'][$gid]) ? $arrPermissions['rea'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'rea\', '.$gid.', this)"')),
				'DD_CHANGE_STATE'	=> new hdropdown('perm[chs]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['chs'][$gid]) ? $arrPermissions['chs'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'chs\', '.$gid.', this)"')),
				'DD_ADD_ALBUM' 		=> new hdropdown('perm[ada]['.$gid.']', array('options' => $arrPermissionDropdown, 'value' => (isset($arrPermissions['ada'][$gid]) ? $arrPermissions['ada'][$gid] : -1), 'js' => 'onchange="calculate_permission(\'ada\', '.$gid.', this)"')),
					
				'CALC_CREATE' 		=> $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'cre', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',
				'CALC_UPDATE' 		=> $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'upd', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',
				'CALC_DELETE' 		=> $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'del', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',
				'CALC_READ' 		=> $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'rea', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',
				'CALC_CHANGE_STATE' => $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'chs', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',
				'CALC_ADD_ALBUM'	=> $this->pdh->get('mediacenter_categories', 'calculated_permissions', array((($id) ? $id : 0), 'ada', $gid)) ? '<span class="positive">'.$this->user->lang('allowed').'</span>' : '<span class="negative">'.$this->user->lang('disallowed').'</span>',			
			));
		}
		
		
		$this->jquery->Tab_header('article_category-tabs');
		$this->jquery->Tab_header('category-permission-tabs');
		$editor = register('tinyMCE');
		$editor->editor_normal(array(
			'relative_urls'	=> false,
			'link_list'		=> true,
			'readmore'		=> false,
		));
		
		$arrCategoryIDs = $this->pdh->sort($this->pdh->get('mediacenter_categories', 'id_list', array()), 'mediacenter_categories', 'sort_id', 'asc');
		$arrCategories['0'] = '--';
		foreach($arrCategoryIDs as $cid){
			$arrCategories[$cid] = $this->pdh->get('mediacenter_categories', 'name_prefix', array($cid)).$this->pdh->get('mediacenter_categories', 'name', array($cid));
		}
		$arrAggregation = $arrCategories;
		unset($arrAggregation[0]);
		if ($id){
			unset($arrCategories[$id]);
			$this->tpl->assign_vars(array(
				'DESCRIPTION'		=> $this->pdh->get('mediacenter_categories', 'description', array($id)),
				'NAME' 				=> $this->pdh->get('mediacenter_categories', 'name', array($id)),
				'ALIAS'				=> $this->pdh->get('mediacenter_categories', 'alias', array($id)),					
				'PER_PAGE'			=> $this->pdh->get('mediacenter_categories', 'per_page', array($id)),
				'DD_PARENT' 		=> new hdropdown('parent', array('js'=>'onchange="renew_all_permissions();"', 'options' => $arrCategories, 'value' => $this->pdh->get('mediacenter_categories', 'parent', array($id)))),
				'DD_PUBLISHED_STATE'=> new hradio('default_published_state]', array('options' => array(0 => $this->user->lang('not_published'), 1 => $this->user->lang('published')), 'value' => $this->pdh->get('mediacenter_categories', 'default_published_state', array($id)))),
				'R_NOTIFY_UNPUBLISHED' => new hradio('notify_unpublished', array('value' => ($this->pdh->get('mediacenter_categories', 'notify_on_onpublished', array($id))))),
				'R_COMMENTS'		=> new hradio('allow_comments', array('value' => ($this->pdh->get('mediacenter_categories', 'allow_comments', array($id))))),
				'R_VOTING'			=> new hradio('allow_voting', array('value' => ($this->pdh->get('mediacenter_categories', 'allow_voting', array($id))))),
				'DD_LAYOUT_TYPE' 	=> new hdropdown('layout', array('options' => $this->user->lang('mc_layout_types'), 'value' => $this->pdh->get('mediacenter_categories', 'layout', array($id)))),
				'DD_MEDIA_TYPE' 	=> new hmultiselect('types', array('options' => $this->user->lang('mc_types'), 'value' => $this->pdh->get('mediacenter_categories', 'types', array($id)))),
				'R_PUBLISHED'		=> new hradio('published', array('value' =>  ($this->pdh->get('mediacenter_categories', 'published', array($id))))),
				'SPINNER_PER_PAGE'	=> new hspinner('per_page', array('value' =>  ($this->pdh->get('mediacenter_categories', 'per_page', array($id))), 'max'  => 50, 'min'  => 5,'step' => 5,'onlyinteger' => true)),
			));
			
		} else {
			
			$this->tpl->assign_vars(array(
				'PER_PAGE' => 25,	
				'DD_PARENT' => new hdropdown('parent', array('js'=>'onchange="renew_all_permissions();"', 'options' => $arrCategories, 'value' => 0)),
				'DD_PUBLISHED_STATE'=> new hradio('default_published_state]', array('options' => array(0 => $this->user->lang('not_published'), 1 => $this->user->lang('published')), 'value' => 1)),
				'R_NOTIFY_UNPUBLISHED' => new hradio('notify_unpublished', array('value' => 0)),
				'R_COMMENTS'		=> new hradio('allow_comments', array('value' => 1)),
				'DD_LAYOUT_TYPE' 	=> new hdropdown('layout', array('options' => $this->user->lang('mc_layout_types'), 'value' => 0)),
				'DD_MEDIA_TYPE' 	=> new hmultiselect('types', array('options' => $this->user->lang('mc_types'), 'value' => array(0,1,2))),
				'R_PUBLISHED'		=> new hradio('published', array('value' =>  1)),
				'R_VOTING'			=> new hradio('allow_voting', array('value' => 1)),
				'SPINNER_PER_PAGE'	=> new hspinner('per_page', array('value' => $this->config->get('per_page', 'mediacenter'), 'max'  => 50, 'min'  => 5,'step' => 5,'onlyinteger' => true)),	
			));
		}

		$this->tpl->assign_vars(array(
			'CID' => $id,
		));
		$this->core->set_vars(array(
			'page_title'		=> (($id) ? $this->user->lang('mc_manage_categories').': '.$this->pdh->get('mediacenter_categories', 'name', array($id)) : $this->user->lang('mc_add_category')),
			'template_path'		=> $this->pm->get_data('mediacenter', 'template_path'),
			'template_file'		=> 'admin/manage_categories_edit.html',
			'display'			=> true)
		);
	}

	// ---------------------------------------------------------
	// Display form
	// ---------------------------------------------------------
	public function display() {
	
		$this->tpl->add_js("
			$(\"#article_categories-table tbody\").sortable({
				cancel: '.not-sortable, input, tr th.footer, th',
				cursor: 'pointer',
			});
		", "docready");
	
		$view_list = $this->pdh->get('mediacenter_categories', 'id_list', array());
		$hptt_page_settings = array(
			'name'				=> 'hptt_mc_admin_manage_categories_categorylist',
			'table_main_sub'	=> '%intCategoryID%',
			'table_subs'		=> array('%intCategoryID%', '%intArticleID%'),
			'page_ref'			=> 'manage_media.php',
			'show_numbers'		=> false,
			'show_select_boxes'	=> true,
			'selectboxes_checkall'=>true,
			'show_detail_twink'	=> false,
			'table_sort_dir'	=> 'asc',
			'table_sort_col'	=> 0,
			'table_presets'		=> array(
					array('name' => 'mediacenter_categories_sort_id',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_categories_editicon',	'sort' => false, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_categories_published',	'sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_categories_name',		'sort' => true, 'th_add' => '', 'td_add' => ''),
					array('name' => 'mediacenter_categories_alias',		'sort' => true, 'th_add' => '', 'td_add' => ''),
					array('name' => 'mediacenter_categories_album_count','sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
					array('name' => 'mediacenter_categories_media_count','sort' => true, 'th_add' => 'width="20"', 'td_add' => ''),
			),
		);
		$hptt = $this->get_hptt($hptt_page_settings, $view_list, $view_list, array('%link_url%' => $this->root_path.'plugins/mediacenter/admin/manage_media.php', '%link_url_suffix%' => ''));
		$page_suffix = '&amp;start='.$this->in->get('start', 0);
		$sort_suffix = '?sort='.$this->in->get('sort');
		
		$item_count = count($view_list);
		
		$this->confirm_delete($this->user->lang('mc_confirm_delete_category'));
		
		$intMediaCount = $this->pdh->get('mediacenter_media', 'id_list', array());
		if(!$intMediaCount || $intMediaCount === 0){
			$strFolder = $this->pfh->FolderPath('files', 'mediacenter');
			$arrFiles = scandir($strFolder);
			$blnOldFiles = false;
			foreach($arrFiles as $strFile){
				if(valid_folder($strFile)){
					$blnOldFiles = true;
					break;
				}
			}
			
			if($blnOldFiles){
				$this->tpl->assign_vars(array(
					'S_SHOW_OLD_INFO' => true	
				));
			}
		}

		$this->tpl->assign_vars(array(
			'CATEGORY_LIST'		=> $hptt->get_html_table($this->in->get('sort'), $page_suffix,null,1,null,false, array('mediacenter_categories', 'checkbox_check')),
			'HPTT_COLUMN_COUNT'	=> $hptt->get_column_count())
		);

		$this->core->set_vars(array(
			'page_title'		=> $this->user->lang('mc_manage_categories'),
			'template_path'		=> $this->pm->get_data('mediacenter', 'template_path'),
			'template_file'		=> 'admin/manage_categories.html',
			'display'			=> true)
		);
	}
	
}
registry::register('Manage_Categories');
?>