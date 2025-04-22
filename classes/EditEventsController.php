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

use Calendar\Dto\Event as EventDto;
use Calendar\Infra\Editor;
use Calendar\Model\BirthdayEvent;
use Calendar\Model\Calendar;
use Calendar\Model\CalendarRepo;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Plib\Codec;
use Plib\CsrfProtector;
use Plib\Random;
use Plib\Request;
use Plib\Response;
use Plib\View;

class EditEventsController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $config;

    /** @var CalendarRepo */
    private $calendarRepo;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var Random */
    private $random;

    /** @var Editor */
    private $editor;

    /** @var View */
    private $view;

    /** @param array<string,string> $config */
    public function __construct(
        string $pluginFolder,
        array $config,
        CalendarRepo $calendarRepo,
        CsrfProtector $csrfProtector,
        Random $random,
        Editor $editor,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->config = $config;
        $this->calendarRepo = $calendarRepo;
        $this->csrfProtector = $csrfProtector;
        $this->random = $random;
        $this->editor = $editor;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        $do = $request->post("calendar_do") !== null;
        switch ($request->get("action") ?? "") {
            case "create":
                return !$do ? $this->createAction($request) : $this->doCreateAction($request);
            case "generate_ids":
                return !$do ? $this->generateIds($request) : $this->doGenerateIdsAction($request);
            case "edit_single":
                return !$do ? $this->editSingleAction($request) : $this->doEditSingleAction($request);
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
        $calendar = $this->calendarRepo->find();
        $events = array_map(function (Event $event): array {
            $startDate = $event->getIsoStartDate();
            if (!$event->isFullDay()) {
                $startDate .= " " . $event->getIsoStartTime();
            }
            return [
                "start_date" => $startDate,
                "summary" => $event->summary(),
                "recurring" => !($event->recurrence() === "none" || $event instanceof BirthdayEvent),
            ];
        }, $calendar->events());
        $js = $this->pluginFolder . "js/overview.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "js/overview.js";
        }
        $output = $this->view->render('event-table', [
            'selected' => $request->selected() ? $request->selected() : 'calendar',
            'events' => $events,
            'jsUrl' => $request->url()->path($js)->with("v", CALENDAR_VERSION)->relative(),
        ]);
        return $this->respondWith($request, $output);
    }

    private function createAction(Request $request): Response
    {
        $event = $this->createDefaultEvent($request);
        return $this->respondWith($request, $this->renderEditForm($request, $event, null, "create"));
    }

    private function generateIds(Request $request): Response
    {
        $calendar = $this->calendarRepo->find();
        return $this->respondWith($request, $this->renderGenerateIdsForm($request, $calendar));
    }

    private function editSingleAction(Request $request): Response
    {
        $calendar = $this->calendarRepo->find();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        if ($event->recurrence() === "none" || $event instanceof BirthdayEvent) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->respondWith($request, $this->renderEditSingleForm($request, $event, $id));
    }

    private function updateAction(Request $request): Response
    {
        $calendar = $this->calendarRepo->find();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->respondWith($request, $this->renderEditForm($request, $event->toDto(), $id, "update"));
    }

    private function deleteAction(Request $request): Response
    {
        $calendar = $this->calendarRepo->find();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->respondWith($request, $this->renderEditForm($request, $event->toDto(), $id, "delete"));
    }

    private function renderEditForm(Request $request, EventDto $event, ?string $id, string $action): string
    {
        $this->editor->init(["calendar_textarea_description"], $this->config["edit_editor_init"]);
        $url = $request->url()->with("admin", "plugin_main")->with("action", $action);
        if ($id !== null) {
            $url = $url->with("event_id", $id);
        }
        $js = $this->pluginFolder . "js/event_editor.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "js/event_editor.js";
        }
        $isFullDay = ($event->starttime === "00:00" || $event->starttime === "")
            && ($event->endtime === "23:59" || $event->endtime === "");
        return $this->view->render('edit-form', [
            'js_url' => $request->url()->path($js)->with("v", CALENDAR_VERSION)->relative(),
            'action' => $url->relative(),
            'full_day' => $isFullDay ? "checked" : "",
            'event' => [
                "start_date" => $event->datestart ? $event->datestart . "T" . ($event->starttime ?: "00:00") : "",
                "end_date" => ($event->dateend ?: $event->datestart) . "T" . ($event->endtime ?: "23:59"),
                "summary" => $event->event,
                "linkadr" => $event->linkadr,
                "linktxt" => $event->description,
                "location" => $event->location,
            ],
            'recur_options' => $this->recurOptions($event),
            'until' => $event->until,
            'button_label' => $action === "delete" ? "label_delete" : "label_save",
            'csrf_token' => $this->csrfProtector->token(),
        ]);
    }

    /** @return array<string,string> */
    private function recurOptions(EventDto $event): array
    {
        $res = [];
        $event->recur = $event->recur === "" ? "none" : $event->recur;
        foreach (["none", "daily", "weekly", "yearly"] as $recur) {
            $res[$recur] = $event->recur === $recur ? "selected" : "";
        }
        return $res;
    }

    private function renderGenerateIdsForm(Request $request, Calendar $calendar): string
    {
        return $this->view->render("generate_ids", [
            "csrf_token" => $this->csrfProtector->token(),
            "count" => $calendar->numberOfEventsWithoutId(),
        ]);
    }

    private function renderEditSingleForm(Request $request, Event $event, string $id): string
    {
        $url = $request->url()->with("admin", "plugin_main")->with("action", "edit_single")->with("event_id", $id);
        $now = LocalDateTime::fromIsoString(date("Y-m-d\TH:i", $request->time()));
        if ($now !== null) {
            [$occurrence, ] = $event->earliestOccurrenceAfter($now);
            if ($occurrence !== null) {
                $date = $occurrence->start()->getIsoDate();
            } else {
                $date = "";
            }
        } else {
            $date = "";
        }
        return $this->view->render("edit_single", [
            "action" => $url->relative(),
            "start_date" => $event->start()->getIsoDate(),
            "recurring" => $event->recurrence(),
            "until" => $event->recursUntil() !== null ? $event->recursUntil()->getIsoDate() : "",
            "summary" => $event->summary(),
            "date" => $date,
            "csrf_token" => $this->csrfProtector->token(),
        ]);
    }

    private function doCreateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->calendarRepo->find();
        return $this->upsert($request, $calendar, null);
    }

    private function doGenerateIdsAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->calendarRepo->find();
        $calendar->generateIds(function (): string {
            return Codec::encodeBase32hex($this->random->bytes(15));
        });
        if (!$this->calendarRepo->save($calendar)) {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderGenerateIdsForm($request, $calendar));
        }
        return $this->redirectToOverviewResponse($request);
    }

    private function doEditSingleAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->calendarRepo->find();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        $date = LocalDateTime::fromIsoString($request->post("editdate") . "T00:00");
        $splitId = $calendar->split($id, $date, function (): string {
            return Codec::encodeBase32hex($this->random->bytes(15));
        });
        if (!$splitId) {
            return $this->respondWith($request, $this->view->message("fail", "error_split")
                . $this->renderEditSingleForm($request, $event, $id));
        }
        if (!$this->calendarRepo->save($calendar)) {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditSingleForm($request, $event, $id));
        }
        // TODO redirect to split event?
        $url = $request->url()->with("action", "update")->with("event_id", $splitId);
        return Response::redirect($url->absolute());
    }

    private function doUpdateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $id = $request->get("event_id");
        $calendar = $this->calendarRepo->find();
        if ($id === null || ($calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        return $this->upsert($request, $calendar, array_key_exists($id, $calendar->events()) ? $id : null);
    }

    private function upsert(Request $request, Calendar $calendar, ?string $id): Response
    {
        $dto = $this->eventPost($request);
        if ($id === null) {
            $dto->id = Codec::encodeBase32hex($this->random->bytes(15));
            $event = $calendar->addEvent($dto);
        } else {
            $dto->id = $id;
            $event = $calendar->updateEvent($dto);
        }
        if ($event === null) {
            return $this->respondWith($request, $this->view->message("fail", "error_invalid_event")
                . $this->renderEditForm($request, $dto, $id, $id !== null ? "create" : "update"));
        }
        if ($this->calendarRepo->save($calendar)) {
            return $this->redirectToOverviewResponse($request);
        } else {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $event->toDto(), $id, $id !== null ? "create" : "update"));
        }
    }

    private function eventPost(Request $request): EventDto
    {
        $datetime = explode("T", $request->post("datestart") ?? "", 2);
        $datestart = $datetime[0];
        $starttime = $datetime[1] ?? "";
        $datetime = explode("T", $request->post("dateend") ?? "", 2);
        $dateend = $datetime[0];
        $endtime = $datetime[1] ?? "";
        if ($request->post("full_day")) {
            $starttime = "00:00";
            $endtime = "23:59";
        }
        $dto = new EventDto();
        $dto->datestart = $datestart;
        $dto->dateend = $dateend;
        $dto->starttime = $starttime;
        $dto->endtime = $endtime;
        $dto->event = $request->post("event") ?? "";
        $dto->description = $request->post("linktxt") ?? "";
        $dto->linkadr = $request->post("linkadr") ?? "";
        $dto->location = $request->post("location") ?? "";
        $dto->recur = $request->post("recur") ?? "";
        $dto->until = $request->post("until") ?? "";
        $dto->id = "";
        return $dto;
    }

    private function doDeleteAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return $this->respondWith($request, $this->view->message("fail", "error_unauthorized"));
        }
        $calendar = $this->calendarRepo->find();
        $id = $request->get("event_id");
        if ($id === null || ($event = $calendar->event($id)) === null) {
            return $this->redirectToOverviewResponse($request);
        }
        $calendar->delete($id);
        if ($this->calendarRepo->save($calendar)) {
            return $this->redirectToOverviewResponse($request);
        } else {
            return $this->respondWith($request, $this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $event->toDto(), $id, "delete"));
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

    private function createDefaultEvent(Request $request): EventDto
    {
        $dto = new EventDto();
        $dto->datestart = date("Y-m-d", $request->time());
        $dto->event = $this->view->plain("event_summary");
        return $dto;
    }
}
