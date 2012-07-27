<?php
/**
 * @package cis-login
 */
/**
 * Class for the front end login page and actions.
 *
 * @author daithi
 * @package cis-login
 */
class CISocialLoginWP {

	/** @var string Holds the html from the view file for parsing */
	private $html;
	/** @var array An array of shortcode=>value pairs */
	private $shortcodes;

	/**
	 * constructor
	 */
	public function __construct() {
		
		//set default params
		$this->shortcodes = array();
		
		//actions
		add_action('wp_head', array(&$this, 'head'));
		add_action('init', array(&$this,'init'));
		add_action('wp_init', array(&$this,'init'));
		
		if(@$_REQUEST['cis_login_action']=='login') 
			add_action('init', array(&$this, 'login'));
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
		
		$this->html = file_get_contents(CISOCIAL_LOGIN_DIR . "/public_html/CISocialLoginWP.php");
		$this->shortcodes['errors'] = cis_login_get_errors();
		$this->shortcodes['login form nonce'] = wp_create_nonce("login form nonce");
		$this->shortcodes['git hub login link'] = $cis_login_client_github->get_login_link();
		$this->shortcodes['messages'] = cis_login_get_messages();
		$this->set_shortcodes();

		$this->load_scripts();
		$this->load_styles();

		print $this->html;
	}
	
	/**
	 * Print javascript globals to &lt;head> tags on frontend.
	 */
	public function head(){
		
		$ajaxurl = admin_url('admin-ajax.php');
		$github_popup = wp_create_nonce('github popup');
		
		?>
		<script type="text/javascript">
			var ajaxurl = '<?=$ajaxurl?>';
			var cis_login_nonces = {
				github_popup : '<?=$github_popup?>'
			};
		</script>
		<?php
	}
	
	/**
	 * Methods to be run just after wp core loads.
	 */
	public function init(){
		
		$this->load_scripts();
		$this->load_styles();
	}
	
	/**
	 * login into wordpress normaly
	 *
	 * @todo
	 * @global CISocialLoginClientGithub $cis_login_client_github
	 * @return boolean 
	 */
	public function login(){
		
		//security check
		if(!wp_verify_nonce($_REQUEST['_wpnonce'], "login form nonce")){
			cis_login_error("Invalid nonce");
			return false;
		}
		
		cis_login_message ("Wordpress login");
		
		/**
		 * @deprecated
		 * git hub login
		 *
		global $cis_login_client_github;
		if(@$_REQUEST['github_login'])
			$cis_login_client_github->login();
		//default wp login
		else*/
	}
	
	/**
	 * Loads javascript files
	 * 
	 * @return void 
	 */
	private function load_scripts() {
		
		wp_register_script('cisocial-login', CISOCIAL_LOGIN_URL . "/public_html/js/CISocialLoginWP.js", array(
			'jquery',
			'thickbox'
		));
		
		wp_enqueue_script('cisocial-login');
	}

	/**
	 * Loads css files
	 * 
	 * @return void 
	 */
	private function load_styles() {
		wp_register_style('cisocial-login', CISOCIAL_LOGIN_URL . "/public_html/css/CISocialLoginWP.css", array(
			'thickbox'
		));
		
		wp_enqueue_style('cisocial-login');
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
