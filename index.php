<?php
/*
CMSimple - Calendar Plugin (based on v. 0.5 from 2006 by Michael Svarrer)

-- 1.2.10 Update eliminating rare problems with magic quotes. Fix by cmb.
-- 1.2.9 Update with ideas of cmb (empty lines bug on some servers, rel_"nofollow" added)
-- 1.2.8 Security update (on adive by cmb)
-- 1.2.7 (10/2011) Slovak translation completed by Stanka
    - Fixed bug in calendar: Now calendar title-tooltip can show more than 1 event per day.

-- 1.2.6 Maintenance release by svasti
    - Bug fixed: number of table columns now determined dynamically
    - Bug fixed: switching off field doesn't delete values any more (added all switched off fields as hidden values)
    - Beauty fix: Date of multi-day events is presented in a nicer way
    - User demand: Date can again be entered directly (date check added, "-,--,?,??" possible as days)
    - User demand: Swichted off fields are now also off in all input tables
      (was a big job!) and remailing fields now fill available space automatically
    - Some changes in css
    - Bug fixed: Enddate in Birthday would screw up the coloring of the calendar display, Now enddate automatically deleted in birthday case
    - changed all <input.. and <img .. to tag('input... and tag('img.. so that even the backand validates (could be used on member pages)
    - added filepath_data in config
    - added possibility to have the notice "no next event sceduled" in next event
-- 1.2 Build 5 by svasti
    - Possibility to enter any text in link-text field,
    - further cleaning up the code,
    - width definition of input fields moved to css, now IE and FF display the input table in the same way,
    - sorting on save, function written by manu

-- Version 1.2 modified by Holger http://cmsimple.holgerirmler.de
   - Fixed some PHP 5.3.0 deprecated code (split() ...)
   - Added xh_debugmode() and a bit security
   - Removed direct edit of the eventfiles in admin-area
   - Bugfix: layout crashed on eventspage when a birthday (###) was the first event in a month
   - Added the DatePicker v5.4 by frequency-decoder.com and made the date inputs readonly
   - Replaced ";" by " " in users input to prevent a script-crash and to keep the field-structure in the event-files
   - nextevent() will return now nothing when there is no future event
   - $plugin_cf['calendar']['date_delimiter']: is ready to use full-stop ".", forward slash "/" and minus/dash "-",
     a changed delimitter at runtime will be converted by the DatePicker, so no crashed eventfiles anymore
   - Slightly changed the "narrow" style in the backend

-- Version 1.1 modified by svasti http://svasti.de
   - xh-unicode-compatible
   - lots of obsolete attributes moved from config to css (otherwise no validation in html5)
   - elimination of unnecessary attributes
   - changed obsolete php code ("eregi" is obsolete in php 5.3)
   - improvement in backend, 3 input styles available (wide, medium narrow)
   - tooltips in config
   - extra line in event display giving the period for which the events are listed,
   - possibility to show past events, new way to show end of events
   - bug corrections: begin and end display of events lasting several days were mixed up
   - events from different years would mess up the event-display,
   - new way to show end of events (date and/or time),
   - correction of other minor bugs (like birthday in calendar view) plus beautifying the code display a bit... but not too much

----------------------------------------------------------------------------------------------------------------
-- Version 1.0 By Tory
-- The function of showing/not showing time, location and link in event view will now work again
----------------------------------------------------------------------------------------------------------------
Modified by Bob (cmsimple.fr), 02/2008. (this is from CMSimple Flex package)
----------------------------------------------------------------------------------------------------------------
-- Version 0.9 By Tory
-- Minor bugs corrected regarding Date-delimiter problem in events in Calendar view.
-- Event Page is now configured in the language file. You therefore don't need to add Eventpage-name in Calender call in Template or Calendar-page
-- Singular-Plural text regarding age in birtdays in EventPage are now added to language files.
-- Parameters added to language files are:
 $plugin_tx['calendar']['event_page']="Events";  - The Eventpage, in wich Events in Calendar are related to.
 $plugin_tx['calendar']['age_singular_text']="year"; age text for ages <2 Years
 $plugin_tx['calendar']['age_plural1_text']="years";
 age text for ages >1 and <5 years
 $plugin_tx['calendar']['age_plural2_text']="years"; age text for ages >4 years, used in many east european contries

- Version 0.8 By Tory
Events can now be added / edited / deleted from a CMSimple page, without the Administrator Rights.
Just create a page and add the following line:
#CMSimple $output.=EditEvents();#
The page can be behind Memberspage or Register plugin to allow just a single group of members to be Editor of the Event File. Eg.: #CMSimple $output.=access('admin');$output.=EditEvents();# (Used in combination with register plugin.)
Only index.php file in Calender plugin has been changed since Version 0.7
- Version 0.7 By Tory based on Svarrers version 0.5 : 20. Sep. 2007
Several dates events are now handled so that they can last over several days, weeks, month or years.
Begin and End date/time are entered in the eventfile separately. They are only shown in Eventpage with
the Begin date and End date. In the Calender it is possible to select whether the between dates should be
higlighted or not.
In Nextevent and Event-page are automatically written Begin date-time or End date-time to a several dates event.
- Version 0.6 Mod by Tory : 16. Sep 2007
New Function added: nextevent() will display next coming event compared to current date-time.
New way of sorting events that makes it possible to have more events the same day, and have them sorted
by date and time.
Events are entered in the eventfile this way:
Date;Event;Location (### = birthday);Link (int:?InternalPage / ext:www.ExternalPage.com),LinkTxT;Time (eg: 09:45)
Date can be: begin-date,end-date,end-time for events over several days e.g: 19-6-2007,27-6-2007,16:30
- Version 0.5 : 17. mar 2006
New feature added external linkage and miltiple dates for same event syntax
date delimiter can be /.- for input. For output the event table parameter
$plugin_cf['calendar']['date_delimiter'] controls the output
New CSS clases added for the event_monthyear, event_heading,event_data
Improved way of generating event table implemented
- Version 0.4 : 11. sept 2005
 Title added to link in calendar mode showing event
 $end_month added in the events function, here you specify the amount
 of months the events function should show.
 Prev/Next month button avaliable, set 'prev_next_button' to true to enable
 Bug when using week_starts_monday = false
- Version 0.3 : Third release - 16. jun 2005
Parameters added to config
 $plugin_cf['calendar']['calendar_width']
 $plugin_cf['calendar']['event_width_date']="20%";
 $plugin_cf['calendar']['event_width_event']="60%";
 $plugin_cf['calendar']['event_width_location']="20%";
- Version 0.2 : Second release - 15. jun 2005
Some Notice: Undefined variable: for different variables
*/

