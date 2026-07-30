@props(['pet'])

<article class="group-pet panel">
    <x-avatar
        :src="$pet['image']"
        :alt="$pet['image_alt']"
        size="profile"
        class="group-pet__avatar"
    />
    <div class="group-pet__body">
        <h3>{{ $pet['name'] }}</h3>
        <p>{{ $pet['detail'] }}</p>
        <x-icon-text icon="circle-check-big">{{ $pet['status'] }}</x-icon-text>
    </div>
</article>
