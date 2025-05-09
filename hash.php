<?php
$password = '0000';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>
