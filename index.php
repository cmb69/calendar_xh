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
    ob_start();
    (new Calendar\NextEventController)->defaultAction();
    return ob_get_clean();
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



//function to edit the eventfile
function EditEvents($editeventswidth)
{
    ob_start();
    (new Calendar\EditEventsController($editeventswidth))->defaultAction();
    return ob_get_clean();
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
