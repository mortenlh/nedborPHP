<?php

namespace Momme\Nedbor;

class NedborGraph
{
    private array $values;
    private string $color;
    private string $label;
    private string $total;

    public function __construct(array $values, string $color, string $label)
    {
        $tmp = array();
        for ($i = 0; $i < count($values); $i++) {
            $tmp[] = (int)round($values[$i], 0);
        }
        $this->total = count($values) > 0 ? $values[count($values) - 1] : '0';
        $this->values = $tmp;
        $this->color = $color;
        $this->label = $label;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getValueAt(int $index): int
    {
        return $this->values[$index];
    }

    public function setValues(array $values): void
    {
        $tmp = array();
        for ($i = 0; $i < count($values); $i++) {
            $tmp[] = (int)round($values[$i], 0);
        }
        $this->total = count($values) > 0 ? $values[count($values) - 1] : '0';
        $this->values = $tmp;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getTotal(): string
    {
        return $this->total;
    }
}
