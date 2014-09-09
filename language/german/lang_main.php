<?php
/*
 * Project:     EQdkp guildrequest
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2011-09-02 10:09:49 +0200 (Fr, 02. Sep 2011) $
 * -----------------------------------------------------------------------
 * @author      $Author: Aderyn $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     guildrequest
 * @version     $Rev: 11183 $
 *
 * $Id: lang_main.php 11183 2011-09-02 08:09:49Z Aderyn $
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
	'mc_layout_types'			=> array('Gitter', 'Medienspezifische Ansicht'),
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
 );

?>