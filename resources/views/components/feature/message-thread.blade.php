@props(['thread'])

<section data-section="message-thread" {{ $attributes->class(['panel', 'panel--clip']) }}>
    <x-feature.message-thread-header :contact="$thread['contact']" />
    <x-object.message-context :context="$thread['context']" />
    <x-feature.thread-message-list :messages="$thread['messages']" :date-label="$thread['date_label']" />
    <x-feature.message-composer
        :contact-name="$thread['contact']['name']"
        :contact-key="$thread['contact']['key']"
        :placeholder="$thread['reply_placeholder']"
    />
</section>
