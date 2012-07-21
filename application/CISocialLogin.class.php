<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CISocialLogin
 *
 * @author daithi
 */
class CISocialLogin {

	/** @var string Holds the html from the view file for parsing */
	private $html;

	/**
	 * constructor
	 */
	public function __construct() {
		
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

		$this->html = file_get_contents(CISocialLogin_dir . "/public_html/CISocialLogin.php");
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
