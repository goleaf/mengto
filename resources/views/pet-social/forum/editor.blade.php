@php
    $editing = $topic !== null;
    $topicType = $editing ? $topic->type->value : 'question';
    $topicCategory = $editing ? $topic->category : 'behavior';
    $topicVisibility = $editing ? $topic->visibility->value : 'public';
    $topicStatus = $editing ? $topic->status->value : 'published';
@endphp

<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <header class="forum-header">
            <div class="forum-header__copy">
                <p class="forum-header__eyebrow">{{ $editing ? 'Update discussion' : 'New forum topic' }}</p>
                <h1>{{ $editing ? 'Keep the context current.' : 'Make the question useful later.' }}</h1>
                <p>A precise title, relevant pet context, and what you already tried help the community answer the situation rather than guess around it.</p>
            </div>
            <div class="forum-header__actions">
                <a href="{{ $editing ? route('pet-social.forum.topics.show', $topic) : route('pet-social.forum.index') }}" class="forum-button">
                    <x-lucide-x aria-hidden="true" />
                    Cancel
                </a>
            </div>
        </header>

        @if ($errors->any())
            <div class="forum-errors" role="alert">
                <strong>Please review the form.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="forum-thread-layout">
            <main>
                <form
                    method="POST"
                    action="{{ $editing ? route('pet-social.forum.topics.update', $topic) : route('pet-social.forum.topics.store') }}"
                    enctype="multipart/form-data"
                    class="forum-form"
                    data-forum-editor
                    data-draft-key="{{ $editing ? 'topic-'.$topic->id : 'new' }}"
                    data-similar-endpoint="{{ route('pet-social.forum.topics.similar') }}"
                >
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <div class="forum-form__grid">
                        <label class="forum-form__field">
                            <span>Topic type</span>
                            <select name="type" required>
                                @forelse ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('type', $topicType) === $key)>{{ $label }}</option>
                                @empty
                                    <option value="question">Question</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Category</span>
                            <select name="category" required>
                                @forelse ($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(old('category', $topicCategory) === $key)>{{ $category['label'] }}</option>
                                @empty
                                    <option value="other">Other</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Subcategory</span>
                            <select name="subcategory">
                                <option value="">Choose if useful</option>
                                @forelse ($categories as $category)
                                    <optgroup label="{{ $category['label'] }}">
                                        @forelse ($category['subcategories'] as $key => $label)
                                            <option value="{{ $key }}" @selected(old('subcategory', $editing ? $topic->subcategory : '') === $key)>{{ $label }}</option>
                                        @empty
                                            <option disabled>No subcategories</option>
                                        @endforelse
                                    </optgroup>
                                @empty
                                    <option value="">No subcategories available</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Related pet</span>
                            <select name="pet_key">
                                @forelse ($pets as $key => $label)
                                    <option value="{{ $key }}" @selected(old('pet_key', $editing ? $topic->pet_key : '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">No pet attached</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>Clear title</span>
                            <input
                                name="title"
                                value="{{ old('title', $editing ? $topic->title : '') }}"
                                minlength="20"
                                maxlength="180"
                                placeholder="Young dog avoids the lift after a loud noise"
                                autocomplete="off"
                                required
                            >
                            <small>Similar solved topics appear below as you type.</small>
                        </label>

                        <div class="forum-form__field forum-form__field--full">
                            <span>Possible matches</span>
                            <div class="similar-topics" data-similar-topics aria-live="polite"></div>
                        </div>

                        <label class="forum-form__field forum-form__field--full">
                            <span>What happened and what result do you need?</span>
                            <textarea name="body" minlength="60" maxlength="10000" required>{{ old('body', $editing ? $topic->body : '') }}</textarea>
                            <small>Include timing, frequency, triggers, changes, and relevant limits. Do not publish a home address or full medical record.</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>What have you already tried?</span>
                            <textarea name="tried" maxlength="2500">{{ old('tried') }}</textarea>
                        </label>

                        <label class="forum-form__field">
                            <span>Preferred answer</span>
                            <select name="desired_answer">
                                <option value="">Any relevant answer</option>
                                @forelse ($desired_answers as $key => $label)
                                    <option value="{{ $key }}" @selected(old('desired_answer', $editing ? $topic->desired_answer : '') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="">Any relevant answer</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Location, only as precise as needed</span>
                            <input name="location" value="{{ old('location', $editing ? $topic->location : '') }}" maxlength="120" placeholder="Vilnius">
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>Tags separated by commas</span>
                            <input
                                name="tags"
                                value="{{ old('tags', $editing ? implode(', ', $topic->tags ?? []) : '') }}"
                                maxlength="300"
                                placeholder="{{ implode(', ', array_slice($suggested_tags, 0, 5)) }}"
                            >
                        </label>

                        <label class="forum-form__field">
                            <span>Audience</span>
                            <select name="visibility" required>
                                @forelse ($visibility_options as $key => $label)
                                    <option value="{{ $key }}" @selected(old('visibility', $topicVisibility) === $key)>{{ $label }}</option>
                                @empty
                                    <option value="public">Public</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Who can reply?</span>
                            <select name="comment_policy" required>
                                @forelse ($comment_policies as $key => $label)
                                    <option value="{{ $key }}" @selected(old('comment_policy', $editing ? $topic->comment_policy : 'registered') === $key)>{{ $label }}</option>
                                @empty
                                    <option value="registered">Registered members</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Language</span>
                            <select name="language" required>
                                <option value="en" @selected(old('language', $editing ? $topic->language : 'en') === 'en')>English</option>
                                <option value="lt" @selected(old('language', $editing ? $topic->language : 'en') === 'lt')>Lithuanian</option>
                                <option value="ru" @selected(old('language', $editing ? $topic->language : 'en') === 'ru')>Russian</option>
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Veterinary contact status</span>
                            <select name="veterinary_status">
                                <option value="not-medical">Not a medical topic</option>
                                <option value="seen">Already seen by a veterinarian</option>
                                <option value="not-seen">Not yet seen</option>
                                <option value="scheduled">Appointment scheduled</option>
                                <option value="unavailable">Unable to reach a clinic</option>
                            </select>
                        </label>

                        <label class="forum-form__field">
                            <span>Photos, up to four</span>
                            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                            <small>GPS metadata is not used. Blur faces, addresses, document numbers, and microchip identifiers.</small>
                        </label>

                        <label class="forum-form__field">
                            <span>Short video</span>
                            <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime">
                            <small>Maximum 20 MB. Video does not replace an in-person diagnosis.</small>
                        </label>

                        <label class="forum-form__field forum-form__field--full">
                            <span>Media description</span>
                            <input name="photo_alt" value="{{ old('photo_alt') }}" maxlength="240" placeholder="Scout waits several metres from the closed lift doors">
                        </label>
                    </div>

                    <div class="forum-form__checks">
                        <label>
                            <input type="checkbox" name="is_medical" value="1" @checked(old('is_medical', $editing ? $topic->is_medical : false))>
                            Health or medical context
                        </label>
                        <label>
                            <input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent', $editing ? $topic->is_urgent : false))>
                            Time-sensitive context
                        </label>
                        <label>
                            <input type="checkbox" name="sensitive_media" value="1" @checked(old('sensitive_media'))>
                            Media needs a content warning
                        </label>
                    </div>

                    <aside class="forum-safety">
                        <x-lucide-shield-check aria-hidden="true" />
                        <div>
                            <strong>Urgent symptoms belong with a clinic, not a queue of forum replies</strong>
                            <span>Difficulty breathing, loss of consciousness, seizures, major bleeding, suspected poisoning, severe trauma, or inability to urinate require immediate professional contact.</span>
                        </div>
                    </aside>

                    <div class="forum-form__actions">
                        <button type="submit" name="intent" value="draft" class="forum-button">
                            <x-lucide-file-clock aria-hidden="true" />
                            Save draft
                        </button>
                        <button type="submit" name="intent" value="publish" class="forum-button forum-button--primary">
                            <x-lucide-send aria-hidden="true" />
                            {{ $topicStatus === 'draft' ? 'Publish topic' : ($editing ? 'Save changes' : 'Publish topic') }}
                        </button>
                    </div>
                </form>
            </main>

            <aside class="forum-sidebar">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Before publishing</span></div>
                    <div class="forum-mini-list">
                        <span>Use a title that names the actual situation.</span>
                        <span>Separate observation, personal experience, and professional advice.</span>
                        <span>Attach only the pet details needed to understand the question.</span>
                        <span>Use city or district instead of a home address.</span>
                        <span>Link important claims to a current primary source.</span>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-layout.app-shell>
