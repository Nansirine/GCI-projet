<?php
// Environnement
define('APP_ENV', 'development'); // 'production' en prod
define('APP_URL', 'http://localhost/gestion_projet');
define('APP_NAME', 'Buildflow');

// Configuration email SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'nansirinesikirou1@gmail.com');
define('SMTP_PASSWORD', 'lwnr kizk dwmu nvvh'); // Mot de passe d’application Gmail
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_EMAIL', SMTP_USERNAME);
define('SMTP_FROM_NAME', 'genieconcept');



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
define('ALLOWED_CIVIL_FILE_TYPES', [
  'dwg','dxf','dgn',
  'rvt','rte','rfa','ifc','nwd','nwf','pln',
  'mpp','xer','gan',
  'stb','r3d','std','edb','sdb','gwb',
  'xlsx','csv',
  'pdf','docx','doc',
  'shp','kml','kmz','xyz',
]);
define('ALLOWED_PLAN_TYPES', ALLOWED_CIVIL_FILE_TYPES);
define('ALLOWED_RAPPORT_TYPES', ALLOWED_CIVIL_FILE_TYPES);
