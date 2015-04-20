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


class editalbum_pageobject extends pageobject {
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
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  private $arrData = array();
 
  public function save(){
  	$objForm = register('form', array('editalbum'));
  	$objForm->langPrefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->add_fields($this->fields());
  	$arrValues = $objForm->return_values();

  	if ($objForm->error){
  		$this->arrData = $arrValues;
  		$this->display();
  	} else {
  		//Check Permissions
  		$intCategoryID = (int)$arrValues['category_id'];
  		$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  		if ((!$arrPermissions || !$arrPermissions['add_album']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  			$this->user->check_auth('u_mediacenter_something');
  		}
  		
  		if ($this->url_id) {
  			//Check if Personal Album
  			if ($this->pdh->get('mediacenter_albums', 'personal_album', array($this->url_id))){
  				if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array($this->url_id))) {
  					$this->user->check_auth('u_mediacenter_something');
  				};
  			}
  			
  			if($this->blnAdminMode){
  				//Admin
  				$mixResult = $this->pdh->put('mediacenter_albums', 'update_album', array((int)$this->url_id, $arrValues['name'], $arrValues['description'], (int)$arrValues['category_id'], $arrValues['personal_album'], (int)$arrValues['user_id']));
  			} else {
  				//User
  				$mixResult = $this->pdh->put('mediacenter_albums', 'update_album', array((int)$this->url_id, $arrValues['name'], $arrValues['description'], (int)$arrValues['category_id'], $arrValues['personal_album']));	
  			}

   		} else {
   			if($this->blnAdminMode){
  				$mixResult = $this->pdh->put('mediacenter_albums', 'insert_album', array($arrValues['name'], $arrValues['description'], (int)$arrValues['category_id'], $arrValues['personal_album'], (int)$arrValues['user_id']));
   			} else {
   				$mixResult = $this->pdh->put('mediacenter_albums', 'insert_album', array($arrValues['name'], $arrValues['description'], (int)$arrValues['category_id'], $arrValues['personal_album']));	
   			} 
   		}
  		$this->pdh->process_hook_queue();
  		
  		if($this->in->get('simple_head')){
  			$this->tpl->add_js('$.FrameDialog.closeDialog();', 'docready');
  		}
  		
  		if ($mixResult) $this->core->message($this->user->lang('save_suc'), $this->user->lang('success'), 'green');
  	}
  }
  
  
  public function display(){
	$objForm = register('form', array('editalbum'));
	$objForm->langPrefix = 'mc_';
	$objForm->validate = true;
	$objForm->add_fields($this->fields());
	
	
	$arrValues = array();
  	if ($this->url_id) {

  		//Check if Personal Album
  		if ($this->pdh->get('mediacenter_albums', 'personal_album', array($this->url_id))){
  			if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array($this->url_id))) {
  				$this->user->check_auth('u_mediacenter_something');
  			};
  		}
  		
  		$arrValues = $this->pdh->get('mediacenter_albums', 'data', array($this->url_id));
  	} else {
  		$arrValues['user_id'] = $this->user->id;
  	}
  	
	//Output, with Values
	if (count($this->arrData)) $arrValues = $this->arrData;
	
	//Set Category
	if ($this->in->get('cid', 0)) $arrValues['category_id'] = $this->in->get('cid', 0);
	
	$objForm->output($arrValues);
	
	$this->tpl->assign_vars(array(
			'TITLE' 		=> (($this->url_id) ? $this->user->lang('mc_edit_album') : $this->user->lang('mc_new_album') ),
			'ADMINMODE'		=> $this->blnAdminMode,
	));
	
    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array (
      'page_title'    => $this->user->lang('mc_edit_album'),
      'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
      'template_file' => 'album_edit.html',
      'display'       => true
    ));	
  }
  
  
  //Get Fields for Form
  private function fields(){
  	$arrCategories = array();
  	$arrCategoryIDs = $this->pdh->sort($this->pdh->get('mediacenter_categories', 'id_list', array()), 'mediacenter_categories', 'sort_id', 'asc');
  	foreach($arrCategoryIDs as $cid){
  		if(!$this->blnAdminMode){
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($cid, $this->user->id));
  			if ((!$arrPermissions || !$arrPermissions['add_album'])) continue;
  		}
  		
  		$arrCategories[$cid] = $this->pdh->get('mediacenter_categories', 'name_prefix', array($cid)).$this->pdh->get('mediacenter_categories', 'name', array($cid));
  	}
  	
  	$arrFields = array(
  		'name' => array(
  			'type' => 'text',
  			'lang' => 'mc_f_album_name',
  			'size' => 30,
  			'required'	=> true,
  		),
  		'description' => array(
  			'type' => 'textarea',
  			'cols' => 30,
  			'lang' => 'mc_f_description',
  		),
  		'personal_album' => array(
  			'type' => 'radio',
  			'lang' => 'mc_f_personal_album'
  		),
  		'category_id' => array(
  			'type' => 'dropdown',
  			'lang' => 'mc_f_category',
  			'options' => $arrCategories,
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
  	}
  	
  	return $arrFields;
  }
  
}
?>