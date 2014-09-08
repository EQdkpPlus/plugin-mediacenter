<?php
/*
 * Project:     EQdkp mediacenter
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-11-11 13:32:45 +0100 (So, 11. Nov 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     mediacenter
 * @version     $Rev: 12426 $
 *
 * $Id: mediacenter_plugin_class.php 12426 2012-11-11 12:32:45Z godmod $
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

  public $version    = '2.0.0';
  public $build      = '';
  public $copyright  = 'GodMod';
  public $vstatus    = 'Alpha';
  
  protected static $apiLevel = 20;

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
      'plus_version'      => '2.0',
      'build'             => $this->build,
    ));

    $this->add_dependency(array(
      'plus_version'      => '2.0'
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
    //$this->add_hook('search', 'mediacenter_search_hook', 'search');
	//$this->add_hook('portal', 'mediacenter_portal_hook', 'portal');
    
    //Routing
	$this->routing->addRoute('AddAlbum', 'editalbum', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('EditAlbum', 'editalbum', 'plugins/mediacenter/pageobjects');
	
	$this->routing->addRoute('AddMedia', 'editmedia', 'plugins/mediacenter/pageobjects');
	$this->routing->addRoute('EditMedia', 'editmedia', 'plugins/mediacenter/pageobjects');
	
	$this->routing->addRoute('MediaCenter', 'views', 'plugins/mediacenter/pageobjects');
	
	$this->routing->addRoute('InsertMediaEditor', 'inserteditor', 'plugins/mediacenter/pageobjects');
	
	$this->add_hook('tinymce_normal_setup', 'mediacenter_tinymce_normal_setup_hook', 'tinymce_normal_setup');
	
	
	// -- Menu --------------------------------------------
    $this->add_menu('admin', $this->gen_admin_menu());
	$this->add_menu('main', $this->gen_main_menu());
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
	//TODO: Add here each Category
	$main_menu = array(
        1 => array (
          'link'  => $this->routing->build('MediaCenter', 'Downloads', false, true, true),
          'text'  => 'Downloads',
          'check' => 'u_mediacenter_view',
        ),
		2 => array (
          'link'  => $this->routing->build('MediaCenter', 'Gallery', false, true, true),
          'text'  => 'Gallery',
          'check' => 'u_mediacenter_view',
        ),
		3 => array (
				'link'  => $this->routing->build('MediaCenter', 'Videos', false, true, true),
				'text'  => 'Videos',
				'check' => 'u_mediacenter_view',
	),
		4 => array (
				'link'  => $this->routing->build('MediaCenter', 'Media', false, true, true),
				'text'  => 'Media',
				'check' => 'u_mediacenter_view',
				'default_hide' => 1,
		),
    );
	

    return $main_menu;
  }

}
?>
