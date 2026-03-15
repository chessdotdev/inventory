        </div><!-- /.content -->
    </div><!-- /.main-content -->
</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= $extraScripts ?? '' ?>
<script>
(function () {
    'use strict';

    var sidebar   = document.getElementById('sidebar');
    var overlay   = document.getElementById('sidebarOverlay');
    var hamburger = document.getElementById('hamburgerBtn');

    /* ── Mobile sidebar open / close ── */
    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    if (overlay) overlay.addEventListener('click', closeSidebar);
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });

    /* ── Submenu accordion ── */
    function setHeight(submenu, open) {
        submenu.style.maxHeight = open ? submenu.scrollHeight + 'px' : '0';
    }

    /* Initialise already-open submenus on page load */
    document.querySelectorAll('.has-submenu.open > .submenu').forEach(function (sub) {
        setHeight(sub, true);
    });

    /* Toggle on parent click */
    document.querySelectorAll('.nav-parent').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();

            var li      = this.closest('.has-submenu');
            var submenu = li.querySelector(':scope > .submenu');
            var isOpen  = li.classList.contains('open');

            /* Close every other open item */
            document.querySelectorAll('.has-submenu.open').forEach(function (other) {
                if (other !== li) {
                    other.classList.remove('open');
                    setHeight(other.querySelector(':scope > .submenu'), false);
                }
            });

            /* Toggle this one */
            li.classList.toggle('open', !isOpen);
            setHeight(submenu, !isOpen);
        });
    });

    /* Close sidebar on submenu link click (mobile) */
    document.querySelectorAll('.submenu a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (window.innerWidth < 992) closeSidebar();
        });
    });

}());
</script>
</body>
</html>
