@props(['contactName', 'contactKey', 'placeholder'])

<x-reply-composer
    action="send-message"
    :target="$contactKey"
    :label="__('ui.reply_to_6e5842a7ca').' '.$contactName"
    :placeholder="$placeholder"
    field-id="message-reply"
    variant="message"
/>
