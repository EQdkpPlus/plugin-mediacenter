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
  'mediacenter_long_desc'          => 'Erstelle Gallerien, stelle deinen Benutzern Downloads zur Verfügung, und binde Videos ein',
  
	'mc_plugin_not_installed'	=> 'Das MediaCenter-Plugin ist nicht installiert.',
	'mc_config_saved'			=> 'Die Einstellungen wurden erfolgreich gespeichert.',
	'mc_edit_album'				=> 'Album bearbeiten',
	'mc_massupload'				=> 'Massenupload',
	'mc_f_album_name'			=> 'Albumname',
	'mc_mediacenter'			=> 'Mediathek',
	'mc_f_description'			=> 'Beschreibung',
	'mc_f_personal_album'		=> 'Persönliches Album',
	'mc_f_personal_album_help'	=> 'In ein persönliches Album kann nur der Eigentümer oder Administratoren Bilder hinzufügen',
	'mc_f_category'				=> 'Kategorie',
	'mc_edit_media'				=> 'Datei bearbeiten',
	'mc_new_album'				=> 'Neues Album erstellen',
	'mc_types'					=> array('Datei', 'Video', 'Bild'),
	'mc_f_album'				=> 'Album',
	'mc_f_name'					=> 'Name',
	'mc_f_type'					=> 'Dateityp',
	'mc_f_tags'					=> 'Tags',
	'mc_f_tags_help'			=> 'Trage hier Tags ein, durch Kommas getrennt',
	'mc_f_previewimage'			=> 'Vorschaubild',
	'mc_f_previewimage_help'	=> 'Das Vorschaubild wird bei einigen Videoplattformen automatisch hinzugefügt',
	'mc_f_externalfile'			=> 'Externer Link zur Datei/Video',
	'mc_f_externalfile_help'	=> 'Trage hier den Link zu Videos auf bekannten Videoplattformen ein, bzw. zu einer externen Downloadmöglichkeit für Dateien',
	'mc_f_file'					=> 'Datei',
	'mc_f_filename'				=> 'Dateiname',
	'mc_edit_image'				=> 'Bild bearbeiten',
	'mc_ei_resize'				=> 'Bild zuschneiden',
	'mc_ei_mirror_h'			=> 'Bild horizontal spiegeln',
	'mc_ei_mirror_v'			=> 'Bild vertikal spiegeln',
	'mc_ei_rotate_r'			=> 'Bild um 90 Grad nach rechts drehen',
	'mc_ei_rotate_l'			=> 'Bild um 90 Grad nach links drehen',
	'mc_ei_restore'				=> 'Originalbild wiederherstellen',
	'mc_or'						=> 'oder',
	'mc_select_file'			=> 'Datei auswählen',
	'mc_select_files'			=> 'Dateien auswählen',
	'mc_drop_file'				=> 'Datei hierher ziehen',
	'mc_drop_files'				=> 'Dateien hierher ziehen',
	'mc_manage_media'			=> 'Medien verwalten',
	'mc_manage_categories'		=> 'Kategorien verwalten',
	'mc_add_category'			=> 'Kategorie hinzufügen',
	'mc_confirm_delete_category'=> 'Bist du sicher, dass Du die Kategorien %s wirklich löschen willst? Alle enthaltenen Medien werden dabei auch gelöscht!',
	'mc_perm_ada'				=> 'Alben hinzufügen',	
	'mc_layout_types'			=> array('Gitter', 'Liste'),
	'mc_allow_comments'			=> 'Kommentare erlauben',
	'mc_allow_voting'			=> 'Bewertungen erlauben',
	'mc_default_layout'			=> 'Standardansicht',
	'mc_media_type'				=> 'Erlaubte Typen',
	'mc_save_category'			=> 'Kategorie speichern',
	'mc_add_media'				=> 'Medium hinzufügen',
	'mc_delete_album'			=> 'Album löschen',
	'mc_move_album'				=> 'In Album verschieben',
	'mc_confirm_delete_media'	=> 'Bist du sicher, dass Du die Medien %s wirklich löschen willst?',
	'mc_confirm_delete_album'	=> 'Bist du sicher, dass Du das ausgewählte Album löschen möchtest? Alle enthaltenen Medien werden ebenfalls gelöscht!',
	'mc_selected_media'			=> 'Ausgewählte Medien',
	'mc_f_views'				=> 'Aufrufe',
	'mc_f_published'			=> 'Veröffentlicht',
	'mc_f_featured'				=> 'Hervorgehoben',
	'mc_f_reported'				=> 'Gemeldet',
	'mc_f_delete_comments'		=> 'Kommentare löschen',
	'mc_f_delete_votes'			=> 'Bewertungen löschen',
	'mc_f_downloads'			=> 'Anzahl Downloads',
	'mc_insert_media'			=> 'Medien einfügen',
		
	'action_category_deleted'	=> 'Kategorie gelöscht',
	'action_category_update'	=> 'Kategorie geändert',
	'action_category_added'		=> 'Kategorie hinzugefügt',
	'action_album_deleted'		=> 'Album gelöscht',
	'action_album_update'		=> 'Album geändert',
	'action_album_added'		=> 'Album hinzugefügt',
	'action_media_deleted'		=> 'Medium gelöscht',
	'action_media_update'		=> 'Medium geändert',
	'action_media_added'		=> 'Medium hinzugefügt',
	'action_mediacenter_reset_votes' => 'Bewertungen zurückgesetzt',
		
	'mc_fs_extensions'			=> 'Dateiendungen',
	'mc_f_extensions_image'		=> 'Erlaubte Dateiendungen für Typ "Bild"',
	'mc_f_extensions_image_help'=> 'Trage hier, durch Komma getrennt, die erlaubten Dateiendungen für Bilder ein.',	
	'mc_f_extensions_file'		=> 'Erlaubte Dateiendungen für Typ "Datei"',
	'mc_f_extensions_file_help' => 'Trage hier, durch Komma getrennt, die erlaubten Dateiendungen für Dateien ein.',
	'mc_f_extensions_video'		=> 'Erlaubte Dateiendungen für Typ "Video"',
	'mc_f_extensions_video_help'=> 'Trage hier, durch Komma getrennt, die erlaubten Dateiendungen für lokale Videos ein.',
	'mc_fs_watermark'			=> 'Wasserzeichen',
	'mc_f_watermark_enabled'	=> 'Wasserzeichen für Bilder aktivieren',
	'mc_f_watermark_position'	=> 'Position des Wasserzeichens',
	'mc_watermark_positions'	=> array('rt' => 'Rechts oben', 'rb' => 'Rechts unten', 'lb' => 'Links unten', 'lt' => 'Links oben'),
	'mc_f_watermark_transparency'=> 'Transparenz des Wasserzeichens',
	'mc_f_watermark_logo'		=> 'Wasserzeichen',
	'mc_f_watermark_logo_help'	=> 'Als Wasserzeichen eignen sich sehr gut transparente Bilder im png-Format (Transparenz durch Alphakanal), aber auch jedes andere Bild.',
	'mc_watermark_transparency' => 'Transparenz',
	'mc_f_per_page'				=> 'Einträge pro Seite',
	'mc_fs_defaults'			=> 'Standardeinstellungen',
	'mc_max_uploadsize'			=> 'Maximale Dateigröße für Uploads',
	'mc_start_massupload'		=> 'Massenupload starten',
	'mc_author'					=> 'Autor',
	'mc_date'					=> 'Hochgeladen',
	'mc_video_info'				=> 'Video-Informationen',
	'mc_image_info'				=> 'Bild-Informationen',
	'mc_file_info'				=> 'Datei-Informationen',
	'mc_more_actions'			=> 'Weitere Aktionen',
	'mc_embedd_video'			=> 'Video einbetten',
	'mc_embedd_image'			=> 'Bild einbetten',
	'mc_embedd_file'			=> 'Datei einbetten',
	'mc_edit_media'				=> 'Medium bearbeiten',
	'mc_delete_media'			=> 'Medium löschen',
	'mc_imagedimensions'		=> 'Bildmaße',
	'mc_size'					=> 'Dateigröße',
	'mc_Camera'					=> 'Kamera',
	'mc_ExposureTime'			=> 'Belichtungszeit',
	'mc_CreationTime'			=> 'Aufnahmedatum',
	'mc_FNumber'				=> 'Blendenzahl',
	'mc_FocalLength'			=> 'Brennweite',
	'mc_ISOSpeedRatings'		=> 'ISO-Empfindlichkeit',
	'mc_ShutterSpeedValue'		=> 'Verschlusszeit',
	'mc_ApertureValue'			=> 'Blendenvorwahl',
	'mc_downloads'				=> 'Downloads',
	
	'mc_f_show_featured'		=> 'Vorgestellte Medien',
	'mc_f_show_newest'			=> 'Neueste Medien',
	'mc_f_show_categories'		=> 'Kategorien',
	'mc_f_show_mostviewed'		=> 'Am meisten angesehen',
	'mc_f_show_latestcomments'	=> 'Neueste Kommentare',
	'mc_fs_index_page'			=> 'Startseite',
	'mc_coords'					=> 'Aufnahmeort',
		
	'mc_embedd_bbcode_big'		=> 'BBCode (groß)',
	'mc_embedd_bbcode_small'	=> 'BBCode (klein)',
	'mc_embedd_html_big'		=> 'HTML (groß)',
	'mc_embedd_html_small'		=> 'HTML (klein)',
	'mc_embedd_editor'			=> 'Editor-Code',
	'mc_embedd'					=> 'Medien teilen',
	'mc_report_media'			=> 'Inhalt melden',
	'mc_report_reason'			=> 'Grund der Meldung',
	'mc_report_success'			=> 'Der Inhalt wurde erfolgreich gemeldet',
	'mc_delete_report'			=> 'Meldung löschen',
		
	'mc_sort_options'			=> array(
  				'1|asc' => 'Name aufsteigend',
  				'1|desc'=> 'Name absteigend',
  				'2|asc' => 'Typ aufsteigend',
  				'2|desc'=> 'Typ absteigend',
  				'3|asc' => 'Datum aufsteigend',
  				'3|desc'=> 'Datum absteigend',
  				'4|asc' => 'Views aufsteigend',
  				'4|desc'=> 'Views absteigend',
  			),
	'mc_albums'					=> 'Alben',
	'mc_notify_unpublished_media' => "%1\$s unveröffentlichte Medien in Kategorie %2\$s",
	'mc_download'				=> 'Datei herunterladen',
	'mc_external'				=> 'Externer Link',
	
	'user_sett_f_ntfy_mediacenter_media_comment_new' => 'MediaCenter: Neuer Kommentar bei abonnierten Medien',
	'user_sett_f_ntfy_mediacenter_media_new' => 'MediaCenter: Neue Medien',
	'user_sett_f_ntfy_mediacenter_media_reported' => 'MediaCenter: Gemeldete Medien',
	'user_sett_f_ntfy_mediacenter_media_unpublished' => 'MediaCenter: Unveröffentlichte Medien',
	"mc_notify_reported_media" => "{PRIMARY} hat die Datei \"{ADDITIONAL}\" gemeldet",
	"mc_notify_new_media" => "{PRIMARY} hat eine neue Datei in Kategorie \"{ADDITIONAL}\" hinzugefügt",
	"mc_notify_new_media_grouped" => "Es wurden {COUNT} neue Dateien in Kategorie \"{ADDITIONAL}\" hinzugefügt",
	"mc_notify_new_comment" => "{PRIMARY} haben einen Kommentar zur Datei \"{ADDITIONAL}\" geschrieben",
	"mc_notify_new_comment_grouped" => "{PRIMARY} hat einen Kommentar zur Datei \"{ADDITIONAL}\" geschrieben",
		
	"mc_mymedia" => "Meine Medien",
	"mc_my_albums" => "Meine Alben",
	"mc_selected_media" => "ausgewählte Dateien",
	"mc_change_state_publish" => "Veröffentlichen",
	"mc_change_state_unpublish" => "Unveröffentlichen",
	"mc_perm_rea" => "Ansehen",	
	"mc_manage_category" => "Kategorie bearbeiten",
 );

?>
