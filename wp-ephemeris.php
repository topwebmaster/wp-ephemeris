<?php
/*
Plugin Name: WordPress Ephemeris Calculator
Plugin URI: http://andrewsfreeman.com/wordpress-ephemeris
Description: Adds shortcode and ajax requests for short zodiacs. SEE README.
Version: 0.1
Author: andrewsfreeman
Author URI: http://andrewsfreeman.com
License: GPL2
*/

class WPephemeris {

	# planets, in the order swiss provides them, with english name and symbol
	public $planets = array(
		0 => array(
			'name' => 'Sun',
			'symbol' => '☉',
		),
		1 => array(
			'name' => 'Moon',
			'symbol' => '☽',
		),
		2 => array(
			'name'=> 'Mercury',
			'symbol' => '☿'
		),
		3 => array(
			'name' => 'Venus',
			'symbol' => '♀'
		),
		4 => array(
			'name' => 'Mars',
			'symbol' => '♂'
		),
		5 => array(
			'name' => 'Jupiter',
			'symbol' => '♃'
		),
		6 => array(
			'name' => 'Saturn',
			'symbol' => '♄'
		),
	);

	# zodiacal signs, keys as swiss abbreviates them, with name and symbol
	public $zodiac = array(
		'ar' => array(
			'name' => 'Aries',
			'symbol' => '♈',
		),
		'ta' => array(
			'name' => 'Taurus',
			'symbol' => '♉',
		),
		'ge' => array(
			'name' => 'Gemini',
			'symbol' => '♊',
		),
		'cn' => array(
			'name' => 'Cancer',
			'symbol' => '♋',
		),
		'le' => array(
			'name' => 'Leo',
			'symbol' => '♌',
		),
		'vi' => array(
			'name' => 'Virgo',
			'symbol' => '♍',
		),
		'li' => array(
			'name' => 'Libra',
			'symbol' => '♎',
		),
		'sc' => array(
			'name' => 'Scorpio',
			'symbol' => '♏',
		),
		'sa' => array(
			'name' => 'Sagittarius',
			'symbol' => '♐',
		),
		'cp' => array(
			'name' => 'Capricorn',
			'symbol' => '♑',
		),
		'aq' => array(
			'name' => 'Aquarius',
			'symbol' => '♒',
		),
		'pi' => array(
			'name' => 'Pisces',
			'symbol' => '♓',
		),
	);

	##
	# __construct()   sets up the whole show for WordPress
	# 
	# adds the shortcode, activates shortcode use in widgets, adds ajax requests
	##
	public function __construct() {
		add_shortcode( 'wp-ephemeris', array( 'WPephemeris', 'get_zodiac' ) );
		add_filter( 'widget_text', 'do_shortcode', 11 );
		add_action( 'wp_ajax_nopriv_wpephemeris', array( $this, 'get_zodiac' ) );
		add_action( 'wp_ajax_wpephemeris', array( $this, 'get_zodiac' ) );
	}

	##
	# get_zodiac() the only function that actually does all the work
	# 
	# The shortcode `[wp-ephemeris]` by default prints the result for the 
	# current time/date. Add the today="false" parameter, and you get the result
	# for my personal birthdate.
	# 
	# timeutc="" and date="" attributes adjusts the date for any arbitrary date
	# and time combination given as dd.mm.yyyy and hh.mmss
	##
	public function get_zodiac( $args ) {

		# extra arguments and defaults as local variables
		# $date, $timeutc, $today
		extract( shortcode_atts( array(
			'date' => date( 'd.m.Y' ),
			'timeutc' => date( 'H.i' ),
		), $args ) );

		# if it's an AJAX request, parse the GET variables.
		if ( defined( 'DOING_AJAX' ) ) :
			$date = $_GET['date'] ? $_GET['date'] : $date;
			$timeutc = $_GET['timeutc'] ? $_GET['timeutc'] : "00.0000";
		endif;

		# run swetest with date information and separate out results by newlines
		$swetest = plugin_dir_path( __FILE__ ) . 'src/swetest';
		$result = `$swetest -b$date -t$timeutc -fTZ -roundmin -head 2>&1`;
		$chart = explode( "\n", $result );

		# trim excess information - swetest repeats the date and time
		foreach ( $chart as $index => $swe_output ) :
			$swe_output_cleaned = split( 'ET ', $swe_output );
			$chart[$index] = $swe_output_cleaned[1];
		endforeach;

		# we need our constants
		$ephem = new WPephemeris;

		$chart = array_slice( $chart , 0, 13 ); # only get planet lines

		$output = ""; # for the shortcode
		$json = array(); # for ajax request
		foreach( $ephem->planets as $index => $planet ) :
			$deg = substr( $chart[$index], 0, 2 ); # degrees is first two chars
			$sign = substr( $chart[$index], 3, 2 ); # sign is next two chars
			$json[] = array(
				$planet,
				$deg,
				$ephem->zodiac[$sign]
			);
			$output .= $planet['symbol'] . " " . $deg . "° ";
			$output .= $ephem->zodiac[$sign]['symbol'] .'<br />';
		endforeach;
		
		# ajax request?
		if ( defined( 'DOING_AJAX' ) ):
			echo json_encode( $json );
			exit();
		endif;
		
		# shortcode
		return $output;
	}
}

new WPephemeris;