<?php
/**
 * @package gh-login
 */
/*
Plugin Name: GitHub Login
Plugin URI: http://david-coombes.com
Description: All logging into wordpress with git account
Version: 0.1
Author: David Coombes
Author URI: http://david-coombes.com
*/

//debug?
error_reporting(E_ALL);
ini_set('display_errors','on');

//constants
define('CISOCIAL_LOGIN_DIR', WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)));
define('CISOCIAL_LOGIN_URL', WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)));

//plugin objects
require_once( CISOCIAL_LOGIN_DIR . "/application/CISocialLogin.class.php");

$cis_login = new CISocialLogin();