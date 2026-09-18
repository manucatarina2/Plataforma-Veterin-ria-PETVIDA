<?php
session_start();
session_destroy();
header('Location: /petvida/login.php');
exit;