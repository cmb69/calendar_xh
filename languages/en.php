<?php

$plugin_tx['calendar']['menu_main']="Edit Events";

$plugin_tx['calendar']['monthnames_array']="January,February,March,April,May,June,July,August,September,Oktober,November,December";
$plugin_tx['calendar']['daynames_array']="Su,Mo,Tu,We,Th,Fr,Sa";
$plugin_tx['calendar']['daynames_array_full']="Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday";
$plugin_tx['calendar']['event_page']="Events";
$plugin_tx['calendar']['event_date']="Date";
$plugin_tx['calendar']['event_time']="Time";
$plugin_tx['calendar']['event_summary']="Summary";
$plugin_tx['calendar']['event_location']="Location";
$plugin_tx['calendar']['event_link']="Link";
$plugin_tx['calendar']['event_link_etc']="Link etc.";
$plugin_tx['calendar']['event_started']="Event started:";
$plugin_tx['calendar']['event_date_start']="Start Date";
$plugin_tx['calendar']['event_date_end']="End Date";

$plugin_tx['calendar']['birthday_text']="Birthday";
$plugin_tx['calendar']['age_0']="%d years";
$plugin_tx['calendar']['age_1']="%d year";
$plugin_tx['calendar']['age_2_4']="%d years";
$plugin_tx['calendar']['age_5']="%d years";
$plugin_tx['calendar']['prev_button_text']="◄";
$plugin_tx['calendar']['next_button_text']="►";
$plugin_tx['calendar']['prev_button_title']="Previous month";
$plugin_tx['calendar']['next_button_title']="Next month";
$plugin_tx['calendar']['notice_no_next_event']="No further event scheduled.";

$plugin_tx['calendar']['event_list_heading']="Events in the period from %s till %s";

$plugin_tx['calendar']['format_month_year']="%F %Y";
$plugin_tx['calendar']['format_date']="%n/%j/%Y";
$plugin_tx['calendar']['format_date_time']="%n/%j/%Y %g:%i %a";
$plugin_tx['calendar']['format_time']="%g:%i %a";
$plugin_tx['calendar']['format_date_interval']="%s till %s";
$plugin_tx['calendar']['format_time_interval']="%s - %s";

//new in version 1.1 & 1.2
$plugin_tx['calendar']['event_date_till_date']="till";
$plugin_tx['calendar']['event_link_txt']="Link text";

$plugin_tx['calendar']['eventfile_saved']="Changes in event data saved . . .";
$plugin_tx['calendar']['eventfile_not_saved']="ERROR: could not save event data.";

$plugin_tx['calendar']['label_new']="New";
$plugin_tx['calendar']['label_ids']="Generate IDs";
$plugin_tx['calendar']['label_edit']="Edit";
$plugin_tx['calendar']['label_edit_single']="Edit single occurrence";
$plugin_tx['calendar']['label_delete']="Delete";
$plugin_tx['calendar']['label_description']="Description";
$plugin_tx['calendar']['label_save']="Save";
$plugin_tx['calendar']['label_import_export']="Import/Export";
$plugin_tx['calendar']['label_import']="Import";
$plugin_tx['calendar']['label_export']="Export";
$plugin_tx['calendar']['label_full_day']="full-day";
$plugin_tx['calendar']['label_occurrence']="Occurrence";
$plugin_tx['calendar']['label_recur']="Recur";
$plugin_tx['calendar']['label_recur_none']="none";
$plugin_tx['calendar']['label_recur_daily']="daily";
$plugin_tx['calendar']['label_recur_weekly']="weekly";
$plugin_tx['calendar']['label_recur_yearly']="yearly";
$plugin_tx['calendar']['label_recur_until']="until";

