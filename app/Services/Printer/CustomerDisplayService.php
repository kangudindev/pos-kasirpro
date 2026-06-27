<?php

namespace App\Services\Printer;

class CustomerDisplayService
{
    protected string $html = '';
    protected array $lines = [];
    protected int $maxChars = 40;

    public function __construct(int $maxChars = 40)
    {
        $this->maxChars = $maxChars;
    }

    public function addLine(string $text, string $align = 'left'): self
    {
        $text = substr($text, 0, $this->maxChars);
        $this->lines[] = [
            'text' => $text,
            'align' => $align,
        ];
        return $this;
    }

    public function addDivider(): self
    {
        $this->lines[] = [
            'text' => str_repeat('=', $this->maxChars),
            'align' => 'left',
        ];
        return $this;
    }

    public function addBlank(): self
    {
        $this->lines[] = [
            'text' => '',
            'align' => 'left',
        ];
        return $this;
    }

    public function addProduct(string $name, int $qty, float $price, float $total): self
    {
        $name = substr($name, 0, 20);
        $qtyStr = (string) $qty;
        $totalStr = number_format($total, 0, ',', '.');

        $line = $name . ' ' . str_repeat(' ', max(1, $this->maxChars - strlen($name) - strlen($qtyStr) - strlen($totalStr) - 2)) . $qtyStr . ' ' . $totalStr;

        $this->lines[] = [
            'text' => substr($line, 0, $this->maxChars),
            'align' => 'left',
        ];
        return $this;
    }

    public function addTotal(string $label, float $amount): self
    {
        $amountStr = 'Rp ' . number_format($amount, 0, ',', '.');
        $line = $label . ' ' . str_repeat(' ', max(1, $this->maxChars - strlen($label) - strlen($amountStr))) . $amountStr;

        $this->lines[] = [
            'text' => substr($line, 0, $this->maxChars),
            'align' => 'left',
        ];
        return $this;
    }

    public function render(): string
    {
        $html = '<div style="font-family: monospace; font-size: 16px; white-space: pre; background: #000; color: #0F0; padding: 20px;">';

        foreach ($this->lines as $line) {
            $text = $line['text'];
            $align = $line['align'];

            $style = 'text-align: ' . $align . ';';

            if ($align === 'center') {
                $text = str_pad($text, $this->maxChars, ' ', STR_PAD_BOTH);
            } elseif ($align === 'right') {
                $text = str_pad($text, $this->maxChars, ' ', STR_PAD_LEFT);
            }

            $html .= '<div style="' . $style . '">' . htmlspecialchars($text) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function renderJson(): array
    {
        return [
            'lines' => $this->lines,
            'max_chars' => $this->maxChars,
        ];
    }

    public static function fromTransaction($transaction, $lines, $payments): self
    {
        $display = new self();

        $display->addLine($transaction->location->business->name ?? 'POS KasirPro', 'center');
        $display->addDivider();

        foreach ($lines as $line) {
            $display->addProduct(
                $line->product->name ?? '',
                $line->quantity,
                $line->unit_price,
                $line->quantity * $line->unit_price
            );
        }

        $display->addDivider();
        $display->addTotal('TOTAL', $transaction->final_total);
        $display->addBlank();

        foreach ($payments as $payment) {
            $display->addLine($payment->method_label . ': Rp ' . number_format($payment->amount, 0, ',', '.'));
        }

        if ($payments->sum('amount') > $transaction->final_total) {
            $change = $payments->sum('amount') - $transaction->final_total;
            $display->addDivider();
            $display->addTotal('KEMBALI', $change);
        }

        $display->addBlank();
        $display->addLine('Terima Kasih', 'center');

        return $display;
    }
}
