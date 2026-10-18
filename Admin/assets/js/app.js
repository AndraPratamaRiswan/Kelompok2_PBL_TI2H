
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

    // fitur 7, notifikasi toast
    const toast = document.getElementById('toastMessage');
    let toastTimer;
    // notifikasi toast
    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 3200);
    }
    // umpan balik tombol demo
    document.querySelectorAll('[data-toast]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            showToast(button.dataset.toast || 'Fitur ini belum diaktifkan.');
        });
    });
})();