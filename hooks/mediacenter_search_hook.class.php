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
if (!class_exists('mediacenter_search_hook')){
	class mediacenter_search_hook extends gen_class{

		/**
		* hook_search
		* Do the hook 'search'
		*
		* @return array
		*/
		public function search(){
			// build search array
			$search = array(
				'mediacenter' => array(
					'category'		=> $this->user->lang('mediacenter'),
					'module'		=> 'mediacenter_media',
					'method'		=> 'search',
					'permissions'	=> array('u_mediacenter_view'),
				),
			);
			return $search;
		}
	}
}
?>