if ((!function_exists('sv')) || (strlen($sl) !== 2) || preg_match('/calendar'.DIRECTORY_SEPARATOR.'index.php/i', sv('PHP_SELF')))die('Access denied');

if (!$plugin_cf['calendar']['filepath_data']){
    $datapath = $pth['folder']['plugins'].$plugin."/content/";
} else {
    $datapath = $plugin_cf['calendar']['filepath_data'];
}

/*function to display a calendar in the template or on a page*/
function calendar($year = '', $month = '', $eventpage = '')
{
    ob_start();
    (new Calendar\CalendarController($year, $month, $eventpage))->defaultAction();
    return ob_get_clean();
}

// ****************************************************************
// *                  Display of the event list                   *
// ****************************************************************

//function to display the list of events on a page
function events($month, $year, $end_month, $past_month)
{
    ob_start();
    (new Calendar\EventListController($month, $year, $end_month, $past_month))->defaultAction();
    return ob_get_clean();
}


// ****************************************************************
// *                  Next coming event as marquee                *
// ****************************************************************

function nextevent()
{
    global $plugin_cf,$plugin_tx,$datapath,$sl,$plugin;

    $plugin=basename(dirname(__FILE__),"/");

    $event_date_array = array();
    $event_array = array();
    $event_location_array = array();
    $event_stsl_array = array();

    $t = '';
    $remember_event = '';


    if ($plugin_cf['calendar']['same-event-calendar_for_all_languages']=="true") {
        $eventfile = $datapath."eventcalendar.txt";
    }else {
        $eventfile = $datapath."eventcalendar_".$sl.".txt";
    }

    if(is_file($eventfile)){
    $fp = fopen($eventfile, "r");


    while (!feof($fp)) {
         $line = fgets($fp, 4096);
         $txt = "";
         list($eventdates,$event,$location,,$eventtime) = explode( ";", $line);
         list($event_date_start,$event_end_date,$event_end_time) = explode(",",$eventdates);

         if($event_end_date){

              $txt = $plugin_tx['calendar']['event_date_till_date']." ".tag('br'). $event_end_date ." ". $event_end_time;
              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);

              $txt = $plugin_tx['calendar']['event_event']." ".$plugin_tx['calendar']['event_start'].":".tag('br'). $event_date_start ." ". $eventtime;
              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_end_date);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $event_end_time"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);

         }else{

              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);
             }

        }

    fclose($fp);
    }

    asort($event_date_array);

    $today=strtotime('now');

    foreach($event_date_array as $event_date){
        if($event_date>$today){
            if($remember_event == '')$remember_event=$event_date;
        }
    }
    if($remember_event > $today){
        $i = array_search($remember_event, $event_date_array);
        $t.= "<div class='nextevent_date'>".strftime($plugin_tx['calendar']['event_date_representation_in_next_event_marquee'],$event_date_array[$i]);
        if (strftime('%H:%M',$event_date_array[$i])!="00:00") {
            $t.= " — ".strftime('%H:%M',$event_date_array[$i]);
        }
        $t.= "</div>\n";
        $t.= "<marquee direction='up' scrolldelay=100 scrollamount='1'><div class='nextevent_event'>".$event_array[$i]."</div>\n";
        $t.= "<div class='nextevent_date'>$event_stsl_array[$i]</div>\n";
        $t.= "<div class='nextevent_location'>".$event_location_array[$i]."</div>\n</marquee>\n";
    }

