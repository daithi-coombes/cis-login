<?php
/**
 * @package cis-login
 */
/**
 * The class file for CISocialLoginClientGitHub. Handles both basic and oauth
 * authentication. Tries to default to basic where possible.
 *
 * @author daithi
 * @package cis-login
 */
class CISocialLoginClientGitHub {

	/** @var resource phpCurl resource for github api calls */
	private $ch;
	/** @var string Github username */
	private $github_user;
	/** @var string Github password */
	private $github_pswd;
	/** @var string Holds the html from the view file for parsing */
	private $html;
	/** @var boolean Is user logged in. Used for basic auth iframe javascript */
	private $logged_in;
	/** @var array An array of shortcode=>value pairs for the view file */
	private $shortcodes;

	/**
	 * constructor
	 */
	public function __construct() {

		//default params
		$this->logged_in = false;
		$this->shortcodes = array();
		(@$_REQUEST['cisocial-github-login']) ? $action = $_REQUEST['cisocial-github-login'] : $action = false;

		if ($action)
			if (method_exists($this, $action))
				add_action('init', array(&$this, $action));
		
		if(@$_REQUEST['authenticity_token'] && $_REQUEST['state'])
			if(get_option("cis_login_state", $_REQUEST['state']))
				$this->get_oauth_token();
	}

	/**
	 * Handles the callback for the GitHub OAuth api v3.
	 */
	public function oauth_callback() {
		
		//vars
		$ch = curl_init();
		$state = get_option("cis_login_state");
		$params = array(
			'client_id' => $this->get_client_id(),
			'client_secret' => $this->get_client_secret(),
			'code' => $_REQUEST['code'],
			'state' => $state
		);
		
		//check state (defined in $this->get_authorize_query())
		if($state!=$_REQUEST['state'])
			die("<h1>invalid state</h1>");
		
		//get access token
		curl_setopt($ch, CURLOPT_URL, "https://github.com/login/oauth/access_token");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$response = curl_exec($ch);
		$res = array();
		parse_str($response, $res);
		
		if(!$res['access_token']){
			cis_login_error("Error signing into GitHub");
			cis_login_error("{$res['message']}");
		}
		
		//else login
		$token = $res['access_token'];
		if(!$this->login( $token )){
			cis_login_error("<h1>error getting details from GitHub");
			cis_login_get_errors();
			die();
		}
		
		//print script to redirect to wp-admin
		print "
			<script type=\"text/javascript\">
				window.parent.location = '/wp-admin';
			</script>
			";
		return true;
	}

	/**
	 * Get the github client id.
	 * 
	 * @return mixed Returns client id on success or false if not found.
	 */
	public function get_client_id() {
		
		global $cis_login;
		
		$options = $cis_login->get_settings();
		
		if(!empty($options['cis-login-github-app-clientid']))
			return $options['cis-login-github-app-clientid'];
		else return false;
	}

	/**
	 * Get the github client secret.
	 * 
	 * @return mixed Returns the client secret on success or false if not found.
	 */
	public function get_client_secret() {
		
		global $cis_login;
		
		$options = $cis_login->get_settings();
		
		if(!empty($options['cis-login-github-app-clientsecret']))
			return $options['cis-login-github-app-clientsecret'];
		else return false;
	}

	/**
	 * Prints the view html.
	 * 
	 * Loads the html then sets shortcodes ( @see CISocialLoginClientGItHub::set_shortcodes() )
	 * then loads scripts (@see CISocialLoginClientGItHub::load_scripts() ) and styles
	 * (@see CISocialLoginClientGItHub::load_styles() ) then prints html
	 * @global CISocialLogin $cis_login
	 * @return void
	 */
	public function get_page() {

		global $cis_login;
		$this->html = file_get_contents(CISOCIAL_LOGIN_DIR . "/public_html/CISocialLoginClientGItHub.php");
		$this->shortcodes['login nonce'] = wp_create_nonce('github login nonce');
		$this->shortcodes['errors'] = cis_login_get_errors();
		$this->shortcodes['messages'] = cis_login_get_messages();
		$this->set_shortcodes();

		$this->load_scripts();
		$this->load_styles();

		//print head
		?><head><?php
		wp_head();
		?></head><body><?php

		//if user has just been logged in, then call js redirect method.
		if($this->logged_in){
			(@$cis_login->settings['login-redirect'])	//get redirect url
				? $redirect=$cis_login->settings['login-redirect']
				: $redirect=  get_admin_url();
			
			//redirect
			print "<script>
				jQuery(document).ready(function(){
					cis_client_github.login_redirect('{$redirect}');
				});
			</script>";
		}
		
		//print body
		print $this->html;

		//print footer and die
		wp_footer();
		?></body></html><?php
		die();
	}

