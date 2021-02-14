<?php

namespace Tests\Support;

use App\Support\SimilarityCalculator;
use PHPUnit\Framework\TestCase;

class SimilarityCalculatorTest extends TestCase
{

    public function testJaroDistance()
    {
        $this->assertEquals(0.9611111111111111, SimilarityCalculator::jaroWinklerSimilarity("martha", "marhta"));
        $this->assertEquals(0.7333333333333334, SimilarityCalculator::jaroWinklerSimilarity("CRATE", "TRACE"));
        $this->assertEquals(0.0, SimilarityCalculator::jaroWinklerSimilarity("test", "dbdbdbdb"));
        $this->assertEquals(1.0, SimilarityCalculator::jaroWinklerSimilarity("test", "test"));
        $this->assertEquals(0.6363636363636364, SimilarityCalculator::jaroWinklerSimilarity("hello world", "HeLLo W0rlD"));
        $this->assertEquals(0.0, SimilarityCalculator::jaroWinklerSimilarity("test", ""));
        $this->assertEquals(0.4666666666666666, SimilarityCalculator::jaroWinklerSimilarity("hello", "world"));
        $this->assertEquals(0.4365079365079365, SimilarityCalculator::jaroWinklerSimilarity("hell**o", "*world"));
    }

    public function testCommonPrefix()
    {
        $this->assertEquals("CRE", SimilarityCalculator::commonPrefix("CRE", "CREA"));
        $this->assertEquals("CREA", SimilarityCalculator::commonPrefix("CREATURA", "CREATURA"));
        $this->assertEquals("", SimilarityCalculator::commonPrefix("XXXXX", "YYYYYY"));
    }
}
