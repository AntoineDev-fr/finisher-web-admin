<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Race;

final class PublicController
{
    public function index(Request $request): void
    {
        $races = Race::all();
        $html = View::render('public/index', [
            'title' => 'Races',
            'races' => $races,
        ]);
        Response::html($html);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $race = Race::find($id);
        if ($race === null) {
            Response::abort(404, 'Race not found.');
        }

        $html = View::render('public/show', [
            'title' => 'Race Details',
            'race' => $race,
        ]);
        Response::html($html);
    }
}
