<?php
/**
 * Home-grown CAPTCHA + lightweight anti-spam traps
 *
 * Deliberately self-contained: no third-party service, no Composer package, no system
 * font dependency (GD's built-in font is upscaled and distorted instead, so this keeps
 * working if the host ships no TTF).
 *
 * Difficulty is tuned "medium": readable at a glance by a person, but warped, rotated
 * and noisy enough that naive OCR fails. Three layers run together:
 *   1. the image challenge           (Captcha::verify)
 *   2. a honeypot field bots fill in (Captcha::checkAntiSpam)
 *   3. a signed timestamp, so a form submitted in under 3 seconds is refused
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Captcha {

    const SESSION_KEY   = 'captcha_answer';
    const SESSION_TIME  = 'captcha_issued_at';
    const SESSION_TRIES = 'captcha_tries';

    const LENGTH      = 5;
    const TTL         = 600;   // a code stays valid for 10 minutes
    const MAX_TRIES   = 6;     // then the visitor has to load a new image
    const MIN_SECONDS = 3;     // a human cannot complete the form faster than this

    const HONEYPOT_FIELD  = 'website_url';   // plausible name, so bots fill it in
    const TIMESTAMP_FIELD = 'form_started';

    /** Characters a person cannot confuse: no 0/O, 1/I/L, 5/S, 2/Z */
    const ALPHABET = 'ABCDEFGHJKMNPQRTUVWXY346789';

    /* ------------------------------------------------------------ challenge */

    /** Issue a fresh code, remember it in the session and return it */
    public static function issue() {
        self::session();

        $code = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        $_SESSION[self::SESSION_KEY]   = self::hash($code);
        $_SESSION[self::SESSION_TIME]  = time();
        $_SESSION[self::SESSION_TRIES] = 0;

        return $code;
    }

    /**
     * Check what the visitor typed. Case-insensitive; a code is single-use and is
     * cleared on success, on expiry and once the attempt budget is spent.
     */
    public static function verify($input, &$reason = null) {
        self::session();
        $reason = null;

        $stored = $_SESSION[self::SESSION_KEY] ?? '';
        if ($stored === '') {
            $reason = 'Mã xác nhận đã hết hiệu lực. Vui lòng lấy mã mới và thử lại.';
            return false;
        }

        if (time() - (int)($_SESSION[self::SESSION_TIME] ?? 0) > self::TTL) {
            self::clear();
            $reason = 'Mã xác nhận đã hết hạn. Vui lòng lấy mã mới.';
            return false;
        }

        $_SESSION[self::SESSION_TRIES] = (int)($_SESSION[self::SESSION_TRIES] ?? 0) + 1;
        if ($_SESSION[self::SESSION_TRIES] > self::MAX_TRIES) {
            self::clear();
            $reason = 'Bạn đã nhập sai quá nhiều lần. Vui lòng lấy mã mới.';
            return false;
        }

        $input = trim((string)$input);
        if ($input === '') {
            $reason = 'Vui lòng nhập mã xác nhận trong ảnh.';
            return false;
        }

        if (!hash_equals($stored, self::hash($input))) {
            $reason = 'Mã xác nhận không đúng. Vui lòng thử lại.';
            return false;
        }

        self::clear();
        return true;
    }

    public static function clear() {
        self::session();
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_TIME], $_SESSION[self::SESSION_TRIES]);
    }

    /* --------------------------------------------------------------- traps */

    /** Hidden fields rendered inside the protected form */
    public static function antiSpamFields() {
        $issued = time();
        $sig = self::signTimestamp($issued);

        $html  = '<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;">';
        $html .= '<label>Website<input type="text" name="' . self::HONEYPOT_FIELD . '" value="" tabindex="-1" autocomplete="off"></label>';
        $html .= '</div>';
        $html .= '<input type="hidden" name="' . self::TIMESTAMP_FIELD . '" value="' . $issued . '.' . $sig . '">';

        return $html;
    }

    /** Honeypot + timing checks. Returns false and a reason when the post looks automated. */
    public static function checkAntiSpam(&$reason = null) {
        $reason = null;

        if (trim((string)($_POST[self::HONEYPOT_FIELD] ?? '')) !== '') {
            $reason = 'Yêu cầu của bạn bị từ chối vì nghi ngờ gửi tự động.';
            return false;
        }

        $raw = (string)($_POST[self::TIMESTAMP_FIELD] ?? '');
        $parts = explode('.', $raw, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            $reason = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
            return false;
        }

        [$issued, $sig] = $parts;
        if (!hash_equals(self::signTimestamp((int)$issued), $sig)) {
            $reason = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
            return false;
        }

        $age = time() - (int)$issued;
        if ($age < self::MIN_SECONDS) {
            $reason = 'Biểu mẫu được gửi quá nhanh. Vui lòng thử lại.';
            return false;
        }
        if ($age > 7200) {
            $reason = 'Trang đã mở quá lâu. Vui lòng tải lại và gửi lại.';
            return false;
        }

        return true;
    }

    /* ---------------------------------------------------------------- image */

    /** Render the current code as a PNG and write it straight to the output buffer */
    public static function render($code, $width = 190, $height = 60) {
        $scale = 2;                       // draw large, downsample at the end: smoother glyphs
        $w = $width * $scale;
        $h = $height * $scale;

        $canvas = imagecreatetruecolor($w, $h);
        imagealphablending($canvas, true);

        // Background: light, slightly tinted, with speckle
        $bg = imagecolorallocate($canvas, random_int(238, 252), random_int(238, 252), random_int(240, 255));
        imagefilledrectangle($canvas, 0, 0, $w, $h, $bg);
        for ($i = 0, $dots = (int)($w * $h / 90); $i < $dots; $i++) {
            $c = imagecolorallocate($canvas, random_int(160, 225), random_int(160, 225), random_int(170, 230));
            imagesetpixel($canvas, random_int(0, $w - 1), random_int(0, $h - 1), $c);
        }

        // Glyphs: GD built-in font blown up, each one rotated on its own
        $font = 5;                                   // 9 x 15 px
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);
        $glyphScale = (int)round($h * 0.62 / $charH);
        $len = strlen($code);
        $slot = (int)($w / ($len + 0.6));

        for ($i = 0; $i < $len; $i++) {
            $tile = imagecreatetruecolor($charW, $charH);
            $tileBg = imagecolorallocate($tile, 255, 0, 255);          // magenta = transparent key
            imagefilledrectangle($tile, 0, 0, $charW, $charH, $tileBg);
            imagecolortransparent($tile, $tileBg);

            $ink = imagecolorallocate($tile, random_int(15, 90), random_int(15, 90), random_int(70, 150));
            imagestring($tile, $font, 0, 0, $code[$i], $ink);

            $bigW = $charW * $glyphScale;
            $bigH = $charH * $glyphScale;
            $big = imagecreatetruecolor($bigW, $bigH);
            $bigBg = imagecolorallocate($big, 255, 0, 255);
            imagefilledrectangle($big, 0, 0, $bigW, $bigH, $bigBg);
            imagecolortransparent($big, $bigBg);
            imagecopyresampled($big, $tile, 0, 0, 0, 0, $bigW, $bigH, $charW, $charH);

            $rotated = imagerotate($big, random_int(-22, 22), $bigBg);
            imagecolortransparent($rotated, $bigBg);

            $x = (int)($slot * ($i + 0.35) - imagesx($rotated) / 2 + random_int(-4, 4));
            $y = (int)(($h - imagesy($rotated)) / 2 + random_int(-6, 6));
            imagecopy($canvas, $rotated, max(0, $x), max(0, $y), 0, 0, imagesx($rotated), imagesy($rotated));

            imagedestroy($tile);
            imagedestroy($big);
            imagedestroy($rotated);
        }

        // Interference: a few thick curves drawn over the glyphs
        for ($l = 0; $l < 3; $l++) {
            $lineColor = imagecolorallocate($canvas, random_int(90, 180), random_int(90, 180), random_int(120, 200));
            imagesetthickness($canvas, $scale);
            $amp = random_int($h / 8, $h / 4);
            $phase = random_int(0, 100) / 10;
            $yBase = random_int((int)($h * 0.25), (int)($h * 0.75));
            $prevX = 0;
            $prevY = $yBase;
            for ($x = 0; $x <= $w; $x += 6) {
                $y = (int)($yBase + sin($x / 28 + $phase) * $amp);
                imageline($canvas, $prevX, $prevY, $x, $y, $lineColor);
                $prevX = $x;
                $prevY = $y;
            }
        }
        imagesetthickness($canvas, 1);

        // Wave warp: shift every column a little so the baseline is not straight
        $warped = imagecreatetruecolor($w, $h);
        imagefilledrectangle($warped, 0, 0, $w, $h, $bg);
        $amp = $h / 18;
        $period = random_int(40, 70);
        $offset = random_int(0, 100) / 10;
        for ($x = 0; $x < $w; $x++) {
            $dy = (int)round(sin($x / $period + $offset) * $amp);
            imagecopy($warped, $canvas, $x, max(0, $dy), $x, max(0, -$dy), 1, $h - abs($dy));
        }
        imagedestroy($canvas);

        // Downsample to the requested size
        $out = imagecreatetruecolor($width, $height);
        imagecopyresampled($out, $warped, 0, 0, 0, 0, $width, $height, $w, $h);
        imagedestroy($warped);

        // Frame
        $border = imagecolorallocate($out, 203, 213, 225);
        imagerectangle($out, 0, 0, $width - 1, $height - 1, $border);

        imagepng($out);
        imagedestroy($out);
    }

    /** True when the server can actually draw the image */
    public static function isAvailable() {
        return function_exists('imagecreatetruecolor') && function_exists('imagepng');
    }

    /* -------------------------------------------------------------- private */

    private static function hash($code) {
        return hash('sha256', mb_strtolower(trim($code), 'UTF-8') . '|' . self::secret());
    }

    private static function signTimestamp($issued) {
        return substr(hash_hmac('sha256', 'form|' . (int)$issued, self::secret()), 0, 32);
    }

    /**
     * Per-site secret, created once and stored with the other settings so the signed
     * timestamp cannot be forged from outside.
     */
    private static function secret() {
        static $secret = null;
        if ($secret !== null) {
            return $secret;
        }

        $secret = (string)Setting::get('form_hmac_secret', '');
        if (strlen($secret) < 32) {
            $secret = bin2hex(random_bytes(32));
            Setting::set('form_hmac_secret', $secret, 'security', 'Khoá ký biểu mẫu chống spam (tự sinh)');
        }
        return $secret;
    }

    private static function session() {
        if (session_status() === PHP_SESSION_NONE) {
            if (class_exists('Admin')) {
                Admin::startSession();
            } else {
                session_start();
            }
        }
    }
}
