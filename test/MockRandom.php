<?php

class MockRandom extends \PHP\Random {
    protected $generator;

    public function __construct(Closure $generator) {
        $this->generator = $generator;
    }

    protected function genRandom() {
        $gen = $this->generator;
        return $gen();
    }
}