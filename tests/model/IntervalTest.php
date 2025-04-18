<?php

namespace Calendar\Model;

use PHPUnit\Framework\TestCase;

class IntervalTest extends TestCase
{
    /** @dataProvider plusData */
    public function testPlus(Interval $interval, int $days, Interval $expected): void
    {
        $this->assertEquals($expected, $interval->plus($days));
    }

    public function plusData(): array
    {
        return [
            [new Interval(0, 0, 0), 7, new Interval(7, 0, 0)],
            [new Interval(8, 0, 0, true), 7, new Interval(1, 0, 0, true)],
            [new Interval(4, 0, 0, true), 7, new Interval(3, 0, 0)],
            [new Interval(4, 7, 0, true), 7, new Interval(2, 17, 0)],
            [new Interval(4, 7, 12, true), 7, new Interval(2, 16, 48)],
            [new Interval(4, 0, 12, true), 7, new Interval(2, 23, 48)],
        ];
    }
}
