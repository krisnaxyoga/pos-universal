<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\Printer;

class ThermalPrintService
{
    private int $paperWidth;

    public function __construct(?int $paperWidth = null)
    {
        $this->paperWidth = $paperWidth ?? (int) Setting::get('printer_paper_width', 32);
    }

    /**
     * Generate raw ESC/POS bytes for a transaction receipt.
     */
    public function generateReceiptBytes(Transaction $transaction): string
    {
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);

        try {
            $this->printHeader($printer);
            $this->printTransactionInfo($printer, $transaction);
            $this->printItems($printer, $transaction);
            $this->printTotals($printer, $transaction);
            $this->printPayment($printer, $transaction);
            $this->printFooter($printer);

            $printer->cut();
        } finally {
            $bytes = $connector->getData();
            $printer->close();
        }

        return $bytes;
    }

    private function printHeader(Printer $printer): void
    {
        $companyName = Setting::get('company_name', 'Your Company Name');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $logoPath = Setting::get('app_logo');

        $printer->setJustification(Printer::JUSTIFY_CENTER);

        if ($logoPath) {
            $absolutePath = public_path($logoPath);
            if (is_file($absolutePath)) {
                try {
                    $img = EscposImage::load($absolutePath, false);
                    $printer->bitImage($img);
                } catch (\Throwable $e) {
                    // Logo gagal di-render, lanjut tanpa logo
                }
            }
        }

        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text($companyName . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        if ($companyAddress) {
            $printer->text($companyAddress . "\n");
        }
        if ($companyPhone) {
            $printer->text('Telp: ' . $companyPhone . "\n");
        }

        $printer->text($this->divider() . "\n");
    }

    private function printTransactionInfo(Printer $printer, Transaction $transaction): void
    {
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text($this->twoColumns('No', $transaction->transaction_number) . "\n");
        $printer->text($this->twoColumns('Kasir', (string) ($transaction->user->name ?? '-')) . "\n");
        $printer->text($this->twoColumns('Tanggal', $transaction->created_at->format('d/m/Y H:i')) . "\n");
        $printer->text($this->divider() . "\n");
    }

    private function printItems(Printer $printer, Transaction $transaction): void
    {
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        foreach ($transaction->items as $item) {
            $printer->text($item->product_name . "\n");
            $line = sprintf(
                '  %s x %s',
                $item->quantity,
                number_format((float) $item->product_price, 0, ',', '.')
            );
            $printer->text($this->twoColumns(
                $line,
                number_format((float) $item->subtotal, 0, ',', '.')
            ) . "\n");
        }

        $printer->text($this->divider() . "\n");
    }

    private function printTotals(Printer $printer, Transaction $transaction): void
    {
        $printer->text($this->twoColumns(
            'Subtotal',
            number_format((float) $transaction->subtotal, 0, ',', '.')
        ) . "\n");

        if ((float) $transaction->discount > 0) {
            $printer->text($this->twoColumns(
                'Diskon',
                '-' . number_format((float) $transaction->discount, 0, ',', '.')
            ) . "\n");
        }

        if ((float) $transaction->tax > 0) {
            $printer->text($this->twoColumns(
                'Pajak',
                number_format((float) $transaction->tax, 0, ',', '.')
            ) . "\n");
        }

        $printer->setEmphasis(true);
        $printer->text($this->twoColumns(
            'TOTAL',
            'Rp ' . number_format((float) $transaction->total, 0, ',', '.')
        ) . "\n");
        $printer->setEmphasis(false);
        $printer->text($this->divider() . "\n");
    }

    private function printPayment(Printer $printer, Transaction $transaction): void
    {
        $methodLabel = match ($transaction->payment_method) {
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'ewallet' => 'E-Wallet',
            'bon' => 'BON/HUTANG',
            default => ucfirst((string) $transaction->payment_method),
        };

        $printer->text($this->twoColumns('Metode', $methodLabel) . "\n");

        if ($transaction->payment_method === 'bon') {
            $status = $transaction->isBonPaid()
                ? 'LUNAS (' . optional($transaction->bon_paid_at)->format('d/m/Y') . ')'
                : 'BELUM LUNAS';
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text('STATUS: ' . $status . "\n");
            $printer->setEmphasis(false);
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            if ($transaction->customer_info) {
                $info = $transaction->customer_info;
                if (!empty($info['name'])) {
                    $printer->text('Pelanggan: ' . $info['name'] . "\n");
                }
                if (!empty($info['phone'])) {
                    $printer->text('Telp: ' . $info['phone'] . "\n");
                }
                if (!empty($info['address'])) {
                    $printer->text('Alamat: ' . $info['address'] . "\n");
                }
            }
        } else {
            $printer->text($this->twoColumns(
                'Bayar',
                number_format((float) $transaction->paid, 0, ',', '.')
            ) . "\n");
            if ((float) $transaction->change > 0) {
                $printer->text($this->twoColumns(
                    'Kembali',
                    number_format((float) $transaction->change, 0, ',', '.')
                ) . "\n");
            }
        }

        $printer->text($this->divider() . "\n");
    }

    private function printFooter(Printer $printer): void
    {
        $footer = Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!');
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($footer . "\n");
        $printer->text("\n\n");
    }

    private function divider(): string
    {
        return str_repeat('-', $this->paperWidth);
    }

    private function twoColumns(string $left, string $right): string
    {
        $space = $this->paperWidth - mb_strlen($left) - mb_strlen($right);
        if ($space < 1) {
            return $left . ' ' . $right;
        }
        return $left . str_repeat(' ', $space) . $right;
    }
}
