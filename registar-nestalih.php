<?php
/**
 * Plugin Name:       Register of Missing Persons in Serbia
 * Plugin URI:        https://www.nestalisrbija.rs/
 * Description:       Display on your site all missing persons from the central Register of Missing Persons of Serbia.
 * Version:           1.0.0
 * Author:            Ivijan-Stefan Sipić
 * Author URI:        https://infinitumform.com/
 * Requires at least: 5.0
 * Tested up to:      5.9
 * Requires PHP:      7.0
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       registar-nestalih
 * Domain Path:       /languages
 * Network:           true
 * Update URI:        https://github.com/InfinitumForm/registar-nestalih
 *
 * Copyright (C) 2022 Ivijan-Stefan Stipic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Define plugin file (changes not allowed)
if ( ! defined( 'MISSING_PERSONS_FILE' ) ) {
	define( 'MISSING_PERSONS_FILE', __FILE__ );
}

// Define plugin path (changes not allowed)
if ( ! defined( 'MISSING_PERSONS_ROOT' ) ) {
	define( 'MISSING_PERSONS_ROOT', rtrim(plugin_dir_path( MISSING_PERSONS_FILE ), '\\/') );
}

// Load main constants
include_once MISSING_PERSONS_ROOT . '/constants.php';

// Load main class
include_once MISSING_PERSONS_ROOT . '/classes/Init.php';

// Load Requirements Class
include_once MISSING_PERSONS_ROOT . '/classes/Requirements.php';
if(Registar_Nestalih_Requirements::passes([
	'file' => MISSING_PERSONS_FILE,
	'title' => __('Register of Missing Persons of Serbia', 'registar-nestalih'),
	'slug' => 'registar-nestalih'
])) {
	// Load plugin
	Registar_Nestalih::instance();
}