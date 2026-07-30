@props(['entry', 'eager' => false])

@switch($entry['type'])
    @case('post')
        <x-feature.feed-card :post="$entry['data']" :eager="$eager" />
        @break

    @case('neighbor')
        <x-object.neighbor-card :neighbor="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('pet')
        <x-object.pet-directory-card :pet="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('group')
        <x-object.group-card :group="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('meetup')
        <x-object.meetup-card :meetup="$entry['data']" :eager="$eager" role="listitem" />
        @break
@endswitch
