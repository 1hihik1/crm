<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminToolsController extends Controller
{
    public function clearCache(Request $request): RedirectResponse
    {
        Artisan::call('cache:clear');

        return back()->with('message', 'Кэш приложения очищен.');
    }

    public function clearConfig(Request $request): RedirectResponse
    {
        Artisan::call('config:clear');

        return back()->with('message', 'Кэш конфигурации очищен.');
    }

    public function clearViews(Request $request): RedirectResponse
    {
        Artisan::call('view:clear');

        return back()->with('message', 'Кэш представлений очищен.');
    }
}
