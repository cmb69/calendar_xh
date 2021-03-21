<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2021 Christoph M. Becker
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

use Fa\RequireCommand as FaRequireCommand;

class EditEventsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        (new FaRequireCommand)->execute();
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        echo $this->eventForm($events);
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $deleted = false;
        $added = false;

        $varnames = array(
            'datestart', 'dateend', 'starttime', 'endtime', 'event', 'linkadr', 'linktxt', 'location'
        );
        $post = [];
        foreach ($varnames as $var) {
            $post[$var] = isset($_POST[$var]) ? $_POST[$var] : [];
        }
        $events = [];
        foreach (array_keys($post['datestart']) as $i) {
            if (!isset($_POST['delete'][$i])) {
                if (!$this->isValidDate($post['datestart'][$i])) {
                    $post['datestart'][$i] = '';
                }
                assert($post['dateend'][$i] !== null);
                if (!$this->isValidDate($post['dateend'][$i])) {
                    $post['dateend'][$i] = '';
                }
                //Birthday should never have an enddate
                if (trim($post['location'][$i]) === '###') {
                    $post['dateend'][$i] = '';
                }
                $events[] = new Event(...array_column($post, $i));
            } else {
                $deleted = true;
            }
        }

        if (isset($_POST['add'])) {
            $events[] = $this->createDefaultEvent();
            $added = true;
        }

        if (!$deleted && !$added) {
            // sorting new event inputs, idea of manu, forum-message
            usort($events, /** @return int */ function (Event $a, Event $b) {
                $a_i = "{$a->getDateStart()}T{$a->getStartTime()}";
                $b_i = "{$b->getDateStart()}T{$b->getStartTime()}";
                if ($a_i == $b_i) {
                    return 0;
                }
                return ($a_i < $b_i) ? -1 : 1;
            });
            if ((new EventDataService($this->dpSeparator()))->writeEvents($events)) {
                echo XH_message('success', $this->lang['eventfile_saved']);
            } else {
                echo XH_message('fail', $this->lang['eventfile_not_saved']);
            }
        }

        echo $this->eventForm($events);
    }

    /**
     * @param Event[] $events
     * @return string
     */
    private function eventForm($events)
    {
        $view = new View('event-form');
        $view->data = [
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'events' => $events,
        ];
        return (string) $view;
    }

    /**
     * Checking the date format. Some impossible dates can be given, but don't hurt.
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate($date)
    {
        return (bool) preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    /**
     * @return Event
     */
    private function createDefaultEvent()
    {
        return new Event(
            date('Y-m-d'),
            '',
            '',
            '',
            $this->lang['event_event'],
            '',
            '',
            ''
        );
    }
}
