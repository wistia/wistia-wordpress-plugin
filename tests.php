<?php

include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wistia-anti-mangler.php';

function test_http() {
	unset( $_SERVER['https'] ); // @codingStandardsIgnoreLine
	unset( $_SERVER['HTTPS'] ); // @codingStandardsIgnoreLine
	$wam = new WistiaAntiMangler();

	$scripts = array( 'testing' );
	// test http
	$expected = '<script charset="ISO-8859-1" src="http://fast.wistia.com/static/concat/testing.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: single http request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	$scripts = array( 'example1', 'example2' );
	$expected = '<script charset="ISO-8859-1" src="http://fast.wistia.com/static/concat/example1%2Cexample2.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: multi http request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	@$_SERVER['https'] = 'Off'; // @codingStandardsIgnoreLine
	$scripts = array( 'testing' );
	// test http
	$expected = '<script charset="ISO-8859-1" src="http://fast.wistia.com/static/concat/testing.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: single http request does not give proper tag (ISAPI with IIS)\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	$scripts = array( 'example1', 'example2' );
	$expected = '<script charset="ISO-8859-1" src="http://fast.wistia.com/static/concat/example1%2Cexample2.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: multi http request does not give proper tag (ISAPI with IIS)\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	return 0;
}

function test_https() {
	$wam = new WistiaAntiMangler();

	@$_SERVER['https'] = 'On'; // @codingStandardsIgnoreLine
	$scripts = array( 'testing' );
	$expected = '<script charset="ISO-8859-1" src="https://fast.wistia.com/static/concat/testing.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: single https request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		echo "\n";
		return 1;
	}

	$scripts = array( 'example1', 'example2' );
	$expected = '<script charset="ISO-8859-1" src="https://fast.wistia.com/static/concat/example1%2Cexample2.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: multi https request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	@$_SERVER['HTTPS'] = 'On'; // @codingStandardsIgnoreLine
	$scripts = array( 'testing' );
	$expected = '<script charset="ISO-8859-1" src="https://fast.wistia.com/static/concat/testing.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: single HTTPS request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		echo "\n";
		return 1;
	}

	$scripts = array( 'example1', 'example2' );
	$expected = '<script charset="ISO-8859-1" src="https://fast.wistia.com/static/concat/example1%2Cexample2.js"></script>';
	$actual = $wam->concat_script_tag( $scripts );
	if ( $expected !== $actual ) {
		echo "error: multi HTTPS request does not give proper tag\n";
		echo 'expected: ' . esc_html( $expected );
		echo "\n";
		echo 'actual..: ' . esc_html( $expected );
		return 1;
	}

	return 0;
}

$exit_code = 0;
echo "Running http tests\n";
$exit_code += test_http();
echo "Running https tests\n";
$exit_code += test_https();
if ( 0 === $exit_code ) {
	echo "Passed all tests!\n";
} else {
	echo "Failed Travis Tests\n";
}
exit( esc_html( $exit_code ) );
