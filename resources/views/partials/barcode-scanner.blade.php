{{-- Barcode Scanner Partial --}}
{{-- Include this partial after the barcode input field --}}
{{-- Required: barcode input must have id="barcode" --}}

<!-- Scan Barcode Modal -->
<div id="barcode-scanner-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/60" onclick="closeBarcodeScanner()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md relative">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-camera mr-2"></i> Scan Barcode
                </h3>
                <button onclick="closeBarcodeScanner()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <div id="barcode-reader" class="w-full rounded-lg overflow-hidden"></div>
                <p id="barcode-scan-status" class="mt-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Menunggu kamera...
                </p>
            </div>
            <div class="p-4 border-t dark:border-gray-700 flex justify-end">
                <button onclick="closeBarcodeScanner()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Lookup Result Banner -->
<div id="barcode-lookup-result" class="hidden mt-3 p-3 rounded-lg text-sm"></div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrCode = null;
    let scannerRunning = false;
    const BARCODE_LOOKUP_URL = "{{ route('products.lookup-barcode') }}";

    function openBarcodeScanner() {
        const modal = document.getElementById('barcode-scanner-modal');
        modal.classList.remove('hidden');
        document.getElementById('barcode-scan-status').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menunggu kamera...';
        startScanner();
    }

    function closeBarcodeScanner() {
        const modal = document.getElementById('barcode-scanner-modal');
        modal.classList.add('hidden');
        stopScanner();
    }

    function onBarcodeDetected(barcode) {
        document.getElementById('barcode').value = barcode;

        // Flash the input field
        const barcodeInput = document.getElementById('barcode');
        barcodeInput.classList.add('ring-2', 'ring-green-500', 'border-green-500');
        setTimeout(() => {
            barcodeInput.classList.remove('ring-2', 'ring-green-500', 'border-green-500');
        }, 2000);

        // Lookup product by barcode
        lookupBarcode(barcode);
    }

    function lookupBarcode(barcode) {
        const resultBanner = document.getElementById('barcode-lookup-result');
        resultBanner.className = 'mt-3 p-3 rounded-lg text-sm bg-blue-50 text-blue-700 border border-blue-200';
        resultBanner.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mencari produk...';
        resultBanner.classList.remove('hidden');

        fetch(BARCODE_LOOKUP_URL + '?barcode=' + encodeURIComponent(barcode), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                const p = data.product;
                // Auto-fill form fields
                const nameField = document.getElementById('name');
                if (nameField && !nameField.value.trim()) {
                    nameField.value = p.name;
                    nameField.classList.add('ring-2', 'ring-green-500', 'border-green-500');
                    setTimeout(() => nameField.classList.remove('ring-2', 'ring-green-500', 'border-green-500'), 2000);
                }

                const skuField = document.getElementById('sku');
                if (skuField && !skuField.value.trim()) {
                    skuField.value = p.sku;
                }

                const priceField = document.getElementById('price');
                if (priceField && !priceField.value) {
                    priceField.value = p.price;
                }

                const costField = document.getElementById('cost');
                if (costField && !costField.value) {
                    costField.value = p.cost;
                }

                const categoryField = document.getElementById('category_id');
                if (categoryField && p.category_id) {
                    categoryField.value = p.category_id;
                }

                resultBanner.className = 'mt-3 p-3 rounded-lg text-sm bg-green-50 text-green-700 border border-green-200';
                resultBanner.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Produk ditemukan: <strong>' + p.name + '</strong> — field otomatis terisi (field yang sudah ada isi tidak ditimpa)';
            } else {
                resultBanner.className = 'mt-3 p-3 rounded-lg text-sm bg-yellow-50 text-yellow-700 border border-yellow-200';
                resultBanner.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Barcode <strong>' + barcode + '</strong> belum terdaftar. Silakan isi data produk baru.';
            }

            // Auto hide after 5 seconds
            setTimeout(() => resultBanner.classList.add('hidden'), 5000);
        })
        .catch(() => {
            resultBanner.className = 'mt-3 p-3 rounded-lg text-sm bg-yellow-50 text-yellow-700 border border-yellow-200';
            resultBanner.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Barcode tercatat: <strong>' + barcode + '</strong>';
            setTimeout(() => resultBanner.classList.add('hidden'), 3000);
        });
    }

    function startScanner() {
        if (scannerRunning) return;

        html5QrCode = new Html5Qrcode("barcode-reader");

        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 280, height: 120 },
                aspectRatio: 1.5,
            },
            (decodedText) => {
                document.getElementById('barcode-scan-status').innerHTML =
                    '<i class="fas fa-check-circle text-green-500 mr-1"></i> Barcode terdeteksi: <strong>' + decodedText + '</strong>';

                // Auto close then process
                setTimeout(() => {
                    closeBarcodeScanner();
                    onBarcodeDetected(decodedText);
                }, 600);
            },
            (errorMessage) => {
                // Scan error (ignored — continuously scanning)
            }
        ).then(() => {
            scannerRunning = true;
            document.getElementById('barcode-scan-status').innerHTML =
                '<i class="fas fa-barcode mr-1"></i> Arahkan kamera ke barcode...';
        }).catch((err) => {
            document.getElementById('barcode-scan-status').innerHTML =
                '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i> Gagal akses kamera: ' + err;
        });
    }

    function stopScanner() {
        if (html5QrCode && scannerRunning) {
            html5QrCode.stop().then(() => {
                scannerRunning = false;
                html5QrCode.clear();
            }).catch(() => {
                scannerRunning = false;
            });
        }
    }

    // Physical barcode scanner detection (rapid keystrokes + Enter)
    (function() {
        let barcodeBuffer = '';
        let lastKeyTime = 0;
        const SCANNER_THRESHOLD = 50; // ms between keystrokes (scanners type very fast)
        const MIN_LENGTH = 4; // minimum barcode length

        document.addEventListener('keydown', function(e) {
            const barcodeInput = document.getElementById('barcode');
            // Only intercept if barcode input is NOT focused (scanner sends to any active element)
            if (document.activeElement === barcodeInput) return;

            const now = Date.now();

            if (e.key === 'Enter' && barcodeBuffer.length >= MIN_LENGTH) {
                e.preventDefault();
                onBarcodeDetected(barcodeBuffer);
                barcodeBuffer = '';
                return;
            }

            if (now - lastKeyTime > SCANNER_THRESHOLD * 3) {
                barcodeBuffer = '';
            }

            if (e.key.length === 1) {
                barcodeBuffer += e.key;
                lastKeyTime = now;
            }
        });
    })();
</script>
