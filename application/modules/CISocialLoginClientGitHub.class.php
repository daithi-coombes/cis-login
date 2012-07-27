<?php
/**
 * @package cis-login
 */
/**
 * The class file for CISocialLoginClientGitHub
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
	/** @var array An array of shortcode=>value pairs for the view file */
	private $shortcodes;

	/**
	 * constructor
	 */
	public function __construct() {

		//default params
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
	 * @return void
	 */
	public function get_page() {

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
		?><body><?php
		//print body
		print $this->html;

		//print footer and die
		wp_footer();
		?></body></html><?php
		die();
	}

	public function get_autorize_query(){
		
		global $wpdb;
		$client_id = $this->get_client_id();
		$state = rand_md5();
		
		if(!$client_id) return;
		update_option("cis_login_state", $state);		
		
		return "?client_id=" . $this->get_client_id() . "&scope=user,public_repo,repo,delete_repo,gist&state={$state}";
	}
	
	/**
	 * Method calls for just after wp core loads.
	 */
	public function init() {

		$this->load_scripts();
		$this->load_styles();
	}

	/**
	 * Logging in method used in iframe.
	 * 
	 * Redirect to wp-admin admin on success, error reports on failure.
	 *
	 * @global CISocialLogin $cis_login
	 * @global wpdb $wpdb
	 * @return boolean 
	 */
	public function login( $token=false ) {

		//vars
		global $wpdb;
		$ch = curl_init();
		$this->github_user = @$_REQUEST['user'];
		$this->github_pswd = @$_REQUEST['pswd'];

		//if token has not been set then get basic auth
		if(!$token) $this->api_get_basic_token();
		
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
		
		return true;
	}

	/**
	 * Get an access token from the github api.
	 *
	 * @return mixed Returns token on success, false on failure 
	 */
	private function api_get_basic_token() {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_USERPWD, "{$this->github_user}:{$this->github_pswd}");
		
		//vars
		switch ($type) {
			
			//basic auth
			case "basic":
				$params = array(
					'scope' => 'user,public_repo,repo,delete_repo,gist',
					'note' => 'this_is_the_note'
				);
				$post_str = json_encode($params);
				$url = "https://api.github.com/authorizations"; //?scope=user&note=this_is_the_note";

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
				$response = json_decode(curl_exec($ch));

				if (@$response->message || !$response->token) {
					cis_login_error("no token returned => {$response->message}");
					return false;
				}
				$token = $response->token;
				break;

			/**
			 * oauth
			 * @deprecated
			 *
			case "oauth":
				break;
			
				$url = "https://github.com/login/oauth/authorize";
				$params = array(
					'client_id' => $this->get_client_id(),
					'scope' => 'user,public_repo,repo,delete_repo,gist',
					'state' => $state
				);
				$url = "{$url}?" . http_build_query($params);
				$query = "?client_id=" . $this->get_client_id() . "&scope=user,public_repo,repo,delete_repo,gist&state=$state";
				header("Location: {$url}{$query}");
				die();
				
				/**
				 *@deprecated 
				 *
				curl_setopt($ch, CURLOPT_URL, $url . $query);
				//curl_setopt($ch, CURLOPT_POST, true);
				//curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_USERPWD, "{$this->github_user}:{$this->github_pswd}");
				$response = curl_exec($ch);
				
				update_option("cis_login_state", $state);
				print $response;
				 * 
				 *
				break;
			 * 
			 */

			default:
				break;
		}


		return $token;
	}

	/**
	 *@deprecated 
	 */
	private function get_oauth_token(){
		
	}
	
	/**
	 * Loads javascript files
	 * 
	 * @return void 
	 */
	private function load_scripts() {
		wp_enqueue_script('jquery');
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
