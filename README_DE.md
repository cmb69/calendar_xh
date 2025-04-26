# Calendar\_XH

Calendar\_XH ermöglicht die Administration und die Anzeige von
Veranstaltungskalendern und -listen auf CMSimple\_XH Websites.

Diese Version ist ein Fork von Calendar 1.2.10. Es ist zu beachten, dass
svasti Calendar 1.2 weiter entwickelt hat, und dass [diese
Version](https://github.com/cmsimple-xh/calendar) eine große Anzahl
zusätzlicher Features anbietet, allerdings zu Lasten der Einfachheit.

## Inhaltsverzeichnis

  - [Voraussetzungen](#voraussetzungen)
  - [Download](#download)
  - [Installation](#installation)
  - [Einstellungen](#einstellungen)
  - [Verwendung](#verwendung)
    - [Datum und Zeit-Formate](#datum-und-zeit-formate)
    - [Veranstaltungs-Editor](#veranstaltungs-editor)
    - [Kalender](#kalender)
    - [Große Kalender](#große-kalender)
    - [Veranstaltungsliste](#veranstaltungsliste)
    - [Nächste Veranstaltung](#nächste-veranstaltung)
    - [Veranstaltungsseiten](#veranstaltungsseiten)
    - [Import/Export](#importexport)
  - [Fehlerbehebung](#fehlerbehebung)
  - [Lizenz](#lizenz)
  - [Danksagung](#danksagung)

## Voraussetzungen

Calendar_XH ist ein Plugin für [CMSimple_XH](https://www.cmsimple-xh.org/de/).
Es benötigt CMSimple_XH ≥ 1.7.0, und PHP ≥ 7.1.0.
Calendar_XH benötigt weiterhin [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.6;
ist dieses noch nicht installiert (see *Einstellungen*→*Info*),
laden Sie das [aktuelle Release](https://github.com/cmb69/plib_xh/releases/latest)
herunter, und installieren Sie es.

## Download

Das [aktuelle Release](https://github.com/cmb69/calendar_xh/releases/latest)
kann von Github herunter geladen werden.

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

### Datum und Zeit-Formate

Das Format der Datums- und Zeitangaben, die Nutzern angezeigt werden,
kann unter *Plugins* → *Calendar* → *Sprache* → *Format* eingestellt werden.
Die Einträge für *Month year*, *Date*, *Date time* und *Time*
akzeptieren beliebigen Text und Platzhalter.
Folgende Platzhalter werden unterstützt:

- `%Y`: vollständige numerische Repräsentation eines Jahres, 4-stellig
- `%F`: vollständige textuelle Repräsentation eines Monats, wie `Januar` or `März`
- `%m`: numerische Repräsentation eines Monats, 2-stellig
- `%n`: numerische Repräsentation eines Montas ohne führende Null
- `%d`: Tag des Montas, 2-stellig
- `%j`: Tag des Monats ohne führende Null
- `%a`: kleines ante Meridiem bzw. post Meridiem
- `%g`: 12 Stunden Format einer Stunde ohne führende Null
- `%H`: 24 Stunden Format einer Stunde, 2-stellig
- `%G`: 24 Stunden Format einer Stunde ohne führende Null
- `%i`: Minuten mit führender Null, 2-stellig

### Veranstaltungs-Editor

Die Administration der Veranstaltungen erfolgt im Backend (`Plugins` →
`Calendar` → `Events bearbeiten`).

Alternativ kann der Veranstaltungs-Editor auf einer normalen
CMSimple\_XH Seite eingebettet werden, so dass die Veranstaltungen
ebenfalls von Nicht-Admins bearbeitet werden können. Dazu wird der
folgende Pluginaufruf verwendet:

    {{{editevents()}}}

Das Einbetten des Veranstaltungs-Editors auf einer Seite sollte nur
erfolgen, wenn der Zugriff auf diese Seite Authorisierung durch
[Register\_XH](https://github.com/cmb69/register_xh) oder
[Memberpages](https://github.com/cmsimple-xh/memberpages) erfordert.

Zusätzlich zu normalen Veranstaltungen werden täglich, wöchentlich und
monatlich wiederkehrende Veranstaltungen unterstützt. Wiederkehrende
Veranstaltungen können begrenzt (d.h. sie wiederholen sich für immer) oder
bis zu einem bestimmten Datum begrenzt sein, das das letzte Startdatum in einer
*inklusiven* Weise markiert. Wird eine wiederkehrende Veranstaltung bearbeitet,
werden alle ihre Einzel-Veranstaltungen modifiziert, es sei denn Sie wählen
`Einzel-Veranstaltung bearbeiten` was dazu führt, dass die wiederkehrende
Veranstaltung in bis zu drei Teile aufgeteilt wird, die dann individuell
bearbeitet werden können.

Darüber hinaus können *Geburtstage* definiert werden, indem `###` als Ort,
der Name des Geburtstagskinds als Veranstaltung, und das Geburtsdatum als
Anfangsdatum eingetragen wird. Dabei handelt es sich um Sonderfälle von
jährlich wiederkehrenden Veranstaltungen.

Von Calendar_XH 2.6 an akzeptiert das `description` Feld (das aus Gründen
der Abwärtskompatibilität im Frontend weiterhin als `link` bezeichnet wird),
beliebiges HTML. Es wird unbedingt empfohlen die Beschreibungen kurz zu
halten, und HTML Markup sparsam zu verwenden; andernfalls kann die
Veranstaltungsliste fürchterlich aussehen.

### Kalender

Veranstaltungskalender können vom Template aus angezeigt werden:

    <?=calendar()?>

oder nur auf einer Seite:

    {{{calendar()}}}

Alle definierten Veranstaltungen des aktuellen Monats werden im Kalender
hervorgehoben und zu der Veranstaltungsseite (*event page*) verlinkt,
die in den Spracheinstellungen von Calendar\_XH gewählt wurde.

Es ist möglich ein Jahr und einen Monat anzugeben, um den Kalender auf
einen bestimmten Monat festzulegen. Beispielsweise zeigt

    {{{calendar(2025, 4)}}}

immer den Kalender von April 2025.

### Große Kalender

Während die klassischen [Kalender](#kalender) für eher kleine Größen
optimiert sind (wie eine Seitenleiste im Template), sind große Kalender
für große Inhaltsbereiche optimiert. Daher zeigen die klassischen Kalender
nur die Tage an, während auf die Veranstaltungen für die jeweiligen Tage nur als Popup
zugegriffen werden kann; die *Tage* werden auf die [Veranstaltungsliste](#veranstaltungsliste) verlinkt.
Große Kalender hingegen zeigen die Titel der
Veranstaltungen direkt an, und diese werden zu den URLs der Veranstaltungen
verlinkt, so dass große Kalender erfordern, dass
[Veranstaltungsseiten](#veranstaltungsseiten) aktiviert sind.

Um einen großen Kalender auf einer Seite darzustellen, wird der folgende
Pluginaufruf verwendet:

    {{{calendar_big}}}

### Veranstaltungsliste

Die Veranstaltungsliste sollte auf der Veranstaltungsseite (*event
page*), die in den Spracheinstellungen von Calendar\_XH gewählt wurde,
durch den folgenden Pluginaufruf eingebettet werden:

    {{{events()}}}

Die klassische Veranstaltungsliste wird als Tabelle dargestellt, was
je nach Template, den Veranstaltungsdaten und dem verwendeten Browser
möglicherweise nicht gut aussieht.
Sie können die Ausgabe der `time`, `location` und `link` Spalten
under `Plugins` → `Calendar` → `Konfiguration` → `Show` unterdrücken,
was das Darstellungsproblem entschärfen kann.
Allerdings werden dem Nutzer möglicherweise relevante Veranstaltungs-Informationen
nicht präsentiert.

Eine Alternative ist `eventlist_new` unter `Plugins` → `Calendar` →
`Konfiguration` → `Eventlist` → `Template` auszuwählen.
Die neuartige Veranstaltungsliste wird als wirkliche Liste angezeigt,
so dass alle Informationen anzeigt werden können, ohne dass es häßlich aussieht.

Beachten Sie, dass die neuartige Veranstaltungsliste strukturierte Daten
gemäß <https://schema.org/Event> hinzufügt (SEO).

Es ist möglich ein Jahr und einen Monat anzugeben, um den Veranstaltungsliste
auf einen bestimmten Zeitraum festzulegen. Beispielsweise zeigt

    {{{events(4, 2025)}}}

immer die Veranstaltung um den April 2025, gemäß den Konfigurationseinstellungen.
Es ist zu beachten, dass die Reihenfolge von Jahr und Monat anders ist als
beim Kalender, ohne dass dies einen besonderen Grund hat.

Der Zeitraum der Veranstaltungen, die angezeigt werden, kann im Backend
konfiguriert werden (`Plugins` → `Calendar` → `Konfiguration` → `Show` →
`Number of future months` und `Number of previous months`).
Die Vorgaben (`11` and `0`) zeigen den aktuellen Monat und elf zukünftige
Monate, d.h. ein ganzes Jahr.

Anstatt diese Konfigurationseinstellungen zu ändern (oder auch zusätzlich),
können die Werte im Pluginaufruf angegeben werden. Beispielsweise zeigt

    {{{events(0, 0, 2, 0)}}}

die Veranstaltungsliste für einen Zeitraum von vier Monaten:
den vergangenen Monat, den aktuellen Monat und zwei zukünftige Monate.
Dies kann auf einen bestimmten Zeitraum festgelegt werden. Beispielsweise zeigt

    {{{events(4, 2025, 2, 1)}}}

immer die Veranstaltungen von März bis Juni 2025.

Um eine Liste vergangener Veranstaltung anzuzeigen, kann ein negativer Wert
für die zukünftigen Monate verwendet werden (typischerweise `-1`).
Beispielsweise zeigt

    {{{events(0, 0, -1, 12}}}

die Veranstaltungen der vergangenen zwölf Monate in *absteigender* Reihenfolge.

### Nächste Veranstaltung

Optional kann die nächste geplante Veranstaltung als Lauftext
(*marquee*-artig) angezeigt werden, entweder im Template:

    <?=nextevent()?>

oder auf einer Seite:

    {{{nextevent()}}}

### Veranstaltungsseiten

Es ist möglich jede Veranstaltung auf ihrer eigenen Seite anzeigen zu lassen,
was nützlich ist um auf bestimmte Veranstaltungen zu verlinken.
Diese Feature kann unter `Plugins` → `Calendar` → `Konfiguration` → `Event` → `Allow single`
aktiviert werden. Als Voraussetzung benötigen alle Veranstaltungen ordnungsgemäße
IDs, die bei Bedarf unter `Plugins` → `Calendar` → `Events bearbeiten`
erzeugt werden können.

Ist das Feature aktiviert, werden Veranstaltungen mit einem leeren Link-Feld
statt dessen die URL ihrer Veranstaltungsseite verwenden, was für die in der
neuen Veranstaltungsliste generierte strukturierten Daten relevant ist, und
auch für die iCalendar-Interoperabilität. Daher sollten für alle externen
Veranstaltungen Links eingetragen werden, aber für eigene Events das Link-Feld
besser leer gelassen werden.

### Import/Export

Um vorhandene `.ics` Dateien (iCalendar-Format) zu importieren, müssen
diese zunächst im `content/calendar/` Ordner (neben der `calendar.2.6.csv` Datei)
abgelegt werden. Unter `Plugins` → `Calendar` → `Import` kann dann der
eigentliche Import der Datei(en) durchgeführt werden.
Es ist zu beachten, dass nur eine kleiner Teil der iCalendar-Features
unterstützt wird, in etwa das, was Calendar_XH bietet.

Unter `Plugins` → `Calendar` → `Import/Export` können Sie ebenfalls den
Kalender nach calendar.ics (iCalendar-Format) exportieren.  Die Datei wird
im `content/calendar/` Ordner (neben der `calendar.2.6.csv` Datei) angelegt.

Ein besonderes Problem bezüglich des iCalendar Import/Export ist,
dass Calendar_XH keine dauerhaften UIDs für die Veranstaltungen
verwaltet. Beim Import werden die UIDs des iCalendar ignoriert,
so dass keine Synchronisierung erfolgt. Wird beispielsweise der selbe
iCalendar zweimal importiert, werden alle Veranstaltungen doppelt
vorhanden sein. Beim Export werden UIDs ad-hoc erzeugt; diese sind
möglicherweise nicht eindeutig. Aber schlimmer ist, dass die Bearbeitung
einer Veranstaltung die UID ändert, so dass keine Synchronisierung
erfolgt, wenn Nutzer Ihre `calendar.ics` importieren.
Daher ist es vermutlich das Beste, wenn Sie Ihre exportierten
iCalendar Dateien nicht veröffentlichen.

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf [Github](https://github.com/cmb69/calendar_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Calendar\_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Calendar\_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Calendar\_XH erhalten haben. Falls nicht, siehe
<http://www.gnu.org/licenses/>.

Copyright © 2005-2006 Michael Svarrer  
Copyright © 2007-2008 Tory  
Copyright © 2008 Patrick Varlet  
Copyright © 2011 Holger Irmler  
Copyright © 2011-2013 Frank Ziesing  
Copyright © 2017-2023 Christoph M. Becker

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
