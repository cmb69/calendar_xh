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

    public function __construct($editeventswidth)
    {
        parent::__construct();
        $this->editeventswidth = $editeventswidth;
    }

    public function defaultAction()
    {
        global $pth, $sl, $plugin;

        if (!$this->editeventswidth) {
            $this->editeventswidth = $this->conf['event-input_memberpages_narrow_medium_or_wide'];
        }
        $imageFolder = "{$pth['folder']['plugins']}{$plugin}/images";
        $events = (new EventDataService)->readEvents();

        if (isset($_POST['action'])) {
            $action = $_POST['action'];
        } elseif(isset($_GET['action'])) {
            $action = $_GET['action'];
        } else {
            $action = 'editevents';
        }

        if ($action == "editevents" || $action == "plugin_text") {
            $o .= $this->eventForm($events, $this->editeventswidth);
        } elseif ($action == 'saveevents') {
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

            foreach (array('datestart', 'starttime', 'dateend', 'endtime','event', 'location', 'linkadr', 'linktxt') as $var) {
                $$var = array_map('stsl', $$var);
            }

            $deleted = false;
            $added = false;

            $newevent = array();
            foreach ($event as $j => $i) {
                if (!isset($delete[$j]) || $delete[$j] == '') {
                    //Checking the date format. Some impossible dates can be given, but don't hurt.
                    $pattern = '/[\d\d\|\?{1-2}|\-{1-2}]\\' . $this->dpSeperator() . '\d\d\\' . $this->dpSeperator() . '\d{4}$/';
                    if (!preg_match($pattern,$datestart[$j])) {
                        $datestart[$j] = '';
                    }
                    if (!preg_match($pattern,$dateend[$j])) {
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
            if($add <> '') {
                $entry = array(
                    'datestart'   => date('d') . $this->dpSeperator() . date('m') . $this->dpSeperator() . date('Y'),
                    'starttime'   => '',
                    'dateend'     => '',
                    'endtime'     => '',
                    'event'       => $this->lang['event_event'],
                    'location'    => '',
                    'linkadr'     => '',
                    'linktxt'     => ''
                );
                $newevent[] = $entry;
                $added = true;
            }

            if (!$deleted && !$added) {
                // sorting new event inputs, idea of manu, forum-message
                usort($newevent, array($this, 'dateSort'));
                if (!(new EventDataService)->writeEvents($newevent)) {
                    $o .= "<p><strong>{$this->lang['eventfile_not_saved']}</strong></p>\n";
                } else {
                    $o .= "<p><strong>{$this->lang['eventfile_saved']}</strong></p>\n";
                }
            }

            $editeventstableclass = 'calendar_input';
            if ($this->editeventswidth == 'wide') {
                $editeventstableclass = 'calendar_input_wide';
            }

            $o .= $this->eventForm($newevent, $this->editeventswidth);
        }
        echo $o;
    }

    //==========================================================
    //makes the form for editing events wide, medium or narrow
    //==========================================================
    private function eventForm($events, $editeventswidth)
    {
        global $hjs, $pth, $sl, $plugin, $tx;

        $hjs .= '<script type="text/javascript" src="'
             .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.js">{ "lang":"'.$sl.'" }</script>'."\n";
        $hjs .= tag('link rel="stylesheet" type="text/css" href="'
             .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.css"')."\n";

        $imageFolder = $pth['folder']['plugins'] . $plugin . "/images";

        switch ($editeventswidth) {
            case 'narrow':
                $columns = 5;
                break;
            case 'wide':
                $columns = 8;
                break;
            default:
                $colums = 6;
        }
        $tableclass = "calendar_input_{$editeventswidth}";

        $o = "<form method=\"POST\" action=\"$sn\">\n";
        $o .= "<input type=\"hidden\" value=\"saveevents\" name=\"action\">\n";
        $o .= "<table class=\"calendar_input $tableclass\">\n";
        $o .= "<tr>\n";
        $o .= "<td colspan=\"$columns\"><input class=\"submit\" type=\"submit\" value=\""
            . ucfirst($tx['action']['save']) . "\" name=\"send\"></td>\n";
        $o .= "<td style=\"text-align: right; width: 16px;\"><input type=\"image\" src=\""
            . $imageFolder . "/add.png\" style=\"width: 16px; height: 16px;\" name=\"add[0]\" value=\"add\" alt=\"Add entry\">\n</td>\n";
        $o .= "</tr>\n";

        //========================
        //narrow width input table
        //========================
        if ($editeventswidth == 'narrow') {
            if ($this->conf['show_event_time'] == 'true') {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td class=\"calendar_input_datefield\">"
                    . $this->lang['event_start'] . tag('br')
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time']    . "</td>\n"
                    . "<td class=\"calendar_input_datefield\">"
                    . $this->lang['event_end'] . tag('br')
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time'] . "</td>\n"
                    . "<td>" . $this->lang['event_event'] . "</td>\n"
                    . "<td> </td>\n"
                    . "</tr>\n";
            } else {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td colspan=\"2\">" . $this->lang['event_start'] . " " . $this->lang['event_date'] . "</td>\n"
                    . "<td colspan=\"2\">" . $this->lang['event_end'] . " " . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_event'] . "</td>\n"
                    . "<td></td>\n"
                    . "</tr>\n";
            }
            $i = 0;
            foreach ($events as $entry) {
                if ($this->conf['show_event_time'] == 'true') {
                    $o .= "<tr>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['datestart'] . '" name="datestart[' . $i . ']" id="datestart' . $i . '"') . "</td>\n";

                    $o .= "<td class=\"calendar_input_time\">"
                        . tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                        . $entry['starttime'] . '" name="starttime[' . $i . ']"') . "</td>\n" ;

                    $o .= "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['dateend'] . '" name="dateend[' . $i . ']" id="dateend' . $i . '"') . "</td>\n" ;
                                                           //3
                    $o .= "<td class=\"calendar_input_time\">"
                       .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                       .  $entry['endtime'] . '" name="endtime[' . $i . ']"') . "</td>\n" ;
                } else {
                    $o .= "<tr>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['datestart'] . '" name="datestart[' . $i . ']" id="datestart' . $i . '"') . "</td>\n";

                    $o .= tag('input type="hidden" value="'. $entry['starttime'] . '" name="starttime[' . $i . ']"') . "\n";

                    $o .= "<td style=\"width: 0\"></td>";

                    $o .= "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['dateend'] . '" name="dateend[' . $i . ']" id="dateend' . $i . '"') . "</td>\n";

                    $o .= tag('input type="hidden" value="' . $entry['endtime'] . '" name="endtime[' . $i . ']"') ."\n";

                    $o .= "<td style=\"width: 0\"></td>";
                }

                $o .= "<td>" . tag('input class="calendar_input_event event_highlighting" type="normal"  value="'
                    . $entry['event'] . '" name="event[' . $i . ']"') . "</td>\n";

                $o .= $this->renderDatePickerScript($i);

                $o .= "<td>"
                    . tag('input type="image" src="'
                    . $imageFolder . '/delete.png" style="width: 16px; height: 16px" name="delete['
                    . $i.']" value="delete" alt="Delete Entry"') . "\n"
                    . "</td>\n</tr>\n";
                if ($this->conf['show_event_location'] == 'true') {
                    $o .= "<tr>\n"
                        . "<td class=\"calendarinput_line2\" colspan=\"4\">"
                        . $this->lang['event_location'] ."</td>\n"
                        . "<td>" . tag('input type="normal" class="calendar_input_event" value="'
                        . $entry['location'] . '" name="location[' . $i . ']"') . "</td>\n<td></td>\n</tr>\n";
                } else {
                    $o .= tag('input type="hidden" value="' . $entry['location'] . '" name="location[' . $i .']"');
                }

                if ($this->conf['show_event_link'] == 'true') {
                    $o .= "</tr>\n<tr>\n"
                        . "<td class=\"calendarinput_line2\" colspan=\"4\">"
                        . $this->lang['event_link'] . "</td>\n"
                        . "<td>"
                        . tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                        . $entry['linkadr'] . '" name="linkadr[' . $i . ']"') . "</td>\n<td>&nbsp;</td>\n</tr>\n";
  
                    $o .= "<td class=\"calendarinput_line2\" colspan=\"4\">"
                       .  $this->lang['event_link_txt'] . "</td>\n"
                       .  "<td>"
                       .  tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                       .  $entry['linktxt'] . '" name="linktxt[' . $i . ']"') . "</td>\n<td></td>\n</tr>\n"
                       .  "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";
                } else {
                    $o .= tag('input type="hidden" value="'
                        . $entry['linkadr']  .'" name="linkadr[' . $i . ']"')
                        . tag('input type="hidden" value="' . $entry['linktxt'] . '" name="linktxt[' . $i . ']"');
                }
                $i++;
            }
    
            //========================
            // wide width input table
            //========================
        } elseif ($editeventswidth == 'wide') {
            if ($this->conf['show_event_time'] == 'true') {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td>" . $this->lang['event_start'] . tag('br')
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time'] . "</td>\n"
                    . "<td>" . $this->lang['event_end'] . tag('br')
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time'] . "</td>\n"
                    . "<td>" . $this->lang['event_event'] . "</td>\n";
            } else {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td colspan=\"2\">" . $this->lang['event_start'] . " "
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td colspan='2'>" . $this->lang['event_end'] . " "
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_event'] . "</td>\n";
            }

            if ($this->conf['show_event_location'] == 'true') {
                $o .= "<td>" . $this->lang['event_location'] ."</td>\n";
            } else {
                $o .= "<td style=\"width: 0\"></td>";
            }

            if ($this->conf['show_event_link'] == 'true') {
                $o .= "<td>" . $this->lang['event_link'] . "</td>\n"
                    . "<td>" . $this->lang['event_link_txt'] . "</td>\n";
            } else {
                $o .= "<td style=\"width: 0\"></td><td style=\"width: 0\"></td>";
            }

            $o .= "<td></td>\n</tr>\n";

            $i = 0;
            foreach ($events as $entry) {
                $o .= "<tr>\n"
                    . "<td class=\"calendar_input_datefield\">"
                    . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                    . $entry['datestart'] . '" name="datestart[' . $i . ']" id="datestart' . $i . '"') . "</td>\n";

                if ($this->conf['show_event_time'] == 'true') {
                    $o .= "<td class=\"calendar_input_time\">"
                        . tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                        . $entry['starttime'] . '" name="starttime[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<td style=\"width: 0\">" . tag('input type="hidden" value="'
                        . $entry['starttime'] . '" name="starttime[' . $i . ']"') . "</td>\n";
                }

                $o .= "<td class=\"calendar_input_datefield\">"
                    . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                    . $entry['dateend'] . '" name="dateend[' . $i . ']" id="dateend' . $i .'"') . "</td>\n";

                if ($this->conf['show_event_time'] == 'true') {
                    $o .= "<td class=\"calendar_input_time\">"
                        . tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                        . $entry['endtime'] . '" name="endtime[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<td style=\"width: 0\">" . tag('input type="hidden" value="'
                        . $entry['endtime'] . '" name="endtime[' . $i . ']"') . "</td>\n";
                }

                $o .= $this->renderDatePickerScript($i);
    
                $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                    . $entry['event'] . '" name="event[' . $i . ']"') . "</td>\n";

                if ($this->conf['show_event_location'] == 'true') {
                    $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                        . $entry['location'] . '" name="location[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                        . $entry['location'] . '" name="location[' . $i . ']"') . "</td>";
                }

                if ($this->conf['show_event_link'] == 'true') {
                    $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                        . $entry['linkadr'] . '" name="linkadr[' . $i . ']"') . "</td>\n";
                    $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                        . $entry['linktxt'] . '" name="linktxt[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<td style=\"width: 0\">". tag('input type="hidden" value="'. $entry['linkadr'] . '" name="linkadr[' . $i . ']"') . "</td>"
                        . "<td style=\"width: 0\">". tag('input type="hidden" value="'. $entry['linktxt'] . '" name="linktxt[' . $i . ']"') . "</td>";
                }
                $o .= "<td>"
                    . tag('input type="image" src="'
                    . $imageFolder .'/delete.png" style="width: 16px; height: 16px" name="delete[' . $i . ']" value="delete" alt="Delete Entry"') . "\n"
                    . "</td>\n</tr>\n";
                $i++;
            }
         } else {
            //==========================
            // medium width input table
            //==========================
            if ($this->conf['show_event_time'] == 'true') {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td>" . $this->lang['event_start']
                    . tag('br') . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time'] . "</td>\n"
                    . "<td>" . $this->lang['event_end'] . tag('br')
                    . $this->lang['event_date'] . "</td>\n"
                    . "<td>" . $this->lang['event_time'] . "</td>\n";

                if ($this->conf['show_event_location'] == 'true') {
                    $o .= "<td>" . $this->lang['event_event'] . "</td>\n"
                        . "<td>" . $this->lang['event_location'] . "</td>\n";
                } else {
                    $o .= "<td colspan=\"2\">"
                        . $this->lang['event_event']
                        . "</td>\n";
                }
                $o .= "<td> </td>\n</tr>\n";
            } else {
                $o .= "<tr class=\"firstline_calendarinput\">\n"
                    . "<td>" . $this->lang['event_start']
                    . " " . $this->lang['event_date'] . "</td>\n";

                $o .= "<td style=\"width: 0\"></td>";

                $o .= "<td>" . $this->lang['event_end']
                    . " " . $this->lang['event_date'] . "</td>\n";

                $o .= "<td style=\"width: 0\"></td>";

                if ($this->conf['show_event_location'] == 'true') {
                    $o .= "<td>" . $this->lang['event_event'] .   "</td>\n"
                        . "<td>" . $this->lang['event_location']. "</td>\n";
                } else {
                    $o .= "<td colspan=\"2\">" . $this->lang['event_event'] . "</td>\n";
                }
                $o .= "<td> </td>\n</tr>\n";
            }
            $i = 0;
            foreach ($events as $entry) {
                if ($this->conf['show_event_time'] == 'true') {
                    $o .= "<tr>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['datestart'] . '" name="datestart[' . $i . ']" id="datestart' . $i . '"') . "</td>\n"
                        . "<td class=\"calendar_input_time\">"
                        . tag('input type="normal" class="calendar_input_time" maxlength="5" value="'
                        . $entry['starttime'] . '" name="starttime[' . $i . ']"') . "</td>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['dateend'] . '" name="dateend[' . $i . ']" id="dateend' . $i . '"') ."</td>\n"
                        . "<td class=\"calendar_input_time\">"
                        . tag('input type="normal" class="calendar_input_time" maxlength="5" value="'
                        . $entry['endtime'] . '" name="endtime[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<tr>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['datestart'] . '" name="datestart[' . $i . ']" id="datestart' . $i . '"') . "</td>\n"
                        . "<td style=\"width: 0;\">"
                        . tag('input type="hidden" value="' . $entry['starttime'] . '" name="starttime[' . $i . ']"') . "</td>\n"
                        . "<td class=\"calendar_input_datefield\">"
                        . tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                        . $entry['dateend'] . '" name="dateend[' . $i . ']" id="dateend' . $i . '"') . "</td>\n"
                        . "<td style=\"width: 0;\">"
                        . tag('input type="hidden" value="' . $entry['endtime'] . '" name="endtime[' . $i . ']"') . "</td>\n";
                }

                $o .=  $this->renderDatePickerScript($i);

                if ($this->conf['show_event_location'] == 'true') {
                    $o .= "<td>" . tag('input type="normal" class="calendar_input_event event_highlighting" value="'
                        . $entry['event'] . '" name="event[' . $i . ']"') . "</td>\n"
                        . "<td>" . tag('input type="normal"  class="calendar_input_event" value="'
                        . $entry['location'] . '" name="location[' . $i . ']"') . "</td>\n";
                } else {
                    $o .= "<td colspan=\"2\">" . tag('input type="normal" class="calendar_input_event event_highlighting" value="'
                        . $entry['event'] . '" name="event[' . $i . ']"') . "\n"
                        . tag('input type="hidden" value="' . $entry['location'] . '" name="location[' . $i . ']"') . "</td>\n";
                }

                $o .= "<td style=\"text-align: right;\">"
                    . tag('input type="image" src="' . $imageFolder
                    . '/delete.png" style="width: 16px; height: 16px" name="delete[' . $i . ']" value="delete" alt="Delete Entry"') . "\n"
                    . "</td>\n</tr>\n" ;

                if ($this->conf['show_event_link'] == 'true') {
                    $o .= "<tr>\n"
                        . "<td class=\"calendarinput_line2\" colspan=\"4\">" . $this->lang['event_link'] . " / "
                        . $this->lang['event_link_txt'] . "</td>\n"
                        . "<td>" . tag('input type="normal" class="calendar_input_event" value="' . $entry['linkadr']
                        . '" name="linkadr[' . $i . ']"') . "</td>\n"
                        . "<td>" . tag('input type="normal" class="calendar_input_event" value="'
                        . $entry['linktxt'] . '" name="linktxt[' . $i . ']"') . "</td>\n"
                        . "<td>&nbsp;</td>\n</tr>\n";
                } else {
                    $o .= "<input type='hidden' value='" . $entry['linkadr'] . "' name='linkadr[$i]'>"
                        . "<input type='hidden' value='" . $entry['linktxt'] . "' name='linktxt[$i]'>";
                }
                $i++;
            }
        }
        $o .= "<tr>\n";
        $o .= "<td colspan=\"$columns\"><input class=\"submit\" type=\"submit\" value=\"" . ucfirst($tx['action']['save'])."\" name=\"send\"></td>\n";
        $o .= "<td><input type=\"image\" src=\"$imageFolder/add.png\" style=\"width: 16px; height: 16px;\" name=\"add[0]\" value=\"add\" alt=\"Add entry\">\n</td>\n";
        $o .= "</tr>\n";
        $o .= "</table>\n";
        $o .= "</form>";
        return $o;
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
}