$plugin_tx['calendar']['message_ids_0']="All events have an ID.";
$plugin_tx['calendar']['message_ids_1']="There is one event without ID.";
$plugin_tx['calendar']['message_ids_2_4']="There are %d events without ID.";
$plugin_tx['calendar']['message_ids_5']="There are %d events without ID.";
$plugin_tx['calendar']['message_generate_ids']="Do you want to generate the missing IDs?";
$plugin_tx['calendar']['message_ignored_0']="All events have been imported!";
$plugin_tx['calendar']['message_ignored_1']="One event could not be imported!";
$plugin_tx['calendar']['message_ignored_2_4']="%d events could not be imported!";
$plugin_tx['calendar']['message_ignored_5']="%d events could not be imported!";

$plugin_tx['calendar']['error_invalid_event']="The event is invalid!";
$plugin_tx['calendar']['error_export']="Could not export to calendar.ics!";
$plugin_tx['calendar']['error_unauthorized']="You are not authorized for this action!";
$plugin_tx['calendar']['error_split']="There is no occurrence of the event on this date!";

$plugin_tx['calendar']['syscheck_extension']="the PHP extension '%s' is loaded";
$plugin_tx['calendar']['syscheck_fail']="failure";
$plugin_tx['calendar']['syscheck_message']="Checking that %1\$s … %2\$s";
$plugin_tx['calendar']['syscheck_phpversion']="PHP version ≥ %s";
$plugin_tx['calendar']['syscheck_plugin']="the CMSimple_XH plugin '%s' is installed";
$plugin_tx['calendar']['syscheck_success']="okay";
$plugin_tx['calendar']['syscheck_title']="System check";
$plugin_tx['calendar']['syscheck_warning']="warning";
$plugin_tx['calendar']['syscheck_writable']="'%s' is writable";
$plugin_tx['calendar']['syscheck_xhversion']="CMSimple_XH version ≥ %s";

$plugin_tx['calendar']['cf_week_starts_mon']="Wether Monday is the first day of the calendar week. Otherwise the week starts on Sunday.";
$plugin_tx['calendar']['cf_prev_next_button']="Whether there will be back and forward links in the calendar around the month name.";
$plugin_tx['calendar']['cf_eventlist_template']="The template to use for the event list. 'eventlist' is the classic table layout; 'eventlist_new' has a div based layout (vertical by default).";
$plugin_tx['calendar']['cf_nextevent_orientation']="The scrolling direction of the next event marquee. Either 'vertical' for vertical scrolling, or 'horizontal' for horizontal scrolling.";
$plugin_tx['calendar']['cf_edit_editor_init']="The init (configuration) HTML editor used for the event descriptions. Depending  on the editor, different inits are available, typically at least 'minimal' and 'full'. If the editor does not support the configured init, only a textarea will be shown.";
$plugin_tx['calendar']['cf_show_days_between_dates']="Whether to color all the days of multi day events in the calendar display.";
$plugin_tx['calendar']['cf_show_event_time']="Whether to display beginning and ending time in the classic event list.";
$plugin_tx['calendar']['cf_show_event_location']="Whether to display the location in the classic event list.";
$plugin_tx['calendar']['cf_show_event_link']="Whether to display links in the classic event list.";
$plugin_tx['calendar']['cf_week-end_day_1']="Put here the day of the week which you would like to get the weekend color. \"0\" being the first and \"6\" the last day of the week.";
$plugin_tx['calendar']['cf_week-end_day_2']="Same als weekend day 1.";
$plugin_tx['calendar']['cf_show_number_of_future_months']="Number of future months which are to be shown in the event display. Default is present month, \"2\" results in present and next month.";
$plugin_tx['calendar']['cf_show_number_of_previous_months']="To show also past events in the event list, enter the number of previous month to be shown";
$plugin_tx['calendar']['cf_show_period_of_events']="Whether to display the period of events above the event list.";
$plugin_tx['calendar']['cf_same-event-calendar_for_all_languages']="Whether the same event file will be used for all languages in multi language sites. By default every language has its own event calendar.";
