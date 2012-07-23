<h1>City Index Social Login</h1>
<!--[--errors--]-->
<!--[--messages--]-->
<div id="message-1" class="updated">
<p>Please note if you don't provide the details for some networks, either they will be disactivated or basic authentication will be used</p>
</div>

<form method="post">
	
	<input type="hidden" name="_wpnonce" value="<!--[--settings form nonce--]-->"/>
	<input type="hidden" name="cisocial-login-action" value="save_settings"/>
	
	<ul>
		<li><input type="submit" value="Save Changes"/></li>
		<li>GitHub Details
			<ul>
				<li><input type="text" name="cis-login-github-app-clientid" value="<!--[--github app clientid--]-->" id="github-app-clientid"><label for="github-app-clientid">GitHub Client ID</label></li>
				<li><input type="text" name="cis-login-github-app-clientsecret" value="<!--[--github app clientsecret--]-->" id="github-app-clientsecret"><label for="github-app-clientsecret">GitHub Client Secret</label></li>
			</ul>
		</li>
		<li><input type="submit" value="Save Changes"/></li>
	</ul>
</form>