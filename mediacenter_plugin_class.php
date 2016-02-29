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
  header('HTTP/1.0 404 Not Found');
  exit;
}


/*+----------------------------------------------------------------------------
  | mediacenter
  +--------------------------------------------------------------------------*/
class mediacenter extends plugin_generic
{
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array('user', 'config', 'pdc', 'pfh', 'pdh', 'routing');
    return array_merge(parent::$shortcuts, $shortcuts);
  }

  public $version    = '2.1.2';
  public $build      = '';
  public $copyright  = 'GodMod';
  public $vstatus    = 'Alpha';
  
  protected static $apiLevel = 23;

  /**
    * Constructor
    * Initialize all informations for installing/uninstalling plugin
    */
  public function __construct()
  {
    parent::__construct();

    $this->add_data(array (
      'name'              => 'MediaCenter',
      'code'              => 'mediacenter',
      'path'              => 'mediacenter',
      'template_path'     => 'plugins/mediacenter/templates/',
      'icon'              => 'fa fa-picture-o',
      'version'           => $this->version,
      'author'            => $this->copyright,
      'description'       => $this->user->lang('mediacenter_short_desc'),
      'long_description'  => $this->user->lang('mediacenter_long_desc'),
      'homepage'          => EQDKP_PROJECT_URL,
      'manuallink'        => false,
      'plus_version'      => '2.1',
      'build'             => $this->build,
    ));

    $this->add_dependency(array(
      'plus_version'      => '2.1'
    ));

    // -- Register our permissions ------------------------
    // permissions: 'a'=admins, 'u'=user
    // ('a'/'u', Permission-Name, Enable? 'Y'/'N', Language string, array of user-group-ids that should have this permission)
    // Groups: 1 = Guests, 2 = Super-Admin, 3 = Admin, 4 = Member
	$this->add_permission('u', 'view',    'Y', $this->user->lang('view'),    array(2,3,4));	
	$this->add_permission('a', 'manage', 'N', $this->user->lang('manage'), array(2,3));
	$this->add_permission('a', 'settings', 'N', $this->user->lang('menu_settings'), array(2,3));	


    // -- PDH Modules -------------------------------------

	$this->add_pdh_read_module('mediacenter_media');
	$this->add_pdh_read_module('mediacenter_categories');
	$this->add_pdh_read_module('mediacenter_albums');
	
	$this->add_pdh_write_module('mediacenter_albums');
	$this->add_pdh_write_module('mediacenter_media');
	$this->add_pdh_write_module('mediacenter_categories');

	// -- Hooks -------------------------------------------
	$this->add_hook('portal', 'mediacenter_portal_hook', 'portal');
	$this->add_hook('tinymce_normal_setup', 'mediacenter_tinymce_normal_setup_hook', 'tinymce_normal_setup');
	$this->add_hook('main_menu_items', 'mediacenter_main_menu_items_hook', 'main_menu_items');
	$this->add_hook('article_parse', 'mediacenter_article_parse_hook', 'article_parse');
	$this->add_hook('search', 'mediacenter_search_hook', 'search');
	$this->add_hook('plugin_statistics', 'mediacenter_plugin_statistics_hook', 'plugin_statistics');
	
    //Routing
	$this->routing->addRoute('AddAlbum', 'editalbum', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('EditAlbum', 'editalbum', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('AddMedia', 'editmedia', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('EditMedia', 'editmedia', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('MediaCenter', 'views', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('InsertMediaEditor', 'inserteditor', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('MyMedia', 'mymedia', 'plugins/mediacenter/pageobjects');

	// -- Menu --------------------------------------------
    $this->add_menu('admin', $this->gen_admin_menu());
	$this->add_menu('main', $this->gen_main_menu());
	
	//Portal modules
	$this->add_portal_module('mc_latest_media');
	$this->add_portal_module('mc_random_media');
	$this->add_portal_module('mc_featured_media');
	$this->add_portal_module('mc_most_viewed_media');
	
	//Add Mediacenter Stylesheets
	$this->tpl->css_file($this->root_path.'plugins/mediacenter/templates/base_template/mediacenter.css');

  }

  /**
    * pre_install
    * Define Installation
    */
   public function pre_install()
  {
    // include SQL and default configuration data for installation
    include($this->root_path.'plugins/mediacenter/includes/sql.php');

    // define installation
    for ($i = 1; $i <= count($mediacenterSQL['install']); $i++)
      $this->add_sql(SQL_INSTALL, $mediacenterSQL['install'][$i]);
	  
    $this->pdc->del_prefix('pdh_mediacenter_');
  }
  
  /**
   * post_install
   * Add Default Settings
   */
  public function post_install(){
  	
  	//Default Settings
  	$arrSave = array(
  		'per_page' => 25,
  		'extensions_image' => 'png, jpeg, jpg, gif',
  		'extensions_file'	=> 'png, jpeg, jpg, gif, txt, zip, mp3',
  		'extensions_video'	=> 'mp4, webm, ogg',
  		'show_featured'	=> 1,
  		'show_newest'	=> 1,
  		'show_categories' => 1,
  		'show_mostviewed' => 1,
  		'show_latestcomments' => 1,
  		'show_maps'	=> 1,
  	);
  	
  	$this->config->set($arrSave, '', 'mediacenter');
  	
  	$this->ntfy->addNotificationType('mediacenter_media_unpublished', 'mc_notify_unpublished_media', 'mediacenter', 1, 1);
  	$this->ntfy->addNotificationType('mediacenter_media_new', 'mc_notify_new_media', 'mediacenter', 0, 0, true, 'mc_notify_new_media_grouped', 3, 'fa-picture-o');
  	$this->ntfy->addNotificationType('mediacenter_media_comment_new', 'mc_notify_new_comment', 'mediacenter', 0, 1, true, 'mc_notify_new_comment_grouped', 3, 'fa-comment');
  	$this->ntfy->addNotificationType('mediacenter_media_reported', 'mc_notify_reported_media', 'mediacenter', 1, 1, false, '', 0, 'fa-warning');
  }

  /**
    * pre_uninstall
    * Define uninstallation
    */
  public function pre_uninstall()
  {
    // include SQL data for uninstallation
    include($this->root_path.'plugins/mediacenter/includes/sql.php');

    for ($i = 1; $i <= count($mediacenterSQL['uninstall']); $i++)
      $this->add_sql(SQL_UNINSTALL, $mediacenterSQL['uninstall'][$i]);
    
    $this->ntfy->deleteNotificationType('mediacenter_media_unpublished');
    $this->ntfy->deleteNotificationType('mediacenter_media_new');
    $this->ntfy->deleteNotificationType('mediacenter_media_comment_new');
    $this->ntfy->deleteNotificationType('mediacenter_media_reported');
    
    $this->pdh->put('comment', 'delete_page', array('mediacenter'));
  }


  /**
    * gen_admin_menu
    * Generate the Admin Menu
    */
  private function gen_admin_menu()
  {

    $admin_menu = array (array(
        'name' => $this->user->lang('mediacenter'),
        'icon' => 'fa fa-picture-o',
    	1 => array (
    		'link'  => 'plugins/mediacenter/admin/manage_categories.php'.$this->SID,
    		'text'  => $this->user->lang('mc_manage_media'),
    		'check' => 'a_mediacenter_manage',
    		'icon'  => 'fa fa-picture-o'
    		),
        2 => array (
          'link'  => 'plugins/mediacenter/admin/settings.php'.$this->SID,
          'text'  => $this->user->lang('settings'),
          'check' => 'a_mediacenter_settings',
          'icon'  => 'fa-wrench'
        ),
    ));


    return $admin_menu;
  }
  
   /**
    * gen_main_menu
    * Generate the Main Menu
    */
  private function gen_main_menu()
  {
  	$main_menu = array();
	$main_menu[] = array(
		'link'  		=> $this->routing->build('MediaCenter', false, false, true, true),
		'text'  		=> $this->user->lang('mediacenter'),
		'check' 		=> 'u_mediacenter_view',
		//'default_hide'	=> 1,
		'link_category' => 'mc_mediacenter',
	);

    return $main_menu;
  }

}
?>
