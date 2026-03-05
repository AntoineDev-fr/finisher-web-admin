<?php
declare(strict_types=1);

namespace App\Core;

final class ApiClient
{
    private string $publicUrl;
    private string $internalUrl;
    private string $email;
    private string $password;
    private int $timeout;

    public function __construct(array $config)
    {
        $this->publicUrl = rtrim($config['public_url'] ?? '', '/');
        $this->internalUrl = rtrim($config['internal_url'] ?? '', '/');
        $this->email = (string)($config['email'] ?? '');
        $this->password = (string)($config['password'] ?? '');
        $this->timeout = 10;
    }

    public function uploadPhoto(array $file): array
    {
        $token = $this->getToken();
        if ($token === '') {
            return ['ok' => false, 'error' => 'API token missing'];
        }

        $tmp = $file['tmp_name'] ?? '';
        $name = $file['name'] ?? 'photo.jpg';
        if ($tmp === '' || !is_file($tmp)) {
            return ['ok' => false, 'error' => 'Invalid upload file'];
        }

        $url = $this->internalUrl . '/api/uploads';
        $post = [
            'photo' => curl_file_create($tmp, $file['type'] ?? 'image/jpeg', $name),
        ];

        $res = $this->request('POST', $url, [
            'Authorization: Bearer ' . $token,
        ], $post);

        if (!$res['ok']) {
            return $res;
        }

        return [
            'ok' => true,
            'photo_path' => $res['data']['photo_path'] ?? '',
            'photo_url' => $res['data']['photo_url'] ?? '',
        ];
    }

    public function deletePhoto(string $photoPath): bool
    {
        $token = $this->getToken();
        if ($token === '') {
            return false;
        }

        $file = basename($photoPath);
        if ($file === '') {
            return false;
        }

        $url = $this->internalUrl . '/api/uploads/' . rawurlencode($file);
        $res = $this->request('DELETE', $url, [
            'Authorization: Bearer ' . $token,
        ]);

        return $res['ok'];
    }

    private function getToken(): string
    {
        if (!empty($_SESSION['_api_token']) && !empty($_SESSION['_api_token_exp'])) {
            if ((int)$_SESSION['_api_token_exp'] > time()) {
                return (string)$_SESSION['_api_token'];
            }
        }

        $token = $this->login();
        if ($token !== '') {
            return $token;
        }

        return '';
    }

    private function login(): string
    {
        if ($this->email === '' || $this->password === '') {
            return '';
        }

        $url = $this->internalUrl . '/api/auth/login';
        $payload = json_encode([
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $res = $this->request('POST', $url, [
            'Content-Type: application/json',
        ], $payload);

        if (!$res['ok']) {
            return '';
        }

        $token = $res['data']['token'] ?? '';
        $expires = (int)($res['data']['expires_in'] ?? 3600);
        if ($token !== '') {
            $_SESSION['_api_token'] = $token;
            $_SESSION['_api_token_exp'] = time() + max(60, $expires - 60);
        }

        return (string)$token;
    }

    private function request(string $method, string $url, array $headers = [], mixed $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'error' => $error ?: 'Request failed'];
        }

        $data = json_decode($raw, true);
        if ($status >= 200 && $status < 300) {
            return ['ok' => true, 'data' => is_array($data) ? $data : []];
        }

        $message = 'API error (' . $status . ')';
        if (is_array($data) && isset($data['error'])) {
            $message = (string)$data['error'];
        }

        return ['ok' => false, 'error' => $message, 'status' => $status];
    }
}
