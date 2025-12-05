<?php

namespace Momme\Nedbor;

use PHPUnit\Framework\TestCase;

final class NedborCsvTest extends TestCase
{
    public function testClassConstruction()
    {
        $nedborcsv = new NedborCsv();
        $this->assertIsArray($nedborcsv->getCsvdata());
    }
}
