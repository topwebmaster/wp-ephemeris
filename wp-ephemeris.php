<?php
/*
Plugin Name: WordPress Ephemeris Calculator
Plugin URI: http://andrewsfreeman.com/wordpress-ephemeris
Description: Provides a birthdate field to calculate the planetary positions at birth. SEE README.
Version: 0.1
Author: andrewsfreeman
Author URI: http://andrewsfreeman.com
License: GPL2
*/

class WPephemeris {
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

	public function __construct() {
		add_shortcode( 'asf-zodiac', array( 'WPephemeris', 'get_zodiac' ) );
		add_filter( 'widget_text', 'do_shortcode', 11 );
		add_action( 'wp_ajax_nopriv_wpephemeris', array( $this, 'get_zodiac' ) );
		add_action( 'wp_ajax_wpephemeris', array( $this, 'get_zodiac' ) );
	}

	public function get_zodiac( $atts ) {
		extract( shortcode_atts( array(
		'date' => '29.05.1991',
		'timeutc' => '12.000',
		'today' => 'true'
		), $atts ) );

		if ( $_GET['date'] ) :
			$today = false;
			$date = $_GET['date'];
		endif;

		if ( $today ) :
			$date = date( 'd.m.Y' );
			$timeutc = date( 'H.i' );
		endif;

		# run swetest with date information and separate out results by newlines
		$swetest = plugin_dir_path( __FILE__ ) . 'src/swetest';
		$result = `$swetest -b$date -t$timeutc -fTZ -roundmin -head 2>&1`;
		$chart = explode( "\n", $result );

		# trim excess information
		foreach ( $chart as $index => $planet ) :
			$chart[$index] = substr( $planet, 23 );
		endforeach;

		$ephem = new WPephemeris;

		# only get planets
		$chart = array_slice( $chart , 0, 13 );

		# return output
		$output = "";
		$wheel = array();
		foreach( $ephem->planets as $index => $planet ) :
			$deg = substr( $chart[$index], 0, 2 );
			$sign = substr( $chart[$index], 3, 2 );
			$wheel[] = array(
				$planet,
				$deg,
				$ephem->zodiac[$sign]
				);
			$output .= '' . $planet['symbol'] . " " . $deg . "° " . $ephem->zodiac[$sign]['symbol'] .'<br />';
		endforeach;
		
		# ajax request?
		if ( defined( 'DOING_AJAX' ) ):
			echo json_encode( $wheel );
			exit();
		endif;
		
		# shortcode
		return $output;
	}
}

new WPephemeris;
