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
  		$blnWatermarkChanged = false;
  		if (!$arrValues['watermark_logo']) {
  			$arrValues['watermark_logo'] = $this->config->get('watermark_logo', 'mediacenter');
  		} else {
  			$blnWatermarkChanged = true;
  		}
  		if($arrValues['watermark_position'] != $this->config->get('watermark_position', 'mediacenter') || $arrValues['watermark_transparency'] != $this->config->get('watermark_transparency', 'mediacenter') || $arrValues['watermark_enabled'] != $this->config->get('watermark_enabled', 'mediacenter')){
  			$blnWatermarkChanged = true;
  		}
  		
  		if($blnWatermarkChanged){
  			$this->deleteWatermarkImages();
  		}
  		
	  	// update configuration
	    $this->config->set($arrValues, '', 'mediacenter');
	    // Success message
	    $messages[] = $this->user->lang('mc_config_saved');
	
	    $this->display($messages);
  	}
   
  }
  
  
  private function fields(){
  	$arrFields = array(
  		'defaults' => array(
  			'per_page' => array(
  				'type' => 'spinner',
  				'max'  => 50,
  				'min'  => 5,
  				'step' => 5,
  				'onlyinteger' => true,
  				'default' => 25,
  			),	
  		),
  		'index_page' => array(
  			'show_featured' => array(
  				'type' => 'radio',
  			),
  			'show_newest' => array(
  				'type' => 'radio',
  			),
  			'show_categories' => array(
  				'type' => 'radio',
  			),
  			'show_mostviewed' => array(
  				'type' => 'radio',
  			),
  			'show_latestcomments' => array(
  				'type' => 'radio',
  			),
  			'show_bestrated' => array(
  				'type' => 'radio',
  			),
  		),
  		'extensions' => array(
	  		'extensions_image' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
	  		
	  		'extensions_file' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
	  		
	  		'extensions_video' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
  		),
  		'maps' => array(
  			'show_maps' => array(
  				'type' => 'radio',
  			),
  		),
  		'exif' => array(
  			'show_exif' => array(
  				'type' => 'radio',	
  			),
  			'rotate_exif' => array(
  				'type' => 'radio',	
  			)
  		),
  		'watermark' => array(
	  		'watermark_enabled' => array(
  				'type' 			=> 'radio',
	  			'dependency'	=> array(1 => array('watermark_type', 'watermark_text', 'watermark_position', 'watermark_transparency', 'watermark_logo')),
  			),
  			'watermark_type' => array(
  				'type' 			=> 'radio',
  				'dependency'	=> array(0 => array('watermark_text', 'watermark_fontsize'), 1 => array('watermark_logo')),
  				'options'		=> array($this->user->lang('mc_watermark_type_text'), $this->user->lang('mc_watermark_type_image'))
  			),
  			'watermark_text' => array(
  				'type'			=> 'text',
  				'size'			=> 50,
  				'default'		=> '{USER} @ my EQdkp Plus',
  			),
  			'watermark_fontsize' => array(
  				'type'			=> 'spinner',
  				'min'			=> 1,
  				'default'		=> 15,
  			),
  			'watermark_logo' => array(
  				'type'	=> 'file',
  				'preview' => true,
  				'extensions'	=> array('jpg', 'png'),
  				'mimetypes'		=> array(
  						'image/jpeg',
  						'image/png',
  				),
  				'folder'		=> $this->pfh->FolderPath('watermarks', 'mediacenter'),
  				'numerate'		=> true,
  				'default'		=> false,
	  		),

  			'watermark_transparency' => array(
  				'type'	=> 'slider',
  				'min'	=> 0,
  				'max'	=> 100,
  				'value' => 0,
  				'width'	=> '300px',
  				'label' => $this->user->lang('mc_watermark_transparency'),
  				'range' => false,
  			),
  			'watermark_position' => array(
  				'type' => 'dropdown',
  				'options' => $this->user->lang('mc_watermark_positions'),
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
    if ($arrValues['watermark_logo'] && strlen($arrValues['watermark_logo'])) $arrValues['watermark_logo'] = $this->root_path.$arrValues['watermark_logo'];

    // -- Template ------------------------------------------------------------
	// initialize form class
	$objForm = register('form', array('mc_settings'));
	$objForm->reset_fields();
  	$objForm->lang_prefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->use_fieldsets = true;
  	$objForm->use_dependency = true;
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
  
  private function deleteWatermarkImages(){
  	$strThumbfolder = $this->pfh->FolderPath('thumbs', 'mediacenter');
  	$arrFiles = scandir($strThumbfolder);
  	foreach($arrFiles as $strFile){
  		if(valid_folder($strFile)){
  			if(strpos($strFile, 'wm_') === 0){
  				$this->pfh->Delete($strThumbfolder.$strFile);
  			}
  		}
  	}
  }
}

registry::register('MediaCenterSettings');

?>