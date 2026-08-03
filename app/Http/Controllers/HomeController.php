<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

final class HomeController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('content.index');
    }
}
