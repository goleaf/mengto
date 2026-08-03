<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <x-page-header
            :eyebrow="$topic !== null ? __('ui.update_discussion_ef5f7b09b7') : __('ui.new_forum_topic_ea6af1ec44')"
            :title="$topic !== null ? __('ui.keep_the_context_current_b10461f439') : __('ui.make_the_question_useful_later_76244c4d57')"
            :description="__('ui.a_precise_title_relevant_pet_context_and_what_e3cf1f9d19')"
            heading-id="forum-topic-editor-heading"
            :action-label="__('ui.cancel_19766ed6cc')"
            action-icon="x"
            :action-href="$topic !== null ? route('forum.topics.show', $topic) : route('forum.index')"
            action-variant="paper"
            data-section="forum-topic-editor-header"
        />

        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('ui.please_review_the_form_a60324c25c')"
            />
        @endif

        <section class="forum-topic-editor" data-forum-editor-shell>
            <header class="forum-topic-editor__guidance" data-forum-publishing-guidance>
                <div class="forum-topic-editor__guidance-heading">
                    <span class="forum-topic-editor__guidance-icon" aria-hidden="true">
                        <x-lucide-list-checks />
                    </span>
                    <div>
                        <p class="forum-topic-editor__eyebrow">{{ __('ui.before_publishing_8f20cb234f') }}</p>
                        <p class="forum-topic-editor__guidance-description">{{ __('forum.editor.guidance_description') }}</p>
                    </div>
                </div>

                <ul class="forum-topic-editor__guidance-list">
                    <li><x-lucide-check aria-hidden="true" /><span>{{ __('ui.use_a_title_that_names_the_actual_situation_f8c9b435a8') }}</span></li>
                    <li><x-lucide-check aria-hidden="true" /><span>{{ __('ui.separate_observation_personal_experience_and_professional_advice_bd56518ea8') }}</span></li>
                    <li><x-lucide-check aria-hidden="true" /><span>{{ __('ui.attach_only_the_pet_details_needed_to_understand_48d873de27') }}</span></li>
                    <li><x-lucide-check aria-hidden="true" /><span>{{ __('ui.use_city_or_district_instead_of_a_home_cdb8305a75') }}</span></li>
                    <li><x-lucide-check aria-hidden="true" /><span>{{ __('ui.link_important_claims_to_a_current_primary_source_312d15e202') }}</span></li>
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
                            <span>{{ __('ui.topic_type_a5f4489edb') }}</span>
                            <select name="type" required>
                                @forelse ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('type', $topic?->type->value ?? 'question') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="question">{{ __('ui.question_289aff12b0') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.category_292c06f004') }}</span>
                            <select name="category" required>
                                @forelse ($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(old('category', $topic?->category ?? 'behavior') === $key)>{{ $category['label'] }}</option>
                                @empty
                                    <option value="other">{{ __('ui.other_f97e9da0e3') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.subcategory_f44f9a8ccd') }}</span>
                            <select name="subcategory">
                                <option value="">{{ __('ui.choose_if_useful_5cd22283e6') }}</option>
                                @forelse ($categories as $category)
                                    <optgroup label="{{ $category['label'] }}">
                                        @forelse ($category['subcategories'] as $key => $label)
                                            <option value="{{ $key }}" @selected(old('subcategory', $topic?->subcategory ?? '') === $key)>{{ $label }}</option>
                                        @empty
                                            <option disabled>{{ __('ui.no_subcategories_9f1010c1a3') }}</option>
                                        @endforelse
                                    </optgroup>
                                @empty
                                    <option value="">{{ __('ui.no_subcategories_available_7e51bea808') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.related_pet_d24b433866') }}</span>
                            <select name="pet_key">
                                @forelse ($pets as $key => $label)
                                    <option value="{{ $key }}" @selected(old('pet_key', $topic?->pet_key ?? '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">{{ __('ui.no_pet_attached_e2f5a6b134') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <livewire:forum.animal-taxonomy-selector
                            :selected="$selected_taxon_ids"
                        />

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.clear_title_24abd2f8a8') }}</span>
                            <input
                                name="title"
                                value="{{ old('title', $topic?->title ?? '') }}"
                                minlength="20"
                                maxlength="180"
                                placeholder="{{ __('ui.young_dog_avoids_the_lift_after_a_loud_014815bad2') }}"
                                autocomplete="off"
                                required
                            >
                            <small>{{ __('ui.similar_solved_topics_appear_below_as_you_type_06be37c5db') }}</small>
                        </label>

                        <div class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.possible_matches_ca1114630f') }}</span>
                            <div class="similar-topics" data-similar-topics aria-live="polite"></div>
                        </div>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.what_happened_and_what_result_do_you_need_e13a953718') }}</span>
                            <textarea name="body" minlength="60" maxlength="10000" required>{{ old('body', $topic?->body ?? '') }}</textarea>
                            <small>{{ __('ui.include_timing_frequency_triggers_changes_and_relevant_limits_fc8621cef8') }}</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.what_have_you_already_tried_aa7bc64b67') }}</span>
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
                            <span>{{ __('ui.preferred_answer_81ea6030d8') }}</span>
                            <select name="desired_answer">
                                <option value="">{{ __('ui.any_relevant_answer_e60ba64267') }}</option>
                                @forelse ($desired_answers as $key => $label)
                                    <option value="{{ $key }}" @selected(old('desired_answer', $topic?->desired_answer ?? '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">{{ __('ui.any_relevant_answer_e60ba64267') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.location_only_as_precise_as_needed_6337e9ede9') }}</span>
                            <input name="location" value="{{ old('location', $topic?->location ?? '') }}" maxlength="120" placeholder="{{ __('ui.vilnius_c283e0869a') }}">
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('ui.tags_separated_by_commas_7b14ddf80a') }}</span>
                            <input
                                name="tags"
                                value="{{ old('tags', implode(', ', $topic?->tags ?? [])) }}"
                                maxlength="300"
                                placeholder="{{ implode(', ', array_slice($suggested_tags, 0, 5)) }}"
                            >
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.audience_545c023576') }}</span>
                            <select name="visibility" required>
                                @forelse ($visibility_options as $key => $label)
                                    <option value="{{ $key }}" @selected(old('visibility', $topic?->visibility->value ?? 'public') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="public">{{ __('ui.public_591935b15b') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.who_can_reply_caea4961a3') }}</span>
                            <select name="comment_policy" required>
                                @forelse ($comment_policies as $key => $label)
                                    <option value="{{ $key }}" @selected(old('comment_policy', $topic?->comment_policy ?? 'registered') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="registered">{{ __('ui.registered_members_1757e26849') }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.language_a4fe65264e') }}</span>
                            <select name="language" required>
                                <option value="en" @selected(old('language', $topic?->language ?? 'en') === 'en')>{{ __('ui.english_ba118bf7fc') }}</option>
                                <option value="lt" @selected(old('language', $topic?->language ?? 'en') === 'lt')>{{ __('ui.lithuanian_8625f6a206') }}</option>
                                <option value="ru" @selected(old('language', $topic?->language ?? 'en') === 'ru')>{{ __('ui.russian_5bcc40adf6') }}</option>
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.veterinary_contact_status_3baba76369') }}</span>
                            <select name="veterinary_status">
                                <option value="not-medical">{{ __('ui.not_a_medical_topic_0665d1efda') }}</option>
                                <option value="seen">{{ __('ui.already_seen_by_a_veterinarian_f3574b2d44') }}</option>
                                <option value="not-seen">{{ __('ui.not_yet_seen_ae5fafa302') }}</option>
                                <option value="scheduled">{{ __('ui.appointment_scheduled_d05754458e') }}</option>
                                <option value="unavailable">{{ __('ui.unable_to_reach_a_clinic_c1e6c6d11d') }}</option>
                            </select>
                        </label>
                    </div>

                    <div class="forum-form__checks">
                        <label>
                            <input type="checkbox" name="is_medical" value="1" @checked(old('is_medical', $topic?->is_medical ?? false))>
                            {{ __('ui.health_or_medical_context_2507d5510a') }}
                        </label>
                        <label>
                            <input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent', $topic?->is_urgent ?? false))>
                            {{ __('ui.time_sensitive_context_904c837c13') }}
                        </label>
                        <label>
                            <input type="checkbox" name="sensitive_media" value="1" @checked(old('sensitive_media'))>
                            {{ __('ui.media_needs_a_content_warning_8dc9ce3c6b') }}
                        </label>
                    </div>

                    <aside class="forum-safety" role="note">
                        <x-lucide-shield-check aria-hidden="true" />
                        <div>
                            <strong>{{ __('ui.urgent_symptoms_belong_with_a_clinic_not_a_445f51cbf1') }}</strong>
                            <span>{{ __('ui.difficulty_breathing_loss_of_consciousness_seizures_major_bleeding_96adae8585') }}</span>
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
                            <span>{{ __('ui.photos_up_to_four_820ba622c8') }}</span>
                            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-forum-photos>
                            <small>{{ __('ui.gps_metadata_is_not_used_blur_faces_addresses_e2180697f1') }}</small>
                        </label>

                        <label class="forum-form__field">
                            <span>{{ __('ui.short_video_e8faa500e9') }}</span>
                            <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" data-forum-video>
                            <small>{{ __('ui.maximum_20_mb_video_does_not_replace_an_aa78b2d56c') }}</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>{{ __('forum_accessibility.media.description') }}</span>
                            <input name="photo_alt" value="{{ old('photo_alt') }}" maxlength="240" placeholder="{{ __('ui.scout_waits_several_metres_from_the_closed_lift_2f77b6d5cd') }}" data-forum-media-description>
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
                                <option value="">{{ __('ui.choose_if_useful_5cd22283e6') }}</option>
                                <option value="en" @selected(old('video_caption_locale') === 'en')>{{ __('ui.english_ba118bf7fc') }}</option>
                                <option value="lt" @selected(old('video_caption_locale') === 'lt')>{{ __('ui.lithuanian_8625f6a206') }}</option>
                                <option value="ru" @selected(old('video_caption_locale') === 'ru')>{{ __('ui.russian_5bcc40adf6') }}</option>
                            </select>
                        </label>
                    </div>
                </section>

                <div class="forum-form__actions forum-topic-editor__actions">
                    <button type="submit" name="intent" value="draft" class="forum-button">
                        <x-lucide-file-clock aria-hidden="true" />
                        {{ __('ui.save_draft_3de100106d') }}
                    </button>
                    <button type="submit" name="intent" value="publish" class="forum-button forum-button--primary">
                        <x-lucide-send aria-hidden="true" />
                        {{ ($topic?->status->value ?? 'published') === 'draft' ? __('ui.publish_topic_54f6ce2e71') : ($topic !== null ? __('ui.save_changes_dd0ae7a5cb') : __('ui.publish_topic_54f6ce2e71')) }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-shell>