// if no next event - as suggested by oldnema
    elseif ($plugin_tx['calendar']['notice_no_next_event_sceduled'])
    {
        $t.= "<div class='nextevent_date'>" . tag('br') . $plugin_tx['calendar']['notice_no_next_event_sceduled'] . "</div>";
    }

    return $t;
}


// ***
// Helper-function to parse the date-seperator, set in plugin-config,
// to build the config-string for the Date-Picker and to
// check for allowed seperators
// Allowed seperators:
// full-stop ".", forward slash "/" and minus/dash "-"
// ***

function dpSeperator($mode='') {

    global $plugin_cf;

    $sep = $plugin_cf['calendar']['date_delimiter'];
    $dp_sep = ''; //the string to configure the DatePicker

    if ($sep != '/' && $sep != '-') {
        $sep = '.'; //set default
    }

    switch ($sep) {
    case '.':
        $dp_sep = 'dt';
        break;
    case '/':
        $dp_sep = 'sl';
        break;
    case '-':
        $dp_sep = 'ds';
        break;
    }

    if ($mode == 'dp') {
        return $dp_sep;
    }
    else {
        return $sep;
    }
}


// ****************************************************************
// *            Backend, editing the event file                   *
// ****************************************************************

//==========================================================
//makes the form for editing events wide, medium or narrow
//==========================================================
function EventForm($events,$editeventswidth)
{
    global $hjs,$plugin_cf,$plugin_tx,$pth,$datapath,$sl,$plugin,$tx;

    $hjs .= '<script type="text/javascript" src="'
         .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.js">{ "lang":"'.$sl.'" }</script>'."\n";
    $hjs .= tag('link rel="stylesheet" type="text/css" href="'
         .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.css"')."\n";

    $imageFolder = $pth['folder']['plugins'] . $plugin . "/images";

    $columns = 7;
    if ($editeventswidth=="narrow") {$columns = 6;}
    if ($editeventswidth=="wide") {$columns = 9;}
    $columns = $columns - 1;
    $tableclass="calendar_input_" . $editeventswidth;

    $o = "<form method='POST' action='$sn'>\n";
    $o .= "<input type='hidden' value='saveevents' name='action'>\n";
    $o .= "<table class='calendar_input $tableclass'>\n";
    $o .= "<tr>\n";
    $o .= "<td colspan='$columns'><input class='submit' type='submit' value='"
       .  ucfirst($tx['action']['save'])."' name='send'></td>\n";
    $o .= "<td style='text-align:right;width:16px;'><input type='image' src='"
       .  $imageFolder . "/add.png' style='width:16;height:16;' name='add[0]' value='add' alt='Add entry'>\n</td>\n";
    $o .= "</tr>\n";

    //========================
    //narrow width input table
    //========================
    if ($editeventswidth=="narrow") {
        if($plugin_cf['calendar']['show_event_time']=='true'){
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td class='calendar_input_datefield'>"
               .  $plugin_tx[$plugin]['event_start']   . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td class='calendar_input_datefield'>"
               .  $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
               .  "<td> </td>\n"
               .  "</tr>\n";
        } else {
            $o .= "<tr class='firstline_calendarinput'>\n" .
            "<td colspan='2'>" . $plugin_tx[$plugin]['event_start']   . " " . $plugin_tx[$plugin]['event_date'] . "</td>\n" .
            "<td colspan='2'>" . $plugin_tx[$plugin]['event_end']     . " " . $plugin_tx[$plugin]['event_date'] . "</td>\n" .
            "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n" .
            "<td></td>\n" .
            "</tr>\n";
        }
        $i = 0;
        foreach($events as $entry) {
              if($plugin_cf['calendar']['show_event_time']=='true')
              {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

                  $o .= "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n" ;

                  $o .= "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']  . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n" ;
                                                         //3
                  $o .= "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n" ;
              }
              else
              {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

                  $o .= tag('input type="hidden" value="'. $entry['starttime'] . '" name="starttime['.$i.']"') . "\n" ;

                  $o .= "<td style='width:0'></td>";

                  $o .= "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n" ;

                  $o .= tag('input type="hidden"  value="' . $entry['endtime'] . '" name="endtime['.$i.']"') ."\n" ;

                  $o .= "<td style='width:0'></td>";

              }

              $o .= "<td>" . tag('input class="calendar_input_event event_highlighting" type="normal"  value="'
                 .  $entry['event']     . '" name="event['.$i.']"') . "</td>\n";

                 $o .= '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';

            $o .= "<td>"
               .  tag('input type="image" src="'
               .  $imageFolder . '/delete.png" style="width:16;height:16" name="delete['
               .  $i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n";
            if ($plugin_cf['calendar']['show_event_location']=="true")
            {
                $o .= "<tr>\n"
                   .  "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx[$plugin]['event_location'] ."</td>\n"
                   .  "<td>" . tag('input type="normal"  class="calendar_input_event"  value="'
                   .  $entry['location']  . '" name="location['.$i.']"') . "</td>\n<td></td>\n</tr>\n";
            }
            else
            {
                $o .= tag('input type="hidden" value="'.$entry['location'].'" name="location['-$i.']"');
            }

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "</tr>\n<tr>\n"
                   .  "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx['calendar']['event_link'] . "</td>\n"
                   .  "<td>"
                   .  tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                   .  $entry['linkadr'] . '" name="linkadr['.$i.']"') . "</td>\n<td>&nbsp;</td>\n</tr>\n";

                $o .= "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx['calendar']['event_link_txt'] . "</td>\n"
                   .  "<td>"
                   .  tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                   .  $entry['linktxt'] . '" name="linktxt['.$i.']"') . "</td>\n<td></td>\n</tr>\n"
                   .  "<tr><td colspan = '6'>&nbsp;</td></tr>\n";
            }
            else
            {
                $o .= tag('input type="hidden" value="'
                   .  $entry['linkadr']  .'" name="linkadr['.$i.']"')
                   .  tag('input type="hidden" value="'.$entry['linktxt']  .'" name="linktxt['.$i.']"');
            }
            $i++;
        }

    //========================
    // wide width input table
    //========================
    }elseif ($editeventswidth=="wide") {

        if($plugin_cf['calendar']['show_event_time']=='true')
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']   . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";


        }
        else
        {

            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td colspan='2'>" . $plugin_tx[$plugin]['event_start']   . " "
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td colspan='2'>" . $plugin_tx[$plugin]['event_end']     . " "
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";
        }

        if ($plugin_cf['calendar']['show_event_location']=="true")
        {
            $o .= "<td>" . $plugin_tx[$plugin]['event_location'] ."</td>\n";
        }
        else
        {
            $o .= "<td style='width:0'></td>";
        }

        if ($plugin_cf['calendar']['show_event_link']=="true")
        {
            $o .= "<td>" . $plugin_tx[$plugin]['event_link'] .    "</td>\n"
               .  "<td>" . $plugin_tx['calendar']['event_link_txt'] . "</td>\n";
        }
        else
        {
            $o .= "<td style='width:0'></td><td style='width:0'></td>";
        }

        $o .= "<td></td>\n</tr>\n";

        $i = 0;
        foreach($events as $entry)
        {
            $o .= "<tr>\n"
               .  "<td class='calendar_input_datefield'>"
               .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
               .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                $o .= "<td class='calendar_input_time'>"
                   .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                   .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n";
            }
            else
            {
                $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                   .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n";
            }

            $o .= "<td class='calendar_input_datefield'>"
               .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
               .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n";

            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                $o .= "<td class='calendar_input_time'>"
                   .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                   .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n";
            }
            else
            {
                $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                   .  $entry['endtime'] . '" name="endtime['.$i.']"') . "</td>\n";
            }

            $o .=  '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';

            $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
               .  $entry['event']  . '" name="event['.$i.']"') . "</td>\n";

            if ($plugin_cf['calendar']['show_event_location']=="true")
            {
                $o .=    "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   . $entry['location']  . '" name="location['.$i.']"') . "</td>\n";
            }
            else
            {
                    $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                       .  $entry['location'] . '" name="location['.$i.']"') . "</td>";

            }

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   .  $entry['linkadr']   . '" name="linkadr['.$i.']"') . "</td>\n";
                $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   .  $entry['linktxt']   . '" name="linktxt['.$i.']"') . "</td>\n";
            }
            else
            {
                    $o .= "<td style='width:0'>". tag('input type="hidden" value="'.$entry['linkadr']  .'" name="linkadr['.$i.']"') . "</td>"
                       .  "<td style='width:0'>". tag('input type="hidden" value="'.$entry['linktxt']  .'" name="linktxt['.$i.']"') . "</td>";

            }
            $o .= "<td>"
               .  tag('input type="image" src="'
               .  $imageFolder .'/delete.png" style="width:16;height:16" name="delete['.$i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n";
            $i++;
        }

     }
     else
     //==========================
     // medium width input table
     //==========================
     {
        if($plugin_cf['calendar']['show_event_time']=='true')
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']
               .  tag('br') . $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n";

            if($plugin_cf['calendar']['show_event_location']=='true')
            {
                $o .=  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
                   .   "<td>" . $plugin_tx[$plugin]['event_location']. "</td>\n";
            }
            else
            {
                $o .=  "<td colspan='2'>"
                   .   $plugin_tx[$plugin]['event_event']
                   .   "</td>\n";
            }
            $o .= "<td> </td>\n</tr>\n";
        }
        else
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']
               .  " " . $plugin_tx[$plugin]['event_date'] . "</td>\n";

            $o .= "<td style='width:0'></td>";

            $o .= "<td>" . $plugin_tx[$plugin]['event_end']
               .  " " . $plugin_tx[$plugin]['event_date'] . "</td>\n";

            $o .= "<td style='width:0'></td>";

            if($plugin_cf['calendar']['show_event_location']=='true') {
                $o .=  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
                   .   "<td>" . $plugin_tx[$plugin]['event_location']. "</td>\n";
            }
            else
            {
                $o .=  "<td colspan='2'>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";
            }
            $o .= "<td> </td>\n</tr>\n";
        }
        $i = 0;
        foreach($events as $entry)
        {
            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n"
                     .  "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') ."</td>\n"
                     .  "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n";
            }
            else
            {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield' >"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n"
                     .  "<td style='width:0;'>"
                     .  tag('input type="hidden" value="' . $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n"
                     .  "<td style='width:0;'>"
                     .  tag('input type="hidden" value="' . $entry['endtime'] . '" name="endtime['.$i.']"') . "</td>\n";
            }

              $o .=  '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';


            if($plugin_cf['calendar']['show_event_location']=='true')
            {
               $o .= "<td>" . tag('input type="normal"  class="calendar_input_event event_highlighting" value="'
                  .  $entry['event']  . '" name="event['.$i.']"') . "</td>\n"
                  .  "<td>" . tag('input type="normal"  class="calendar_input_event" value="'
                  .  $entry['location']. '" name="location['.$i.']"') . "</td>\n";
            }
            else
            {
               $o .= "<td colspan='2'>" . tag('input type="normal"  class="calendar_input_event event_highlighting" value="'
                  .  $entry['event']  . '" name="event['.$i.']"') . "\n"
                  .  tag('input type="hidden" value="' .  $entry['location']. '" name="location['.$i.']"') . "</td>\n";
            }

            $o .= "<td style='text-align:right;'>"
               .  tag('input type="image" src="'.$imageFolder
               .  '/delete.png" style="width:16;height:16" name="delete['.$i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n" ;

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "<tr>\n"
                   . "<td class='calendarinput_line2' colspan='4'>".$plugin_tx['calendar']['event_link']." / "
                   . $plugin_tx['calendar']['event_link_txt'] . "</td>\n"
                   . "<td>" . tag('input type="normal" class="calendar_input_event" value="' . $entry['linkadr']
                   . '" name="linkadr['.$i.']"') . "</td>\n"
                   . "<td>" . tag('input type="normal" class="calendar_input_event" value="'
                   . $entry['linktxt']  . '" name="linktxt['.$i.']"') . "</td>\n"
                   . "<td>&nbsp;</td>\n</tr>\n";
            }
            else
            {
                $o .= "<input type='hidden' value='".$entry['linkadr']  ."' name='linkadr[$i]'>"
                   .  "<input type='hidden' value='".$entry['linktxt']  ."' name='linktxt[$i]'>" ;
            }
            $i++;
        }
    }
    $o .= "<tr>\n";
    $o .= "<td colspan=$columns><input class='submit' type='submit' value='" . ucfirst($tx['action']['save'])."' name='send'></td>\n";
    $o .= "<td><input type='image' src='$imageFolder/add.png' style='width:16;height:16;' name='add[0]' value='add' alt='Add entry'>\n</td>\n";
    $o .= "</tr>\n";
    $o .= "</table>\n";
    $o .= "</form>";
    return $o;
}


