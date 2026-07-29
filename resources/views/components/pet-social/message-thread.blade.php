@props(['thread'])

<section data-section="message-thread" {{ $attributes->class(['pc-panel', 'pc-panel--clip']) }}>
    <x-pet-social.message-thread-header :contact="$thread['contact']" />
    <x-pet-social.message-context :context="$thread['context']" />
    <x-pet-social.thread-message-list :messages="$thread['messages']" :date-label="$thread['date_label']" />
    <x-pet-social.message-composer
        :contact-name="$thread['contact']['name']"
        :placeholder="$thread['reply_placeholder']"
    />
</section>
