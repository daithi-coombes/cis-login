
<!--[--errors--]-->
<!--[--messages--]-->
<!--[--login form--]-->

<form method="post" action="http://cityindex.loc/wp-login.php" id="cisocial-login" name="cisocial-login">

	<p class="login-username">
		<label for="user_login">Username</label>
		<input type="text" tabindex="10" size="20" value="" class="input" id="user_login" name="log">
	</p>
	<p class="login-password">
		<label for="user_pass">Password</label>
		<input type="password" tabindex="20" size="20" value="" class="input" id="user_pass" name="pwd">
	</p>

	<p class="login-remember"><label><input type="checkbox" tabindex="90" value="forever" id="rememberme" name="rememberme"> Remember Me</label></p>
	<p class="login-submit">
		<input type="submit" tabindex="100" value="Log In" class="button-primary" id="wp-submit" name="wp-submit">
		<input type="hidden" value="http://cityindex.loc/?page_id=2" name="redirect_to">
	</p>

</form>

<h1>here</h1>
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
			<input type="checkbox" name="remember" id="cisl-remember"><label for="cisl-remember">Remember Me</label>
		</li>
		<li>
			<input type="submit" name="wp_login" value="Login"/>
		</li>
	</ul>
</form>

<!--[--git hub login link--]-->