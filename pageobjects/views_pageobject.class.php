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
    	'mymedia'	=> array('process' => 'view_mymedia'),
    	'myalbums' 	=> array('process' => 'view_myalbums'),
    	'tags' 		=> array('process' => 'view_tags'),
    	'a'			=> array('process' => 'view_album'),
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  //For URL: index.php/MediaCenter/MyMedia/
  public function view_mymedia(){

  	
  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'mymedia.html',
  			'display'       => true
  	));
  }
  
  //For URL: index.php/MediaCenter/Tags/
  public function view_tags(){

  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'tags.html',
  			'display'       => true
  	));
  }
  
  //For URL: index.php/MediaCenter/Downloads/MyAlbumname-a1/
  public function view_album(){

  	$intAlbumID = $this->in->get('a');
  	
  	//d($this->pdh->get('mediacenter_album', 'data', array($intAlbumID)));
  	
  	// -- EQDKP ---------------------------------------------------------------
  	$this->core->set_vars(array (
  			'page_title'    => $this->user->lang('mediacenter'),
  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  			'template_file' => 'album.html',
  			'display'       => true
  	));
  	
  }
  
  public function display(){
  	
  	
  	if (is_numeric($this->url_id)){
  		//For URL: index.php/MediaCenter/Downloads/MyFileName-17.html
  		
  		$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  		d($arrMediaData);
  		
  		// -- EQDKP ---------------------------------------------------------------
  		$this->core->set_vars(array (
  				'page_title'    => $this->user->lang('mediacenter'),
  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  				'template_file' => 'media.html',
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
  			
  			echo 'View Category. CategoryID: '.$intCategoryId;
  			$arrCategoryData = $this->pdh->get('mediacenter_categories', 'data', array($intCategoryId));
  			d($arrCategoryData);
  			
  		} else {
  			echo 'Category not found';
  		}
  		
  		// -- EQDKP ---------------------------------------------------------------
  		$this->core->set_vars(array (
  				'page_title'    => $this->user->lang('mediacenter'),
  				'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
  				'template_file' => 'category.html',
  				'display'       => true
  		));
  		
  		
  	} else {
  		// -- EQDKP ---------------------------------------------------------------
	  	$this->core->set_vars(array (
	  			'page_title'    => $this->user->lang('mediacenter'),
	  			'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
	  			'template_file' => 'index.html',
	  			'display'       => true
	  	));
  	}
  	
  	
  	/*
  	d($this->url_id);
  	d($this->page);
  	d($this->page_path);
  	d(registry::get_const('patharray'));
  	*/
  }
}
?>