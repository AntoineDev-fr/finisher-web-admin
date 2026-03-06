<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Validator;
use App\Core\Pdf;
use App\Core\ApiClient;

final class RaceController
{
    private array $config;
    private ApiClient $apiClient;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiClient = new ApiClient($config['api'] ?? []);
    }

    public function index(Request $request): void
    {
        $res = $this->apiClient->fetchAllRaces('');
        if (!$res['ok']) {
            Response::abort(502, 'API error: ' . ($res['error'] ?? 'unknown'));
        }
        $races = $res['data'] ?? [];
        $html = View::render('races/index', [
            'title' => 'Races',
            'races' => $races,
            'q' => '',
        ]);
        Response::html($html);
    }

    public function search(Request $request): void
    {
        $q = trim((string)$request->queryParam('q', ''));
        $res = $this->apiClient->fetchAllRaces($q);
        if (!$res['ok']) {
            Response::html('', 502);
        }
        $races = $res['data'] ?? [];
        $html = View::render('races/_table', ['races' => $races], false);
        Response::html($html);
    }

    public function create(Request $request): void
    {
        $defaults = $this->defaultFormData();
        $html = View::render('races/create', [
            'title' => 'Create Race',
            'errors' => [],
            'old' => $defaults,
        ]);
        Response::html($html);
    }

    public function store(Request $request): void
    {
        $data = $request->post;
        $files = $request->files;

        [$errors, $clean] = Validator::validateRace($data, $files, true);
        if (!empty($errors)) {
            $old = $this->mergeOld($data);
            $html = View::render('races/create', [
                'title' => 'Create Race',
                'errors' => $errors,
                'old' => $old,
            ]);
            Response::html($html, 422);
        }

        $res = $this->apiClient->createRace($clean, $files['photo']);
        if (!$res['ok']) {
            $apiError = $res['data']['error'] ?? '';
            if ($apiError === 'validation_error' && isset($res['data']['fields'])) {
                $errors = $res['data']['fields'];
            } else {
                $errors = ['form' => $res['error'] ?? 'API error'];
            }
            $old = $this->mergeOld($data);
            $html = View::render('races/create', [
                'title' => 'Create Race',
                'errors' => $errors,
                'old' => $old,
            ]);
            Response::html($html, 422);
        }

        $race = $res['data'] ?? [];
        $id = (int)($race['id'] ?? 0);
        if ($id <= 0) {
            Response::abort(502, 'Invalid API response.');
        }

        View::setFlash('flash_success', 'Race created.');
        Response::redirect('/admin/races/' . $id);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $res = $this->apiClient->getRace($id);
        if (!$res['ok']) {
            Response::abort(404, 'Race not found.');
        }
        $race = $res['data'];

        $html = View::render('races/show', [
            'title' => 'Race Details',
            'race' => $race,
        ]);
        Response::html($html);
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $res = $this->apiClient->getRace($id);
        if (!$res['ok']) {
            Response::abort(404, 'Race not found.');
        }
        $race = $res['data'];

        $old = $this->mergeOld($race);
        $html = View::render('races/edit', [
            'title' => 'Edit Race',
            'errors' => [],
            'old' => $old,
            'race' => $race,
        ]);
        Response::html($html);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $resRace = $this->apiClient->getRace($id);
        if (!$resRace['ok']) {
            Response::abort(404, 'Race not found.');
        }
        $race = $resRace['data'];

        $data = $request->post;
        $files = $request->files;

        $merged = array_merge($race, $data);
        [$errors, $clean] = Validator::validateRace($merged, $files, false);
        if (!empty($errors)) {
            $old = $this->mergeOld($merged);
            $html = View::render('races/edit', [
                'title' => 'Edit Race',
                'errors' => $errors,
                'old' => $old,
                'race' => $race,
            ]);
            Response::html($html, 422);
        }

        $file = null;
        if (!empty($files['photo']) && ($files['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file = $files['photo'];
        }

        $res = $this->apiClient->updateRace($id, $clean, $file);
        if (!$res['ok']) {
            $apiError = $res['data']['error'] ?? '';
            if ($apiError === 'validation_error' && isset($res['data']['fields'])) {
                $errors = $res['data']['fields'];
            } else {
                $errors = ['form' => $res['error'] ?? 'API error'];
            }
            $old = $this->mergeOld($merged);
            $html = View::render('races/edit', [
                'title' => 'Edit Race',
                'errors' => $errors,
                'old' => $old,
                'race' => $race,
            ]);
            Response::html($html, 422);
        }

        View::setFlash('flash_success', 'Race updated.');
        Response::redirect('/admin/races/' . $id);
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->param('id');
        $res = $this->apiClient->deleteRace($id);
        if (!$res['ok']) {
            Response::abort(502, 'API error: ' . ($res['error'] ?? 'unknown'));
        }

        View::setFlash('flash_success', 'Race deleted.');
        Response::redirect('/admin/races');
    }

    public function pdf(Request $request): void
    {
        $id = (int)$request->param('id');
        $res = $this->apiClient->getRace($id);
        if (!$res['ok']) {
            Response::abort(404, 'Race not found.');
        }
        $race = $res['data'];

        $photoDataUri = $this->photoDataUri($race['photo_path'] ?? '');
        $html = $this->buildPdfHtml($race, $photoDataUri);
        Pdf::download($html, 'race-' . $id . '.pdf');
    }

    private function defaultFormData(): array
    {
        return [
            'nom' => '',
            'description' => '',
            'date_event' => '',
            'prix' => '',
            'latitude' => '48.8566',
            'longitude' => '2.3522',
            'contact_nom' => '',
            'contact_email' => '',
        ];
    }

    private function mergeOld(array $data): array
    {
        $data['date_event'] = $this->toInputDate((string)($data['date_event'] ?? ''));
        return $data;
    }

    private function toInputDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        try {
            $dt = new \DateTime($value);
            return $dt->format('Y-m-d\\TH:i');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function photoDataUri(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');
        if (!str_starts_with($relativePath, 'uploads/')) {
            return '';
        }

        $url = View::apiImageUrl($relativePath);
        $data = @file_get_contents($url);
        if ($data === false || $data === '') {
            return '';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($data);
        $encoded = base64_encode($data);

        return 'data:' . $mime . ';base64,' . $encoded;
    }

    private function buildPdfHtml(array $race, string $photoDataUri): string
    {
        $e = fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        $photoHtml = '';
        if ($photoDataUri !== '') {
            $photoHtml = '<div style="margin:16px 0;"><img src="' . $e($photoDataUri) . '" style="max-width:400px;"></div>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>' .
            'body{font-family:DejaVu Sans, sans-serif;font-size:12px;color:#111;}' .
            'h1{font-size:18px;}' .
            'table{width:100%;border-collapse:collapse;}' .
            'td{padding:6px;border:1px solid #ddd;}' .
            '</style></head><body>' .
            '<h1>Race Details</h1>' .
            $photoHtml .
            '<table>' .
            '<tr><td>Name</td><td>' . $e($race['nom']) . '</td></tr>' .
            '<tr><td>Description</td><td>' . $e($race['description']) . '</td></tr>' .
            '<tr><td>Date</td><td>' . $e($race['date_event']) . '</td></tr>' .
            '<tr><td>Price</td><td>' . $e($race['prix']) . '</td></tr>' .
            '<tr><td>Latitude</td><td>' . $e($race['latitude']) . '</td></tr>' .
            '<tr><td>Longitude</td><td>' . $e($race['longitude']) . '</td></tr>' .
            '<tr><td>Contact</td><td>' . $e($race['contact_nom']) . '</td></tr>' .
            '<tr><td>Email</td><td>' . $e($race['contact_email']) . '</td></tr>' .
            '<tr><td>Created</td><td>' . $e($race['created_at']) . '</td></tr>' .
            '<tr><td>Updated</td><td>' . $e($race['updated_at']) . '</td></tr>' .
            '</table>' .
            '</body></html>';
    }
}
