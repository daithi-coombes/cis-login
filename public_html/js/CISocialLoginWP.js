var cis_login;

jQuery(document).ready(function($){
	
	cis_login = new CISocialLogin($);
});

var CISocialLogin = function($){
	
	this.login_github = function(){
		tb_show('git hub login', ajaxurl+'?_wpnonce='+cis_login_nonces.github_popup+'&action=login_form_github&TB_iframe=true');
	}
	
}