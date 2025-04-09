# Calendar\_XH

Calendar\_XH facilitates the administration and display of event
calendars and lists on CMSimple\_XH websites.

This version is a fork of Calendar 1.2.10. Note that svasti has
developed Calendar 1.2 further and [that
version](https://github.com/cmsimple-xh/calendar) offers plenty more
features at the cost of greatly increased complexity.

## Table of Contents

  - [Requirements](#requirements)
  - [Download](#download)
  - [Installation](#installation)
  - [Settings](#settings)
  - [Usage](#usage)
    - [Date and Time Formats](#date-and-time-formats)
    - [Event Editor](#event-editor)
    - [Calendars](#calendars)
    - [Event List](#event-list)
    - [Next Event](#next-event)
    - [Import/Export](#importexport)
  - [Troubleshooting](#troubleshooting)
  - [License](#license)
  - [Credits](#credits)

## Requirements

Calendar_XH is a plugin for [CMSimple_XH](https://www.cmsimple-xh.org/).
It requires CMSimple_XH ≥ 1.7.0, and PHP ≥ 7.1.0.
Calendar_XH also requires [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.6;
if that is not already installed (see *Settings*→*Info*),
get the [lastest release](https://github.com/cmb69/plib_xh/releases/latest),
and install it.

## Download

The [lastest release](https://github.com/cmb69/calendar_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins. See
the [CMSimple\_XH
wiki](https://wiki.cmsimple-xh.org/doku.php/installation#plugins) for further
details.

1.  Backup the data on your server.
2.  Unzip the distribution on your computer.
3.  Upload the whole directory `calendar/` to your server into
    CMSimple\_XH's `plugins/` directory.
4.  Set write permissions for the subdirectories `config/`, `css/` and
    `languages/`.
5.  Navigate to *Plugins* → *Calendar* in the back-end to check if all
    requirements are fulfilled.

## Settings

The plugin's configuration is done as with many other CMSimple\_XH
plugins in the website's back-end. Select *Plugins* → *Calendar*.

You can change the default settings of Calendar\_XH under *Config*.
Hints for the options will be displayed when hovering over the help icon
with your mouse.

Localization is done under *Language*. You can translate the character
strings to your own language if there is no appropriate language file
available, or customize them according to your needs.

The look of Calendar\_XH can be customized under *Stylesheet*.

## Usage

### Date and Time Formats

The format of date and times shown to users can be configured
under *Plugins* → *Calendar* → *Language* → *Format*.
The entries for *Month year*, *Date*, *Date time* and *Time*
accept arbitrary text, plus placeholders.
The following placeholders are supported:

- `%Y`: full numeric representation of a year, 4 digits
- `%F`: full textual representation of a month, such as `January` or `March`
- `%m`: numeric representation of a month, 2 digits
- `%n`: numeric representation of a month, without leading zeros
- `%d`: day of the month, 2 digits
- `%j`: day of the month without leading zeros
- `%a`: lowercase ante meridiem and post meridiem, respectively
- `%g`: 12-hour format of an hour without leading zeros
- `%H`: 24-hour format of an hour, 2 digits
- `%G`: 24-hour format of an hour without leading zeros
- `%i`: minutes with leading zeros, 2 digits

### Event Editor

The administration of the events is done in the back-end (*Plugins* →
*Calendar* → *Edit Events*).

Alternatively, you can embed the event editor on a normal CMSimple\_XH
page, so the events can be edited by non-admins also. Use the following
plugin call:

    {{{editevents()}}}

Embedding the event editor on a page should only be done if access to
this page requires authorization via
[Register\_XH](https://github.com/cmb69/register_xh) or
[Memberpages](https://github.com/cmsimple-xh/memberpages).

Besides normal events, it is also possible to define *birthdays* by
entering `###` as location, the name of the birthday child as event and
the date of birth as start date.

As of Calendar_XH 2.6, the `description` field (still called `link`
in the front-end for backward compatibility reasons), accepts arbitrary
HTML.  It is strongly recommended to keep the descriptions short, and
use HTML markup sparingly; otherwise the event list may look ugly.

### Calendars

You can show the event calendar either from the template:

    <?=calendar()?>

or only on a page:

    {{{calendar()}}}

All defined events of the current month are highlighted in the calendar,
and linked to the *event page* that is defined in the language settings
of Calendar\_XH.

### Event List

The event list is supposed to be embedded on the *event page* that is
defined in the language settings of Calendar\_XH with the following
plugin call:

    {{{events()}}}

The classic event list is displayed as table, so it may not look great,
depending on the template, the event data, and the browser.
You can suppress the output of the `time`, `location` and `link` columns
under `Plugins` → `Calendar` → `Config` → `Show`, what may mitigate the issue.
However, in this case not all relevant information about the events
might be available to the user.

An alternative is to switch to `eventlist_new` under
`Plugins` → `Calendar` → `Config` → `Eventlist` → `Template`.
The new style event list will be displayed as a real list, so all information
can be shown without looking ugly. 

Note that the new style event list adds structured data according to
<https://schema.org/Event> for SEO purposes.

### Next Event

Optionally, you can show the next scheduled event in a *marquee* like manner, either
from the template:

    <?=nextevent()?>

or on a page:

    {{{nextevent()}}}

### Import/Export

To import existing `.ics` files (iCalendar format), you have to put them
in the `content/calendar/` folder (right besides the `calendar.2.6.csv` file).
Then navigate to `Plugins` → `Calendar` → `Import/Export` where you can actually
import the desired file(s). Note that only a very minimalistic import is
implemented so far. Also note that no synchronization is being done on import.

Under `Plugins` → `Calendar` → `Import/Export` you can also export the
calendar to calendar.ics (iCalendar format).  The file will be created
in the `content/calendar/` folder (right besides the `calendar.2.6.csv` file).

## Troubleshooting
Report bugs and ask for support either on [Github](https://github.com/cmb69/calendar_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Calendar\_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Calendar\_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Calendar\_XH.  If not, see <http://www.gnu.org/licenses/>.

Copyright © 2005-2006 Michael Svarrer  
Copyright © 2007-2008 Tory  
Copyright © 2008 Patrick Varlet  
Copyright © 2011 Holger Irmler  
Copyright © 2011-2013 Frank Ziesing  
Copyright © 2017-2023 Christoph M. Becker

## Credits

The Calendar plugin for CMSimple has originally be developed by Michael
Svarrer, and then been improved by Tory, Patrick Varlet, Holger Irmler
and Frank Ziesing. Many thanks to all these developers\!

The plugin logo has been designed by [Alessandro
Rei](http://www.mentalrey.it/). Many thanks for publishing this icon
under GPL.

And last but not least many thanks to [Peter Harteg](http://harteg.dk/),
the father of CMSimple, and all developers of
[CMSimple\_XH](http://www.cmsimple-xh.org) without whom this amazing CMS
wouldn't exist.