	/**
	 * Returns the html link for logging in used in the view file for the
	 * CISocialLoginWP module.
	 */
	public function get_login_link(){
		
		//vars
		$client_id = $this->get_client_id();
		$link = "";
		$state = rand_md5();
		
		//if client_id then give link for oauth
		if($client_id){
			update_option("cis_login_state", $state);		
			$link = "<a href=\"https://github.com/login/oauth/authorize?client_id=" . $this->get_client_id() . "&scope=user,public_repo,repo,delete_repo,gist&state={$state}\">";
		}
				
		//else default to link for basic auth
		else $link = "<a href=\"javascript:void(0)\" onclick=\"tb_show('GitHub Basic Auth','/wp-admin/admin-ajax.php?action=login_form_github&TB_iframe=true')\">";
		
		//return
		return $link .= "
			Login with GitHub
			</a>
			";
	}
	
	/**
	 * Method calls for just after wp core loads.
	 */
	public function init() {

		$this->load_scripts();
		$this->load_styles();
	}

	/**
	 * Makes a request for the user's github email. Needs an auth token set up
	 * first. Oauth tokens are recieved from $this->oauth_callback and passed as
	 * param. Method defaults to getting a basic auth token from
	 * $this->api_get_basic_token(). If email matches one on wordpress account
	 * method return true.
	 *
	 * @global wpdb $wpdb
	 * @return boolean 
	 */
	public function login( $token=false ) {

		//vars
		global $cis_login;
		global $wpdb;
		$ch = curl_init();
		$this->github_user = @$_REQUEST['user'];
		$this->github_pswd = @$_REQUEST['pswd'];

		//if !token, then get token from basic auth
		if(!$token) $token = $this->api_get_basic_token();
		
		//get users email
		curl_setopt($ch, CURLOPT_URL, "https://api.github.com/user/emails?access_token={$token}");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->github_pswd && $this->github_user)
			curl_setopt($ch, CURLOPT_USERPWD, "{$this->github_user}:{$this->github_pswd}");
		$response = json_decode(curl_exec($ch));
		if (@$response->message || @$response['message']) {
			($response->message) ? $msg = $response->message : $msg = $response['message'];
			cis_login_error("error getting user email => {$msg}");
			return false;
		}
		
		//check emails against users
		$emails = "'" . implode("','", $response) . "'";
		$res = $wpdb->get_results("
			SELECT {$wpdb->users}.user_email, {$wpdb->users}.ID
			FROM {$wpdb->users}
			WHERE {$wpdb->users}.user_email IN ({$emails})
			");
			
		//if github email not in wp error report
		if (!$res) {
			cis_login_error("Please make sure your email account on wordpress and github match<br/>GitHub.com emails => {$emails}");
			return false;
		}
		
		wp_set_auth_cookie($res[0]->ID);
		$this->logged_in = true;
		
		return true;
	}

	/**
	 * Get an access token from the github api for basic authentication.
	 *
	 * @return mixed Returns token on success, false on failure 
	 */
	private function api_get_basic_token() {

		$ch = curl_init();
		$params = array(
			'scope' => 'user,public_repo,repo,delete_repo,gist',
			'note' => 'this_is_the_note'
		);
		$post_str = json_encode($params);
		$url = "https://api.github.com/authorizations"; //?scope=user&note=this_is_the_note";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->github_user}:{$this->github_pswd}");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
		$response = json_decode(curl_exec($ch));

		if (@$response->message || !$response->token) {
			cis_login_error("no token returned => {$response->message}");
			return false;
		}
		$token = $response->token;

		return $token;
	}
	
	/**
	 * Loads javascript files
	 * 
	 * @return void 
	 */
	private function load_scripts() {
		
		wp_register_script('cis-client-github', CISOCIAL_LOGIN_URL . "/public_html/js/CISocialLoginClientGitHub.js", array(
			'jquery',
			'block-ui'
		));
		
		wp_enqueue_script('cis-client-github');
	}

	/**
	 * Loads css files
	 * 
	 * @return void 
	 */
	private function load_styles() {

		wp_register_style('cisocial-login-client-github', CISOCIAL_LOGIN_URL . "/public_html/css/CISocialLoginClientGitHub.css",array(
			'colors'
		));

		wp_enqueue_style('cisocial-login-client-github');
	}

	/**
	 * Sets values for the shortcodes in the view file.
	 * 
	 * Replaces the codes with values in @see FSNetworkRegister::$html . To add
	 * shortcodes to the view file use the syntax:
	 * <code> <!--[--identifying string--]--> </code>. In the construct of this
	 * class add the value to the array @see FSNetworkRegister::$shortcodes.
	 * eg: $this->shortcodes['identifying string'] = $this->method_returns_html()
	 * 
	 * @return void
	 */
	private function set_shortcodes() {
		foreach ($this->shortcodes as $code => $val)
			$this->html = str_replace("<!--[--{$code}--]-->", $val, $this->html);
	}

}
?>
