<?php

namespace Calendar\Infra;

use Calendar\Model\Calendar;
use Calendar\Model\Event;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ICalendarWriterTest extends TestCase
{
    public function testWritesICalendar(): void
    {
        $calendar = new Calendar([$this->lunchBreak(), $this->weekend(), $this->birthday()]);
        $dir = vfsStream::setup("root");
        $sut = new ICalendarWriter($dir->url() . "/", "example.com", new Html2Text());
        $sut->write($calendar);
        $actual = $dir->getChild("calendar.ics")->getContent();
        $expected = <<<'EOS'
            BEGIN:VCALENDAR
            PRODID:-//3-magi.net//Calendar_XH//EN
            VERSION:2.0
            BEGIN:VEVENT
            UID:0@example.com
            DTSTART:20230104T120000
            DTEND:20230104T130000
            SUMMARY:Lunch break
            URL:http://example.com/lunchbreak
            DESCRIPTION:Tips for lunch breaks
            LOCATION:whereever I am
            END:VEVENT
            BEGIN:VEVENT
            UID:1@example.com
            DTSTART;VALUE=DATE:20230107
            DTEND;VALUE=DATE:20230108
            SUMMARY:Weekend
            END:VEVENT
            BEGIN:VEVENT
            UID:2@example.com
            DTSTART;VALUE=DATE:20000101
            DTEND;VALUE=DATE:20000101
            RRULE:FREQ=YEARLY
            SUMMARY:Millenium
            END:VEVENT
            END:VCALENDAR
            EOS;
        $expected = str_replace("\n", "\r\n", $expected) . "\r\n";
        $this->assertSame($expected, $actual);
    }

    private function lunchBreak(): Event
    {
        return Event::create(
            "2023-01-04",
            "2023-01-04",
            "12:00",
            "13:00",
            "Lunch break",
            "http://example.com/lunchbreak",
            "Tips for lunch breaks",
            "whereever I am",
            "",
            "",
            ""
        );
    }

    private function weekend(): Event
    {
        return Event::create(
            "2023-01-07",
            "2023-01-08",
            "",
            "",
            "Weekend",
            "",
            "",
            "",
            "",
            "",
            ""
        );
    }

    private function birthday(): Event
    {
        return Event::create(
            "2000-01-01",
            "",
            "",
            "",
            "Millenium",
            "",
            "",
            "###",
            "",
            "",
            ""
        );
    }
}
