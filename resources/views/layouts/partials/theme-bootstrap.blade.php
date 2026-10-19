<script>
    (function () {
        var root = document.documentElement;
        var systemTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        var savedTheme = null;

        try {
            savedTheme = window.localStorage.getItem('snd-theme');
        } catch (error) {
            savedTheme = null;
        }

        var theme = savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : systemTheme;
        root.setAttribute('data-theme', theme);
        root.setAttribute('data-theme-preference', savedTheme === 'light' || savedTheme === 'dark' ? 'user' : 'system');
        root.style.colorScheme = theme;
    })();
</script>
