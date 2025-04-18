/**
 * Copyright 2017-2023 Christoph M. Becker
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

// @ts-check

class CalendarWidget {
    /**
     * @param {Element} element
     */
    constructor (element) {
        this.element = element;
        let anchors = /** @type {NodeListOf<HTMLAnchorElement>} */
            (element.querySelectorAll(".calendar_monthyear a"));
        anchors.forEach(anchor => {
            anchor.onclick = event => {
                this.retrieveCalendar(anchor.href, false);
                event.preventDefault();
            };
        });
        history.replaceState({calendar_url: location.href}, document.title, location.href);
        window.addEventListener("popstate", event => this.onPopState(event));
    }

    /**
     * @param {string} url
     * @param {boolean} isPop
     */
    retrieveCalendar(url, isPop) {
        var request = new XMLHttpRequest();
        this.element.classList.add("calendar_loading");
        request.open("GET", url);
        request.setRequestHeader("X-CMSimple-XH-Request", "calendar");
        request.onload = () => {
            if (request.status >= 200 && request.status < 300) {
                this.replaceCalendar(request.response, url, isPop);
            }
            this.element.classList.remove("calendar_loading");
        };
        request.send();
    }

    /**
     * @param {string} response
     * @param {string} url
     * @param {boolean} isPop
     */
    replaceCalendar(response, url, isPop) {
        this.element.innerHTML = response;
        new CalendarWidget(this.element);
        if (!isPop) {
            history.pushState({calendar_url: url}, document.title, url);
        }
    }

    /**
     * @param {PopStateEvent} event
     */
    onPopState(event) {
        if (event.state && "calendar_url" in event.state) {
            this.retrieveCalendar(event.state.calendar_url, true);
        }
    }
}

document.querySelectorAll(".calendar_calendar").forEach(element => new CalendarWidget(element));
