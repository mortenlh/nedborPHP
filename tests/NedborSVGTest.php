<?php

namespace Momme\Nedbor;

use PHPUnit\Framework\TestCase;

final class NedborSVGTest extends TestCase
{
    public function testClassConstruction()
    {
        $nedborSvg = new NedborSVG(200, 300);
        $this->assertSame(200, $nedborSvg->getHeight());
        $this->assertSame(300, $nedborSvg->getWidth());
        $this->assertIsArray($nedborSvg->getGraphs());
        $this->assertEmpty($nedborSvg->getGraphs());
    }

    public function testSetHeightAndWidth()
    {
        $nedborSvg = new NedborSVG(0, 0);
        $nedborSvg->setHeight(400);
        $this->assertSame(400, $nedborSvg->getHeight());
        $nedborSvg->setWidth(500);
        $this->assertSame(500, $nedborSvg->getWidth());
    }

    public function testAddGraph()
    {
        $nedborSvg = new NedborSVG(200, 300);
        $this->assertEmpty($nedborSvg->getGraphs());
        $nedborSvg->addGraph(array_fill(0, 12, 10), 'red', 'line 1');
        $nedborSvg->addGraph(array_fill(0, 12, 20), 'blue', 'line 2');
        $nedborSvg->addGraph(array_fill(0, 12, 30), 'green', 'line 3');
        $this->assertCount(3, $nedborSvg->getGraphs());
    }
}
