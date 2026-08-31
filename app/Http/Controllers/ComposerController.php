<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseComposerRequest;
use App\Services\FeedPresenter;
use App\Services\GroupCatalog;
use App\Services\PlaceCatalog;
use App\Services\PlacePresenter;
use App\Services\PreviewService;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ComposerController extends Controller
{
    public function __invoke(
        BrowseComposerRequest $request,
        string $kind,
        PreviewService $preview,
        FeedPresenter $feed,
        ProfilePresenter $profiles,
        GroupCatalog $groups,
        PlaceCatalog $places,
        PlacePresenter $placePresenter,
    ): View|RedirectResponse {
        abort_unless(in_array($kind, [
            'post',
            'group',
            'meetup',
            'walk',
            'pet',
            'place',
            'place-correction',
            'place-warning',
            'place-review',
            'place-question',
            'place-claim',
            'message',
            'profile',
            'pet-profile',
            'profile-privacy',
            'pet-privacy',
            'report-profile',
            'post-edit',
            'report-post',
            'report-group',
            'report-event',
            'report-place',
            'delete-post',
        ], true), 404);

        if ($kind === 'meetup') {
            return to_route('meetups.create');
        }

        if ($kind === 'pet') {
            return to_route('pets.manage.create');
        }

        if ($kind === 'place') {
            return to_route('places.submissions.create');
        }

        $validated = $request->validated();

        abort_if($kind === 'report-profile' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-post' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-group' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-event' && ! isset($validated['target']), 404);

        if ($kind === 'report-event') {
            return to_route('meetups.show', [
                'event' => (string) $validated['target'],
            ]);
        }

        abort_if(
            in_array($kind, [
                'place-correction',
                'place-warning',
                'place-review',
                'place-question',
                'place-claim',
                'report-place',
            ], true) && ! isset($validated['target']),
            404,
        );
        abort_if($kind === 'post-edit' && ! isset($validated['post']), 404);
        abort_if($kind === 'delete-post' && ! isset($validated['post']), 404);
        abort_if(
            $kind === 'report-profile' && $profiles->reportContext((string) $validated['target']) === null,
            404,
        );
        abort_if(
            $kind === 'report-post' && $feed->reportContext((string) $validated['target']) === null,
            404,
        );
        abort_if(
            $kind === 'report-group' && $groups->reportContext((string) $validated['target']) === null,
            404,
        );
        abort_if(
            in_array($kind, [
                'place-warning',
                'place-review',
                'place-question',
                'place-claim',
                'report-place',
            ], true)
                && $placePresenter->reportContext((string) $validated['target']) === null,
            404,
        );
        abort_if(
            $kind === 'place-correction'
                && $placePresenter->correctionContext((string) $validated['target']) === null,
            404,
        );
        abort_if(
            isset($validated['place'])
                && $places->find((string) $validated['place']) === null,
            404,
        );
        abort_if(
            in_array($kind, ['post-edit', 'delete-post'], true)
                && $feed->editablePost((string) $validated['post']) === null,
            404,
        );

        return view('compose', $preview->composerData($kind, $validated));
    }
}
