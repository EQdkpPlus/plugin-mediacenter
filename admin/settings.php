<?php
/*
 * Project:     EQdkp mediacenter
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-10-13 22:48:23 +0200 (Sa, 13. Okt 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     mediacenter
 * @version     $Rev: 12273 $
 *
 * $Id: settings.php 12273 2012-10-13 20:48:23Z godmod $
 */

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'mediacenter');

$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path.'common.php');


/*+----------------------------------------------------------------------------
  | mediacenterSettings
  +--------------------------------------------------------------------------*/
class MediaCenterSettings extends page_generic
{
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array('pm', 'user', 'config', 'core', 'in', 'jquery', 'html', 'tpl');
    return array_merge(parent::$shortcuts, $shortcuts);
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
      'save' => array('process' => 'save', 'csrf' => true),
    );
	
	$this->user->check_auth('a_mediacenter_settings');  
	
    parent::__construct(null, $handler);

    $this->process();
  }
  
  private $arrData = false;

  /**
   * save
   * Save the configuration
   */
  public function save()
  {

  	$objForm = register('form', array('mc_settings'));
  	$objForm->langPrefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->add_fieldsets($this->fields());
  	$arrValues = $objForm->return_values();

  	if ($objForm->error){
  		$this->arrData = $arrValues;
  	} else {
  	
	  	// update configuration
	    $this->config->set($arrValues, '', 'mediacenter');
	    // Success message
	    $messages[] = $this->user->lang('mc_config_saved');
	
	    $this->display($messages);
  	}
   
  }
  
  
  private function fields(){
  	$arrFields = array(
  		'extensions' => array(
	  		'extensions_image' => array(
	  			'type' => 'text',
	  			'size' => 40,
	  		),
	  		
	  		'extensions_file' => array(
	  			'type' => 'text',
	  			'size' => 40,
	  		),
	  		
	  		'extensions_video' => array(
	  			'type' => 'text',
	  			'size' => 40,
	  		),
  		),
  	);
  
  	return $arrFields;
  }
  

  /**
   * display
   * Display the page
   *
   * @param    array  $messages   Array of Messages to output
   */
  public function display($messages=array())
  {
    // -- Messages ------------------------------------------------------------
    if ($messages)
    {
      foreach($messages as $name)
        $this->core->message($name, $this->user->lang('mediacenter'), 'green');
    }
    
    $arrValues = $this->config->get_config('mediacenter');
    if ($this->arrData !== false) $arrValues = $this->arrData;

    // -- Template ------------------------------------------------------------
	// initialize form class
	$objForm = register('form', array('mc_settings'));
	$objForm->reset_fields();
  	$objForm->lang_prefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->use_fieldsets = true;
  	$objForm->add_fieldsets($this->fields());
		
	// Output the form, pass values in
	$objForm->output($arrValues);

    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array(
      'page_title'    => $this->user->lang('mediacenter').' '.$this->user->lang('settings'),
      'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
      'template_file' => 'admin/settings.html',
      'display'       => true
    ));
  }
}

registry::register('MediaCenterSettings');

?>