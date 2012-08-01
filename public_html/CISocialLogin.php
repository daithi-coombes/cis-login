<h1>City Index Social Login</h1>
<!--[--errors--]-->
<!--[--messages--]-->
<div id="message-1" class="updated">
<p>Please note if you don't provide the details for some networks, either they will be disactivated or basic authentication will be used</p>
</div>

<form method="post">
	
	<input type="hidden" name="_wpnonce" value="<!--[--settings form nonce--]-->"/>
	<input type="hidden" name="cisocial-login-action" value="save_settings"/>
	<input type="hidden" name="redirect_to" value="<!--[--redirect to--]-->"/>
	
	<ul>
		<li><input type="submit" value="Save Changes"/></li>
		<li>
			<label for="login-page">Select login page</label>
			<select name="login-page" id="login-page">
				<option></option>
				<!--[--list pages login-page--]-->
			</select>
			<a href="javascript:void(0)" title="Leave blank to choose the wordpress default (wp-login.php)
			   Use the shortcode [CI Social Login] to get the login form" class="tooltip">
				<img src="<!--[--images dir--]-->/icon-help.gif" with="16" height="16" border="0"/>
			</a>
		</li>
		<li>
			<label for="login-redirect">Select login redirect page</label>
			<select name="login-redirect" id="login-redirect">
				<option></option>
				<!--[--list pages login-redirect--]-->
			</select>
			<a href="javascript:void(0)" title="Leave blank to choose the wordpress default (dashboard)" class="tooltip">
				<img src="<!--[--images dir--]-->/icon-help.gif" with="16" height="16" border="0"/>
			</a>
		</li>
		<li>
			<hr/>
		</li>
		<li>
			GitHub Details
			<a href="javascript:void(0)" title="If no client id or secret id used, then basic authentication will be used here. For secutiry reasons it is advised that the client and secret id's are provided." class="tooltip">
				<img src="<!--[--images dir--]-->/icon-help.gif" with="16" height="16" border="0"/>
			</a>
			<ul>
				<li><input type="text" name="cis-login-github-app-clientid" value="<!--[--github app clientid--]-->" id="github-app-clientid"><label for="github-app-clientid">GitHub Client ID</label></li>
				<li><input type="text" name="cis-login-github-app-clientsecret" value="<!--[--github app clientsecret--]-->" id="github-app-clientsecret"><label for="github-app-clientsecret">GitHub Client Secret</label></li>
			</ul>
		</li>
		<li><input type="submit" value="Save Changes"/></li>
	</ul>
</form>