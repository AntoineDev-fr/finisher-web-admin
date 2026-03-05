<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Validator;
use App\Core\Mailer;
use App\Core\Pdf;
use App\Core\ApiClient;
use App\Models\Race;

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
        $races = Race::all();
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
        $races = Race::search($q);
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

        $upload = $this->apiClient->uploadPhoto($files['photo']);
        if (!$upload['ok']) {
            $errors = ['photo' => $upload['error'] ?? 'Upload failed.'];
            $old = $this->mergeOld($data);
            $html = View::render('races/create', [
                'title' => 'Create Race',
                'errors' => $errors,
                'old' => $old,
            ]);
            Response::html($html, 422);
        }

        $photoPath = (string)($upload['photo_path'] ?? '');
        if ($photoPath === '') {
            Response::abort(500, 'Upload failed.');
        }
        $now = date('Y-m-d H:i:s');

        $clean['photo_path'] = $photoPath;
        $clean['created_at'] = $now;
        $clean['updated_at'] = $now;

        $race = Race::create($clean);
        $link = rtrim($this->config['app']['url'], '/') . '/races/' . $race['id'];
        $this->sendCreationEmail($race, $link);

        View::setFlash('flash_success', 'Race created.');
        Response::redirect('/admin/races/' . $race['id']);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

        $html = View::render('races/show', [
            'title' => 'Race Details',
            'race' => $race,
        ]);
        Response::html($html);
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

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
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

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

        $photoPath = $race['photo_path'];
        if (!empty($files['photo']) && ($files['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->apiClient->uploadPhoto($files['photo']);
            if (!$upload['ok']) {
                $errors = ['photo' => $upload['error'] ?? 'Upload failed.'];
                $old = $this->mergeOld($merged);
                $html = View::render('races/edit', [
                    'title' => 'Edit Race',
                    'errors' => $errors,
                    'old' => $old,
                    'race' => $race,
                ]);
                Response::html($html, 422);
            }

            $photoPath = (string)($upload['photo_path'] ?? '');
            if ($photoPath === '') {
                Response::abort(500, 'Upload failed.');
            }
            $this->apiClient->deletePhoto($race['photo_path']);
        }

        $clean['photo_path'] = $photoPath;
        $clean['updated_at'] = date('Y-m-d H:i:s');

        Race::update($id, $clean);
        View::setFlash('flash_success', 'Race updated.');
        Response::redirect('/admin/races/' . $id);
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->param('id');
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

        Race::delete($id);
        $this->apiClient->deletePhoto($race['photo_path']);

        View::setFlash('flash_success', 'Race deleted.');
        Response::redirect('/admin/races');
    }

    public function pdf(Request $request): void
    {
        $id = (int)$request->param('id');
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

        $photoDataUri = $this->photoDataUri($race['photo_path']);
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

    private function sendCreationEmail(array $race, string $link): void
    {
        $mailer = new Mailer($this->config);
        $subject = 'Race created: ' . $race['nom'];
        $html = $this->buildEmailHtml($race, $link);
        $sent = $mailer->send($race['contact_email'], $subject, $html);

        if (!$sent) {
            View::setFlash('flash_error', 'Email could not be sent.');
        }
    }

    private function buildEmailHtml(array $race, string $link): string
    {
        $e = fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        return '<h2>Race confirmation</h2>' .
            '<p>Your race has been created with the following data:</p>' .
            '<ul>' .
            '<li>Name: ' . $e($race['nom']) . '</li>' .
            '<li>Description: ' . $e($race['description']) . '</li>' .
            '<li>Date: ' . $e($race['date_event']) . '</li>' .
            '<li>Price: ' . $e($race['prix']) . '</li>' .
            '<li>Latitude: ' . $e($race['latitude']) . '</li>' .
            '<li>Longitude: ' . $e($race['longitude']) . '</li>' .
            '<li>Contact: ' . $e($race['contact_nom']) . '</li>' .
            '<li>Email: ' . $e($race['contact_email']) . '</li>' .
            '</ul>' .
            '<p>View details: <a href="' . $e($link) . '">' . $e($link) . '</a></p>';
    }

    private function photoDataUri(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');
        if (!str_starts_with($relativePath, 'uploads/')) {
            return '';
        }

        $fullPath = $this->publicPath() . DIRECTORY_SEPARATOR . $relativePath;
        if (!is_file($fullPath)) {
            return '';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fullPath);
        $data = base64_encode((string)file_get_contents($fullPath));

        return 'data:' . $mime . ';base64,' . $data;
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
