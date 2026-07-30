@props(['contactName', 'contactKey', 'placeholder'])

<x-reply-composer
    action="send-message"
    :target="$contactKey"
    :label="'Reply to '.$contactName"
    :placeholder="$placeholder"
    field-id="message-reply"
    variant="message"
/>
