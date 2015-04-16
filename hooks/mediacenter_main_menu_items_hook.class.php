<?php
/*
 * Project:     EQdkp Shoutbox
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-11-11 18:36:16 +0100 (So, 11. Nov 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     shoutbox
 * @version     $Rev: 12434 $
 *
 * $Id: shoutbox_search_hook.class.php 12434 2012-11-11 17:36:16Z godmod $
 */

if (!defined('EQDKP_INC'))
{
  header('HTTP/1.0 404 Not Found');exit;
}


/*+----------------------------------------------------------------------------
  | shoutbox_search_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('mediacenter_main_menu_items_hook'))
{
  class mediacenter_main_menu_items_hook extends gen_class
  {

	/**
    * hook_search
    * Do the hook 'search'
    *
    * @return array
    */
	public function main_menu_items()
	{
		$main_menu = array();
		
		$arrCategories = $this->pdh->get('mediacenter_categories', 'category_tree', array(true, false));
		
		$main_menu[] = array(
				'link'  		=> $this->routing->build('MediaCenter', false, false, true, true),
				'text'  		=> $this->user->lang('mediacenter'),
				'check' 		=> 'u_mediacenter_view',
				'default_hide'	=> 1,
				'link_category' => 'mc_mediacenter',
		);
		
		foreach($arrCategories as $intCategoryID => $strCategoryName){
			$main_menu[] = array(
				'link'  		=> $this->controller_path_plain.$this->pdh->get('mediacenter_categories', 'path', array($intCategoryID)),
				'text'  		=> $strCategoryName,
				'check' 		=> 'u_mediacenter_view',
				'default_hide'	=> 1,
				'link_category' => 'mc_mediacenter',
			);
		}

		return $main_menu;
	}
  }
}
?>