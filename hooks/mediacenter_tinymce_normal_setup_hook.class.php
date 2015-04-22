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
if (!class_exists('mediacenter_tinymce_normal_setup_hook'))
{
  class mediacenter_tinymce_normal_setup_hook extends gen_class
  {

	/**
    * hook_search
    * Do the hook 'search'
    *
    * @return array
    */
	public function tinymce_normal_setup($arrOptions)
	{
		if($this->user->check_auth('u_mediacenter_view', false)) {
			$arrOptions['js'] .= "
			var fileURL = '".$this->controller_path."InsertMediaEditor/".$this->SID."&simple_head=1'; 
	 		editor.addButton('custom_buttons', {
	         title: 'Insert Media',
	         icon: 'pageobject',
	         onclick: function() {
	            win = editor.windowManager.open({
					file : fileURL,
					title : \"Insert Media\",
					width : 700,
					height : 450,
					resizable : \"yes\",
					inline : \"yes\",  // This parameter only has an effect if you use the inlinepopups plugin!
					popup_css : true, // Disable TinyMCEs default popup CSS
					close_previous : \"yes\"
				});
	         }
	      });	
			";
		}

		return $arrOptions;
	}
  }
}
?>