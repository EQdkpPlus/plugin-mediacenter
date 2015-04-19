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