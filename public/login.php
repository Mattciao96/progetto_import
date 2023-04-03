<?php
if (isset($_REQUEST['procedure']) and $_REQUEST['procedure'] == 'login') {
  $login = '
  <form>
  <label for="username">Username:</label>
  <input type="text" id="usernameDolomiti" name="username" required><br><br>

  <label for="password">Password:</label>
  <input type="password" id="passwordDolomiti" name="password" required><br><br>

  <input type="submit" value="Log in">
</form>';
echo $login;
}