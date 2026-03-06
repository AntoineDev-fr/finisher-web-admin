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

    public function listRaces(int $page = 1, int $limit = 50, string $q = ''): array
    {
        $query = '?page=' . $page . '&limit=' . $limit;
        if ($q !== '') {
            $query .= '&q=' . rawurlencode($q);
        }

        $url = $this->internalUrl . '/api/races' . $query;
        return $this->request('GET', $url);
    }

    public function fetchAllRaces(string $q = ''): array
    {
        $page = 1;
        $limit = 50;
        $all = [];
        $total = 0;

        while (true) {
            $res = $this->listRaces($page, $limit, $q);
            if (!$res['ok']) {
                return $res;
            }

            $data = $res['data'] ?? [];
            $items = is_array($data['data'] ?? null) ? $data['data'] : [];
            $total = (int)($data['total'] ?? $total);
            foreach ($items as $item) {
                $all[] = $item;
            }

            $hasMore = (bool)($data['hasMore'] ?? false);
            if (!$hasMore) {
                break;
            }
            $page++;
        }

        return ['ok' => true, 'data' => $all, 'total' => $total];
    }

    public function getRace(int $id): array
    {
        $url = $this->internalUrl . '/api/races/' . $id;
        return $this->request('GET', $url);
    }

    public function createRace(array $data, array $file): array
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

        $url = $this->internalUrl . '/api/races';
        $post = $this->buildMultipart($data, $file, $name);

        return $this->request('POST', $url, [
            'Authorization: Bearer ' . $token,
        ], $post);
    }

    public function updateRace(int $id, array $data, ?array $file = null): array
    {
        $token = $this->getToken();
        if ($token === '') {
            return ['ok' => false, 'error' => 'API token missing'];
        }

        $url = $this->internalUrl . '/api/races/' . $id;

        if ($file !== null && ($file['tmp_name'] ?? '') !== '' && is_file($file['tmp_name'])) {
            $name = $file['name'] ?? 'photo.jpg';
            $post = $this->buildMultipart($data, $file, $name);
            return $this->request('POST', $url, [
                'Authorization: Bearer ' . $token,
            ], $post);
        }

        $payload = json_encode($data);
        return $this->request('PUT', $url, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ], $payload);
    }

    public function deleteRace(int $id): array
    {
        $token = $this->getToken();
        if ($token === '') {
            return ['ok' => false, 'error' => 'API token missing'];
        }

        $url = $this->internalUrl . '/api/races/' . $id;
        return $this->request('DELETE', $url, [
            'Authorization: Bearer ' . $token,
        ]);
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

        return [
            'ok' => false,
            'error' => $message,
            'status' => $status,
            'data' => is_array($data) ? $data : [],
        ];
    }

    private function buildMultipart(array $data, array $file, string $name): array
    {
        $post = $data;
        $post['photo'] = curl_file_create(
            $file['tmp_name'],
            $file['type'] ?? 'image/jpeg',
            $name
        );
        return $post;
    }
}
