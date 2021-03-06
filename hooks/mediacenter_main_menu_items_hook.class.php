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