@props(['pet'])

<article class="group-pet panel">
    <x-ui.avatar
        :src="$pet['image']"
        :alt="$pet['image_alt']"
        size="profile"
        class="group-pet__avatar"
    />
    <div class="group-pet__body">
        <h3>{{ $pet['name'] }}</h3>
        <p>{{ $pet['detail'] }}</p>
        <x-ui.icon-text icon="circle-check-big">{{ $pet['status'] }}</x-ui.icon-text>
    </div>
</article>
