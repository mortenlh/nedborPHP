<?php

declare(strict_types=1);

namespace Momme\Nedbor;

use PHPUnit\Framework\TestCase;

final class NedborTest extends TestCase
{
    public function testClassConstruction()
    {
        $nedbor = new Nedbor('data/');
        $this->assertSame('data/', $nedbor->getPath());

        $this->assertIsArray($nedbor->getPeriods());
        $this->assertEmpty($nedbor->getPeriods());

        $this->assertSame(array_fill(0, 12, 0), $nedbor->getMaxmonth());

        $this->assertSame(array_fill(0, 12, PHP_INT_MAX), $nedbor->getMinmonth());

        $this->assertIsArray($nedbor->getSumnedbor());
        $this->assertEmpty($nedbor->getSumnedbor());

        $this->assertIsArray($nedbor->getNedbordata());
        $this->assertEmpty($nedbor->getNedbordata());

        $this->assertIsArray($nedbor->getTotalnedbor());
        $this->assertEmpty($nedbor->getTotalnedbor());
    }

    public function testGetMaxSumNedbor()
    {
        $nedbor = new Nedbor('data/');
        $nedbor->getCvsdata();
        $this->assertSame(1046.3, $nedbor->getMaxSumNedbor());
    }

    public function testEmptyPath()
    {
        $nedbor = new Nedbor('');
        $nedbor->getCvsdata();
        $this->assertEmpty($nedbor->getPeriods());
    }

    public function testWrongPath()
    {
        $nedbor = new Nedbor('/hest/');
        $this->assertDirectoryDoesNotExist('/hest/');
        $nedbor->getCvsdata();
        $this->assertIsArray($nedbor->getPeriods());
        $this->assertEmpty($nedbor->getPeriods());
        $this->assertSame(array_fill(0, 12, 0), $nedbor->getMaxmonth());

        $this->assertSame(array_fill(0, 12, PHP_INT_MAX), $nedbor->getMinmonth());

        $this->assertIsArray($nedbor->getNedbordata());
        $this->assertEmpty($nedbor->getNedbordata());

        $this->assertIsArray($nedbor->getSumnedbor());
        $this->assertEmpty($nedbor->getSumnedbor());

        $this->assertIsArray($nedbor->getTotalnedbor());
        $this->assertEmpty($nedbor->getTotalnedbor());
    }

    public function testHtmlOutput()
    {
        $nedbor = new Nedbor('data/');
        $nedbor->getCvsdata();
        $html = strval($nedbor);
        $this->assertStringContainsStringIgnoringCase('</table>', $html);
        $html = $nedbor->nedborToHTML();
        $this->assertStringContainsStringIgnoringCase('</table>', $html);
        $html = $nedbor->sumnedborToHTML();
        $this->assertStringContainsStringIgnoringCase('</table>', $html);
    }

    public function testSumSvg()
    {
        $nedbor = new Nedbor('data/');
        $nedbor->getCvsdata();
        $svg = $nedbor->sumnedborSVG(['red', 'white']);
        $this->assertStringContainsStringIgnoringCase('</svg>', $svg);
        $svglink = $nedbor->sumnedborSVGLink(['red', 'white']);
        $testlink = 'data/sumgraph.svg';
        $this->assertSame($testlink, $svglink);
        $this->assertFileExists('data/sumgraph.svg');
    }

    public function testMeanForPeriod()
    {
        $nedbor = new Nedbor('data/');
        $this->assertSame(0.0, $nedbor->getNedborPeriodMean('2013'));
        $nedbor->getCvsdata();
        $this->assertSame('45,4', number_format($nedbor->getNedborPeriodMean('2013'), 1, ',', ''));
        $this->assertSame(45.41, $nedbor->getNedborPeriodMean('2013'));
        $this->assertSame(-1.0, $nedbor->getNedborPeriodMean('2003'));
    }

    public function testMedianForPeriod()
    {
        $nedbor = new Nedbor('data/');
        $this->assertSame(0.0, $nedbor->getNedborPeriodMedian('2013'));
        $nedbor->getCvsdata();
        $this->assertSame('42,4', number_format($nedbor->getNedborPeriodMedian('2013'), 1, ',', ''));
        $this->assertSame(42.35, $nedbor->getNedborPeriodMedian('2013'));
        $this->assertSame(-1.0, $nedbor->getNedborPeriodMedian('2003'));
    }
}
