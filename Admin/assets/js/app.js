
(() => {
    // fitur 4, filer bulan daftar pesanan (demo)
    const monthFilter = document.getElementById('monthFilter');
    if (monthFilter) {
        monthFilter.addEventListener('change', () => {
            showToast(`Filter ${monthFilter.value} masih berupa tampilan demo.`);
        });
    }

    // fitur 5, cetak laporan (demo)
    const printButton = document.getElementById('printReport');
    if (printButton) {
        printButton.addEventListener('click', () => window.print());
        }

    // fitur 6, pop up profil admin (demo)
    const profileButton = document.getElementById('profileButton');
    const profileMenu = document.getElementById('profileMenu');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', () => {
            const willOpen = profileMenu.hidden;
            profileMenu.hidden = !willOpen;
            profileButton.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.profile-wrap')) {
                profileMenu.hidden = true;
                profileButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();