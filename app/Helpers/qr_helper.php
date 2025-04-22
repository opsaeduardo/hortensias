<?php  // app/Helpers/qr_helper.php
namespace App\Helpers; 

if (!function_exists('encrypt_qr_id')) {
    function encrypt_qr_id(string $id): string
    {
        $method = 'AES-256-CBC';
        $key    = 'm1S3cret0QRP4r4ElCl13nt3';
        $iv     = substr(hash('sha256', 'ivSecretQRAqui16b'), 0, 16);

        $b64 = openssl_encrypt($id, $method, $key, 0, $iv);
        return rtrim(strtr($b64, '+/', '-_'), '='); // base64url
    }
}

if (!function_exists('decrypt_qr_id')) {
    function decrypt_qr_id(string $token): ?string
    {
        $method = 'AES-256-CBC';
        $key    = 'm1S3cret0QRP4r4ElCl13nt3';
        $iv     = substr(hash('sha256', 'ivSecretQRAqui16b'), 0, 16);

        $token  = strtr($token, '-_', '+/');
        $pad    = 4 - (strlen($token) % 4);
        if ($pad < 4) $token .= str_repeat('=', $pad);

        $plain = openssl_decrypt($token, $method, $key, 0, $iv);
        return $plain === false ? null : $plain;
    }
}
