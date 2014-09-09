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

		return $arrOptions;
	}
  }
}
?>