<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\ApiClient;

final class PublicController
{
    private ApiClient $apiClient;

    public function __construct(array $config)
    {
        $this->apiClient = new ApiClient($config['api'] ?? []);
    }

    public function index(Request $request): void
    {
        $res = $this->apiClient->fetchAllRaces('');
        if (!$res['ok']) {
            Response::abort(502, 'API error: ' . ($res['error'] ?? 'unknown'));
        }
        $races = $res['data'] ?? [];
        $html = View::render('public/index', [
            'title' => 'Races',
            'races' => $races,
        ]);
        Response::html($html);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $res = $this->apiClient->getRace($id);
        if (!$res['ok']) {
            Response::abort(404, 'Race not found.');
        }
        $race = $res['data'];

        $html = View::render('public/show', [
            'title' => 'Race Details',
            'race' => $race,
        ]);
        Response::html($html);
    }
}
