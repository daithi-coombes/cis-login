<?php
/**
 * @package cis-login
 */
/**
 * Description of CISocialLogin
 *
 * @author daithi
 * @package cis-login
 */
class CISocialLogin {

	/** @var array An array of options taken from wp option cis_login_settings*/
	public $settings;
	/** @var string Holds the html from the view file for parsing */
	private $html;
	/** @var array An array of shortcode=>value pairs for the view file */
	private $shortcodes;

	/**
	 * constructor
	 */
	public function __construct() {
				
		//default params
		$this->settings = $this->get_settings();
		$this->shortcodes = array();
		(@$_REQUEST['cisocial-login-action'])
			? $action = $_REQUEST['cisocial-login-action']
			: $action = false;
		
		
		//look for actions
		if($action)
			if(method_exists($this, $action))
				add_action('admin_init', array(&$this, $action));
	}

	/**
	 * Sets up the admin menu in dashboard. Called from plugin dir/index.php.
	 */
	public function admin_menu(){
		add_menu_page("CI Social Login", "Ci Social Login", "administrator", "cisocial-login", array(&$this, 'get_page'));
	}
	
	/**
	 * Returns the options for this plugin.
	 * 
	 * Options taken from wp option "cis_login_settings".
	 *
	 * @return type 
	 */
	public function get_settings(){
		return get_option("cis_login_settings");
	}
	
	/**
	 * Prints the view html.
	 * 
	 * Loads the html then sets shortcodes ( @see CISocialLogin::set_shortcodes() )
	 * then loads scripts (@see CISocialLogin::load_scripts() ) and styles
	 * (@see CISocialLogin::load_styles() ) then prints html
	 * @return void
	 */
	public function get_page() {

		global $cis_login_client_github;
		$settings = get_option("cis_login_settings");
		
		$this->html = file_get_contents( CISOCIAL_LOGIN_DIR . "/public_html/CISocialLogin.php");
		$this->shortcodes['errors'] = cis_login_get_errors();
		$this->shortcodes['messages'] = cis_login_get_messages();
		$this->shortcodes['github app clientid'] = $settings['cis-login-github-app-clientid'];
		$this->shortcodes['github app clientsecret'] = $settings['cis-login-github-app-clientsecret'];
		$this->shortcodes['list pages'] = $this->get_page_list();
		$this->shortcodes['settings form nonce'] = wp_create_nonce('settings form nonce');
		
		$this->set_shortcodes();
		$this->load_scripts();
		$this->load_styles();
		
		print $this->html;
	}

	/**
	 * Save settings in the dashboard page.
	 * 
	 * Settings saved as wp option "cis_login_settings".
	 *
	 * @return boolean 
	 */
	public function save_settings(){
		
		//securty check
		if(!wp_verify_nonce($_REQUEST['_wpnonce'], 'settings form nonce')){
			cis_login_error("Invalid Nonce");
			return false;
		}
		
		$settings = array();
		
		//parse 3rd party access keys
		foreach($_REQUEST as $key=>$val)
			if(preg_match("/cis-login-/", $key))
				$settings[$key] = $val;
		
		//parse login settings
		$settings['login-page'] = get_permalink($_REQUEST['login-page']);
		if($_REQUEST['login-redirect']){
			$settings['login-redirect'] = get_permalink($_REQUEST['login-redirect']); //$redirect->post_name;
		}
		
		
		update_option("cis_login_settings", $settings);
		
		cis_login_message('Settings saved');
		
		return true;
	}

	/**
	 * Filter callback to set the redirect after successfull login. 
	 * 
	 * @param string $redirect The redirect url
	 * @param string $request The $_REQUEST global
	 * @param string $user The user id
	 * @return string 
	 */
	public function set_wp_login_redirect($redirect, $request, $user){
		
		if(!empty($this->settings['login-redirect']))
			$redirect = $this->settings['login-redirect'];
		return $redirect;
	}

	/**
	 * Filter callback to set the logout url.
	 *
	 * @param string $logout_url
	 * @return string 
	 */
	public function set_wp_logout_url($logout_url){
		
		//append logout var to login url
		$logout_url = url_query_append($this->settings['login-page'], array(
			'logout' => 1
		));		
	
		//return;
		return $logout_url;
	}
	
	/**
	 * Filter callback to change the login url in the wp core using filters. 
	 * Defaults to wordpress's wp-login.php if no page is set in plugin 
	 * settings.
	 * 
	 * @params array $default Wordpress default url is passed during callback.
	 * @return string
	 */
	public function set_wp_login_url( $login_url, $redirect=false ){
		
		if(!empty($this->settings['login-page']))
			$login_url = $this->settings['login-page'];
		return $login_url;
	}
	
	/**
	 * Builds html list of &lt;option> tags for view file.
	 * 
	 * @return string
	 */
	private function get_page_list(){
		
		$pages = get_pages();
		$html = "";
		
		foreach($pages as $page)
			$html .= "<option value=\"{$page->ID}\">{$page->post_title}</option>\n";
			
		return $html;
	}
	
	/**
	 * Loads javascript files
	 * 
	 * @return void 
	 */
	private function load_scripts() {
		;
	}

	/**
	 * Loads css files
	 * 
	 * @return void 
	 */
	private function load_styles() {
		;
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
