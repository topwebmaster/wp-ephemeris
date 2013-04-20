<?php
$planets = array(
	0 => 'Sun',
	1 => 'Moon',
	2 => 'Mercury',
	3 => 'Venus',
    4 => 'Mars',
    5 => 'Jupiter',
	6 => 'Saturn',
	7 => 'Uranus',
	8 => 'Neptune',
	9 => 'Pluto',
	10 => 'mean Node',
	11 => 'true Node',
	12 => 'mean Apogee'
);

$zodiac = array(
	'ar' => 'Aries',
	'ta' => 'Taurus',
	'ge' => 'Gemini',
	'cn' => 'Cancer',
	'le' => 'Leo',
	'vi' => 'Virgo',
	'li' => 'Libra',
	'sc' => 'Scorpio',
	'sa' => 'Sagittarius',
	'cp' => 'Capricorn',
	'aq' => 'Aquarius',
	'pi' => 'Pisces',
);

# run swetest with date information and separate out results by newlines
$result = shell_exec('./src/swetest -b29.05.1991 -t12.000 -fTZ -roundmin -head');
$chart = explode( "\n", $result );

# trim excess information
foreach ( $chart as $index => $planet ) :
	$chart[$index] = substr( $planet, 23 );
endforeach;


# only get planets
$chart = array_slice( $chart , 0, 13 );

# print it all baby!
foreach( $planets as $index => $planet ) :
	$deg = substr( $chart[$index], 0, 2 );
	$sign = substr( $chart[$index], 3, 2 );
	echo $planet . " is " . $deg . " degrees " . $zodiac[$sign] .'.';
	echo "\n";
endforeach;