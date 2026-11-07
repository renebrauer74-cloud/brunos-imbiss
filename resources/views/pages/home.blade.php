<x-layouts.site :home="true">
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    <main id="main">
        @include('pages.home.hero')
        @include('pages.home.favorites')
        @include('pages.home.menu')
        @include('pages.home.photos')
        @include('pages.home.info')
    </main>
</x-layouts.site>
