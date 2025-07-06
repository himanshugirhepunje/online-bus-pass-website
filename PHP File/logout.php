<?php
session_start();
session_destroy();
header("Location: ../HTML File/login.html");
exit();
?>
