@props(['contactName', 'placeholder'])

<div class="border-t border-paw-line bg-white p-4 sm:p-5">
    <label for="message-reply" class="sr-only">Reply to {{ $contactName }}</label>
    <textarea
        id="message-reply"
        rows="2"
        placeholder="{{ $placeholder }}"
        aria-disabled="true"
        disabled
        class="pc-field pc-field--textarea"
    ></textarea>

    <x-pet-social.action-group class="mt-3">
        <x-pet-social.static-action label="Attach" icon="paperclip" size="toolbar" />
        <x-pet-social.static-action label="Send" icon="send" variant="primary" size="toolbar" />
    </x-pet-social.action-group>
</div>
