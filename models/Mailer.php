<?php
/**
 * Outgoing mail
 *
 * A small SMTP client written against the raw protocol, because this project has no
 * Composer/vendor directory and shared hosting PHP cannot be relied on to deliver via
 * mail(). Credentials live in the `settings` table and are edited in
 * Admin > Email & SMTP (chip-admin/email-settings.php).
 *
 * Mailer::send() never throws: a delivery failure must not cost the site a lead, so the
 * caller stores the inquiry first and only then tries to notify.
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Mailer {

    const SETTING_GROUP = 'email';

    /** Settings an admin can edit, with their defaults */
    const DEFAULTS = [
        'smtp_enabled'          => '0',
        'smtp_host'             => '',
        'smtp_port'             => '587',
        'smtp_secure'           => 'tls',     // tls (STARTTLS) | ssl (implicit) | none
        'smtp_user'             => '',
        'smtp_pass'             => '',
        'smtp_from_email'       => '',
        'smtp_from_name'        => 'VNCHIP Technology JSC',
        'smtp_timeout'          => '15',
        'contact_notify_emails' => '',        // comma separated
        'contact_notify_enabled'=> '1',
    ];

    private static $lastLog = [];

    public static function config($key) {
        return Setting::get($key, self::DEFAULTS[$key] ?? '');
    }

    /** True when SMTP has enough settings to attempt a connection */
    public static function isConfigured() {
        return self::config('smtp_enabled') === '1'
            && trim(self::config('smtp_host')) !== ''
            && trim(self::config('smtp_from_email')) !== '';
    }

    /** Addresses that should be notified about a new inquiry */
    public static function notifyRecipients() {
        $raw = (string)self::config('contact_notify_emails');
        $list = preg_split('/[,;\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return array_values(array_filter($list, static function ($addr) {
            return filter_var($addr, FILTER_VALIDATE_EMAIL) !== false;
        }));
    }

    /** Transcript of the last SMTP conversation, for the admin test button */
    public static function lastLog() {
        return self::$lastLog;
    }

    /**
     * Send one HTML message. Returns true on success; $error receives a readable reason.
     */
    public static function send($to, $subject, $htmlBody, &$error = null, $replyTo = null) {
        self::$lastLog = [];
        $error = null;

        $to = trim((string)$to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $error = 'Địa chỉ người nhận không hợp lệ: ' . $to;
            return false;
        }

        $fromEmail = trim(self::config('smtp_from_email'));
        $fromName  = trim(self::config('smtp_from_name')) ?: 'VNCHIP';

        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = 'no-reply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        }

        $headers = [
            'Date: ' . date('r'),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (($_SERVER['HTTP_HOST'] ?? 'localhost')) . '>',
            'From: ' . self::formatAddress($fromName, $fromEmail),
            'To: ' . $to,
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        if (self::isConfigured()) {
            return self::sendSmtp($fromEmail, $to, $headers, $htmlBody, $error);
        }

        // Fallback for hosts where the local MTA works and no SMTP server was configured.
        $sent = @mail($to, self::encodeHeader($subject), $htmlBody, implode("\r\n", array_slice($headers, 3)));
        if (!$sent) {
            $error = 'SMTP chưa được cấu hình và hàm mail() của máy chủ cũng không gửi được.';
        }
        self::$lastLog[] = 'transport: php mail() fallback -> ' . ($sent ? 'ok' : 'failed');
        return (bool)$sent;
    }

    /* ------------------------------------------------------------------ SMTP */

    private static function sendSmtp($fromEmail, $to, array $headers, $body, &$error) {
        $host    = trim(self::config('smtp_host'));
        $port    = (int)self::config('smtp_port') ?: 587;
        $secure  = strtolower(self::config('smtp_secure'));
        $user    = self::config('smtp_user');
        $pass    = self::config('smtp_pass');
        $timeout = max(5, (int)self::config('smtp_timeout'));

        $transport = ($secure === 'ssl') ? 'ssl://' . $host : $host;

        $context = stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true],
        ]);

        $errNo = 0;
        $errStr = '';
        $socket = @stream_socket_client(
            $transport . ':' . $port,
            $errNo,
            $errStr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            $error = "Không kết nối được tới {$host}:{$port} ({$errStr})";
            self::$lastLog[] = 'connect: FAILED ' . $errStr;
            return false;
        }

        stream_set_timeout($socket, $timeout);

        try {
            self::expect($socket, 220);

            $ehloName = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
            self::command($socket, 'EHLO ' . $ehloName, 250);

            if ($secure === 'tls') {
                self::command($socket, 'STARTTLS', 220);
                $ok = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$ok) {
                    throw new RuntimeException('Không bật được mã hoá STARTTLS.');
                }
                self::$lastLog[] = 'tls: enabled';
                self::command($socket, 'EHLO ' . $ehloName, 250);
            }

            if ($user !== '') {
                self::command($socket, 'AUTH LOGIN', 334);
                self::command($socket, base64_encode($user), 334, '[username]');
                self::command($socket, base64_encode($pass), 235, '[password]');
            }

            self::command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::command($socket, 'DATA', 354);

            $data = implode("\r\n", $headers) . "\r\n\r\n" . self::stuffDots($body) . "\r\n.";
            self::command($socket, $data, 250, '[message body]');

            self::command($socket, 'QUIT', [221, 250]);
            fclose($socket);
            return true;

        } catch (Throwable $e) {
            $error = $e->getMessage();
            self::$lastLog[] = 'ERROR: ' . $e->getMessage();
            @fclose($socket);
            return false;
        }
    }

    private static function command($socket, $line, $expected, $logAs = null) {
        self::$lastLog[] = '> ' . ($logAs ?? self::firstLine($line));
        fwrite($socket, $line . "\r\n");
        return self::expect($socket, $expected);
    }

    private static function expect($socket, $expected) {
        $expected = (array)$expected;
        $reply = '';

        // A multi-line reply keeps a dash after the code until the final line.
        while (($line = fgets($socket, 515)) !== false) {
            $reply .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        if ($reply === '') {
            throw new RuntimeException('Máy chủ SMTP không phản hồi (hết thời gian chờ).');
        }

        self::$lastLog[] = '< ' . trim(self::firstLine($reply));

        $code = (int)substr($reply, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException('SMTP trả về ' . $code . ': ' . trim(preg_replace('/\s+/', ' ', $reply)));
        }
        return $reply;
    }

    /* -------------------------------------------------------------- helpers */

    private static function firstLine($text) {
        $pos = strpos($text, "\n");
        $line = $pos === false ? $text : substr($text, 0, $pos);
        return mb_strimwidth(rtrim($line, "\r"), 0, 160, '…');
    }

    /** RFC 5321: a line starting with "." inside DATA must be doubled */
    private static function stuffDots($body) {
        $body = str_replace(["\r\n", "\r"], "\n", (string)$body);
        $body = str_replace("\n", "\r\n", $body);
        return preg_replace('/^\./m', '..', $body);
    }

    private static function encodeHeader($text) {
        $text = trim(preg_replace('/[\r\n]+/', ' ', (string)$text));
        if (preg_match('//u', $text) && !preg_match('/[^\x20-\x7E]/', $text)) {
            return $text;
        }
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private static function formatAddress($name, $email) {
        $name = trim((string)$name);
        if ($name === '') {
            return $email;
        }
        return self::encodeHeader($name) . ' <' . $email . '>';
    }

    /**
     * Notification body for a new contact inquiry.
     * Every visitor-supplied value is escaped: this HTML lands in a mailbox.
     */
    public static function inquiryNotificationBody(array $inquiry, $adminUrl = '') {
        $row = static function ($label, $value) {
            if (trim((string)$value) === '') return '';
            return '<tr>'
                 . '<td style="padding:8px 14px;background:#F8FAFC;border:1px solid #E2E8F0;font-weight:700;color:#0F172A;width:170px;">' . e($label) . '</td>'
                 . '<td style="padding:8px 14px;border:1px solid #E2E8F0;color:#334155;">' . nl2br(e($value)) . '</td>'
                 . '</tr>';
        };

        $html  = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1E293B;max-width:680px;">';
        $html .= '<h2 style="color:#F25B22;margin:0 0 4px;">Liên hệ mới từ website</h2>';
        $html .= '<p style="margin:0 0 18px;color:#64748B;">Gửi lúc ' . e(date('d/m/Y H:i')) . ' từ biểu mẫu Talk To Us.</p>';
        $html .= '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;">';
        $html .= $row('Họ và tên', $inquiry['fullname'] ?? '');
        $html .= $row('Email', $inquiry['email'] ?? '');
        $html .= $row('Điện thoại', $inquiry['phone'] ?? '');
        $html .= $row('Công ty', $inquiry['company'] ?? '');
        $html .= $row('Quan tâm', $inquiry['service_interest'] ?? '');
        $html .= $row('Nội dung', $inquiry['message'] ?? '');
        $html .= '</table>';

        if ($adminUrl !== '') {
            $html .= '<p style="margin:20px 0 0;">'
                   . '<a href="' . e($adminUrl) . '" style="background:#F25B22;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:700;">'
                   . 'Mở trong trang quản trị</a></p>';
        }

        $html .= '<p style="margin:22px 0 0;color:#94A3B8;font-size:12px;">Email tự động từ website VNCHIP. Bạn có thể bấm Reply để trả lời trực tiếp người gửi.</p>';
        $html .= '</div>';

        return $html;
    }
}
