<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <x-page-header
            :eyebrow="$topic !== null ? __('ui.update_discussion') : __('ui.new_forum_topic')"
            :title="$topic !== null ? __('ui.keep_the_context_current') : __('ui.make_the_question_useful_later')"
            :description="__('ui.a_precise_title_relevant_pet_context_and_what_you_already_tried_help_the_community_answer_the_situation_rather_than_guess_around_it')"
            heading-id="forum-topic-editor-heading"
            :action-label="__('ui.cancel')"
            action-icon="x"
            :action-href="$topic !== null ? route('forum.topics.show', $topic) : route('forum.index')"
            action-variant="paper"
            data-section="forum-topic-editor-header"
        />

        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('ui.please_review_the_form')"
            />
        @endif

        <section class="forum-topic-editor" data-forum-editor-shell>
            <header class="forum-topic-editor__guidance" data-forum-publishing-guidance>
                <div class="forum-topic-editor__guidance-heading">
                    <span class="forum-topic-editor__guidance-icon" aria-hidden="true">
                        <x-ui-icon name="list-checks" />
                    </span>
                    <div>
                        <p class="forum-topic-editor__eyebrow">{{ __('ui.before_publishing') }}</p>
                        <p class="forum-topic-editor__guidance-description">{{ __('forum.editor.guidance_description') }}</p>
                    </div>
                </div>

                <ul class="forum-topic-editor__guidance-list">
                    <li><x-ui-icon name="check" /><span>{{ __('ui.use_a_title_that_names_the_actual_situation') }}</span></li>
                    <li><x-ui-icon name="check" /><span>{{ __('ui.separate_observation_personal_experience_and_professional_advice') }}</span></li>
                    <li><x-ui-icon name="check" /><span>{{ __('ui.attach_only_the_pet_details_needed_to_understand_the_question') }}</span></li>
                    <li><x-ui-icon name="check" /><span>{{ __('ui.use_city_or_district_instead_of_a_home_address') }}</span></li>
                    <li><x-ui-icon name="check" /><span>{{ __('ui.link_important_claims_to_a_current_primary_source') }}</span></li>
                </ul>
            </header>

            <form
                method="POST"
                action="{{ $topic !== null ? route('forum.topics.update', $topic) : route('forum.topics.store') }}"
                enctype="multipart/form-data"
                class="forum-form forum-topic-editor__form"
                data-forum-editor
                data-draft-key="{{ $topic !== null ? 'topic-'.$topic->id : 'new' }}"
                data-similar-endpoint="{{ route('forum.topics.similar') }}"
            >
                @csrf
                @if ($topic !== null)
                    @method('PUT')
                @endif

                <section
                    class="forum-topic-editor__section"
                    aria-labelledby="forum-editor-context-heading"
                    data-forum-editor-section="context"
                >
                    <header class="forum-topic-editor__section-heading">
                        <span aria-hidden="true">01</span>
                        <div>
                            <h2 id="forum-editor-context-heading">{{ __('forum.editor.context_title') }}</h2>
                            <p>{{ __('forum.editor.context_description') }}</p>
                        </div>
                    </header>

                    <div class="forum-form__grid">
                        <label class="forum-form__field">
                            <span>{{ __('ui.topic_type') }}</span>
                            <select name="type" required>
                                @forelse ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('type', $topic?->type->value ?? 'question') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="question">{{ __('ui.question') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.category') }}</span>
                            <select name="category" required>
                                @forelse ($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(old('category', $topic?->category ?? 'behavior') === $key)>{{ $category['label'] }}</option>
                                @empty
                                    <option value="other">{{ __('ui.other') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.subcategory') }}</span>
                            <select name="subcategory">
                                <option value="">{{ __('ui.choose_if_useful') }}</option>
                                @forelse ($categories as $category)
                                    <optgroup label="{{ $category['label'] }}">
                                        @forelse ($category['subcategories'] as $key => $label)
                                            <option value="{{ $key }}" @selected(old('subcategory', $topic?->subcategory ?? '') === $key)>{{ $label }}</option>
                                        @empty
                                            <option disabled>{{ __('ui.no_subcategories') }}</option>
                                        @endforelse
                                    </optgroup>
                                @empty
                                    <option value="">{{ __('ui.no_subcategories_available') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.related_pet') }}</span>
                            <select name="pet_key">
                                @forelse ($pets as $key => $label)
                                    <option value="{{ $key }}" @selected(old('pet_key', $topic?->pet_key ?? '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">{{ __('ui.no_pet_attached') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <livewire:forum.animal-taxonomy-selector
                            :selected="$selected_taxon_ids"
                        />

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.clear_title') }}</span>
                            <input
                                name="title"
                                value="{{ old('title', $topic?->title ?? '') }}"
                                minlength="20"
                                maxlength="180"
                                placeholder="{{ __('ui.young_dog_avoids_the_lift_after_a_loud_noise') }}"
                                autocomplete="off"
                                required
                            >
                            <small>{{ __('ui.similar_solved_topics_appear_below_as_you_type') }}</small>
                        </label>

                        <div class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.possible_matches') }}</span>
                            <div class="similar-topics" data-similar-topics aria-live="polite"></div>
                        </div>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.what_happened_and_what_result_do_you_need') }}</span>
                            <textarea name="body" minlength="60" maxlength="10000" required>{{ old('body', $topic?->body ?? '') }}</textarea>
                            <small>{{ __('ui.include_timing_frequency_triggers_changes_and_relevant_limits_do_not_publish_a_home_address_or_full_medical_record') }}</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.what_have_you_already_tried') }}</span>
                            <textarea name="tried" maxlength="2500">{{ old('tried') }}</textarea>
                        </label>
                    </div>
                </section>

                <section
                    class="forum-topic-editor__section"
                    aria-labelledby="forum-editor-response-heading"
                    data-forum-editor-section="response"
                >
                    <header class="forum-topic-editor__section-heading">
                        <span aria-hidden="true">02</span>
                        <div>
                            <h2 id="forum-editor-response-heading">{{ __('forum.editor.response_title') }}</h2>
                            <p>{{ __('forum.editor.response_description') }}</p>
                        </div>
                    </header>

                    <div class="forum-form__grid">

                        <label class="forum-form__field">
                            <span>{{ __('ui.preferred_answer') }}</span>
                            <select name="desired_answer">
                                <option value="">{{ __('ui.any_relevant_answer') }}</option>
                                @forelse ($desired_answers as $key => $label)
                                    <option value="{{ $key }}" @selected(old('desired_answer', $topic?->desired_answer ?? '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">{{ __('ui.any_relevant_answer') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.location_only_as_precise_as_needed') }}</span>
                            <input name="location" value="{{ old('location', $topic?->location ?? '') }}" maxlength="120" placeholder="{{ __('ui.vilnius') }}">
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.tags_separated_by_commas') }}</span>
                            <input
                                name="tags"
                                value="{{ old('tags', implode(', ', $topic?->tags ?? [])) }}"
                                maxlength="300"
                                placeholder="{{ implode(', ', array_slice($suggested_tags, 0, 5)) }}"
                            >
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.audience') }}</span>
                            <select name="visibility" required>
                                @forelse ($visibility_options as $key => $label)
                                    <option value="{{ $key }}" @selected(old('visibility', $topic?->visibility->value ?? 'public') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="public">{{ __('ui.public') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.who_can_reply') }}</span>
                            <select name="comment_policy" required>
                                @forelse ($comment_policies as $key => $label)
                                    <option value="{{ $key }}" @selected(old('comment_policy', $topic?->comment_policy ?? 'registered') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="registered">{{ __('ui.registered_members') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.language') }}</span>
                            <select name="language" required>
                                <option value="en" @selected(old('language', $topic?->language ?? 'en') === 'en')>{{ __('ui.english') }}</option>
                                <option value="lt" @selected(old('language', $topic?->language ?? 'en') === 'lt')>{{ __('ui.lithuanian') }}</option>
                                <option value="ru" @selected(old('language', $topic?->language ?? 'en') === 'ru')>{{ __('ui.russian') }}</option>
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.veterinary_contact_status') }}</span>
                            <select name="veterinary_status">
                                <option value="not-medical">{{ __('ui.not_a_medical_topic') }}</option>
                                <option value="seen">{{ __('ui.already_seen_by_a_veterinarian') }}</option>
                                <option value="not-seen">{{ __('ui.not_yet_seen') }}</option>
                                <option value="scheduled">{{ __('ui.appointment_scheduled') }}</option>
                                <option value="unavailable">{{ __('ui.unable_to_reach_a_clinic') }}</option>
                            </select>
                        </label>
                    </div>

                    <div class="forum-form__checks">
                        <label>
                            <input type="checkbox" name="is_medical" value="1" @checked(old('is_medical', $topic?->is_medical ?? false))>
                            {{ __('ui.health_or_medical_context') }}
                        </label>
                        <label>
                            <input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent', $topic?->is_urgent ?? false))>
                            {{ __('ui.time_sensitive_context') }}
                        </label>
                        <label>
                            <input type="checkbox" name="sensitive_media" value="1" @checked(old('sensitive_media'))>
                            {{ __('ui.media_needs_a_content_warning') }}
                        </label>
                    </div>

                    <aside class="forum-safety" role="note">
                        <x-ui-icon name="shield-check" />
                        <div>
                            <strong>{{ __('ui.urgent_symptoms_belong_with_a_clinic_not_a_queue_of_forum_replies') }}</strong>
                            <span>{{ __('ui.difficulty_breathing_loss_of_consciousness_seizures_major_bleeding_suspected_poisoning_severe_trauma_or_inability_to_urinate_require_immediate_professional') }}</span>
                        </div>
                    </aside>
                </section>

                <section
                    class="forum-topic-editor__section"
                    aria-labelledby="forum-editor-media-heading"
                    data-forum-editor-section="media"
                >
                    <header class="forum-topic-editor__section-heading">
                        <span aria-hidden="true">03</span>
                        <div>
                            <h2 id="forum-editor-media-heading">{{ __('forum.editor.media_title') }}</h2>
                            <p>{{ __('forum.editor.media_description') }}</p>
                        </div>
                    </header>

                    <div class="forum-form__grid">

                        <label class="forum-form__field">
                            <span>{{ __('ui.photos_up_to_four') }}</span>
                            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-forum-photos>
                            <small>{{ __('ui.gps_metadata_is_not_used_blur_faces_addresses_document_numbers_and_microchip_identifiers') }}</small>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.short_video') }}</span>
                            <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" data-forum-video>
                            <small>{{ __('ui.maximum_20_mb_video_does_not_replace_an_in_person_diagnosis') }}</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('forum_accessibility.media.description') }}</span>
                            <input name="photo_alt" value="{{ old('photo_alt') }}" maxlength="240" placeholder="{{ __('ui.scout_waits_several_metres_from_the_closed_lift_doors') }}" data-forum-media-description>
                            <small>{{ __('forum_accessibility.media.description_help') }}</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('forum_accessibility.media.video_transcript') }}</span>
                            <textarea name="video_transcript" maxlength="10000" data-forum-video-transcript>{{ old('video_transcript') }}</textarea>
                            <small>{{ __('forum_accessibility.media.video_transcript_help') }}</small>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('forum_accessibility.media.caption_file') }}</span>
                            <input type="file" name="video_captions" accept=".vtt,text/vtt" data-forum-caption>
                            <small>{{ __('forum_accessibility.media.caption_file_help') }}</small>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('forum_accessibility.media.caption_locale') }}</span>
                            <select name="video_caption_locale" data-forum-caption-locale>
                                <option value="">{{ __('ui.choose_if_useful') }}</option>
                                <option value="en" @selected(old('video_caption_locale') === 'en')>{{ __('ui.english') }}</option>
                                <option value="lt" @selected(old('video_caption_locale') === 'lt')>{{ __('ui.lithuanian') }}</option>
                                <option value="ru" @selected(old('video_caption_locale') === 'ru')>{{ __('ui.russian') }}</option>
                            </select>
                        </label>
                    </div>
                </section>

                <div class="forum-form__actions forum-topic-editor__actions">
                    <button type="submit" name="intent" value="draft" class="forum-button">
                        <x-ui-icon name="file-clock" />
                        {{ __('ui.save_draft') }}
                    </button>
                    <button type="submit" name="intent" value="publish" class="forum-button forum-button--primary">
                        <x-ui-icon name="send" />
                        {{ ($topic?->status->value ?? 'published') === 'draft' ? __('ui.publish_topic') : ($topic !== null ? __('ui.save_changes') : __('ui.publish_topic')) }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-shell>
