<?php
/*	Project:	EQdkp-Plus
 *	Package:	mc_most_viewed_media Plugin
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

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found'); exit;
}

/*+----------------------------------------------------------------------------
  | mc_most_viewed_media_portal
  +--------------------------------------------------------------------------*/
class mc_most_viewed_media_portal extends portal_generic{

	/**
	* Portal path
	*/
	protected static $path = 'mc_most_viewed_media';
	/**
	* Portal data
	*/
	protected static $data = array(
		'name'			=> 'MediaCenter Featured Media Module',
		'version'		=> '0.1.0',
		'author'		=> 'GodMod',
		'contact'		=> 'https://eqdkp-plus.eu',
		'description'	=> 'Displays Most Viewed Media from the MediaCenter',
		'lang_prefix'	=> 'mc_',
		'multiple'		=> true,
	);
	
	protected static $apiLevel = 20;

	protected static $multiple = true;
	
	public function get_settings($state){
		$arrCategories = $this->pdh->geth('mediacenter_albums', 'album_tree', array(false, false, true));
		
		$settings = array(
				'output_count_limit'	=> array(
						'type'		=> 'spinner',
						'default'	=> '1',
				),
				'categories'	=> array(
						'type'		=> 'multiselect',
						'options'	=> $arrCategories,
				),
		);
		
		return $settings;
	}

	/**
	* output
	* Get the portal output
	*
	* @returns string
	*/
	public function output(){
		$output = "";
		$arrMedia = array();
		
		$arrCategories = $this->config('categories');
		$intNumber = $this->config('output_count_limit');
		if(!$arrCategories) return $this->user->lang('mc_portal_settings_warning');
		foreach($arrCategories as $strCategorieString){
			if(substr($strCategorieString, 0, 1) === 'c'){
				$intCategoryId = intval(substr($strCategorieString, 1));
				//Check Permissions
				$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
				if (!$arrPermissions['read']) continue;
				if(!$this->pdh->get('mediacenter_categories', 'published', array($intCategoryId))) continue;
				$arrCategoryMedia = $this->pdh->get('mediacenter_media', 'id_list_for_category', array($intCategoryId, true, true));
				if(is_array($arrCategoryMedia)) $arrMedia = array_merge($arrMedia, $arrCategoryMedia);
				
			} else {
				$intAlbumId = intval($strCategorieString);
				$intCategoryId = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumId));
				//Check Permissions
				$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryId, $this->user->id));
				if (!$arrPermissions['read']) continue;
				if(!$this->pdh->get('mediacenter_categories', 'published', array($intCategoryId))) continue;
				$arrCategoryMedia = $this->pdh->get('mediacenter_media', 'id_list', array($intAlbumId, true));
				if(is_array($arrCategoryMedia)) $arrMedia = array_merge($arrMedia, $arrCategoryMedia);
			}
		}
		
		$arrMedia = array_unique($arrMedia);
		$arrMedia = $this->pdh->sort($arrMedia, 'mediacenter_media', 'views', 'desc');
		
		$arrMedia = $this->pdh->limit($arrMedia, 0, $intNumber);
		
		$output = '<div class="mcPortalBox colorswitch">';
		foreach($arrMedia as $intMediaID){
			$strPath = $this->server_path.$this->controller_path_plain.$this->pdh->get('mediacenter_media', 'path', array($intMediaID));
			$arrMediaData = $this->pdh->get('mediacenter_media', 'data', array($intMediaID));
			$intViews =  $this->pdh->get('mediacenter_media', 'views', array($intMediaID));
			if((int)$arrMediaData['type'] === 0){
				$strAuthor = $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
				$strDate =  $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
				$strCategory = (strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? $this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true));
				
				$strOut = '<div>';
				$strOut .= '<div class="mcPreviewImageSmall file"><a href="'.$strPath.'">'.$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 1)).'</a></div>';
				$strOut .= '<div class="mcBoxContent"><h3><a href="'.$strPath.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a></h3>
					'.$this->pdh->geth('mediacenter_media', 'type', array($intMediaID)).' - '.$strDate.' - <i class="fa fa-eye"></i> '.$intViews.'<br />'.$strAuthor.' - '.$strCategory.'
					</div>';
				$strOut .= '</div>';
			
				$output .= $strOut;
			}elseif($arrMediaData['type'] === 1){
				$strAuthor = $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
				$strDate =  $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
				$strCategory = (strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? $this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true));
				
				$strOut = '<div>';
				$strOut .= '<div class="mcPreviewImageSmall video"><a href="'.$strPath.'">'.$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)).'</a></div>';
				$strOut .= '<div class="mcBoxContent"><h3><a href="'.$strPath.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a></h3>
					'.$this->pdh->geth('mediacenter_media', 'type', array($intMediaID)).' - '.$strDate.' - <i class="fa fa-eye"></i> '.$intViews.'<br />'.$strAuthor.' - '.$strCategory.'
					</div>';
				$strOut .= '</div>';
			
				$output .= $strOut;
			} else {
				$strAuthor = $this->core->icon_font('fa-user').' '.$this->pdh->geth('user', 'name', array($this->pdh->get('mediacenter_media', 'user_id', array($intMediaID)),'', '', true));
				$strDate =  $this->time->createTimeTag($this->pdh->get('mediacenter_media', 'date', array($intMediaID)), $this->pdh->geth('mediacenter_media', 'date', array($intMediaID)));
				$strCategory = (strlen($this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)))) ? $this->pdh->geth('mediacenter_media', 'album_id', array($intMediaID, true)): $this->pdh->geth('mediacenter_media', 'category_id', array($intMediaID, true));
				
				$strOut = '<div>';
				$strOut .= '<div class="mcPreviewImageSmall image"><a href="'.$strPath.'">'.$this->pdh->geth('mediacenter_media', 'previewimage', array($intMediaID, 2)).'</a></div>';
				$strOut .= '<div class="mcBoxContent"><h3><a href="'.$strPath.'">'.$this->pdh->get('mediacenter_media', 'name', array($intMediaID)).'</a></h3>
					'.$this->pdh->geth('mediacenter_media', 'type', array($intMediaID)).' - '.$strDate.' - <i class="fa fa-eye"></i> '.$intViews.'<br />'.$strAuthor.' - '.$strCategory.'
					</div>';
				$strOut .= '</div>';
			
				$output .= $strOut;
			}
		}
		$output .= '</div>';
		return $output;
	}
}

?>