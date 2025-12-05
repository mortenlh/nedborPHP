<?php

namespace Momme\Nedbor;

class NedborCsv
{
    private array $csvdata;

    public function __construct()
    {
        $this->csvdata = [];
    }

    public function readCsvfile(string $csvfile): void
    {
        $this->csvdata = [];
        $csvhandle = @fopen($csvfile, "r");
        if ($csvhandle) {
            while (($line = fgets($csvhandle)) !== false) {
                $mdata = explode(';', $line);
                $val = trim($mdata[1]);
                if (strlen($val) == 0) {
                    $val = '';
                }
                $this->csvdata[] = [
                    'maaned' => $mdata[0],
                    'mm' => $val
                ];
            }
        }
    }

    public function getCvsPeriods($path): array
    {
        $out = array();
        if ($dirhandle = @opendir($path)) {
            while (($file = readdir($dirhandle)) != false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (strpos($file, 'n') === 0 && strcmp($ext, 'csv') === 0) {
                    $out[] = substr($file, 1, strlen($file) - 5);
                }
            }
        }
        usort($out, array('Momme\Nedbor\NedborCsv','periodesSort'));
        return $out;
    }

    public function getCsvdata(): array
    {
        return $this->csvdata;
    }

    private function periodesSort($a, $b): int
    {
        return strcmp($a,$b);
    }
}
