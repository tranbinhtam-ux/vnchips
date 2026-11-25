<?php
/**
 * CAPTCHA image endpoint
 *
 * Issues a fresh code into the visitor's session and streams the PNG challenge.
 * Loaded by the Talk To Us form as <img src="captcha.php">; the refresh button simply
 * re-requests it with a cache-busting query string.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/models/Captcha.php';

// config.php buffers output so stray warnings cannot break header()/redirects.
// Binary image data must not be mixed with any of that.
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
header('X-Content-Type-Options: nosniff');

if (!Captcha::isAvailable()) {
    // No GD on this host: say so in a 1x1 PNG rather than emitting a broken image,
    // and let the form fall back to its other anti-spam checks.
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    exit;
}

$code = Captcha::issue();

header('Content-Type: image/png');
Captcha::render($code);
