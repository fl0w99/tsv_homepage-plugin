<?php

// Das Beispiel und der Error beginnen ab Zeile 89




/*
  Plugin Name: Event Post
  Plugin URI: https://event-post.com?mtm_campaign=wp-plugin&mtm_kwd=event-post&mtm_medium=dashboard
  Description: Add calendar and/or geolocation metadata on any posts.
  Version: 5.7
  Author: N.O.U.S. Open Useful and Simple
  Contributors: bastho, sabrinaleroy, unecologeek, agencenous
  Author URI: https://apps.avecnous.eu/?mtm_campaign=wp-plugin&mtm_kwd=event-post&mtm_medium=dashboard
  License: GPLv2
  Text Domain: event-post
  Domain Path: /languages/
  Tags: Post,posts,event,date,geolocalization,gps,widget,map,openstreetmap,EELV,calendar,agenda,blocks
 */

/**
 *
 * @package event-post
 */
global $EventPost;
$EventPost = new EventPost();

$EventPost_cache=array();

function EventPost(){
	global $EventPost;
	return $EventPost;
}
function event_post_format_color($color){
	return str_replace('#', '', $color);
}
function event_post_get_all_terms($post_id){
	$taxonomies= get_taxonomies('','names');

	return wp_get_post_terms($post_id, $taxonomies);
}


/**
 * The main class where everything begins.
 *
 * Add calendar and/or geolocation metadata on posts
 */
class EventPost {
	const META_START = 'event_begin';
	const META_END = 'event_end';
	const META_COLOR = 'event_color';
	const META_ICON = 'event_icon';
	// http://codex.wordpress.org/Geodata
	const META_ADD = 'geo_address';
	const META_LAT = 'geo_latitude';
	const META_LONG = 'geo_longitude';
	// https://schema.org/eventStatus
	const META_STATUS = 'event_status';
	// https://schema.org/location
	const META_VIRTUAL_LOCATION = 'event_virtual_location';
	// https://pending.schema.org/eventAttendanceMode
	const META_ATTENDANCE_MODE = 'event_attendance_mode';

	public $list_id;
	public $NomDuMois;
	public $Week;
	public $settings;
	public $dateformat;

	private $pagination;
	public $version = '5.7';
	public $plugin_path ;
	private $script_sufix;

	public $map_interactions;
	public $quick_edit_fields;

	public $Shortcodes;

	public $attendance_modes;
	public $statuses;
	private $is_schema_output=false;
	
	
	
	
	public function example(){
	
		// Die Funktion soll einen Ortsnamen von der Postleitzahl trennen
		// $needle ist in dem Fall der Such-String, also das Leerzeichen zwischen Ort und PLZ
		// strstr() gibt dann alles an Sting ab $needle aus 
		
		$str = '63073 Offenbach';		//Beispiel String
		$needle = ' ';

		echo strstr($str, $needle);		// Hier wird die Ausgabe ausgeführt
							// Die Ausgabe wäre " Offenbach"

	}
	
	
	
	
	/**
	 * Die Funktion wandelt heximal in dezimal um. Gibt einen RGB Array von der gegebenen heximal Farbe zurück 
	 * @param string $color
	 * @return array $color($R, $G, $B)
	 *
	 * Die Fehlermeldung lautet:
	 * Warning: strstr(): Empty needle in /homepages/16/d38267557/htdocs/Blasorchester_2018/wp-content/plugins/event-post/eventpost.php on line 335
	 *
	 * also ist der Such-String $needle -> in dem Fall die zweite Position in der strstr() Klammer leer
	 *
	 * In der Funktionsdefinition wird wahrscheinlich deshalb die Farbe schon belegt, dass falls sie nicht vergeben ist, oder es keine hex zahl ist,
	 * der Converter trotzdem eine 6stellige Hexzahl bekommt
	 */
	public function hex2dec($color = '000000') {
		$tbl_color = array();
		 
		if (!strstr('#', $color)){	
							// Überprüft, ob ein # in dem String Color vorkommt wenn ja wir 1 zurückgegeben
							// Gibt immer 1 zurück, außer $color ist '#' 
			$color = '#' . $color;		// concat
		}
		
		
		// Ab hier wird der Array gebildet
		$tbl_color['R'] = hexdec(substr($color, 1, 2));
		$tbl_color['G'] = hexdec(substr($color, 3, 2));
		$tbl_color['B'] = hexdec(substr($color, 5, 2));
		return $tbl_color;
	}
	
	
	
	// In der folgenden Funktion wird der Converter aufgerufen
	
	/**
	 * Generate, return or output date event datas
	 * @param WP_Post object $post
	 * @param string $class
	 * @filter eventpost_get_single
	 * @return string
	 */
	public function get_single($post = null, $class = '', $context='') {
		if ($post == null) {
			$post = $this->retreive();
		}
		$datas_date = $this->print_date($post, null, $context);
		$datas_cat = $this->print_categories($post, $context);
		$datas_loc = $this->print_location($post, $context);
		$classes = array(
			'event_data',
			'status-'.$post->status,
			'location-type-'.$post->attendance_mode,
			$class
		);
		if ($datas_date != '' || $datas_loc != '') {
			$rgb = $this->hex2dec($post->color);
			return '<div class="' . implode(' ', $classes) . '" style="border-left-color:#' . $post->color . ';background:rgba(' . $rgb['R'] . ',' . $rgb['G'] . ',' . $rgb['B'] . ',0.1)" itemscope itemtype="http://microformats.org/profile/hcard">'
					. apply_filters('eventpost_get_single', $datas_date . $datas_cat . $datas_loc, $post)
					. '</div>';
		}
		return '';
	}
	
