<?php

namespace Momme\Nedbor;

class NedborSVG
{
    private int $height;
    private int $width;
    private int $xstep;
    private array $graphs;
    private array $monthMeanSum;
    private int $xstart;
    private int $ystart;
    private string $background;
    private string $foreground;
    private string $xGridColor;
    private string $yGridColor;
    private string $yGridColor2;

    public function __construct(int $height, int $width)
    {
        $this->height = $height;
        $this->width = $width;
        $this->xstep = floor($width / 12);
        $this->xstart = 100;
        $this->ystart = 40;
        $this->foreground = '#d7ccc8';
        $this->background = '#212121';
        $this->xGridColor = '#616161';
        $this->yGridColor = '#757575';
        $this->yGridColor2 = '#424242';
        $this->graphs = [];
        $this->monthMeanSum = [];
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function getGraphs(): array
    {
        return $this->graphs;
    }

    public function setMonthMeanSum(array $mean): void
    {
        $tmpsum = 0;
        for ($i = 0; $i < count($mean); $i++) {
            $tmpsum += $mean[$i];
            $this->monthMeanSum[] = $tmpsum;
        }
    }

    public function addGraph(array $nedborMonth, string $color, string $label): void
    {
        $graph = new NedborGraph($nedborMonth, $color, $label);
        $this->graphs[] = $graph;
    }

    private function createPath(NedborGraph $graph): string
    {
        $x = $this->xstart;
        $points = $x . ',' . ($this->ystart  + $this->height);
        $g = $graph->getValues();
        for ($i = 0; $i < count($g); $i++) {
            $x += $this->xstep;
            $y = $this->ystart + ($this->height - $g[$i]);
            $points .= ' ' . $x . ',' . $y;
        }
        return '<polyline points="' . $points . '" style="fill:none;stroke-width:4;stroke:' . $graph->getColor() . '"/>';
    }

    private function createMeanPath(): string
    {
        $graph = new NedborGraph($this->monthMeanSum, 'yellow', 'mean');
        $x = $this->xstart;
        $points = $x . ',' . ($this->ystart  + $this->height);
        $g = $graph->getValues();
        for ($i = 0; $i < count($g); $i++) {
            $x += $this->xstep;
            $y = $this->ystart + ($this->height - $g[$i]);
            $points .= ' ' . $x . ',' . $y;
        }
        return '<polyline points="' . $points . '" style="fill:none;stroke-width:4;stroke:' . $graph->getColor() . '" stroke-dasharray="5,10"/>';
    }

    private function makeGrid(): string
    {
        //Y line
        $svg = '
            <text x="50" y="' . ($this->ystart - 20) . '" fill="' . $this->foreground . '" 
                style="font-family:Arial">mm</text>';
        $svg .= '
            <line x1="' . $this->xstart . '" y1="' . ($this->ystart) - 15 . '" 
                x2="' . $this->xstart . '" y2="' . ($this->ystart + $this->height + 10) . '" 
                style="stroke:' . $this->foreground . ';stroke-width:1" />';
        //Y line ticks and labels
        $y = $this->height + $this->ystart;
        for ($i = 0; $i <= $this->height; $i += 100) {
            $x = 70;
            if ($i > 0) {
                $x = 60;
                if ($i > 900) {
                    $x = 50;
                }
            }
            $svg .= '
            <text x="' . $x . '" y="' . ($y + 5) . '" fill="' . $this->foreground . '" 
                style="font-family:Arial">' . ($i) . '</text>';
            if ($i == 0) {
                $x = 65;
            }
            $svg .= '
            <text x="' . $x . '" y="' . (($y + 5) - 50) . '" fill="' . $this->foreground . '" 
                style="font-family:Arial">' . ($i + 50) . '</text>';
            $stroke = $this->yGridColor;
            $svg .= '
            <line x1="90" y1="' . $y . '" x2="' . ($this->xstart + $this->width) . '" y2="' . $y . '" 
                style="stroke:' . $stroke . ';stroke-width:1" />';
            $stroke = $this->yGridColor2;
            $svg .= '
            <line x1="90" y1="' . ($y - 50) . '" x2="' . ($this->xstart + $this->width) . '" y2="' . ($y - 50) . '" 
                style="stroke:' . $stroke . ';stroke-width:1" />';
            $y -= 100;
        }
        //X line
        $svg .= '
            <line x1="' . ($this->xstart - 10) . '" y1="' . ($this->height + $this->ystart) . '" x2="' . ($this->xstart + $this->width) . '" y2="' . ($this->height + $this->ystart) . '" style="stroke:' . $this->foreground . ';stroke-width:1" />';
        //X line ticks and labels
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
        for ($i = 0; $i < 12; $i++) {
            $x = $this->xstart + ($i * $this->xstep);
            $y1 = $this->height + $this->ystart + 5;
            $y2 = $this->ystart - 5;
            $stroke = $this->xGridColor;
            if ($i > 0) {
                $svg .= '
                <line x1="' . $x . '" y1="' . $y1 . '" x2="' . $x . '" y2="' . $y2 . '" style="stroke:' . $stroke . ';stroke-width:1" />';
            }
            $svg .= '
            <text x="' . ($x + 40) . '" y="' . ($y1 + 20) . '" fill="' . $this->foreground . '" style="font-family:Arial">' . ($months[$i]) . '</text>';
        }
        return $svg;
    }

    public function saveToFile($filename): void
    {
        $h = fopen($filename, 'w');
        fwrite($h, $this);
        fclose($h);
    }

    public function __toString(): string
    {
        $ystep = 30;
        $y = $this->ystart + 30;
        $svg = '
        <svg style="background-color:' . $this->background . '" height="' . ($this->height + $this->ystart + 50) . '" width="' . ($this->width + $this->xstart + 20) . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= $this->makeGrid();
        $height = (count($this->graphs) * 30) + 30 + 10;
        $svg .= '
            <rect width="220" height="' . $height . '" x="190" y="' . $y . '" fill="' . $this->background . '" />';
        for ($i = 0; $i < count($this->graphs); $i++) {
            $y += $ystep;
            $total = number_format($this->graphs[$i]->getTotal(), 1, ',', '');
            $svg .= '
            <text x="200" y="' . $y . '" fill="' . $this->graphs[$i]->getColor() . '" font-size="25" style="font-family:Arial">' . $this->graphs[$i]->getLabel() . ': ' . $total . ' mm</text>';
        }
        $y += $ystep;
        $mtotal = $this->monthMeanSum[count($this->monthMeanSum) - 1]; //last element is the total. because Sum.
        $meanTotal = number_format($mtotal, 1, ',', '');
        $svg .= '
            <text x="200" y="' . $y . '" fill="yellow" font-size="25" style="font-family:Arial">Mean: ' . $meanTotal . ' mm (dot)</text>';
        for ($i = 0; $i < count($this->graphs); $i++) {
            $svg .= '
            ' . $this->createPath($this->graphs[$i]);
        }
        $svg .= '
            ' . $this->createMeanPath();
        $svg .= '
        </svg>';
        return $svg . "\n";
    }
}
