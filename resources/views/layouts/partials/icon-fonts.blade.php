<style data-snd-icon-fonts>
    /* Bind the Font Awesome 7 families to Laravel-published assets under the app base path. */
    @font-face {
        font-family: "Font Awesome 7 Free";
        font-style: normal;
        font-weight: 900;
        font-display: block;
        src: url("{{ asset('fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.woff2') }}") format("woff2");
    }

    @font-face {
        font-family: "Font Awesome 7 Free";
        font-style: normal;
        font-weight: 400;
        font-display: block;
        src: url("{{ asset('fonts/vendor/@fortawesome/fontawesome-free/webfa-regular-400.woff2') }}") format("woff2");
    }

    @font-face {
        font-family: "Font Awesome 7 Brands";
        font-style: normal;
        font-weight: 400;
        font-display: block;
        src: url("{{ asset('fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.woff2') }}") format("woff2");
    }
</style>