//function to edit the eventfile
function EditEvents($editeventswidth)
{
    global $plugin_cf,$plugin_tx,$pth,$sl,$plugin,$tx;
    if (!$editeventswidth) {$editeventswidth = $plugin_cf['calendar']['event-input_memberpages_narrow_medium_or_wide'];}
    $imageFolder = $pth['folder']['plugins'] . $plugin . "/images";
    $events = (new Calendar\EventDataService)->readEvents();

    if(isset($_POST['action']))
      $action = $_POST['action'];
    elseif(isset($_GET['action']))
      $action = $_GET['action'];
    else
      $action = "editevents";


    if ($action == "editevents" || $action == "plugin_text") {
        $o .= EventForm($events,$editeventswidth);
     } elseif($action == 'saveevents') {

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

          foreach(array('datestart', 'starttime', 'dateend', 'endtime','event', 'location', 'linkadr', 'linktxt') as $var) {
              $$var = array_map('stsl', $$var);
          }

          $deleted = false;
          $added   = false;

          $newevent = array();
          foreach($event as $j => $i){
            if(!isset($delete[$j]) || $delete[$j] == '') {

              //Checking the date format. Some impossible dates can be given, but don't hurt.
              $pattern = '/[\d\d\|\?{1-2}|\-{1-2}]\\'.dpSeperator().'\d\d\\'.dpSeperator().'\d{4}$/';
              if (!preg_match($pattern,$datestart[$j])) $datestart[$j] = "";
              if (!preg_match($pattern,$dateend[$j])) $dateend[$j] = "";

              //Birthday should never have an enddate
              if ($location[$j]=="###") $dateend[$j] = '';

              $entry = array(
                'datestart'  => str_replace(';',' ',$datestart[$j]),
                'starttime'  => str_replace(';',' ',$starttime[$j]),
                'dateend'    => str_replace(';',' ',$dateend[$j]),
                'endtime'    => str_replace(';',' ',$endtime[$j]),
                'event'      => str_replace(';',' ',$event[$j]),
                'location'   => str_replace(';',' ',$location[$j]),
                'linkadr'    => str_replace(';',' ',$linkadr[$j]),
                'linktxt'    => str_replace(';',' ',$linktxt[$j]));
              $newevent[] = $entry;
            } else
              $deleted = true;
          }
          if($add <> '') {
            $entry = array(
              'datestart'   => date("d").dpSeperator().date("m").dpSeperator().date("Y"),
              'starttime'   => "",
              'dateend'     => "",
              'endtime'     => "",
              'event'       => $plugin_tx[$plugin]['event_event'],
              'location'    => "",
              'linkadr'     => "",
              'linktxt'     => "");
            $newevent[] = $entry;
            $added = true;
          }

          if(!$deleted && !$added) {
              // sorting new event inputs, idea of manu, forum-message
              usort($newevent,'dateSort');
              if(!(new Calendar\EventDataService)->writeEvents($newevent)) $o .= "<p><strong>".$plugin_tx['calendar']['eventfile_not_saved']."</strong></p>\n";
              else  $o .= "<p><strong>".$plugin_tx['calendar']['eventfile_saved']."</strong></p>\n";
          }

          $editeventstableclass="calendar_input";
          if ($editeventswidth=="wide") {$editeventstableclass="calendar_input_wide";}

          $o .= EventForm($newevent,$editeventswidth);


    }
    return $o;
}

     // function by manu
    function dateSort($a, $b){
       $pattern = '!(.*)\\'.dpSeperator().'(.*)\\'.dpSeperator().'(.*)!';
       $replace = '\3\2\1';
       $a_i = preg_replace($pattern,$replace,$a['datestart']).$a['starttime'];
       $b_i = preg_replace($pattern,$replace,$b['datestart']).$b['starttime'];
       if ($a_i == $b_i) return 0;
       return ($a_i < $b_i) ? -1 : 1;
    }

?>
