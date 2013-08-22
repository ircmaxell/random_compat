<?php

use PHP\Random;

class RandomVectorTest extends PHPUnit_Framework_TestCase {

    public static function provideTestInt() {
        return array(
            array(0, 10),
            array(0, 128),
            array(0, 256),
            array(0, 1000),
            array(0, 100000),
            array(1000, 10000000),
            array(0, PHP_INT_MAX),
        );
    }

    public function testFloat() {
        if (!$this->doTestFloat(100) && !$this->doTestFloat(10000)) {
            $this->fail("Float generation failed statistical tests");
        }
    }

    public function doTestFloat($times) {
        $rand = new Random;
        $values = [];
        for ($i = 0; $i < $times; $i++) {
            $values[] = $rand->float();
        }
        $avg = array_sum($values) / count($values);
        $diff = max($avg, 0.5) - min($avg, 0.5);
        // Ensure that the deviation is less than 1 standard deviation
        return (1 / sqrt(12)) > $diff;
    }

    /**
     * @dataProvider provideTestInt
     */
    public function testInt($min, $max) {
        if (!$this->doTestInt($min, $max, 100) && !$this->doTestInt($min, $max, 10000)) {
            $this->fail("Integer generation for $min, $max failed statistical tests");
        }
    }

    public function doTestInt($min, $max, $times) {
        $rand = new Random;
        $values = [];
        for ($i = 0; $i < $times; $i++) {
            $values[] = $rand->int($min, $max);
        }
        $avg = array_sum($values) / count($values);
        $expectedAvg = ($min + $max) / 2;
        $diff = max($avg, $expectedAvg) - min($avg, $expectedAvg);
        // Ensure that the deviation is less than 1 standard deviation
        return (($max - $min) / sqrt(12)) > $diff;
    }

}