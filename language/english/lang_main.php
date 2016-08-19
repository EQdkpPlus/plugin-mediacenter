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
	'mc_embedly_hint'			=> 'To insert Videos, you need a key from  <a href="https://embed.ly/">embedly</a>. You can get one free <a href="https://embed.ly/">here</a> and add it to the field at the EQdkp Plus Settings.',
			
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
	'mc_publish_media'			=> 'Publish media',
	'mc_unpublish_media'			=> 'Unpublish media',
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
	'mc_f_show_bestrated'		=> 'Best rated media',
	'mc_fs_index_page'			=> 'Index page',
	'mc_coords'					=> 'Location',
		
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
	'mc_already_reported'		=> 'The medie already has been reported.',
		
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
		
	"mc_mymedia" => "My Media",
	"mc_my_albums" => "My Albums",
	"mc_selected_media" => "select Files",
	"mc_change_state_publish" => "Publish",
	"mc_change_state_unpublish" => "Unpublish",
	"mc_perm_rea" => "View",
	"mc_manage_category" => "Edit category",
	"mc_filter_media_admin" => array(
			"unpub"		=> "Unpublished media only",
			"reported"	=> "Reported media only",
	),
	"mc_view_report" => "View report",
	"mc_fs_maps" => "Maps",
	"mc_f_show_maps" => "Show map with the Location where the Image was taken",
	
	/* Portal moduls */
	"mc_f_output_count_limit" => "Number of shown media",
	"mc_f_categories" => "Select categories",
	"mc_featured_media" => "Featured media",
	"mc_featured_media_name" => "MediaCenter: featured media",
	"mc_latest_media" => "Newest media",
	"mc_latest_media_name" => "MediaCenter: newest media",
	"mc_most_viewed_media" => "Most viewed media",
	"mc_most_viewed_media_name" => "MediaCenter: most viewed media",
	"mc_random_media" => "Random media",
	"mc_random_media_name" => "MediaCenter: random media",
	"mc_portal_settings_warning" => "You have to do make some settings at the modulesettings so the module can show some media.",
	"mc_show_map" => "Show map",
		
	"mc_index_files" => "Import files",
	"mc_index_files_category_help" => "It is recommended to select a category where all media types are allowed in order to import all files.",
	"mc_index_finfo_warning" => "The files couldn't be imported, because the PHP Extension finfo is not enabled.",
	"mc_index_info" => "On this page, you can import files from a previous installation of the MediaCenter. You should select a category where all media types are allowed to import all files.",
	"mc_index_info_old" => "There are files from a previous installation of the MediaCenter Plugin. Click this link to import the existing files.",
		
	"mc_personal_album_info" => "This is a personal album from %s",
	"mc_statistics" => "Statistics",
	"mc_statistics_media_count" => "%s media",
	"mc_statistics_file_count" => "%s files",
	"mc_statistics_image_count" => "%s images",
	"mc_statistics_video_count" => "%s videos",
	"mc_statistics_views" => "%s views",
	"mc_statistics_downloads" => "%s downloads",
	"mc_statistics_size" => "%s total size",
	"mc_statistics_comments" => "%s comments",
		
	"mc_f_watermark_type" => "Watermark type",
	"mc_f_watermark_text" => "Watermark text",
	"mc_f_watermark_text_help" => "The placeholder {USER} will be replaced by the image owner",
	"mc_watermark_type_text" => "Text",
	"mc_watermark_type_image" => "Image",
	"mc_f_watermark_fontsize" => "Watermark fontsize",
		
	"mc_editmedia_save_error_wrong_type" => "The type of the uploaded file is not allowed in this category.", 	
	"mc_editmedia_save_error_wrong_extension" => "The extension of this file is not allowed for this filetype.",
	"mc_editmedia_save_error_wrong_no_file" => "File not found.",
	"mc_editmedia_save_error_too_big" => "Your uploaded file is too big.",
	"mc_editmedia_save_error_embedly_error" => "The Video is not supported by embedly, or no embedly key found.",
		
	"mc_fs_exif" => "EXIF",
	"mc_f_show_exif" => "Show EXIF-Data of images, if available",
	"mc_f_rotate_exif" => "Rotate images accordingly to their EXIF-data",
		
	"plugin_statistics_mediacenter_media" => "MediaCenter: Uploads",
	"plugin_statistics_mediacenter_views" => "MediaCenter: Views",
	"plugin_statistics_mediacenter_downloads" => "MediaCenter: Downloads",
 );

?>
