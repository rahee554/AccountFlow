/*
 * AccountFlow dashboard behaviour.
 *
 * Extracted from resources/views/blades/dashboard-header.blade.php.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Close offcanvas when clicking on links
    document.querySelectorAll('.offcanvas-body .nav-link[data-bs-dismiss="offcanvas"]').forEach(link => {
        link.addEventListener('click', function () {
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('accountsNavOffcanvas'));
            if (offcanvas) {
                offcanvas.hide();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    function handleResponsiveNav() {
        const nav = document.querySelector('.accounts-nav');
        const moreDropdown = document.querySelector('.more-dropdown');
        const moreDropdownMenu = document.getElementById('moreDropdownMenu');
        const collapsibleItems = document.querySelectorAll('.nav-collapsible');

        if (!nav || !moreDropdown || !moreDropdownMenu) return;

        // Reset all items to visible
        collapsibleItems.forEach(item => {
            item.style.display = 'block';
        });
        moreDropdown.classList.add('d-none');
        moreDropdownMenu.innerHTML = '';

        // Check if overflow
        if (nav.scrollWidth > nav.clientWidth) {
            const navItems = Array.from(collapsibleItems);
            let movedItems = [];

            // Move items to "More" dropdown until no overflow
            for (let i = navItems.length - 1; i >= 0; i--) {
                if (nav.scrollWidth <= nav.clientWidth) break;

                const item = navItems[i];
                const clone = item.cloneNode(true);
                clone.classList.remove('nav-item', 'dropdown');
                clone.classList.add('dropdown-item');

                const link = clone.querySelector('.nav-link');
                if (link) {
                    link.classList.remove('nav-link', 'dropdown-toggle');
                    link.classList.add('dropdown-item');
                    link.removeAttribute('data-bs-toggle');
                }

                moreDropdownMenu.appendChild(clone);
                item.style.display = 'none';
                movedItems.push(item);
            }

            if (movedItems.length > 0) {
                moreDropdown.classList.remove('d-none');
            }
        }
    }

    // Run on load and resize
    handleResponsiveNav();
    window.addEventListener('resize', handleResponsiveNav);

    // Close mobile navigation when clicking on a link
    document.querySelectorAll('.mobile-nav-item, .mobile-nav-subitem').forEach(link => {
        link.addEventListener('click', function () {
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById(
                'mobileNavOffcanvas'));
            if (offcanvas) {
                offcanvas.hide();
            }
        });
    });
});
