<?php

namespace Calendar\Model;

use PHPUnit\Framework\TestCase;

class CalendarTest extends TestCase
{
    public function testRead()
    {
        $lines = file(__DIR__ . '/../ics/basic.ics', FILE_IGNORE_NEW_LINES);
        $calendar = Calendar::fromICalendar($lines, $count);
        $actual = $calendar->events();
        $this->assertContainsOnlyInstancesOf(Event::class, $actual);
        $this->assertCount(3, $actual);

        $first = reset($actual);
        $this->assertSame(0, (new LocalDateTime(1997, 7, 14, 17, 0))->compare($first->start()));
        $this->assertSame(0, (new LocalDateTime(1997, 7, 15, 3, 59))->compare($first->end()));
        $this->assertSame("Bastille Day Party", $first->summary());
        $this->assertSame("", $first->linkadr());
        $this->assertSame("", $first->linktxt());
        $this->assertSame("Place de la Bastille", $first->location());

        $second = next($actual);
        $this->assertSame(0, (new LocalDateTime(1969, 3, 24, 0, 0))->compare($second->start()));
        $this->assertSame(0, (new LocalDateTime(1969, 3, 24, 23, 59))->compare($second->end()));
        $this->assertSame("cmb", $second->summary());
        $this->assertSame("https://3-magi.net/", $second->linkadr());
        $this->assertSame("", $second->linktxt());
        $this->assertSame("a\\\\b;c,d\ne\nf", $second->location());

        $third = next($actual);
        $this->assertSame(0, (new LocalDateTime(2024, 1, 23, 15, 0))->compare($third->start()));
        $this->assertSame(0, (new LocalDateTime(2024, 1, 23, 17, 0))->compare($third->end()));
        $this->assertSame("Digitale Reise Bubenheim", $third->summary());
        $this->assertSame("https://www.digibos.org", $third->linkadr());
        $this->assertSame("Digitale Reise Bubenheim", $third->linktxt());
        $this->assertSame("55270 Bubenheim Schulstraße 2 Dorfgemeinschaftshaus ", $third->location());
    }
}
