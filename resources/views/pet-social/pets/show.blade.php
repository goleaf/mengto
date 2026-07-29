<x-pet-social.app-shell :owner="$owner" title="Scout | PawCircle" active-section="pets">
    <x-pet-social.page-stack data-section="pet-profile">
        <x-pet-social.pet-profile-hero :pet="$pet" />

        <x-pet-social.main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-pet-social.page-stack gap="content">
                    <x-pet-social.content-panel eyebrow="Life with Scout" title="About Scout">
                        <x-pet-social.section-copy :text="$pet['story']" />
                    </x-pet-social.content-panel>

                    <x-pet-social.pet-gallery :photos="$pet['gallery']" />
                    <x-pet-social.recent-moments :posts="$recentMoments" eyebrow="From Mia" />
                </x-pet-social.page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-pet-social.pet-facts title="Care profile" section="care" :facts="$pet['facts']" />
                <x-pet-social.pet-facts title="Good company" section="compatibility" :facts="$pet['compatibility']" />
                <x-pet-social.owner-summary :owner="$owner" />
            </x-slot:sidebar>
        </x-pet-social.main-sidebar-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
