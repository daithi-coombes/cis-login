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
	
	private function get_settings(){
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
		$this->shortcodes['settings form nonce'] = wp_create_nonce('settings form nonce');
		
		$this->set_shortcodes();
		$this->load_scripts();
		$this->load_styles();

		print $this->html;
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
		
		foreach($_REQUEST as $key=>$val)
			if(preg_match("/cis-login-/", $key))
				$settings[$key] = $val;
		
		update_option("cis_login_settings", $settings);
			
		cis_login_message('Settings saved');
		
		return true;
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
