<h2> Login </h2>
<form class = "form-stacked" action = "?user/login" method = "post">
<label>User:</label>
<input name = "username" type = "text" /> <br/>
<label>Password:</label>
<input name = "password" type = "password" />  <br/><br/>
<p> <input class = "btn success Large" type = "submit" value = "Login"/> </p>
</form>

<blockquote>
<h1> Or </h1>
</blockquote>

<h2> Register </h2>
<form class = "form-stacked" action = "?user/register" method = "post">
<label>User:</label>
<input name = "username" type = "text" /> <br/>
<label>Password:</label>
<input name = "password" type = "password" />  <br/><br/>
<label>Verify Password:</label>
<input name = "verify_password" type = "password" />  <br/><br/>
<label>E-mail:</label>
<input name = "email" type = "email" />  <br/><br/>
<p> <input class = "btn success Large" type = "submit" value = "Register"/> </p>
</form>