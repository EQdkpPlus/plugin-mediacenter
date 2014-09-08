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
    $shortcuts = array();
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
    	'myvideos'	=> array('process' => 'view_myvideos'),
    	'myalbums' 	=> array('process' => 'view_myalbums'),
    	'tags' 		=> array('process' => 'view_tags'),
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  //For URL: index.php/MediaCenter/MyVideos/
  public function view_myvideos(){
  	echo "View MyVideos";
  }
  
  public function display(){
  	
  	
  	if (is_numeric($this->url_id)){
  		
  		//For URL: index.php/MediaCenter/Downloads/MyFileName-17.html
  		echo "View Media in Category. MediaID: ".$this->url_id;
  		$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  		d($arrMediaData);
  		
  	} elseif (strlen($this->url_id)) {
  		
  		
  		//For Kategory-View: index.php/MediaCenter/Downloads/
  		//Also Subcategories possible:
  		// index.php/MediaCenter/Blablupp/Sowieso/Downloads/
  		$strCategoryAlias = $this->url_id;
  		$intCategoryId = $this->pdh->get('mediacenter_categories', 'resolve_alias', array($strCategoryAlias));
  		if ($intCategoryId){
  			echo 'View Category. CategoryID: '.$intCategoryId;
  			$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  			d($arrCategoryData);
  		} else {
  			echo 'Category not found';
  		}
  		
  	} else {
  		echo "Startpage of MediaCenter";
  	}
  	
  	
  	
  	d($this->url_id);
  	d($this->page);
  	d($this->page_path);
  	d(registry::get_const('patharray'));
  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mc_edit_media'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'insert_media_editor.html',
  			'display'       => true
  	));
  }
}
?>