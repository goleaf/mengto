<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SearchCase;
use App\Services\SearchPresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class SearchCaseCreateController extends Controller
{
    public function __invoke(Request $request, SearchPresenter $presenter, Gate $gate): View
    {
        $gate->authorize('create', SearchCase::class);

        return view('lost-found.create', $presenter->editor($request->string('pet')->toString()));
    }
}
