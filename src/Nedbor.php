<?php

namespace Momme\Nedbor;

class Nedbor
{
    private array $maxmonth;
    private array $minmonth;
    private array $sumnedbor;
    private array $totalnedbor;
    private array $nedbordata;
    private array $periods;
    private string $path;

    /**
     * Used when finding the mean pr month on all periods.
     */
    private array $monthMean;

    /**
     * Used when finding the smallest value pr month in the period sum table
     */
    private array $minMonthSum;

    public function __construct(string $path)
    {
        $this->maxmonth = array_fill(0, 12, 0);
        $this->minmonth = array_fill(0, 12, PHP_INT_MAX);
        $this->sumnedbor = [];
        $this->nedbordata = [];
        $this->periods = [];
        $this->totalnedbor = [];
        $this->path = $path;
    }

    public function getCvsdata(): void
    {
        $csvread = new \Momme\Nedbor\NedborCsv();
        $this->periods = $csvread->getCvsPeriods($this->path);
        if (count($this->periods) > 0) {
            $this->nedbordata['Måned'] = array_fill(0, 12, "");
        }
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $file = $this->path . 'n' . $key . '.csv';
            $csvread->readCsvfile($file);
            $data = $csvread->getCsvdata();
            $fullyear = true;
            for ($j = 0; $j < count($data); $j++) {
                if (strlen($this->nedbordata['Måned'][$j]) === 0) {
                    $this->nedbordata['Måned'][$j] = $data[$j]['maaned'];
                }
                if (strlen($data[$j]['mm']) > 0) {
                    $this->nedbordata[$key][] = $data[$j]['mm'];
                    if ($data[$j]['mm'] > $this->maxmonth[$j]) {
                        $this->maxmonth[$j] = $data[$j]['mm'];
                    }
                    if ($data[$j]['mm'] < $this->minmonth[$j]) {
                        $this->minmonth[$j] = $data[$j]['mm'];
                    }
                    if ($j === 0) {
                        $this->sumnedbor[$key][$j] = $data[$j]['mm'];
                        $this->minMonthSum[$j][] = $data[$j]['mm'];
                    } else {
                        $this->sumnedbor[$key][$j] = number_format($this->sumnedbor[$key][$j - 1] + $data[$j]['mm'], 1, '.', '');
                        $this->minMonthSum[$j][] = number_format($this->sumnedbor[$key][$j - 1] + $data[$j]['mm'], 1, '.', '');
                    }
                } else {
                    $fullyear = false;
                    $this->nedbordata[$key][] = '';
                    if ($j === 0) {
                        $this->sumnedbor[$key][$j] = 0;
                        $this->minMonthSum[$j][] = 0;
                    } else {
                        $this->sumnedbor[$key][$j] = $this->sumnedbor[$key][$j - 1] + 0;
                        $this->minMonthSum[$j][] = -1;
                    }
                }
            }
            if ($fullyear) {
                $this->totalnedbor[$key] = $this->sumnedbor[$key][count($data) - 1];
            }
        }
        if (count($this->periods) > 0) {
            for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
                $mean = $this->getNedborMonthMean($i + 1);
                $this->monthMean[] = $mean;
            }
        }
    }

    public function __toString(): string
    {
        $html = '
        <table class="w3-table w3-striped w3-bordered w3-table-all w3-small">';
        $html .= '
            <caption>Nedbør (mm)</caption>
            <thead>
                <tr class="w3-indigo">
                    <th class="w3-center">År</th>';
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $html .= '
                    <th class="w3-right-align">' . $this->nedbordata['Måned'][$i] . '</th>';
        }
        $html .= '
                    <th class="w3-right-align">Total</th>
                    <th class="w3-right-align">Mean</th>
                    <th class="w3-right-align">Median</th>
                </tr>
            </thead>
            <tbody>';
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $html .= '
                <tr>
                    <th class="w3-indigo w3-center">' . $key . '</th>';
            for ($j = 0; $j < count($this->nedbordata['Måned']); $j++) {
                $tdclass = 'class="w3-right-align"';
                if ($this->nedbordata[$key][$j] >= 100) {
                    $tdclass = 'class="over100 w3-right-align"';
                }
                if ($this->nedbordata[$key][$j] == $this->maxmonth[$j]) {
                    $tdclass = 'class="maxmonth w3-right-align"';
                }
                if ($this->nedbordata[$key][$j] == $this->minmonth[$j]) {
                    $tdclass = 'class="minmonth w3-right-align"';
                }
                if (strlen($this->nedbordata[$key][$j]) > 0) {
                    $html .= '
                    <td ' . $tdclass . '>' . number_format($this->nedbordata[$key][$j], 1, ',', '') . '</td>';
                } else {
                    $html .= '
                    <td ' . $tdclass . '></td>';
                }
            }
            $totalIndex = count($this->nedbordata['Måned']) - 1;
            $sumtdclass = 'class="w3-right-align"';
            if (min($this->totalnedbor) == $this->sumnedbor[$key][$totalIndex]) {
                $sumtdclass = 'class="minmonth w3-right-align"';
            }
            if (max($this->totalnedbor) == $this->sumnedbor[$key][$totalIndex]) {
                $sumtdclass = 'class="maxmonth w3-right-align"';
            }
            $html .= '
                    <td ' . $sumtdclass . '><strong>' . number_format($this->sumnedbor[$key][$totalIndex], 1, ',', '') . '</strong></td>';
            $mean = $this->getNedborPeriodMean($key);
            $html .= '
                    <td class="w3-right-align"><strong>' . number_format($mean, 1, ',', '') . '</strong></td>';
            $median = $this->getNedborPeriodMedian($key);
            $html .= '
                    <td class="w3-right-align"><strong>' . number_format($median, 1, ',', '') . '</strong></td>';
            $html .= '
                </tr>';
        }
        $html .= '
                <tr>
                    <th  class="w3-indigo w3-center">Mean</th>';
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $mean = $this->getNedborMonthMean($i + 1);
            $this->monthMean[] = $mean;
            $html .= '
                    <td class="w3-right-align"><strong>' . number_format($mean, 1, ',', '') . '</strong></td>';
        }
        $html .= '
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
        $html .= '
                <tr>
                    <th  class="w3-indigo w3-center">Median</th>';
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $median = $this->getNedborMonthMedian($i + 1);
            $html .= '
                    <td class="w3-right-align"><strong>' . number_format($median, 1, ',', '') . '</strong></td>';
        }
        $html .= '
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
        $html .= '
            </tbody>
        </table>';
        return $html;
    }

    public function nedborToHTML(): string
    {
        $html = '
        <table class="w3-table w3-bordered w3-table-all w3-small">';
        $html .= '
            <caption>Nedbør (mm)</caption>
            <thead>
                <tr class="w3-indigo">
                    <th class="w3-center">År</th>';
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $html .= '
                    <th class="w3-right-align">' . $this->nedbordata['Måned'][$i] . '</th>';
        }
        $html .= '
                </tr>
            </thead>
            <tbody>';
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $html .= '
                <tr>
                    <th class="w3-indigo w3-center">' . $key . '</th>';
            for ($j = 0; $j < count($this->nedbordata['Måned']); $j++) {
                $tdclass = 'class="w3-right-align"';
                if ($this->nedbordata[$key][$j] >= 100) {
                    $tdclass = 'class="over100 w3-right-align"';
                }
                if ($this->nedbordata[$key][$j] == $this->maxmonth[$j]) {
                    $tdclass = 'class="maxmonth w3-right-align"';
                }
                if ($this->nedbordata[$key][$j] == $this->minmonth[$j]) {
                    $tdclass = 'class="minmonth w3-right-align"';
                }
                if (strlen($this->nedbordata[$key][$j]) > 0) {
                    $html .= '
                    <td ' . $tdclass . '>' . number_format($this->nedbordata[$key][$j], 1, ',', '') . '</td>';
                } else {
                    $html .= '
                    <td ' . $tdclass . '></td>';
                }
            }
            $html .= '
                </tr>';
        }
        $html .= '
            </tbody>
        </table>';
        return $html;
    }

    public function sumnedborToHTML(): string
    {
        $html = '
        <table class="w3-table w3-bordered w3-table-all w3-small">';
        $html .= '
            <caption>Summering af nedbør (mm)</caption>
            <thead>
                <tr class="w3-indigo">
                    <th class="w3-center">År</th>';
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $html .= '
                    <th class="w3-right-align">' . $this->nedbordata['Måned'][$i] . '</th>';
        }
        $html .= '
                </tr>
            </thead>
            <tbody>';
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $html .= '
                <tr>
                    <th class="w3-indigo w3-center">' . $key . '</th>';
            for ($j = 0; $j < count($this->nedbordata['Måned']); $j++) {
                if (strlen($this->nedbordata[$key][$j]) > 0) {
                    $monthMax = $this->getNedborMonthMax($j + 1);
                    $monthMin = $this->getNedborMonthMin($j + 1);
                    if (floatval($this->sumnedbor[$key][$j]) >= $monthMax) {
                        $html .= '
                        <td class="maxmonth w3-right-align">' . number_format($this->sumnedbor[$key][$j], 1, ',', '') . '</td>';
                    } else if (floatval($this->sumnedbor[$key][$j]) <= $monthMin) {
                        $html .= '
                        <td class="minmonth w3-right-align">' . number_format($this->sumnedbor[$key][$j], 1, ',', '') . '</td>';
                    } else {
                        $html .= '
                        <td class="w3-right-align">' . number_format($this->sumnedbor[$key][$j], 1, ',', '') . '</td>';
                    }
                } else {
                    $html .= '
                    <td class="w3-right-align"></td>';
                }
            }
            $html .= '
                </tr>';
        }
        $html .= '
                <tr>
                    <th class="w3-indigo w3-center">Mean</th>';
        $monthMeanSum = 0;
        for ($i = 0; $i < count($this->nedbordata['Måned']); $i++) {
            $mean = $this->getNedborMonthMean($i + 1);
            $monthMeanSum += $mean;
            $html .= '
                    <td class="w3-right-align">' . number_format($monthMeanSum, 1, ',', '') . '</td>';
        }
        $html .= '
                </tr>';
        $html .= '
            </tbody>
        </table>';
        return $html;
    }

    public function sumnedborSVG(array $color): string
    {
        $height = 100 * ceil($this->getMaxSumNedbor() / 100);
        $nedborSVG = new NedborSVG($height, 12 * 120);
        $colorindex  = 0;
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $nedborSVG->addGraph($this->sumnedbor[$key], $color[$colorindex], $key);
            if ($colorindex == count($color) - 1) {
                $colorindex = 0;
            } else {
                $colorindex++;
            }
        }
        $nedborSVG->setMonthMeanSum($this->monthMean);
        $svg = $nedborSVG;
        $nedborSVG->saveToFile($this->path . 'sumdata' . date('Ymd') . '.svg');
        return $svg;
    }

    public function sumnedborSVGLink(array $color): string
    {
        $height = 100 * ceil($this->getMaxSumNedbor() / 100);
        $nedborSVG = new NedborSVG($height, 12 * 120);
        $colorindex  = 0;
        for ($i = 0; $i < count($this->periods); $i++) {
            $key = $this->periods[$i];
            $nedborSVG->addGraph($this->sumnedbor[$key], $color[$colorindex], $key);
            if ($colorindex == count($color) - 1) {
                $colorindex = 0;
            } else {
                $colorindex++;
            }
        }
        $nedborSVG->setMonthMeanSum($this->monthMean);
        $linkfile = $this->getPath() . 'sumgraph.svg';
        $nedborSVG->saveToFile($linkfile);
        return $linkfile;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPeriods(): array
    {
        return $this->periods;
    }

    public function getMaxmonth(): array
    {
        return $this->maxmonth;
    }

    public function getMinmonth(): array
    {
        return $this->minmonth;
    }

    public function getSumnedbor(): array
    {
        return $this->sumnedbor;
    }

    public function getNedbordata(): array
    {
        return $this->nedbordata;
    }

    public function getTotalnedbor(): array
    {
        return $this->totalnedbor;
    }

    public function getMaxSumNedbor(): float
    {
        $max = 0.0;
        foreach ($this->sumnedbor as $key => $value) {
            $periodMax = floatval(max($value));
            if ($periodMax > $max) {
                $max = $periodMax;
            }
            /*for ($i = 0; $i < count($value); $i++) {
                if ($value[$i] > $max) {
                    $max = floatval($value[$i]);
                }
            }*/
        }
        return $max;
    }

    public function getNedborPeriodMean($periodkey): float
    {
        $size = 0;
        if (count($this->nedbordata) == 0) {
            return 0.0;
        }
        if (!isset($this->nedbordata[$periodkey])) {
            return -1.0;
        }
        for ($i = 0; $i < count($this->nedbordata[$periodkey]); $i++) {
            if (strlen($this->nedbordata[$periodkey][$i]) > 0) {
                $size++;
            }
        }
        $total = floatval($this->sumnedbor[$periodkey][11]); //Total for hele perioden.
        $mean = $total / $size;
        return round($mean, 2);
    }

    public function getNedborPeriodMedian($periodkey): float
    {
        $size = 0;
        if (count($this->nedbordata) == 0) {
            return 0.0;
        }
        $newArray = [];
        if (!isset($this->nedbordata[$periodkey])) {
            return -1.0;
        }
        for ($i = 0; $i < count($this->nedbordata[$periodkey]); $i++) {
            if (strlen($this->nedbordata[$periodkey][$i]) > 0) {
                $size++;
                $newArray[] = floatval($this->nedbordata[$periodkey][$i]);
            }
        }
        if ($size < 1) {
            return 0.0;
        }
        sort($newArray);
        if ($size % 2 === 0) {
            //Even size            
            $toindex = $size / 2;
            $fromindex = $toindex - 1;
            $median = ($newArray[$fromindex] + $newArray[$toindex]) / 2;
        } else {
            $index = ($size - 1) / 2;
            $median = $newArray[$index];
        }
        return round($median, 2);
    }

    public function getNedborMonthMean($monthno): float
    {
        //Monthnr 1..12
        if ($monthno < 1 || $monthno > 12) {
            return -1.0;
        }
        $mean = 0;
        $sum = 0.0;
        $size = 0;
        foreach ($this->nedbordata as $key => $value) {
            if ($key != 'Måned' && strlen($value[$monthno - 1]) > 0) {
                $sum += floatval($value[$monthno - 1]);
                $size++;
            }
        }
        if ($size < 1) {
            return 0.0;
        }
        $mean = $sum / $size;
        return round($mean, 2);
    }

    public function getNedborMonthMedian($monthno): float
    {
        //Monthnr 1..12
        if ($monthno < 1 || $monthno > 12) {
            return -1.0;
        }
        $median = 0;
        $array = [];
        $size = 0;
        foreach ($this->nedbordata as $key => $value) {
            if ($key != 'Måned' && strlen($value[$monthno - 1]) > 0) {
                $array[] = floatval($value[$monthno - 1]);
                $size++;
            }
        }
        if ($size < 1) {
            return 0.0;
        }
        sort($array);
        if ($size % 2 == 0) {
            //Even size
            $toindex = $size / 2;
            $fromindex = $toindex - 1;
            $median = ($array[$fromindex] + $array[$toindex]) / 2;
        } else {
            $index = ($size - 1) / 2;
            $median = $array[$index];
        }
        return round($median, 2);
    }

    public function getNedborMonthMax($monthno): float
    {
        //Monthnr 1..12
        if ($monthno < 1 || $monthno > 12) {
            return -1.0;
        }
        $max = 0.0;
        $size = 0;
        foreach ($this->sumnedbor as $key => $value) {
            if ($key != 'Måned' && strlen($value[$monthno - 1]) > 0) {
                if ($max < floatval($value[$monthno - 1])) {
                    $max = floatval($value[$monthno - 1]);
                }
                $size++;
            }
        }
        if ($size < 1) {
            return 0.0;
        }
        return round($max, 2);
    }

    public function getNedborMonthMin($monthno): float
    {
        //$monthno 1..12
        $min = floatval(PHP_INT_MAX);
        if (isset($this->minMonthSum[$monthno - 1])) {
            for ($i = 0; $i < count($this->minMonthSum[$monthno - 1]); $i++) {
                $val = $this->minMonthSum[$monthno - 1][$i] == -1 ? floatval(PHP_INT_MAX) : floatval($this->minMonthSum[$monthno - 1][$i]);
                if ($val < $min) {
                    $min = $val;
                }
            }
        }
        return round($min, 2);
    }
}
