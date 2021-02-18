<?php

use App\Support\SimilarityCalculator;

test("The jaroWinklerSimilarity() function works correctly.", function () {
    expect(SimilarityCalculator::jaroWinklerSimilarity("martha", "marhta"))->toBe(0.9611111111111111);
    expect(SimilarityCalculator::jaroWinklerSimilarity("CRATE", "TRACE"))->toBe(0.7333333333333334);
    expect(SimilarityCalculator::jaroWinklerSimilarity("test", "dbdbdbdb"))->toBe(0.0);
    expect(SimilarityCalculator::jaroWinklerSimilarity("test", "test"))->toBe(1.0);
    expect(SimilarityCalculator::jaroWinklerSimilarity("hello world", "HeLLo W0rlD"))->toBe(0.6363636363636364);
    expect(SimilarityCalculator::jaroWinklerSimilarity("test", ""))->toBe(0.0);
    expect(SimilarityCalculator::jaroWinklerSimilarity("hello", "world"))->toBe(0.4666666666666666);
    expect(SimilarityCalculator::jaroWinklerSimilarity("hell**o", "*world"))->toBe(0.4365079365079365);
});

test("The commonPrefix() function works correctly.", function () {
    expect(SimilarityCalculator::commonPrefix("CRE", "CREA"))->toBe("CRE");
    expect(SimilarityCalculator::commonPrefix("CREATURA", "CREATURA"))->toBe("CREA");
    expect(SimilarityCalculator::commonPrefix("XXXXX", "YYYYYY"))->toBe("");
});