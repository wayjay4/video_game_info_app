<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IgdbApiService;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public IgdbApiService $igdbApiService;
    public array $headers;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
        $this->headers = $this->igdbApiService->getApiHeaders();
    }

    public function filter(Request $request): Application|Response|ContractsApplication|ResponseFactory
    {
        $headers = $this->headers;

        $query = Http::withHeaders($headers)->withBody("
            search \"{$request->get('filter')}\";
            fields id, name, slug, cover.url, platforms.abbreviation;
            where platforms = (48,49,130,6) & cover != null;
            limit 8;
        ", 'text/plain')
            ->post('https://api.igdb.com/v4/games')
            ->json();

        return response([
            'filter' => $request->get('filter'),
            'result' => $this->igdbApiService->formatSearchedGames($query),
            'message' => 'your search was successful.',
        ], 200);
    }
}
