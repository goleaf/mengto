@props(['entry', 'eager' => false])

@switch($entry['type'])
    @case('post')
        <x-feed-card :post="$entry['data']" :eager="$eager" />
        @break

    @case('neighbor')
        <x-neighbor-card :neighbor="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('pet')
        <x-pet-directory-card :pet="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('group')
        <x-group-card :group="$entry['data']" :eager="$eager" role="listitem" />
        @break

    @case('meetup')
        <x-meetup-card :meetup="$entry['data']" :eager="$eager" role="listitem" />
        @break
@endswitch
