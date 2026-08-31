<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContentPublication;
use App\Models\User;
use App\Services\ContentFeedPresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ContentPublicationController extends Controller
{
    public function __invoke(
        Request $request,
        ContentPublication $contentPublication,
        Gate $gate,
        ContentFeedPresenter $presenter,
    ): View {
        $contentPublication->loadMissing([
            'publishingActor' => fn ($query) => $query->directoryFields(),
        ]);
        $gate->authorize('view', $contentPublication);

        $contentPublication->loadMissing([
            'publishingActor.user:id,name',
            'publishingActor.petProfile:id,name',
            'publishingActor.expertProfile:id,public_name',
            'publishingActor.forumGroup:id,name,name_translation_key,is_system_managed',
            'audienceRule:id,content_publication_id,audience_type',
            'interactionSettings:id,content_publication_id,allow_comments,allow_reactions,allow_reposts,allow_external_sharing,allow_media_downloads',
            'domainLinks:id,content_publication_id,domain_type,domain_key,relationship,is_primary',
        ]);
        $viewer = $request->user();
        $title = $contentPublication->title ?: __('content.publication.untitled');
        $publication = $presenter->present(
            $contentPublication,
            $viewer instanceof User ? $viewer : null,
        );

        return view('content.show', [
            'page_title' => $title,
            'publication' => [...$publication, 'title' => $title],
        ]);
    }
}
