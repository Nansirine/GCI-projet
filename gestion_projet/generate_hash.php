<?php
// Script pour générer un hash de mot de passe
$motDePasse = 'Test@123';
$hash = password_hash($motDePasse, PASSWORD_DEFAULT);
echo $hash;
