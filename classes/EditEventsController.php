<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
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

class EditEventsController extends Controller
{
    private $editeventswidth;

    private $imageFolder;

    public function __construct($editeventswidth)
    {
        global $pth;

        parent::__construct();
        if ($editeventswidth) {
            $this->editeventswidth = $editeventswidth;
        } else {
            $this->editeventswidth = $this->conf['event-input_memberpages_narrow_medium_or_wide'];
        }
        $this->imageFolder = "{$pth['folder']['plugins']}calendar/images/";
    }

    public function defaultAction()
    {
        $events = (new EventDataService)->readEvents();
        echo $this->eventForm($events, $this->editeventswidth);
    }

    public function saveAction()
    {
        $delete      = isset($_POST['delete'])       ? $_POST['delete']       : '';
        $add         = isset($_POST['add'])          ? $_POST['add']          : '';
        $datestart   = isset($_POST['datestart'])    ? $_POST['datestart']    : '';
        $starttime   = isset($_POST['starttime'])    ? $_POST['starttime']    : '';
        $dateend     = isset($_POST['dateend'])      ? $_POST['dateend']      : '';
        $endtime     = isset($_POST['endtime'])      ? $_POST['endtime']      : '';
        $event       = isset($_POST['event'])        ? $_POST['event']        : '';
        $location    = isset($_POST['location'])     ? $_POST['location']     : '';
        $linkadr     = isset($_POST['linkadr'])      ? $_POST['linkadr']      : '';
        $linktxt     = isset($_POST['linktxt'])      ? $_POST['linktxt']      : '';

        $varnames = array(
            'datestart', 'starttime', 'dateend', 'endtime','event', 'location', 'linkadr', 'linktxt'
        );
        foreach ($varnames as $var) {
            $$var = array_map('stsl', $$var);
        }

        $deleted = false;
        $added = false;

        $newevent = array();
        foreach (array_keys($event) as $j) {
            if (!isset($delete[$j]) || $delete[$j] == '') {
                if (!$this->isValidDate($datestart[$j])) {
                    $datestart[$j] = '';
                }
                if (!$this->isValidDate($dateend[$j])) {
                    $dateend[$j] = '';
                }

                //Birthday should never have an enddate
                if ($location[$j] == '###') {
                    $dateend[$j] = '';
                }

                $entry = array(
                    'datestart'  => str_replace(';', ' ', $datestart[$j]),
                    'starttime'  => str_replace(';', ' ', $starttime[$j]),
                    'dateend'    => str_replace(';', ' ', $dateend[$j]),
                    'endtime'    => str_replace(';', ' ', $endtime[$j]),
                    'event'      => str_replace(';', ' ', $event[$j]),
                    'location'   => str_replace(';', ' ', $location[$j]),
                    'linkadr'    => str_replace(';', ' ', $linkadr[$j]),
                    'linktxt'    => str_replace(';', ' ', $linktxt[$j])
                );
                $newevent[] = $entry;
            } else {
                $deleted = true;
            }
        }
        if ($add <> '') {
            $newevent[] = $this->createDefaultEvent();
            $added = true;
        }

        $o = '';
        if (!$deleted && !$added) {
            // sorting new event inputs, idea of manu, forum-message
            usort($newevent, array($this, 'dateSort'));
            if (!(new EventDataService)->writeEvents($newevent)) {
                $o .= "<p><strong>{$this->lang['eventfile_not_saved']}</strong></p>\n";
            } else {
                $o .= "<p><strong>{$this->lang['eventfile_saved']}</strong></p>\n";
            }
        }

        $o .= $this->eventForm($newevent, $this->editeventswidth);
        echo $o;
    }

    private function eventForm($events, $editeventswidth)
    {
        global $hjs, $pth, $sl, $tx;

        $hjs .= '<script type="text/javascript" src="'
             .  $pth['folder']['plugins'] . 'calendar/dp/datepicker.js">{ "lang":"'.$sl.'" }</script>'."\n";
        $hjs .= tag('link rel="stylesheet" type="text/css" href="'
             .  $pth['folder']['plugins'] . 'calendar/dp/datepicker.css"')."\n";

        switch ($editeventswidth) {
            case 'narrow':
                $columns = 5;
                break;
            case 'wide':
                $columns = 8;
                break;
            default:
                $columns = 6;
                $editeventswidth = 'medium';
        }

        $view = new View('event-form');
        $view->tableclass = "calendar_input_{$editeventswidth}";
        $view->columns = $columns;
        $view->saveLabel = ucfirst($tx['action']['save']);
        $view->addIcon = "{$this->imageFolder}add.png";
        $view->table = new HtmlString($this->renderTable($editeventswidth, $events));
        return (string) $view;
    }

    private function renderTable($width, array $events)
    {
        $view = new View("{$width}-table");
        $view->showEventTime = $this->conf['show_event_time'];
        $view->showEventLocation = $this->conf['show_event_location'];
        $view->showEventLink = $this->conf['show_event_link'];
        $view->events = $events;
        $view->deleteIcon = "{$this->imageFolder}delete.png";
        $datePickerScripts = [];
        foreach (array_keys($events) as $i) {
            $datePickerScripts[] = new HtmlString($this->renderDatePickerScript($i));
        }
        $view->datePickerScripts = $datePickerScripts;
        return (string) $view;
    }

    private function renderDatePickerScript($num)
    {
        $separator = $this->dpSeperator('dp');
        return <<<EOS
<script type="text/javascript">
(function () {
    var opts = {
        formElements: {"datestart{$num}": "d-{$separator}-m-{$separator}-Y"},
        showWeeks: true,
        // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
        statusFormat: "l-cc-sp-d-sp-F-sp-Y"
    };
    datePickerController.createDatePicker(opts);
    opts.formElements =  {"dateend{$num}": "d-{$separator}-m-{$separator}-Y"}; 
    datePickerController.createDatePicker(opts);
}());
</script>
EOS;
    }

    /**
     * Checking the date format. Some impossible dates can be given, but don't hurt.
     */
    private function isValidDate($date)
    {
        $pattern = '/[\d\d\|\?{1-2}|\-{1-2}]\\' . $this->dpSeperator() . '\d\d\\'
            . $this->dpSeperator() . '\d{4}$/';
        return preg_match($pattern, $date);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function dateSort($a, $b)
    {
        $pattern = '!(.*)\\' . $this->dpSeperator() . '(.*)\\' . $this->dpSeperator() . '(.*)!';
        $replace = '\3\2\1';
        $a_i = preg_replace($pattern, $replace, $a['datestart']) . $a['starttime'];
        $b_i = preg_replace($pattern, $replace, $b['datestart']) . $b['starttime'];
        if ($a_i == $b_i) {
            return 0;
        }
        return ($a_i < $b_i) ? -1 : 1;
    }

    private function createDefaultEvent()
    {
        return array(
            'datestart'   => date('d') . $this->dpSeperator() . date('m') . $this->dpSeperator() . date('Y'),
            'starttime'   => '',
            'dateend'     => '',
            'endtime'     => '',
            'event'       => $this->lang['event_event'],
            'location'    => '',
            'linkadr'     => '',
            'linktxt'     => ''
        );
    }
}
