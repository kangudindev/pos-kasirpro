<?php

namespace App\Services\Printer;

use Illuminate\Support\Facades\Http;

class EscPosService
{
    protected string $connection = 'network';
    protected string $ipAddress = '192.168.1.100';
    protected int $port = 9100;
    protected string $serialPort = 'COM1';

    public function __construct(array $config = [])
    {
        $this->connection = $config['connection'] ?? 'network';
        $this->ipAddress = $config['ip_address'] ?? '192.168.1.100';
        $this->port = $config['port'] ?? 9100;
        $this->serialPort = $config['serial_port'] ?? 'COM1';
    }

    public function printReceipt($html): bool
    {
        $commands = $this->htmlToEscPos($html);
        return $this->send($commands);
    }

    protected function htmlToEscPos(string $html): string
    {
        $esc = "\x1B";
        $commands = '';

        $commands .= $esc . "@";
        $commands .= $esc . "E\x01";

        $commands .= $esc . "a\x01";
        $commands .= "Receipt\n";
        $commands .= $esc . "a\x00";

        $commands .= $esc . "d\x03";

        $commands .= str_repeat("=", 42) . "\n";

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);
        $tables = $xpath->query("//table");

        foreach ($tables as $table) {
            $rows = $xpath->query(".//tr", $table);
            foreach ($rows as $row) {
                $cells = $xpath->query(".//td|.//th", $row);
                $rowText = '';
                foreach ($cells as $cell) {
                    $rowText .= trim($cell->textContent) . " ";
                }
                $commands .= substr($rowText, 0, 42) . "\n";
            }
        }

        $commands .= str_repeat("=", 42) . "\n";

        $commands .= $esc . "d\x00";

        $commands .= "\n\n\n";

        $commands .= $esc . "m";

        return $commands;
    }

    protected function send(string $commands): bool
    {
        try {
            if ($this->connection === 'network') {
                return $this->sendNetwork($commands);
            } elseif ($this->connection === 'serial') {
                return $this->sendSerial($commands);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Printer error: ' . $e->getMessage());
            return false;
        }
    }

    protected function sendNetwork(string $commands): bool
    {
        $socket = @fsockopen($this->ipAddress, $this->port, $errno, $errstr, 5);

        if (!$socket) {
            \Log::error("Failed to connect to printer: {$errstr}");
            return false;
        }

        fwrite($socket, $commands);
        fclose($socket);

        return true;
    }

    protected function sendSerial(string $commands): bool
    {
        if (!function_exists('exec')) {
            \Log::error('exec() function disabled');
            return false;
        }

        $file = tempnam(sys_get_temp_dir(), 'printer_');
        file_put_contents($file, $commands);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("copy /b {$file} {$this->serialPort}", $output, $return);
        } else {
            exec("cat {$file} > {$this->serialPort}", $output, $return);
        }

        unlink($file);

        return $return === 0;
    }

    public static function initCommand(): string
    {
        return "\x1B\x40";
    }

    public static function boldOn(): string
    {
        return "\x1B\x45\x01";
    }

    public static function boldOff(): string
    {
        return "\x1B\x45\x00";
    }

    public static function centerAlign(): string
    {
        return "\x1B\x61\x01";
    }

    public static function leftAlign(): string
    {
        return "\x1B\x61\x00";
    }

    public static function rightAlign(): string
    {
        return "\x1B\x61\x02";
    }

    public static function cutPaper(): string
    {
        return "\x1B\x69";
    }

    public static function drawerKick(): string
    {
        return "\x1B\x70\x00\x32\xFF";
    }

    public static function bell(): string
    {
        return "\x07";
    }
}
