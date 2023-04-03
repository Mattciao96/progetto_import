<?php

if (isset($_REQUEST['usernameDolomiti']) and $_REQUEST['usernameDolomiti'] == 'luana' and isset($_REQUEST['passwordDolomiti']) and $_REQUEST['passwordDolomiti'] == 'luana') {
  $_SESSION['usernameDolomiti'] = 'luana';
  $_SESSION['passwordDolomiti'] = 'luana';

} elseif (isset($_SESSION['usernameDolomiti']) and $_SESSION['usernameDolomiti'] == 'luana' and isset($_SESSION['passwordDolomiti']) and $_SESSION['passwordDolomiti'] == 'luana') {


} else {
  require('login.php');
}