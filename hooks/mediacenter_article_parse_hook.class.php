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
if (!class_exists('mediacenter_article_parse_hook'))
{
  class mediacenter_article_parse_hook extends gen_class
  {

	/**
    * hook_search
    * Do the hook 'search'
    *
    * @return array
    */
	public function article_parse($arrOptions)
	{
		
		$strContent = $arrOptions['content'];
		//Replace Album
		$arrAlbumObjects = array();
		preg_match_all('#<p(.*)class="system-block mediacenter-album"(.*) data-album-id="(.*)">(.*)</p>#iU', $strContent, $arrAlbumObjects, PREG_PATTERN_ORDER);
		if (count($arrAlbumObjects[0])){
			foreach($arrAlbumObjects[3] as $key => $val){
				$intAlbumID = intval($val);
				
				$strMediaContent = "Mein Album #".$intAlbumID;
				$strContent = str_replace($arrAlbumObjects[0][$key], $strMediaContent, $strContent);
			}
		}
		
		//Replace Media
		$arrMediaObjects = array();
		preg_match_all('#<p(.*)class="system-block mediacenter-media"(.*) data-media-id="(.*)">(.*)</p>#iU', $strContent, $arrMediaObjects, PREG_PATTERN_ORDER);
		if (count($arrMediaObjects[0])){
			foreach($arrMediaObjects[3] as $key => $val){


				$intMediaID = intval($val);
				$strMediaContent = "Mein Media #".$intMediaID;
				$strContent = str_replace($arrMediaObjects[0][$key], $strMediaContent, $strContent);
			}
		}

		$arrOptions['content'] = $strContent;
		
		return $arrOptions;
	}
  }
}
?>