<?php

$plugin_tx['calendar']['menu_main']="Events bearbeiten";

$plugin_tx['calendar']['monthnames_array']="Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember";
$plugin_tx['calendar']['daynames_array']="So,Mo,Di,Mi,Do,Fr,Sa";
$plugin_tx['calendar']['daynames_array_full']="Sonntag,Montag,Dienstag,Mittwoch,Donnerstag,Freitag,Samstag";
$plugin_tx['calendar']['event_page']="Veranstaltungsliste";
$plugin_tx['calendar']['event_date']="Datum";
$plugin_tx['calendar']['event_time']="Uhrzeit";
$plugin_tx['calendar']['event_summary']="Übersicht";
$plugin_tx['calendar']['event_location']="Ort";
$plugin_tx['calendar']['event_link']="Link";
$plugin_tx['calendar']['event_link_etc']="Link etc.";
$plugin_tx['calendar']['event_started']="Veranstaltung begann:";
$plugin_tx['calendar']['event_date_start']="Anfangsdatum";
$plugin_tx['calendar']['event_date_end']="Enddatum";

$plugin_tx['calendar']['birthday_text']="Geburtstag";
$plugin_tx['calendar']['age_0']="%d Jahre alt";
$plugin_tx['calendar']['age_1']="%d Jahr alt";
$plugin_tx['calendar']['age_2_4']="%d Jahre alt";
$plugin_tx['calendar']['age_5']="%d Jahre alt";
$plugin_tx['calendar']['prev_button_text']="◄";
$plugin_tx['calendar']['next_button_text']="►";
$plugin_tx['calendar']['prev_button_title']="Vorheriger Monat";
$plugin_tx['calendar']['next_button_title']="Nächster Monat";
$plugin_tx['calendar']['notice_no_next_event']="Keine weitere Veranstaltung eingeplant.";

$plugin_tx['calendar']['event_list_heading']="Termine für den Zeitraum %s bis %s";

$plugin_tx['calendar']['format_month_year']="%F %Y";
$plugin_tx['calendar']['format_date']="%j. %n. %Y";
$plugin_tx['calendar']['format_date_time']="%j. %n. %Y %G:%i";
$plugin_tx['calendar']['format_time']="%G:%i";
$plugin_tx['calendar']['format_date_interval']="%s bis %s";
$plugin_tx['calendar']['format_time_interval']="%s - %s";

//new in version 1.1 & 1.2
$plugin_tx['calendar']['event_date_till_date']="bis";
$plugin_tx['calendar']['event_link_txt']="Link-Text oder anderer Text";

$plugin_tx['calendar']['eventfile_saved']="Geänderte Veranstaltungsdaten gespeichert . . .";
$plugin_tx['calendar']['eventfile_not_saved']="Unbekannter FEHLER: Änderungen konnten NICHT gespeichert werden.";

$plugin_tx['calendar']['label_new']="Neu";
$plugin_tx['calendar']['label_edit']="Bearbeiten";
$plugin_tx['calendar']['label_delete']="Löschen";
$plugin_tx['calendar']['label_description']="Beschreibung";
$plugin_tx['calendar']['label_save']="Speichern";
$plugin_tx['calendar']['label_import_export']="Import/Export";
$plugin_tx['calendar']['label_import']="Import";
$plugin_tx['calendar']['label_export']="Export";
$plugin_tx['calendar']['label_full_day']="ganztägig";
$plugin_tx['calendar']['label_recur']="Wiederholen";
$plugin_tx['calendar']['label_recur_none']="nein";
$plugin_tx['calendar']['label_recur_yearly']="jährlich";

$plugin_tx['calendar']['message_ignored_0']="Alle Veranstaltungen wurden importiert!";
$plugin_tx['calendar']['message_ignored_1']="Eine Veranstaltung konnte nicht importiert werden!";
$plugin_tx['calendar']['message_ignored_2_4']="%d Veranstaltungen konnten nicht importiert werden!";
$plugin_tx['calendar']['message_ignored_5']="%d Veranstaltungen konnten nicht importiert werden!";

$plugin_tx['calendar']['error_export']="Konnte nicht nach calendar.ics exportieren!";
$plugin_tx['calendar']['error_unauthorized']="Sie sind nicht befugt diese Aktion auszuführen!";

