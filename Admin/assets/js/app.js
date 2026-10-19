(() => {
    'use strict';
    // Rombak JS
    // ========================================================
    // F04.1 — DATA DAN UTILITAS UMUM PESANAN
    // Menyiapkan salinan array dummy, format rupiah/tanggal, dan helper tampilan.
    // Seluruh perubahan hanya di memori browser; refresh mengembalikan data awal.
    // ========================================================
    let orders = Array.isArray(window.sikatOrders) ? window.sikatOrders.map(order => ({ ...order })) : [];
    let toastTimer;

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[character]);
    }

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', maximumFractionDigits: 0
        }).format(Number(value) || 0).replace('IDR', 'Rp');
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
    }

    function formatDate(isoDate, options = { day: '2-digit', month: 'short', year: 'numeric' }) {
        if (!isoDate) return '-';
        const date = new Date(`${isoDate}T00:00:00`);
        if (Number.isNaN(date.getTime())) return isoDate;
        return new Intl.DateTimeFormat('id-ID', options).format(date);
    }

    function formatMonthLabel(isoMonth) {
        if (!isoMonth || !/^\d{4}-\d{2}$/.test(isoMonth)) return isoMonth || '';
        const date = new Date(`${isoMonth}-01T00:00:00`);
        return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(date);
    }

    function getSubtotal(order) {
        return (Number(order.qty) || 0) * (Number(order.harga_satuan) || 0);
    }

    function getTotal(order) {
        return getSubtotal(order) + (Number(order.ongkir) || 0);
    }

    function showToast(message) {
        const toast = $('#toastMessage');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 3200);
    }

    // ========================================================
    // F04.2 — RENDER KARTU DAN FILTER BULAN
    // Membuat opsi bulan yang diperlukan, menyaring pesanan, lalu merender kartu.
    // ========================================================
    const orderList = $('#orderList');
    const monthFilter = $('#monthFilter');
    const emptyOrders = $('#emptyOrders');

    function ensureMonthOption(isoMonth) {
        if (!monthFilter || !isoMonth) return;
        const exists = $$('option', monthFilter).some(option => option.value === isoMonth);
        if (!exists) {
            const option = document.createElement('option');
            option.value = isoMonth;
            option.textContent = formatMonthLabel(isoMonth);
            monthFilter.appendChild(option);
        }
    }

    function renderOrderList() {
        if (!orderList) return;
        const selectedMonth = monthFilter?.value || '';
        const visibleOrders = orders
            .filter(order => !selectedMonth || String(order.tanggal || '').startsWith(selectedMonth))
            .sort((a, b) => `${b.tanggal}-${b.id}`.localeCompare(`${a.tanggal}-${a.id}`));

        orderList.innerHTML = visibleOrders.map(order => {
            const paymentClass = order.pembayaran === 'Lunas' ? 'paid' : 'dp';
            const menuLine = `${order.menu} (${formatNumber(order.qty)} box)`;
            return `
                <article class="order-row" data-order-id="${escapeHtml(order.id)}">
                    <div class="order-info">
                        <strong class="order-number">Pesanan #${escapeHtml(order.id)}</strong>
                        <div class="order-meta">
                            <span>${escapeHtml(order.nomor)}</span>
                            <span>${escapeHtml(order.nama)}</span>
                        </div>
                        <p class="order-menu" title="${escapeHtml(menuLine)}">${escapeHtml(menuLine)}</p>
                        <time class="order-date" datetime="${escapeHtml(order.tanggal)}">${escapeHtml(formatDate(order.tanggal))}</time>
                    </div>
                    <span class="payment-badge payment-badge--${paymentClass}">${escapeHtml(order.pembayaran)}</span>
                    <div class="order-actions">
                        <button class="button button-order-status" type="button" data-action="status" data-order-id="${escapeHtml(order.id)}">${escapeHtml(order.status)}</button>
                        <button class="button button-neutral" type="button" data-action="detail" data-order-id="${escapeHtml(order.id)}">Detail</button>
                        <button class="button button-neutral" type="button" data-action="edit" data-order-id="${escapeHtml(order.id)}">Edit</button>
                    </div>
                </article>`;
        }).join('');

        if (emptyOrders) emptyOrders.hidden = visibleOrders.length > 0;
    }

    if (monthFilter) monthFilter.addEventListener('change', renderOrderList);
    renderOrderList();

    // F04.3 — AKSI PADA KARTU PESANAN
    // Event delegation tetap bekerja setelah daftar dirender ulang: Detail membuka
    // preview, Edit membuka form terisi, dan tombol status menampilkan notifikasi demo.
    if (orderList) {
        orderList.addEventListener('click', event => {
            const button = event.target.closest('button[data-action]');
            if (!button) return;
            const order = orders.find(item => item.id === button.dataset.orderId);
            if (!order) return;

            if (button.dataset.action === 'detail') {
                openDetailModal(order);
            } else if (button.dataset.action === 'edit') {
                openOrderModal('edit', order);
            } else if (button.dataset.action === 'status') {
                showToast(`Status pesanan ${order.id}: ${order.status}. Ubah status melalui Edit (demo).`);
            }
        });
    }

    // ========================================================
    // F04.4 — STATE DAN ELEMEN FORM TAMBAH/EDIT
    // Kedua aksi memakai form yang sama; ID tersembunyi menentukan mode Edit.
    // ========================================================
    const orderModal = $('#orderModal');
    const orderForm = $('#orderForm');
    const orderModalTitle = $('#orderModalTitle');
    const orderIdInput = $('#orderId');
    const orderPhone = $('#orderPhone');
    const orderName = $('#orderName');
    const orderMenu = $('#orderMenu');
    const quantityValue = $('#quantityValue');
    const orderStatus = $('#orderStatus');
    const paymentStatus = $('#paymentStatus');
    const orderDate = $('#orderDate');
    const orderTime = $('#orderTime');
    const shippingCost = $('#shippingCost');
    const dpAmount = $('#dpAmount');
    const dpAmountField = $('#dpAmountField');
    const orderAddress = $('#orderAddress');
    const orderNotes = $('#orderNotes');
    const unitPriceHint = $('#unitPriceHint');
    const orderTotalPreview = $('#orderTotalPreview');
    const confirmCloseModal = $('#confirmCloseModal');
    let orderModalMode = 'add';

    // F04.4.1 — HARGA SATUAN DAN KUANTITAS TERPILIH
    // Harga dibaca dari data-price pada opsi menu; jumlah dibaca dari counter.
    function selectedUnitPrice() {
        const option = orderMenu?.selectedOptions?.[0];
        return Number(option?.dataset?.price || 0);
    }

    function selectedQuantity() {
        return Math.max(0, Number.parseInt(quantityValue?.textContent || '0', 10) || 0);
    }

    function setQuantity(value) {
        const quantity = Math.max(0, Number.parseInt(value, 10) || 0);
        if (quantityValue) quantityValue.textContent = String(quantity);
        updateOrderPreview();
    }

    // F04.4.2 — PREVIEW SUBTOTAL DAN VISIBILITAS INPUT DP
    // Subtotal mengikuti harga x jumlah; field DP diwajibkan hanya untuk pembayaran DP.
    function updateOrderPreview() {
        const price = selectedUnitPrice();
        const qty = selectedQuantity();
        const subtotal = price * qty;
        if (unitPriceHint) unitPriceHint.textContent = price ? `Harga satuan: ${formatRupiah(price)}` : 'Harga satuan akan mengikuti menu pilihan.';
        if (orderTotalPreview) orderTotalPreview.textContent = `Estimasi subtotal menu: ${formatRupiah(subtotal)} · Ongkir: ${formatRupiah(shippingCost?.value || 0)}`;
        if (dpAmountField && paymentStatus) {
            const isDp = paymentStatus.value === 'DP';
            dpAmountField.hidden = !isDp;
            if (dpAmount) dpAmount.required = isDp;
            if (!isDp && dpAmount) dpAmount.value = '0';
        }
    }

    // F04.4.3 — RESET FORM KE NILAI AWAL
    // Mengosongkan input dan mengembalikan default sebelum mode Tambah dibuka.
    function resetOrderForm() {
        orderForm?.reset();
        if (orderIdInput) orderIdInput.value = '';
        if (quantityValue) quantityValue.textContent = '0';
        if (orderStatus) orderStatus.value = 'Dikonfirmasi';
        if (paymentStatus) paymentStatus.value = 'DP';
        if (shippingCost) shippingCost.value = '0';
        if (dpAmount) dpAmount.value = '0';
        if (orderDate) orderDate.value = new Date().toISOString().slice(0, 10);
        if (orderTime) orderTime.value = '10:00';
        updateOrderPreview();
    }

    // F04.4.4 — MEMBUKA MODAL TAMBAH ATAU EDIT
    // Mode Edit mengisi kembali form dengan data pesanan yang dipilih.
    function openOrderModal(mode, order = null) {
        orderModalMode = mode;
        resetOrderForm();

        if (mode === 'edit' && order) {
            orderModalTitle.textContent = 'Edit Pesanan';
            $('#saveOrderButton').textContent = 'Simpan Perubahan (Demo)';
            orderIdInput.value = order.id;
            orderPhone.value = order.nomor || '';
            orderName.value = order.nama || '';
            orderMenu.value = order.menu || '';
            orderStatus.value = order.status || 'Dikonfirmasi';
            paymentStatus.value = order.pembayaran || 'DP';
            orderDate.value = order.tanggal || new Date().toISOString().slice(0, 10);
            orderTime.value = order.jam || '10:00';
            shippingCost.value = String(Number(order.ongkir) || 0);
            dpAmount.value = String(Number(order.dp) || 0);
            orderAddress.value = order.alamat || '';
            orderNotes.value = order.catatan || '';
            setQuantity(order.qty || 0);
        } else {
            orderModalTitle.textContent = 'Tambah Pesanan';
            $('#saveOrderButton').textContent = 'Simpan (Demo)';
            orderDate.value = new Date().toISOString().slice(0, 10);
            orderTime.value = '10:00';
        }

        updateOrderPreview();
        orderModal.hidden = false;
        document.body.classList.add('modal-open');
        window.setTimeout(() => orderPhone?.focus(), 30);
    }

    // F04.4.5 — TOMBOL TAMBAH DAN COUNTER MENU
    // Tombol +/- mengubah kuantitas; memilih menu otomatis memilih jumlah awal 1.
    $('#addOrderButton')?.addEventListener('click', () => openOrderModal('add'));
    $('#quantityMinus')?.addEventListener('click', () => setQuantity(selectedQuantity() - 1));
    $('#quantityPlus')?.addEventListener('click', () => setQuantity(Math.max(1, selectedQuantity() + 1)));
    orderMenu?.addEventListener('change', () => {
        if (orderMenu.value && selectedQuantity() === 0) setQuantity(1);
        updateOrderPreview();
    });
    paymentStatus?.addEventListener('change', updateOrderPreview);
    shippingCost?.addEventListener('input', updateOrderPreview);

    // F04.5 — KONFIRMASI SAAT FORM DITUTUP
    // X, Batal, atau Escape meminta konfirmasi agar isian tidak hilang tanpa sengaja.
    function requestCloseOrderModal() {
        if (orderModal && !orderModal.hidden) confirmCloseModal.hidden = false;
    }

    $$('[data-close-order]').forEach(button => button.addEventListener('click', requestCloseOrderModal));
    $('#returnToFormButton')?.addEventListener('click', () => { confirmCloseModal.hidden = true; });
    $('#discardFormButton')?.addEventListener('click', () => {
        confirmCloseModal.hidden = true;
        orderModal.hidden = true;
        resetOrderForm();
        if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
        showToast('Pengisian pesanan dibatalkan. Perubahan belum disimpan.');
    });

    // F04.6 — VALIDASI DAN SIMPAN DEMO
    // Validasi field, lalu menambah/memperbarui array JavaScript (bukan PostgreSQL).
    orderForm?.addEventListener('submit', event => {
        event.preventDefault();
        if (!orderForm.reportValidity()) return;
        const menuOption = orderMenu.selectedOptions[0];
        const id = orderIdInput.value || nextOrderId();
        const data = {
            id,
            nomor: orderPhone.value.trim(),
            nama: orderName.value.trim(),
            alamat: orderAddress.value.trim(),
            menu: orderMenu.value,
            qty: selectedQuantity(),
            harga_satuan: Number(menuOption?.dataset?.price || 0),
            tanggal: orderDate.value,
            jam: orderTime.value,
            status: orderStatus.value,
            pembayaran: paymentStatus.value,
            ongkir: Math.max(0, Number(shippingCost.value) || 0),
            dp: paymentStatus.value === 'DP' ? Math.max(0, Number(dpAmount.value) || 0) : 0,
            catatan: orderNotes.value.trim()
        };

        if (!data.menu || data.qty < 1) {
            showToast('Pilih menu dan jumlah pesanan minimal 1.');
            return;
        }
        if (data.pembayaran === 'DP' && data.dp <= 0) {
            showToast('Isi jumlah DP jika status pembayaran adalah DP.');
            dpAmount?.focus();
            return;
        }

        const existingIndex = orders.findIndex(order => order.id === id);
        if (existingIndex >= 0) {
            orders[existingIndex] = { ...orders[existingIndex], ...data };
            showToast(`Pesanan ${id} diperbarui sementara (demo).`);
        } else {
            orders.push(data);
            showToast(`Pesanan ${id} ditambahkan sementara (demo).`);
        }

        ensureMonthOption(data.tanggal.slice(0, 7));
        if (monthFilter) monthFilter.value = data.tanggal.slice(0, 7);
        renderOrderList();
        renderReport();
        orderModal.hidden = true;
        confirmCloseModal.hidden = true;
        resetOrderForm();
        document.body.classList.remove('modal-open');
    });

    // F04.7 — MEMBUAT ID PESANAN DEMO BERIKUTNYA
    // Cari nomor terbesar lalu tambahkan satu untuk membuat ID SKKT baru.
    function nextOrderId() {
        const highest = orders.reduce((max, order) => {
            const match = String(order.id || '').match(/(\d+)$/);
            return Math.max(max, match ? Number(match[1]) : 0);
        }, 0);
        return `SKKT-${String(highest + 1).padStart(3, '0')}`;
    }

    // ========================================================
    // F04.8 — DETAIL PESANAN (READ-ONLY)
    // Mengisi komponen detail dari data terpilih tanpa membuka form edit.
    // ========================================================
    const detailModal = $('#detailModal');

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value ?? '-';
    }

    function openDetailModal(order) {
        const subtotal = getSubtotal(order);
        const total = getTotal(order);
        setText('detailModalTitle', `Detail Pesanan #${order.id}`);
        setText('detailCreatedAt', `Data contoh · tanggal kirim ${formatDate(order.tanggal)}`);
        setText('detailOrderStatus', `Status Pesanan: ${order.status}`);
        setText('detailPaymentStatus', `Status Pembayaran: ${order.pembayaran}`);
        setText('detailName', order.nama);
        setText('detailPhone', order.nomor);
        setText('detailAddress', order.alamat || '-');
        setText('detailDate', formatDate(order.tanggal, { day: '2-digit', month: '2-digit', year: 'numeric' }));
        setText('detailTime', order.jam ? `${order.jam} WIB` : '-');
        setText('detailMenu', order.menu);
        setText('detailQty', `${formatNumber(order.qty)} unit`);
        setText('detailUnitPrice', formatRupiah(order.harga_satuan));
        setText('detailSubtotal', formatRupiah(subtotal));
        setText('detailShipping', formatRupiah(order.ongkir));
        setText('detailDp', order.pembayaran === 'DP' ? formatRupiah(order.dp) : 'Rp0');
        setText('detailTotal', formatRupiah(total));
        setText('detailNotes', order.catatan || 'Tidak ada catatan.');
        detailModal.hidden = false;
        document.body.classList.add('modal-open');
    }

    $$('[data-close-detail]').forEach(button => button.addEventListener('click', () => {
        detailModal.hidden = true;
        if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
    }));

    // ========================================================
    // F05.1 — FILTER DATA LAPORAN DAN RINGKASAN
    // Hanya pesanan Selesai pada bulan aktif yang dihitung.
    // Pendapatan menu = qty x harga satuan; ongkir tidak ikut dijumlahkan.
    // ========================================================
    const reportModal = $('#reportModal');
    const reportMonthFilter = $('#reportMonthFilter');
    const reportTableBody = $('#reportTableBody');
    const reportTableFoot = $('#reportTableFoot');
    const reportEmpty = $('#reportEmpty');

    // F05.1.1 — FILTER BERDASARKAN STATUS DAN BULAN
    function getReportOrders() {
        const month = reportMonthFilter?.value || '';
        return orders
            .filter(order => order.status === 'Selesai' && String(order.tanggal || '').startsWith(month))
            .sort((a, b) => String(a.tanggal).localeCompare(String(b.tanggal)));
    }

    // F05.1.2 — HITUNG TOTAL QTY, JENIS MENU, DAN PENDAPATAN
    function calculateReportSummary(reportOrders) {
        return {
            qty: reportOrders.reduce((sum, order) => sum + (Number(order.qty) || 0), 0),
            menuKinds: new Set(reportOrders.map(order => order.menu)).size,
            income: reportOrders.reduce((sum, order) => sum + getSubtotal(order), 0)
        };
    }

    // F05.2 — RENDER KARTU RINGKASAN DAN TABEL LAPORAN
    // Nilai kartu, baris tabel, dan footer total dihitung dari data terfilter.
    function renderReport() {
        if (!reportTableBody) return;
        const reportOrders = getReportOrders();
        const summary = calculateReportSummary(reportOrders);

        setText('reportTotalQty', formatNumber(summary.qty));
        setText('reportMenuKinds', formatNumber(summary.menuKinds));
        setText('reportTotalIncome', formatRupiah(summary.income));

        reportTableBody.innerHTML = reportOrders.map(order => `
            <tr>
                <td>${escapeHtml(formatDate(order.tanggal, { day: '2-digit', month: '2-digit', year: 'numeric' }))}</td>
                <td>${escapeHtml(order.menu)}</td>
                <td>${formatNumber(order.qty)}</td>
                <td>${escapeHtml(formatRupiah(order.harga_satuan))}</td>
                <td>${escapeHtml(formatRupiah(getSubtotal(order)))}</td>
            </tr>`).join('');

        reportTableFoot.innerHTML = reportOrders.length ? `
            <tr>
                <td colspan="2">Total Keseluruhan</td>
                <td>${formatNumber(summary.qty)}</td>
                <td></td>
                <td>${escapeHtml(formatRupiah(summary.income))}</td>
            </tr>` : '';
        reportEmpty.hidden = reportOrders.length > 0;
    }

    // F05.2.1 — BUKA/TUTUP OVERLAY DAN GANTI PERIODE
    $('#printReport')?.addEventListener('click', () => {
        renderReport();
        reportModal.hidden = false;
        document.body.classList.add('modal-open');
    });
    reportMonthFilter?.addEventListener('change', renderReport);
    $$('[data-close-report]').forEach(button => button.addEventListener('click', () => {
        reportModal.hidden = true;
        if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
    }));

    function isAnyModalOpen() {
        return [orderModal, confirmCloseModal, detailModal, reportModal].some(modal => modal && !modal.hidden);
    }

    // ========================================================
    // F05.3 — GENERATOR PDF TANPA LIBRARY EKSTERNAL
    // Membentuk file PDF berisi identitas toko, periode, ringkasan, rincian, dan total.
    // ========================================================
    // F05.3.1 — AMANKAN TEKS UNTUK FORMAT PDF
    // Mengganti karakter non-ASCII dan melakukan escape tanda kurung/backslash.
    function pdfSafeText(value) {
        return String(value ?? '')
            .normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[–—−]/g, '-')
            .replace(/[^\x20-\x7E]/g, '-')
            .replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
    }

    // F05.3.2 — SUSUN HALAMAN, HEADER, KARTU, DAN TABEL PDF
    // Jika baris banyak, data dibagi ke beberapa halaman.
    function buildReportPdf() {
        const reportOrders = getReportOrders();
        const summary = calculateReportSummary(reportOrders);
        const periodLabel = formatMonthLabel(reportMonthFilter?.value || '');
        const pageWidth = 842;
        const pageHeight = 595;
        const margin = 38;
        const contentWidth = pageWidth - margin * 2;
        const rowsPerPage = 12;
        const rows = reportOrders.length ? reportOrders : [];
        const chunks = [];
        if (!rows.length) chunks.push([]);
        else for (let i = 0; i < rows.length; i += rowsPerPage) chunks.push(rows.slice(i, i + rowsPerPage));

        const pages = chunks.map((chunk, pageIndex) => {
            const commands = [];
            const fillRect = (x, top, width, height, color) => {
                const y = pageHeight - top - height;
                commands.push(`${color[0]} ${color[1]} ${color[2]} rg ${x} ${y} ${width} ${height} re f`);
            };
            const line = (x1, top1, x2, top2, color = [0.83, 0.87, 0.91], width = 0.7) => {
                commands.push(`${color[0]} ${color[1]} ${color[2]} RG ${width} w ${x1} ${pageHeight - top1} m ${x2} ${pageHeight - top2} l S`);
            };
            const drawText = (x, baselineTop, value, size = 10, color = [0.17, 0.24, 0.32], bold = false) => {
                commands.push(`BT /${bold ? 'F2' : 'F1'} ${size} Tf ${color[0]} ${color[1]} ${color[2]} rg 1 0 0 1 ${x} ${pageHeight - baselineTop} Tm (${pdfSafeText(value)}) Tj ET`);
            };

            // Header laporan dan informasi toko.
            fillRect(0, 0, pageWidth, 7, [0.12, 0.20, 0.29]);
            drawText(margin, 38, 'SIKAT', 23, [0.12, 0.20, 0.29], true);
            drawText(margin, 57, 'CETAK LAPORAN KEUANGAN', 13, [0.25, 0.37, 0.49], true);
            drawText(margin, 74, 'Catering Nasi Kotak Malang by Tya | Jl. Raya Malang No. 123 | WA: 0812-3768-xxxx', 9, [0.36, 0.43, 0.51]);
            drawText(pageWidth - margin - 125, 38, `Periode: ${periodLabel}`, 9, [0.25, 0.37, 0.49], true);
            if (pageIndex > 0) drawText(pageWidth - margin - 125, 54, `Halaman ${pageIndex + 1}`, 9, [0.42, 0.49, 0.56]);
            line(margin, 88, pageWidth - margin, 88, [0.78, 0.83, 0.88], 1);

            // Tiga kartu ringkasan.
            const summaryTop = 101;
            const cardGap = 10;
            const cardWidth = (contentWidth - cardGap * 2) / 3;
            const cards = [
                { label: 'TOTAL QUANTITY', value: `${formatNumber(summary.qty)} unit`, fill: [0.93, 0.96, 0.98], valueColor: [0.16, 0.25, 0.35] },
                { label: 'JENIS MENU', value: `${formatNumber(summary.menuKinds)} menu berbeda`, fill: [0.93, 0.96, 0.98], valueColor: [0.16, 0.25, 0.35] },
                { label: 'TOTAL PENDAPATAN', value: formatRupiah(summary.income), fill: [0.12, 0.20, 0.29], valueColor: [1, 1, 1] },
            ];
            cards.forEach((card, index) => {
                const x = margin + index * (cardWidth + cardGap);
                fillRect(x, summaryTop, cardWidth, 48, card.fill);
                drawText(x + 12, summaryTop + 17, card.label, 8, index === 2 ? [0.83, 0.88, 0.93] : [0.43, 0.50, 0.57], true);
                drawText(x + 12, summaryTop + 36, card.value, 13, card.valueColor, true);
            });

            // Tabel transaksi yang selesai.
            const tableTop = 165;
            const headerHeight = 25;
            const rowHeight = 21;
            const colX = [margin + 10, margin + 110, margin + 405, margin + 470, margin + 625];
            fillRect(margin, tableTop, contentWidth, headerHeight, [0.25, 0.37, 0.49]);
            drawText(colX[0], tableTop + 16, 'Tanggal', 9, [1, 1, 1], true);
            drawText(colX[1], tableTop + 16, 'Menu', 9, [1, 1, 1], true);
            drawText(colX[2], tableTop + 16, 'Qty', 9, [1, 1, 1], true);
            drawText(colX[3], tableTop + 16, 'Harga Satuan', 9, [1, 1, 1], true);
            drawText(colX[4], tableTop + 16, 'Total', 9, [1, 1, 1], true);

            chunk.forEach((order, rowIndex) => {
                const top = tableTop + headerHeight + rowIndex * rowHeight;
                if (rowIndex % 2 === 0) fillRect(margin, top, contentWidth, rowHeight, [0.96, 0.975, 0.985]);
                else fillRect(margin, top, contentWidth, rowHeight, [0.91, 0.94, 0.96]);
                const dateText = formatDate(order.tanggal, { day: '2-digit', month: '2-digit', year: 'numeric' });
                const menuText = String(order.menu || '').length > 43 ? `${String(order.menu).slice(0, 40)}...` : order.menu;
                drawText(colX[0], top + 14, dateText, 8.5, [0.22, 0.30, 0.39]);
                drawText(colX[1], top + 14, menuText, 8.5, [0.22, 0.30, 0.39]);
                drawText(colX[2], top + 14, formatNumber(order.qty), 8.5, [0.22, 0.30, 0.39]);
                drawText(colX[3], top + 14, formatRupiah(order.harga_satuan), 8.5, [0.22, 0.30, 0.39]);
                drawText(colX[4], top + 14, formatRupiah(getSubtotal(order)), 8.5, [0.16, 0.25, 0.35], true);
                line(margin, top + rowHeight, pageWidth - margin, top + rowHeight, [0.86, 0.89, 0.92], 0.4);
            });

            if (pageIndex === chunks.length - 1) {
                const totalTop = tableTop + headerHeight + chunk.length * rowHeight + 4;
                fillRect(margin, totalTop, contentWidth, 25, [0.88, 0.92, 0.95]);
                drawText(colX[0], totalTop + 16, 'TOTAL KESELURUHAN', 9, [0.15, 0.24, 0.34], true);
                drawText(colX[2], totalTop + 16, formatNumber(summary.qty), 9, [0.15, 0.24, 0.34], true);
                drawText(colX[4], totalTop + 16, formatRupiah(summary.income), 9, [0.15, 0.24, 0.34], true);
            }
            drawText(margin, pageHeight - 25, 'Laporan dibuat dari data dummy untuk demonstrasi SIKAT.', 8, [0.48, 0.55, 0.62]);
            drawText(pageWidth - margin - 85, pageHeight - 25, `Halaman ${pageIndex + 1} dari ${chunks.length}`, 8, [0.48, 0.55, 0.62]);
            return commands.join('\n');
        });

        // F05.3.3 — RAKIT STRUKTUR FILE PDF
        // Membuat objek halaman, font, tabel referensi xref, dan trailer PDF.
        const objects = [];
        objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        const pageRefs = [];
        let nextObject = 5;
        pages.forEach(content => {
            const pageObjectId = nextObject++;
            const contentObjectId = nextObject++;
            pageRefs.push(`${pageObjectId} 0 R`);
            objects[pageObjectId] = `<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${pageWidth} ${pageHeight}] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ${contentObjectId} 0 R >>`;
            objects[contentObjectId] = `<< /Length ${content.length} >>\nstream\n${content}\nendstream`;
        });
        objects[2] = `<< /Type /Pages /Kids [${pageRefs.join(' ')}] /Count ${pageRefs.length} >>`;

        let pdf = '%PDF-1.4\n%SIKAT PDF\n';
        const offsets = [0];
        for (let i = 1; i < objects.length; i++) {
            offsets[i] = pdf.length;
            pdf += `${i} 0 obj\n${objects[i]}\nendobj\n`;
        }
        const xrefOffset = pdf.length;
        pdf += `xref\n0 ${objects.length}\n0000000000 65535 f \n`;
        for (let i = 1; i < objects.length; i++) {
            pdf += `${String(offsets[i]).padStart(10, '0')} 00000 n \n`;
        }
        pdf += `trailer\n<< /Size ${objects.length} /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`;
        return pdf;
    }

    // F05.4 — UNDUH PDF BERDASARKAN PERIODE AKTIF
    // Membuat Blob PDF, memicu unduhan, lalu membersihkan URL sementara.
    $('#downloadReportPdf')?.addEventListener('click', () => {
        const period = reportMonthFilter?.value || 'semua-periode';
        const pdfContent = buildReportPdf();
        const blob = new Blob([pdfContent], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `Laporan_Keuangan_SIKAT_${period}.pdf`;
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 1500);
        showToast('PDF laporan berhasil dibuat dari data dummy.');
    });

    // ========================================================
    // F06.1 — POPUP PROFIL ADMIN
    // Tombol membuka popup; klik di luar popup menutupnya kembali.
    // ========================================================
    const profileButton = $('#profileButton');
    const profileMenu = $('#profileMenu');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', () => {
            const willOpen = profileMenu.hidden;
            profileMenu.hidden = !willOpen;
            profileButton.setAttribute('aria-expanded', String(willOpen));
        });
        document.addEventListener('click', event => {
            if (!event.target.closest('.profile-wrap')) {
                profileMenu.hidden = true;
                profileButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ========================================================
    // F07.1 — NOTIFIKASI TOAST UNTUK AKSI DEMO
    // Menampilkan umpan balik singkat tanpa mengalihkan halaman.
    // ========================================================
    $$('[data-toast]').forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault();
            showToast(button.dataset.toast || 'Fitur ini belum diaktifkan.');
        });
    });

    // F04.2.1 — Tombol Lihat Semua sengaja belum diberi handler sesuai arahan.

    // F04.9 — TUTUP MODAL DENGAN TOMBOL ESCAPE
    // Form Tambah/Edit tetap meminta konfirmasi; modal baca dan laporan bisa ditutup.
    // Overlay yang sudah tertutup juga membuka kembali scroll halaman bila perlu.
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        if (confirmCloseModal && !confirmCloseModal.hidden) {
            confirmCloseModal.hidden = true;
            return;
        }
        if (orderModal && !orderModal.hidden) {
            requestCloseOrderModal();
            return;
        }
        if (detailModal && !detailModal.hidden) detailModal.hidden = true;
        if (reportModal && !reportModal.hidden) reportModal.hidden = true;
        if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
    });

    // F04.10 / F05.5 — TUTUP DENGAN KLIK AREA GELAP
    // Form pesanan meminta konfirmasi; Detail dan Laporan ditutup langsung.
    orderModal?.addEventListener('click', event => {
        if (event.target === orderModal) requestCloseOrderModal();
    });
    detailModal?.addEventListener('click', event => {
        if (event.target === detailModal) {
            detailModal.hidden = true;
            if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
        }
    });
    reportModal?.addEventListener('click', event => {
        if (event.target === reportModal) {
            reportModal.hidden = true;
            if (!isAnyModalOpen()) document.body.classList.remove('modal-open');
        }
    });

    // F04.11 / F05.6 — INISIALISASI SAAT HALAMAN DIBUKA
    // Menghitung preview form dan membangun laporan awal dari data dummy.
    updateOrderPreview();
    renderReport();
})();
