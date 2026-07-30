<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseComposerRequest;
use App\Services\PawCircleEventCatalog;
use App\Services\PawCircleFeedPresenter;
use App\Services\PawCircleGroupCatalog;
use App\Services\PawCirclePlaceCatalog;
use App\Services\PawCirclePlacePresenter;
use App\Services\PawCirclePreviewService;
use App\Services\PawCircleProfilePresenter;
use Illuminate\Contracts\View\View;

class PawCircleComposerController extends Controller
{
    public function __invoke(
        BrowseComposerRequest $request,
        string $kind,
        PawCirclePreviewService $preview,
        PawCircleFeedPresenter $feed,
        PawCircleProfilePresenter $profiles,
        PawCircleGroupCatalog $groups,
        PawCircleEventCatalog $events,
        PawCirclePlaceCatalog $places,
        PawCirclePlacePresenter $placePresenter,
    ): View {
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

        $validated = $request->validated();

        abort_if($kind === 'report-profile' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-post' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-group' && ! isset($validated['target']), 404);
        abort_if($kind === 'report-event' && ! isset($validated['target']), 404);
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
            $kind === 'report-event' && $events->reportContext((string) $validated['target']) === null,
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

        return view('pet-social.compose', $preview->composerData($kind, $validated));
    }
}
