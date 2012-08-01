<!--[--errors--]-->
<!--[--messages--]-->
<form method="post" id="github-login-form" onsubmit="jQuery(body).block({ message: 'logging into github...'})">
	
	<input type="hidden" name="_wpnonce" value="<!--[--login nonce--]-->"/>
	<input type="hidden" name="cisocial-github-login" value="login"/>
	
	<ul>
		<li><input type="text" name="user" id="user"/><label for="user">GitHub Username</label></li>
		<li><input type="password" name="pswd" id="pswd"/><label for="pswd">GitHub Password</label></li>
		<li><input type="submit" value="Login with GitHub"/></li>
	</ul>
</form>
<!--[--logged in script--]-->