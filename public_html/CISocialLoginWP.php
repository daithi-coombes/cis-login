
<!--[--errors--]-->
<!--[--messages--]-->

<form id="cisocial-login" method="post">
	
	<input type="hidden" name="_wpnonce" value="<!--[--login form nonce--]-->"/>
	<input type="hidden" name="cis_login_action" value="login"/>
	
	<ul>
		<li>
			<input type="text" name="user" id="cisl-user"/><label for="cisl-user">Username</label>
		</li>
		<li>
			<input type="password" name="pswd" id="cisl-pswd"/><label for="cisl-pswd">Password</label>
		</li>
		<li>
			<input type="submit" name="wp_login" value="Login"/>
		</li>
	</ul>
</form>
<a href="https://github.com/login/oauth/authorize<!--[--github authorise query--]-->">Login with GitHub</a>