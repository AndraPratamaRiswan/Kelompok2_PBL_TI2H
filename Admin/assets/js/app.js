
(() => {
    // fitur 4, filer bulan daftar pesanan (demo)
    const monthFilter = document.getElementById('monthFilter');
        if (monthFilter) {
            monthFilter.addEventListener('change', () => {
                showToast(`Filter ${monthFilter.value} masih berupa tampilan demo.`);
            });
        }
})();