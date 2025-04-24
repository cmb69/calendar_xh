<?php

namespace Calendar;

use ApprovalTests\Approvals;
use Calendar\Infra\DateTimeFormatter;
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Calendar\Model\NoRecurrence;
use Calendar\Model\YearlyRecurrence;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class EventControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $config;

    /** @var DocumentStore */
    private $store;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->config = XH_includeVar("./config/config.php", "plugin_cf")["calendar"];
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $calendar = Calendar::updateIn($this->store);
        $calendar->addEvent("111", $this->lunchBreak()->toDto());
        $calendar->addEvent("222", $this->christmas()->toDto());
        $this->store->commit();
        $lang = XH_includeVar("./languages/en.php", "plugin_tx")["calendar"];
        $this->dateTimeFormatter = new DateTimeFormatter($lang);
        $this->view = new View("./views/", $lang);
    }

    private function sut(): EventController
    {
        return new EventController(
            $this->config,
            $this->store,
            $this->dateTimeFormatter,
            $this->view,
        );
    }

    public function testDoesNothingWhenNotEnabled(): void
    {
        $request = new FakeRequest(["url" => "http://example.com/?&function=calendar_event&event_id=111"]);
        $response = $this->sut()($request);
        $this->assertSame("", $response->output());
    }

    public function testReportsNotFoundForUnknownEvent(): void
    {
        $this->config["event_allow_single"] = "true";
        $request = new FakeRequest(["url" => "http://example.com/?&function=calendar_event&event_id=unknown"]);
        $response = $this->sut()($request);
        $this->assertSame(404, $response->status());
    }

    public function testRendersKnownEvent(): void
    {
        $this->config["event_allow_single"] = "true";
        $request = new FakeRequest(["url" => "http://example.com/?&function=calendar_event&event_id=111"]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testTruncatesLongDescription(): void
    {
        $this->config["event_allow_single"] = "true";
        $request = new FakeRequest(["url" => "http://example.com/?&function=calendar_event&event_id=222"]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The kitchen smells from …", $response->output());
    }

    private function lunchBreak(): Event
    {
        $start = new LocalDateTime(2023, 1, 4, 12, 0);
        $end = new LocalDateTime(2023, 1, 4, 13, 0);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Lunch break", "", "Tips for lunch breaks", "whereever I am", $recurrence);
    }

    private function christmas(): Event
    {
        $start = new LocalDateTime(2020, 12, 25, 0, 0);
        $end = new LocalDateTime(2020, 12, 26, 23, 59);
        $recurrence = new YearlyRecurrence($start, $end, new LocalDateTime(2030, 12, 25, 0, 0));
        $description = "Christmas is a wonderful time of the year."
            . " Everything is silent and white. Everybody is peaceful."
            . " The snow falls silently on the lake."
            . " The kitchen smells from fresh baked cake.";
        return new Event("222", $start, $end, "Christmas", "", $description, "Tree", $recurrence);
    }
}
