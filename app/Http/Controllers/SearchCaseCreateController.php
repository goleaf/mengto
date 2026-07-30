<?php

namespace App\Http\Controllers;

use App\Models\SearchCase;
use App\Services\SearchPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SearchCaseCreateController extends Controller
{
    public function __invoke(Request $request, SearchPresenter $presenter): View
    {
        Gate::authorize('create', SearchCase::class);

        return view('lost-found.create', $presenter->editor($request->string('pet')->toString()));
    }
}
