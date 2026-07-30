@props(['post'])

<x-feature.reply-composer
    action="create-comment"
    :target="$post['key']"
    :label="'Reply to '.$post['author'].' about '.$post['pet']"
    :placeholder="'Add to the conversation about '.$post['pet'].'...'"
    field-id="post-reply"
    submit-label="Post reply"
    submit-icon="message-circle"
    variant="thread"
/>
