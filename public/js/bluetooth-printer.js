/**
 * Bluetooth Thermal Printer Helper
 *
 * Mengirim data ESC/POS dari endpoint Laravel ke printer thermal
 * lewat Web Bluetooth API. Data dipotong jadi chunk kecil supaya
 * tidak melebihi MTU dari karakteristik GATT (umumnya ~512 byte).
 */
(function (global) {
    'use strict';

    // Service UUID umum untuk printer thermal Bluetooth (SPP-like).
    // Banyak printer (Xprinter, GOOJPRT, MTP, dsb.) memakai 0xFF00 / 0x18F0,
    // sehingga keduanya disertakan sebagai opsi.
    const PRINTER_SERVICES = [
        0x18f0,
        0xff00,
        '000018f0-0000-1000-8000-00805f9b34fb',
        '0000ff00-0000-1000-8000-00805f9b34fb',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
    ];

    const CHUNK_SIZE = 200;

    let cachedDevice = null;
    let cachedCharacteristic = null;

    function isSupported() {
        return typeof navigator !== 'undefined'
            && !!navigator.bluetooth
            && typeof navigator.bluetooth.requestDevice === 'function';
    }

    function base64ToBytes(b64) {
        const binary = atob(b64);
        const len = binary.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes;
    }

    async function findWritableCharacteristic(server) {
        for (const serviceUuid of PRINTER_SERVICES) {
            try {
                const service = await server.getPrimaryService(serviceUuid);
                const characteristics = await service.getCharacteristics();
                const writable = characteristics.find(c =>
                    c.properties.write || c.properties.writeWithoutResponse
                );
                if (writable) {
                    return writable;
                }
            } catch (e) {
                // service tidak tersedia di printer ini, coba berikutnya
            }
        }
        throw new Error('Karakteristik tulis tidak ditemukan pada printer ini.');
    }

    async function connect() {
        if (!isSupported()) {
            throw new Error('Web Bluetooth tidak didukung di browser ini. Gunakan Chrome/Edge di Android atau desktop.');
        }

        if (cachedCharacteristic && cachedDevice && cachedDevice.gatt.connected) {
            return cachedCharacteristic;
        }

        const device = await navigator.bluetooth.requestDevice({
            filters: PRINTER_SERVICES.map(uuid => ({ services: [uuid] })),
            optionalServices: PRINTER_SERVICES,
        });

        device.addEventListener('gattserverdisconnected', () => {
            cachedCharacteristic = null;
        });

        const server = await device.gatt.connect();
        const characteristic = await findWritableCharacteristic(server);

        cachedDevice = device;
        cachedCharacteristic = characteristic;
        return characteristic;
    }

    async function writeBytes(characteristic, bytes) {
        const useWriteWithoutResponse =
            characteristic.properties.writeWithoutResponse;

        for (let offset = 0; offset < bytes.length; offset += CHUNK_SIZE) {
            const chunk = bytes.slice(offset, offset + CHUNK_SIZE);
            if (useWriteWithoutResponse) {
                await characteristic.writeValueWithoutResponse(chunk);
            } else {
                await characteristic.writeValue(chunk);
            }
        }
    }

    async function fetchReceiptBytes(transactionId) {
        const response = await fetch(`/pos/receipt/${transactionId}/escpos`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) {
            throw new Error('Gagal mengambil data struk dari server.');
        }
        const json = await response.json();
        if (!json.success || !json.data) {
            throw new Error(json.message || 'Data struk tidak valid.');
        }
        return base64ToBytes(json.data);
    }

    async function printReceipt(transactionId) {
        const bytes = await fetchReceiptBytes(transactionId);
        const characteristic = await connect();
        await writeBytes(characteristic, bytes);
    }

    async function disconnect() {
        if (cachedDevice && cachedDevice.gatt.connected) {
            cachedDevice.gatt.disconnect();
        }
        cachedDevice = null;
        cachedCharacteristic = null;
    }

    global.BluetoothPrinter = {
        isSupported,
        connect,
        printReceipt,
        disconnect,
    };
})(window);
