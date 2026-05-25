<?php
// Environnement
define('APP_ENV', 'development'); // 'production' en prod
define('APP_URL', 'http://localhost/gestion_projet');
define('APP_NAME', 'Buildflow');



// Masquer erreurs en production
if (APP_ENV === 'production') {
  error_reporting(0);
  ini_set('display_errors', 0);
} else {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
}

// Headers sécurité HTTP
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Limites upload
define('MAX_UPLOAD_SIZE', 20 * 1024 * 1024);  // 20MB
define('UPLOAD_PLANS', __DIR__.'/../uploads/plans/');
define('UPLOAD_RAPPORTS', __DIR__.'/../uploads/rapports/');
define('ALLOWED_PLAN_TYPES', ['pdf','png','jpg','jpeg','dwg']);
define('ALLOWED_RAPPORT_TYPES', ['pdf','doc','docx','jpg','jpeg','png']);
