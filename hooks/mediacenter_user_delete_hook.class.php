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
  | mediacenter_search_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('mediacenter_user_delete_hook')){
	class mediacenter_user_delete_hook extends gen_class{

		/**
		* hook_search
		* Do the hook 'search'
		*
		* @return array
		*/
		public function user_delete($data){
			$userID = $data['user_id'];
			
			$arrUserMedia = $this->pdh->get('mediacenter_media', 'my_media', array($userID));
			
			foreach($arrUserMedia as $intMediaID){
				$this->pdh->put('mediacenter_media', 'delete', array($intMediaID));
			}
			
			$arrUserAlbums = $this->pdh->get('mediacenter_albums', 'my_albums', array($userID));
			
			foreach($arrUserAlbums as $intAlbumID){
				$this->pdh->put('mediacenter_albums', 'delete_album', array($intAlbumID));
			}
			
			$this->pdh->enqueue_hook('mediacenter_albums_update');
			$this->pdh->enqueue_hook('mediacenter_media_update');
			$this->pdh->enqueue_hook('mediacenter_categories_update');
			
			$this->pdh->process_hook_queue();
			
			return true;
		}
	}
}
?>