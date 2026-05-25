<?php
$motDePasse = 'Test@123';
echo password_hash($motDePasse, PASSWORD_DEFAULT);
