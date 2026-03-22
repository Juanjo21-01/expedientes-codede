@php($withSidebarState = $withSidebarState ?? false)

<script>
    function applyStoredTheme() {
        const theme = localStorage.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
            'light');
        document.documentElement.setAttribute('data-theme', theme);
    }

    function syncThemeToggles() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('.theme-toggle-checkbox').forEach(function(toggle) {
            toggle.checked = isDark;
        });
    }

    function toggleTheme() {
        const html = document.documentElement;
        const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.theme = newTheme;
        syncThemeToggles();
    }

    @if ($withSidebarState)
        function initSidebarState() {
            if (window.innerWidth >= 1024) {
                const toggle = document.getElementById('sidebar-drawer');
                if (toggle && localStorage.getItem('sidebar-open') === 'true') {
                    toggle.checked = true;
                }
            }
        }
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        applyStoredTheme();
        syncThemeToggles();

        @if ($withSidebarState)
            initSidebarState();

            const toggle = document.getElementById('sidebar-drawer');
            if (toggle) {
                toggle.addEventListener('change', function() {
                    if (window.innerWidth >= 1024) {
                        localStorage.setItem('sidebar-open', this.checked);
                    }
                });
            }
        @endif
    });

    document.addEventListener('livewire:navigated', function() {
        applyStoredTheme();
        syncThemeToggles();

        @if ($withSidebarState)
            initSidebarState();
        @endif
    });
</script>
