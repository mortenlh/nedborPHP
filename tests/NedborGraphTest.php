<?php

namespace Momme\Nedbor;

use PHPUnit\Framework\TestCase;

final class NedborGraphTest extends TestCase
{
    public function testClassConstruction()
    {
        $val = [1, 2.2, 3.2, 4.5, 7.7];
        $color = 'red';
        $label = 'Line 1';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $this->assertSame('red', $nedborGraph->getColor());
        $this->assertSame('Line 1', $nedborGraph->getLabel());
        $this->assertSame('7.7', $nedborGraph->getTotal());
        $this->assertIsArray($nedborGraph->getValues());
        $this->assertNotEmpty($nedborGraph->getValues());
        $this->assertSame([1, 2, 3, 5, 8], $nedborGraph->getValues());
    }

    public function testClassConstructionWithEmptyValues()
    {
        $val = [];
        $color = '';
        $label = '';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $this->assertSame('', $nedborGraph->getColor());
        $this->assertSame('', $nedborGraph->getLabel());
        $this->assertSame('0', $nedborGraph->getTotal());
        $this->assertIsArray($nedborGraph->getValues());
        $this->assertEmpty($nedborGraph->getValues());
    }

    public function testSetValues()
    {
        $val = [1, 1, 2, 5, 7];
        $color = 'red';
        $label = 'Line 1';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $newValues = [1, 2.2, 3.5, 5.8, 10.1];
        $nedborGraph->setValues($newValues);
        $this->assertSame([1, 2, 4, 6, 10], $nedborGraph->getValues());
    }

    public function testSetValuesWithEmptyArray()
    {
        $val = [1, 2, 3];
        $color = '';
        $label = '';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $newValues = [];
        $nedborGraph->setValues($newValues);
        $this->assertEmpty($nedborGraph->getValues());
        $this->assertSame('0', $nedborGraph->getTotal());
    }

    public function testGetValueAt()
    {
        $val = [1, 1, 3, 5, 7];
        $color = 'red';
        $label = 'Line 1';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $this->assertSame(3, $nedborGraph->getValueAt(2));
    }

    public function testSetColorAndLabel()
    {
        $val = [1, 2, 3];
        $color = '';
        $label = '';
        $nedborGraph = new NedborGraph($val, $color, $label);
        $nedborGraph->setColor('blue');
        $this->assertSame('blue', $nedborGraph->getColor());
        $nedborGraph->setLabel('Newline 1');
        $this->assertSame('Newline 1', $nedborGraph->getLabel());
    }
}