$plugin_tx['calendar']['syscheck_extension']="die PHP-Erweiterung '%s' geladen ist";
$plugin_tx['calendar']['syscheck_fail']="Fehler";
$plugin_tx['calendar']['syscheck_message']="Prüfe, dass %1\$s … %2\$s";
$plugin_tx['calendar']['syscheck_phpversion']="die PHP-Version ≥ %s";
$plugin_tx['calendar']['syscheck_plugin']="das CMSIMPLE_XH Plugin '%s' installiert ist";
$plugin_tx['calendar']['syscheck_success']="OK";
$plugin_tx['calendar']['syscheck_title']="System-Prüfung";
$plugin_tx['calendar']['syscheck_warning']="Warnung";
$plugin_tx['calendar']['syscheck_writable']="'%s' schreibbar ist";
$plugin_tx['calendar']['syscheck_xhversion']="die CMSimple_XH-Version ≥ %s";

$plugin_tx['calendar']['cf_week_starts_mon']="Ob die Kalenderwoche am Montag beginnt. Andernfalls beginnt sie am Sonntag.";
$plugin_tx['calendar']['cf_prev_next_button']="Ob beim Kalender Navigationsmöglichkeiten für einen Monat vor/zurück angezeigt werden sollen.";
$plugin_tx['calendar']['cf_eventlist_template']="Das Template, das für die Veranstaltungsliste verwendet wird. 'eventlist' ist das klassische Tabellen-Layout; 'eventlist_new' hat ein div basiertes Layout (per Voreinstellung vertikal).";
$plugin_tx['calendar']['cf_nextevent_orientation']="Die Rollrichtung der Laufschrift für die nächste Veranstaltung. Entweder 'vertical' für vertikalen Bildlauf, oder 'horizontal' für horizontalen Bildlauf.";
$plugin_tx['calendar']['cf_edit_editor_init']="Die Init (Konfiguration) des HTML-Editors, der für die Veranstaltungsbeschreibungen verwendet wird. Je nach verwendetem Editor sind unterschiedliche Inits verfügbar; normalerweise wenigstens 'minimal' und 'full'. Wird die konfigurierte Init vom Editor nicht unterstützt, wird nur ein Textfeld angezeigt.";
$plugin_tx['calendar']['cf_show_days_between_dates']="Ob im Kalender bei mehrtägigen Veranstaltungen alle Tage eingefärbt werden.";
$plugin_tx['calendar']['cf_date_delimiter']="Zeichen zwischen den Datumsziffern, also zwischen Tag und Monat und Monat und Jahr. Möglich sind \".\", \"-\", \"/\". Dies wird nur für den Import der alten calendar.txt Dateien verwendet.";
$plugin_tx['calendar']['cf_show_event_time']="Ob die Spalte Uhrzeit in der klassischen Veranstaltungsliste angezeigt werden soll";
$plugin_tx['calendar']['cf_show_event_location']="Ob die Spalte Ort in der klassischen Veranstaltungsliste angezeigt werden soll.";
$plugin_tx['calendar']['cf_show_event_link']="Ob die Spalte Link in der klassischen Veranstaltungsliste angezeigt werden soll.";
$plugin_tx['calendar']['cf_week-end_day_1']="Hier den Tag in der Kalenderansicht eintragen, der als Wochenendtag eingefärbt werden soll. \"0\" ist dabei der erste Tag und \"6\" der letzte.";
$plugin_tx['calendar']['cf_week-end_day_2']="Wie bei weekend day 1 wird hier der zweite Wochenendtag angegeben.";
$plugin_tx['calendar']['cf_show_number_of_future_months']="Anzahl der Monate, für die die Veranstaltungsvorschau gezeigt werden soll. Voreingestellt ist 1 für eien Monat.";
$plugin_tx['calendar']['cf_show_number_of_previous_months']="Wenn man auch Veranstaltungen aus der Vergangenheit im Veranstaltungskalender zeigen möchte, kann man hier eine Zahl eintragen";
$plugin_tx['calendar']['cf_show_period_of_events']="Ob am Anfang der Veranstaltungsübersicht der gezeigte Zeitraum angegeben werden soll.";
$plugin_tx['calendar']['cf_same-event-calendar_for_all_languages']="Ob bei mehrsprachigen Websites nur eine Kalenderdatei angelegt wird, die für alle Sprachversionen genutzt wird.";
