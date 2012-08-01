
<!--[--errors--]-->
<!--[--messages--]-->

<form method="post" action="/wp-login.php" id="cisocial-login" name="cisocial-login">

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
		<input type="hidden" value="<!--[--login redirect link--]-->" name="redirect_to">
	</p>

</form>

<!--[--git hub login link--]-->