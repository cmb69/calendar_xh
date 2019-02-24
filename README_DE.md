# Calendar\_XH

Calendar\_XH ermöglicht die Administration und die Anzeige von
Veranstaltungskalendern und -listen auf CMSimple\_XH Websites.

Diese Version ist ein Fork von Calendar 1.2.10. Es ist zu beachten, dass
svasti Calendar 1.2 weiter entwickelt hat, und dass [diese
Version](https://github.com/cmsimple-xh/calendar) eine große Anzahl
zusätzlicher Features anbietet, allerdings zu Lasten der Einfachheit.

## Inhaltsverzeichnis

  - [Voraussetzungen](#voraussetzungen)
  - [Installation](#installation)
  - [Einstellungen](#einstellungen)
  - [Verwendung](#verwendung)
      - [Veranstaltungs-Editor](#veranstaltungs-editor)
      - [Kalender](#kalender)
      - [Veranstaltungsliste](#veranstaltungsliste)
      - [Nächste Veranstaltung](#nächste-veranstaltung)
      - [Import](#import)
  - [Beschränkungen](#beschränkungen)
  - [Lizenz](#lizenz)
  - [Danksagung](#danksagung)

## Voraussetzungen

Calendar\_XH benötigt CMSimple\_XH ≥ 1.6.3 mit dem Fa\_XH Plugin, und
PHP ≥ 5.5.4.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple\_XH-Plugins
auch. Im [CMSimple\_XH
Wiki](https://wiki.cmsimple-xh.org/doku.php/de:installation#plugins) finden
sie ausführliche Hinweise.

1.  Sichern Sie die Daten auf Ihrem Server.
2.  Entpacken Sie die ZIP-Datei auf Ihrem Computer.
3.  Laden Sie das gesamte Verzeichnis `calendar/` auf Ihren Server in
    das `plugins/` Verzeichnis von CMSimple\_XH hoch.
4.  Vergeben Sie Schreibrechte für die Unterverzeichnisse `config/`,
    `css/` und `languages/`.
5.  Navigieren Sie zu *Plugins* → *Calendar* im Administrationsbereich,
    und prüfen Sie, ob alle Voraussetzungen für den Betrieb erfüllt
    sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple\_XH-Plugins auch im Administrationsbereich der Homepage. Wählen
Sie *Plugins* → *Calendar*.

Sie können die Original-Einstellungen von Calendar\_XH in der
*Konfiguration* ändern. Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.

Die Lokalisierung wird unter *Sprache* vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen, falls keine
entsprechende Sprachdatei zur Verfügung steht, oder sie entsprechend
Ihren Anforderungen anpassen.

Das Aussehen von Calendar\_XH kann unter *Stylesheet* angepasst werden.

## Verwendung

### Veranstaltungs-Editor

Die Administration der Veranstaltungen erfolgt im Backend (*Plugins* →
*Calendar* → *Events bearbeiten*). Es ist zu beachten, dass das
Hinzufügen und Löschen von Veranstaltungen zunächst nur vorübergehend
wirkt; um die Änderungen permanent zu übernehmen, müssen die
Veranstaltungen gespeichert werden.

Alternativ kann der Veranstaltungs-Editor auf einer normalen
CMSimple\_XH Seite eingebettet werden, so dass die Veranstaltungen
ebenfalls von Nicht-Admins bearbeitet werden können. Dazu wird der
folgende Pluginaufruf verwendet:

    {{{editevents()}}}

Das Einbetten des Veranstaltungs-Editors auf einer Seite sollte nur
erfolgen, wenn der Zugriff auf diese Seite Authorisierung durch
[Register\_XH](https://github.com/cmb69/register_xh) oder
[Memberpages](https://github.com/cmsimple-xh/memberpages) erfordert.

Zusätzlich zu normalen Veranstaltungen können ebenfalls *Geburtstage*
definiert werden, indem `###` als Ort, der Name des Geburtstagskinds als
Veranstaltung, und das Geburtsdatum als Anfangsdatum eingetragen wird.

### Kalender

Veranstaltungskalender können vom Template aus angezeigt werden:

    <?=calendar()?>

oder nur auf einer Seite:

    {{{calendar()}}}

Alle definierten Veranstaltungen des aktuellen Monats werden im Kalendar
hervorgehoben und zu der Veranstaltungsseite (*event page*) verlinkt,
die in den Spracheinstellungen von Calendar\_XH gewählt wurde.

### Veranstaltungsliste

Die Veranstaltungsliste sollte auf der Veranstaltungsseite (*event
page*), die in den Spracheinstellungen von Calendar\_XH gewählt wurde,
durch den folgenden Pluginaufruf eingebettet werden:

    {{{events()}}}

### Nächste Veranstaltung

Optional kann die nächste geplante Veranstaltung als Lauftext
(*marquee*) angezeigt werden, entweder im Template:

    <?=nextevent()?>

oder auf einer Seite:

    {{{nextevent()}}}

### Import

Um vorhandene `.ics` Dateien (iCalendar-Format) zu importieren, müssen
diese zunächst im `content/` Ordner (neben der `calendar.csv` Datei)
abgelegt werden. Unter *Plugins* → *Calendar* → *Import* kann dann der
eigentliche Import der Datei(en) durchgeführt werden. Es ist zu
beachten, dass die importierten Events als neue Events hinzugefügt
werden; mehrfacher Import der gleichen `.ics` Datei fügt alle Events
mehrfach hinzu.

## Beschränkungen

Calendar\_XH ist nicht für viele Veranstaltungen geeignet. In
Abhängigkeit vom Server sollten ca. 100 Veranstaltungen kein Problem
darstellen, aber bei mehr Veranstaltungen könnte es zu Fehlfunktionen
kommen.

## Lizenz

Calendar\_XH kann unter Einhaltung der
[GPLv3](http://www.gnu.org/licenses/gpl.html) verwendet werden.

Copyright © 2005-2006 Michael Svarrer  
Copyright © 2007-2008 Tory  
Copyright © 2008 Patrick Varlet  
Copyright © 2011 Holger Irmler  
Copyright © 2011-2013 Frank Ziesing  
Copyright © 2017-2018 Christoph M. Becker

## Danksagung

Das Calender Plugin für CMSimple wurde ursprünglich von Michael Svarrer
entwickelt, und dann von Tory, Patrick Varlet, Holger Irmler und Frank
Ziesing verbessert. Vielen Dank an all diese Entwickler\!

Das Plugin-Logo wurde von [Alessandro Rei](http://www.mentalrey.it/)
entworfen. Vielen Dank für die Veröffentlichung unter GPL.

Zu guter Letzt vielen Dank an [Peter Harteg](http://harteg.dk/), den
"Vater" von CMSimple, und allen Entwicklern von
[CMSimple\_XH](http://www.cmsimple-xh.org/), ohne die dieses
fantastische CMS nicht existieren würde.
