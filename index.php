<?php
/**
 * @package cis-login
 */
/*
  Plugin Name: CI Social Login
  Plugin URI: http://david-coombes.com
  Description: All logging into wordpress with git account and other social networks
  Version: 0.1
  Author: David Coombes
  Author URI: http://david-coombes.com
 */

//debug?
error_reporting(E_ALL);
ini_set('display_errors', 'on');

//constants and globals
define('CISOCIAL_LOGIN_DIR', WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)));
define('CISOCIAL_LOGIN_URL', WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)));
$cis_login_error = array();
$cis_login_message = array();

//3rd party's
require_once( CISOCIAL_LOGIN_DIR . "/application/includes/debug.func.php");

//plugin class files
require_once( CISOCIAL_LOGIN_DIR . "/application/CISocialLogin.class.php");
require_once( CISOCIAL_LOGIN_DIR . "/application/modules/CISocialLoginWP.class.php");
require_once( CISOCIAL_LOGIN_DIR . "/application/modules/CISocialLoginClientGitHub.class.php");

//construct objects
$cis_login = new CISocialLogin();
$cis_login_wp = new CISocialLoginWP(); //main controller gets constructed first
$cis_login_client_github = new CISocialLoginClientGItHub();

//ajax calls
add_action('wp_ajax_nopriv_login_form_github', array($cis_login_client_github, 'get_page'));
add_action('wp_ajax_nopriv_github_callback', array(&$cis_login_client_github, 'oauth_callback'));
add_action('wp_ajax_login_form_github', array($cis_login_client_github, 'get_page'));

//actions and filters
add_action('admin_menu', array(&$cis_login, 'admin_menu'));
add_filter('login_url', array($cis_login,'set_wp_login_url'), 10, 2);
add_filter('login_redirect', array($cis_login, 'set_wp_login_redirect'), 10, 3);
add_filter('logout_url', array($cis_login,'set_wp_logout_url'), 10, 1);

//shortcodes
add_shortcode("CI Social Login", array(&$cis_login_wp, 'get_page'));

//load global scripts
$cis_login->register_global_scripts();

/**
 * Add an error to the error que.
 * 
 * @param string $str
 */
function cis_login_error($err) {
	global $cis_login_error;
	$cis_login_error[] = $err;
}

/**
 * Add a message to the message que.
 * 
 * @param string $msg
 */
function cis_login_message($msg) {
	global $cis_login_message;
	$cis_login_message[] = $msg;
}

/**
 * Builds the errors html div for the view file. Loads necessary styles for div.
 * 
 * @global array $cis_login_error
 * @return mixed Returns html if errors found, or false if none.
 */
function cis_login_get_errors(){

	global $cis_login_error;
	$html = "<div id=\"message\" class=\"error\"><ul>\n";

	if(!count($cis_login_error)) return false;
	foreach($cis_login_error as $err)
		$html .= "<li>{$err}</li>\n";

	wp_enqueue_style('colors');
	return $html .= "</ul>\n</div>\n";
}

/**
 * Builds the messages html for the view file. Loads necessary styles for div.
 * 
 * @global array $cis_login_message
 * @return mixed Returns html if messages found, or false if none.
 */
function cis_login_get_messages() {

	global $cis_login_message;
	$html = "<div id=\"message-1\" class=\"updated\"><ul>\n";

	if (!count($cis_login_message))
		return false;
	foreach ($cis_login_message as $msg)
		$html .= "<li>{$msg}</li>\n";

	wp_enqueue_style('colors');
	return $html .= "</ul>\n</div>\n";
}

