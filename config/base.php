<?php
// config/base.php - AUTO BASE URL (works on localhost AND live server)

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);

    // Detect if inside Webtech-Academie-Libre-Projects folder
    if (strpos($script, 'Webtech-Academie-Libre-Projects') !== false) {
        $base = $protocol . $host . '/~chidima.ugwu/Webtech-Academie-Libre-Projects';
    } else {
        // Local XAMPP fallback
        $base = $base = $protocol . $host . '/learnstep';
    }

    define('BASE_URL', rtrim($base, '/'));
    define('ASSETS_URL', BASE_URL . '/assets');
    define('UPLOADS_URL', BASE_URL . '/uploads');
    define('ADMIN_URL', BASE_URL . '/admin');
}
?>
