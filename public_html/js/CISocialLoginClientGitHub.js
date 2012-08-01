var cis_client_github;

jQuery(document).ready(function($){
	
	cis_client_github = new CISocialLoginClientGitHub($);
	cis_client_github.init();
});

var CISocialLoginClientGitHub = function($){
	
	this.init = function(){
	}
	
	this.login_redirect = function( redirect ){
		window.parent.location = redirect;
	}
}