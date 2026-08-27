$().ready(function () {
    var storageKey = 'exmasstree.sidebarSecondaryCollapsed';

    function applySecondarySidebarState(collapsed) {
        $('body').toggleClass('sidebar-secondary-collapsed', collapsed);
        $('.sidebar-secondary-expand').toggleClass('d-none', !collapsed);
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    var savedState = localStorage.getItem(storageKey);
    if (savedState === '1') {
        applySecondarySidebarState(true);
    }

    function toggleSecondarySidebar() {
        var collapsed = !$('body').hasClass('sidebar-secondary-collapsed');
        applySecondarySidebarState(collapsed);
        localStorage.setItem(storageKey, collapsed ? '1' : '0');
    }

    $(document).on('click', '.sidebar-secondary-collapse, .sidebar-secondary-expand', function (e) {
        e.preventDefault();
        toggleSecondarySidebar();
    });
});
