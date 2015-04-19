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

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found');exit;
}

/*+----------------------------------------------------------------------------
  | mediacenter_portal_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('mediacenter_portal_hook')){
	class mediacenter_portal_hook extends gen_class{

		public function portal(){
			//Check for unpublished media
			$arrCategories = $this->pdh->get('mediacenter_categories', 'unpublished_articles_notify', array());
			if (count($arrCategories) > 0 && $this->user->check_auth('a_mediacenter_manage',false)){
				foreach($arrCategories as $intCategoryID => $intUnpublishedCount){
					$this->ntfy->add_persistent(
							'mediacenter_media_unpublished',
							sprintf($this->user->lang('mc_notify_unpublished_media'), $intUnpublishedCount, $this->pdh->get('mediacenter_categories', 'name', array($intCategoryID))),
							$this->server_path.'plugins/mediacenter/admin/manage_media.php'.$this->SID.'&amp;cid='.$intCategoryID,
							1,
							'fa-picture-o'
					);
				}
			}
			
			
		}
	}
}
?>