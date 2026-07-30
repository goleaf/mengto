@props(['post'])

<x-reply-composer
    action="create-comment"
    :target="$post['key']"
    :label="__('presentation.reply_about', ['author' => $post['author'], 'pet' => $post['pet']])"
    :placeholder="__('ui.add_to_the_conversation_about_ebbfe17ac9').' '.$post['pet'].'...'"
    field-id="post-reply"
    submit-label="{{ __('ui.post_reply_860e367626') }}"
    submit-icon="message-circle"
    variant="thread"
/>
