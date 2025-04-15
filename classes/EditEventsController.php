<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2023 Christoph M. Becker
 *
 * This file is part of Calendar_XH.
 *
 * Calendar_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Calendar_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Calendar_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Calendar;

use Calendar\Model\Event;
use Plib\CsrfProtector;
use Plib\Request;
use Plib\Response;
use Plib\View;

class EditEventsController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $config;

    /** @var EventDataService */
    private $eventDataService;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var Editor */
    private $editor;

    /** @var View */
    private $view;

    /** @param array<string,string> $config */
    public function __construct(
        string $pluginFolder,
        array $config,
        EventDataService $eventDataService,
        CsrfProtector $csrfProtector,
        Editor $editor,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->config = $config;
        $this->eventDataService = $eventDataService;
        $this->csrfProtector = $csrfProtector;
        $this->editor = $editor;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        $do = $request->post("calendar_do") !== null;
        switch ($request->get("action") ?? "") {
            case "create":
                return !$do ? $this->createAction($request) : $this->doCreateAction($request);
            case "update":
                return !$do ? $this->updateAction($request) : $this->doUpdateAction($request);
            case "delete":
                return !$do ? $this->deleteAction($request) : $this->doDeleteAction($request);
            default:
                return $this->defaultAction($request);
        }
    }

    private function defaultAction(Request $request): Response
    {
        $calendar = $this->eventDataService->readEvents();
        $events = array_map(function (Event $event): array {
            return [
                "start_date" => $event->getIsoStartDate() . " " . $event->getIsoStartTime(),
                "summary" => $event->summary(),
            ];
        }, $calendar->events());
        $js = $this->pluginFolder . "js/overview.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "js/overview.js";
        }
        $output = $this->view->render('event-table', [
            'selected' => $request->selected() ? $request->selected() : 'calendar',
            'events' => $events,
            'hash' => sha1(serialize($events)),
            'jsUrl' => $request->url()->path($js)->with("v", CALENDAR_VERSION)->relative(),
        ]);
        return $this->respondWith($request, $output);
    }

    private function createAction(Request $request): Response
    {
        $event = $this->createDefaultEvent($request);
        return $this->respondWith($request, $this->renderEditForm($request, $event, null, "create"));
    }

    private function updateAction(Request $request): Response
    {
        $calendar = $this->eventDataService->readEvents();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->respondWith($request, $this->renderEditForm($request, $event, $id, "update"));
    }

    private function deleteAction(Request $request): Response
    {
        $calendar = $this->eventDataService->readEvents();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->respondWith($request, $this->renderEditForm($request, $event, $id, "delete"));
    }

    private function renderEditForm(Request $request, Event $event, ?string $id, string $action): string
    {
        $this->editor->init(["calendar_textarea_description"], $this->config["edit_editor_init"]);
        $url = $request->url()->with("admin", "plugin_main")->with("action", $action);
        if ($id !== null) {
            $url = $url->with("event_id", $id);
        }
        return $this->view->render('edit-form', [
            'action' => $url->relative(),
            'event' => [
                "start_date" => $event->getIsoStartDate() . "T" . $event->getIsoStartTime(),
                "end_date" => $event->getIsoEndDate() . "T" . $event->getIsoEndTime(),
                "summary" => $event->summary(),
                "linkadr" => $event->linkadr(),
                "linktxt" => $event->linktxt(),
                "location" => $event->location(),
            ],
            'button_label' => $action === "delete" ? "label_delete" : "label_save",
            'csrf_token' => $this->csrfProtector->token(),
        ]);
    }

    private function doCreateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->eventDataService->readEvents();
        return $this->upsert($request, $calendar->events(), null);
    }

    private function doUpdateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $id = $request->get("event_id");
        assert($id !== null); // TODO invalid assertion
        $calendar = $this->eventDataService->readEvents();
        return $this->upsert($request, $calendar->events(), array_key_exists($id, $calendar->events()) ? $id : null);
    }

    /** @param array<string,Event> $events */
    private function upsert(Request $request, array $events, ?string $id): Response
    {
        $post = $this->eventPost($request);
        if (!$this->isValidDate($post["datestart"])) {
            $post["datestart"] = "";
        }
        if (!$this->isValidDate($post["dateend"])) {
            $post["dateend"] = "";
        }
        $maybeEvent = Event::create(...array_values($post));
        if ($maybeEvent === null) {
            return $this->redirectToOverviewResponse($request);
        }
        if ($id !== null) {
            $events[$id] = $maybeEvent;
        } else {
            $events[] = $maybeEvent;
        }
        // sorting new event inputs, idea of manu, forum-message
        uasort($events, function (Event $a, Event $b): int {
            return $a->start()->compare($b->start());
        });
        if ($this->eventDataService->writeEvents($events)) {
            return $this->redirectToOverviewResponse($request);
        } else {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $maybeEvent, $id, $id !== null ? "create" : "update"));
        }
    }

    /** @return array{datestart:string,dateend:string,starttime:string,endtime:string,event:string,linkadr:string,linktxt:string,location:string} */
    private function eventPost(Request $request): array
    {
        $datetime = explode("T", $request->post("datestart") ?? "", 2);
        $datestart = $datetime[0];
        $starttime = $datetime[1] ?? "";
        $datetime = explode("T", $request->post("dateend") ?? "", 2);
        $dateend = $datetime[0];
        $endtime = $datetime[1] ?? "";
        return [
            "datestart" => $datestart,
            "dateend" => $dateend,
            "starttime" => $starttime,
            "endtime" => $endtime,
            "event" => $request->post("event") ?? "",
            "linkadr" => $request->post("linkadr") ?? "",
            "linktxt" => $request->post("linktxt") ?? "",
            "location" => $request->post("location") ?? "",
        ];
    }

    private function doDeleteAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->eventDataService->readEvents();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        $calendar->delete($id);
        if ($this->eventDataService->writeEvents($calendar->events())) {
            return $this->redirectToOverviewResponse($request);
        } else {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $event, $id, "delete"));
        }
    }

    private function respondWith(Request $request, string $output): Response
    {
        $response = Response::create($output);
        if ($request->selected() === '' || $request->selected() === "calendar") {
            $response = $response->withTitle("Calendar – " . $this->view->text("menu_main"));
        }
        return $response;
    }

    private function redirectToOverviewResponse(Request $request): Response
    {
        if ($request->selected() === '' || $request->selected() === "calendar") {
            $url = $request->url()->page("calendar")->with("admin", "plugin_main")->with("action", "plugin_text");
        } else {
            $url = $request->url()->page($request->selected());
        }
        return Response::redirect($url->absolute());
    }

    private function isValidDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    private function createDefaultEvent(Request $request): Event
    {
        $event = Event::create(
            date("Y-m-d", $request->time()),
            '',
            '',
            '',
            $this->view->plain("event_summary"),
            '',
            '',
            ''
        );
        assert($event !== null);
        return $event;
    }
}
