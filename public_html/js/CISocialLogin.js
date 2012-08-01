var cis_login;

jQuery(document).ready(function($){

	cis_login = new CISocialLogin($);
	cis_login.init();
});

var CISocialLogin = function($){
	
	this.init = function(){
		
		$('.tooltip').tipsy({
			gravity : 'w'
		});
	}
};