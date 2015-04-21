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

$lang = array(
  'mediacenter'                    => 'MediaCenter',

  // Description
  'mediacenter_short_desc'         => 'MediaCenter',
  'mediacenter_long_desc'          => 'Create galleries, provide downloads, and embed videos',
  
	'mc_plugin_not_installed'	=> 'The MediaCenter-Plugin is not installed.',
	'mc_config_saved'			=> 'The configuration has been successfully saved.',
	'mc_edit_album'				=> 'Edit album',
	'mc_massupload'				=> 'Mass upload',
	'mc_f_album_name'			=> 'Album name',
	'mc_mediacenter'			=> 'MediaCenter',
	'mc_f_description'			=> 'Description',
	'mc_f_personal_album'		=> 'Personal album',
	'mc_f_personal_album_help'	=> 'In a personal album only the owner and administrators are eligible to add new media',
	'mc_f_category'				=> 'Category',
	'mc_edit_media'				=> 'Edit file',
	'mc_new_album'				=> 'Add new album',
	'mc_types'					=> array('File', 'Video', 'Image'),
	'mc_f_album'				=> 'Album',
	'mc_f_name'					=> 'Name',
	'mc_f_type'					=> 'File type',
	'mc_f_tags'					=> 'Tags',
	'mc_f_tags_help'			=> 'Insert tags here, separated by commas',
	'mc_f_previewimage'			=> 'Preview image',
	'mc_f_previewimage_help'	=> 'The preview image will be added automatically on some video platforms',
	'mc_f_externalfile'			=> 'External link to the file/video',
	'mc_f_externalfile_help'	=> 'Insert the link to videos on popular video platforms or to an external download location here',
	'mc_f_file'					=> 'File',
	'mc_f_filename'				=> 'File name',
	'mc_edit_image'				=> 'Edit image',
	'mc_ei_resize'				=> 'Cut image',
	'mc_ei_mirror_h'			=> 'Mirror image horizontal',
	'mc_ei_mirror_v'			=> 'Mirror image vertical',
	'mc_ei_rotate_r'			=> 'Rotate image 90 degrees clockwise',
	'mc_ei_rotate_l'			=> 'Rotate image 90 degrees counter-clockwise',
	'mc_ei_restore'				=> 'Restore original image',
	'mc_or'						=> 'or',
	'mc_select_file'			=> 'Select file',
	'mc_select_files'			=> 'Select files',
	'mc_drop_file'				=> 'Drop file here',
	'mc_drop_files'				=> 'Drop files here',
	'mc_manage_media'			=> 'Manage media',
	'mc_manage_categories'		=> 'Manage categories',
	'mc_add_category'			=> 'Add category',
	'mc_confirm_delete_category'=> 'Do you really want to delete this categories %s? All contained media will be deleted too!',
	'mc_perm_ada'				=> 'Add album',	
	'mc_layout_types'			=> array('Grid', 'List'),
	'mc_allow_comments'			=> 'Allow comments',
	'mc_allow_voting'			=> 'Allow rating',
	'mc_default_layout'			=> 'Default layout',
	'mc_media_type'				=> 'Permitted types',
	'mc_save_category'			=> 'Save category',
	'mc_add_media'				=> 'Add media',
	'mc_delete_album'			=> 'Delete album',
	'mc_move_album'				=> 'Move to album',
	'mc_confirm_delete_media'	=> 'Do you really want to delete this media %s?',
	'mc_confirm_delete_album'	=> 'Do you really want to delete the selected album? All contained media will be deleted too!',
	'mc_selected_media'			=> 'Selected media',
	'mc_f_views'				=> 'Views',
	'mc_f_published'			=> 'Published',
	'mc_f_featured'				=> 'Featured',
	'mc_f_reported'				=> 'Reported',
	'mc_f_delete_comments'		=> 'Delete comments',
	'mc_f_delete_votes'			=> 'Delete ratings',
	'mc_f_downloads'			=> 'Quantity of downloads',
	'mc_insert_media'			=> 'Insert media',
		
	'action_category_deleted'	=> 'Category deleted',
	'action_category_update'	=> 'Category updated',
	'action_category_added'		=> 'Category added',
	'action_album_deleted'		=> 'Album deleted',
	'action_album_update'		=> 'Album updated',
	'action_album_added'		=> 'Album added',
	'action_media_deleted'		=> 'Media deleted',
	'action_media_update'		=> 'Media updated',
	'action_media_added'		=> 'Media added',
	'action_mediacenter_reset_votes' => 'Ratings resetted',
		
	'mc_fs_extensions'			=> 'File extensions',
	'mc_f_extensions_image'		=> 'Allowed file extensions for type "Image"',
	'mc_f_extensions_image_help'=> 'Insert the allowed file extensions for images here, separated by commas.',	
	'mc_f_extensions_file'		=> 'Allowed file extensions for type "File"',
	'mc_f_extensions_file_help' => 'Insert the allowed file extensions for files here, separated by commas.',
	'mc_f_extensions_video'		=> 'Allowed file extensions for type "Video"',
	'mc_f_extensions_video_help'=> 'Insert the allowed file extensions for videos here, separated by commas.',
	'mc_fs_watermark'			=> 'Watermark',
	'mc_f_watermark_enabled'	=> 'Enable watermark for images',
	'mc_f_watermark_position'	=> 'Position of watermark',
	'mc_watermark_positions'	=> array('rt' => 'Top right', 'rb' => 'Bottom right', 'lb' => 'Bottom left', 'lt' => 'Top left'),
	'mc_f_watermark_transparency'=> 'Watermark transparency',
	'mc_f_watermark_logo'		=> 'Watermark',
	'mc_f_watermark_logo_help'	=> 'Transparent images in png format (alpha channel) are suitable to use as a watermark. But you can also use any other image.',
	'mc_watermark_transparency' => 'Transparency',
	'mc_f_per_page'				=> 'Entries per page',
	'mc_fs_defaults'			=> 'Default configuration',
	'mc_max_uploadsize'			=> 'Maximum file size for Uploads',
	'mc_start_massupload'		=> 'Start mass upload',
	'mc_author'					=> 'Author',
	'mc_date'					=> 'Uploaded',
	'mc_video_info'				=> 'Video information',
	'mc_image_info'				=> 'Image information',
	'mc_file_info'				=> 'File information',
	'mc_more_actions'			=> 'More actions',
	'mc_embedd_video'			=> 'Embed video',
	'mc_embedd_image'			=> 'Embed image',
	'mc_embedd_file'			=> 'Embed file',
	'mc_edit_media'				=> 'Edit media',
	'mc_delete_media'			=> 'Delete media',
	'mc_imagedimensions'		=> 'Image dimensions',
	'mc_size'					=> 'File size',
	'mc_Camera'					=> 'Camera',
	'mc_ExposureTime'			=> 'Exposure time',
	'mc_CreationTime'			=> 'Creation time',
	'mc_FNumber'				=> 'F-number',
	'mc_FocalLength'			=> 'Focal length',
	'mc_ISOSpeedRatings'		=> 'ISO speed ratings',
	'mc_ShutterSpeedValue'		=> 'Shutter speed',
	'mc_ApertureValue'			=> 'Aperture',
	'mc_downloads'				=> 'Downloads',
	
	'mc_f_show_featured'		=> 'Featured media',
	'mc_f_show_newest'			=> 'Newest media',
	'mc_f_show_categories'		=> 'Categories',
	'mc_f_show_mostviewed'		=> 'Most viewed',
	'mc_f_show_latestcomments'	=> 'Latest comments',
	'mc_fs_index_page'			=> 'Index page',
	'mc_coords'					=> 'Coordinates',
		
	'mc_embedd_bbcode_big'		=> 'BBCode (big)',
	'mc_embedd_bbcode_small'	=> 'BBCode (small)',
	'mc_embedd_html_big'		=> 'HTML (big)',
	'mc_embedd_html_small'		=> 'HTML (small)',
	'mc_embedd_editor'			=> 'Editor code',
	'mc_embedd'					=> 'Share media',
	'mc_report_media'			=> 'Report media',
	'mc_report_reason'			=> 'Reason for reporting',
	'mc_report_success'			=> 'The media has been reported successfully',
	'mc_delete_report'			=> 'Delete report',
		
	'mc_sort_options'			=> array(
  				'1|asc' => 'Name ascending',
  				'1|desc'=> 'Name descending',
  				'2|asc' => 'Type ascending',
  				'2|desc'=> 'Type descending',
  				'3|asc' => 'Date ascending',
  				'3|desc'=> 'Date descending',
  				'4|asc' => 'Views ascending',
  				'4|desc'=> 'Views descending',
  			),
	'mc_albums'					=> 'Albums',
	'mc_notify_unpublished_media' => "%1\$s unpublished media in category %2\$s",
	'mc_download'				=> 'Download file',
	'mc_external'				=> 'External link',
	
	'user_sett_f_ntfy_mediacenter_media_comment_new' => 'MediaCenter: New comment at subscribed media',
	'user_sett_f_ntfy_mediacenter_media_new' => 'MediaCenter: New media',
	'user_sett_f_ntfy_mediacenter_media_reported' => 'MediaCenter: Reported media',
	'user_sett_f_ntfy_mediacenter_media_unpublished' => 'MediaCenter: Unpublished media',
	"mc_notify_reported_media" => "{PRIMARY} has reported file \"{ADDITIONAL}\"",
	"mc_notify_new_media" => "{PRIMARY} has added a new file to category \"{ADDITIONAL}\"",
	"mc_notify_new_media_grouped" => "There are {COUNT} new files added to category \"{ADDITIONAL}\"",
	"mc_notify_new_comment" => "{PRIMARY} has written a new comment to file \"{ADDITIONAL}\"",
	"mc_notify_new_comment_grouped" => "{PRIMARY} has written new comments to file \"{ADDITIONAL}\"",
	"mc_personal_album_info" => "This is a personal album from %s",
 );

?>
