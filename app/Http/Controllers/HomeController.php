<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HomeDestination;
use App\Models\User;
use App\Services\HomeDestinationResolver;
use App\Services\JoinPagePresenter;
use App\Services\UnavailableAccountResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        HomeDestinationResolver $destinationResolver,
        JoinPagePresenter $presenter,
        UnavailableAccountResponse $unavailableAccount,
    ): View|RedirectResponse {
        $user = $request->user();
        $destination = $destinationResolver->resolve($user instanceof User ? $user : null);

        return match ($destination) {
            HomeDestination::Join => view('join', $presenter->page()),
            HomeDestination::VerifyEmail => redirect()->route('verification.notice'),
            HomeDestination::ContentFeed => redirect()->route('content.index'),
            HomeDestination::UnavailableAccount => $unavailableAccount->redirect($request),
        };
    }
}
