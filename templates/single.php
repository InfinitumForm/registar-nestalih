<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_single_container', $missing_response);

echo '<pre>', var_dump($missing_response), '</pre>';
?>

<?php do_action('registar_nestalih_after_single_container', $missing_response);