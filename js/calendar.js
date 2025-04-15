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
        if (!(element instanceof HTMLElement)) return;
        this.element = element;
        this.name = "calendar-" + parseInt(element.dataset.num || "");
        this.init();
        let state = history.state;
        if (!state) {
            state = {};
        }
        state[this.name] = this.element.innerHTML;
        history.replaceState(state, document.title, location.href);
        window.addEventListener("popstate", event => this.onPopState(event));
    }

    init() {
        let anchors = /** @type {NodeListOf<HTMLAnchorElement>} */
            (this.element.querySelectorAll(".calendar_monthyear a"));
        anchors.forEach(anchor => {
            anchor.onclick = event => {
                this.retrieveCalendar(anchor.href);
                event.preventDefault();
            };
        });
    }

    /**
     * @param {string} url
     */
    retrieveCalendar(url) {
        var request = new XMLHttpRequest();
        this.element.classList.add("calendar_loading");
        request.open("GET", url);
        request.setRequestHeader("X-CMSimple-XH-Request", this.name);
        request.onload = () => {
            if (request.status >= 200 && request.status < 300) {
                this.replaceCalendar(request.response);
                let state = history.state;
                state[this.name] = request.response;
                history.pushState(state, document.title, url);
            }
            this.element.classList.remove("calendar_loading");
        };
        request.send();
    }

    /**
     * @param {string} response
     */
    replaceCalendar(response) {
        this.element.innerHTML = response;
        this.init();
    }

    /**
     * @param {PopStateEvent} event
     */
    onPopState(event) {
        if (event.state && event.state[this.name] !== undefined) {
            this.replaceCalendar(event.state[this.name]);
        }
    }
}

document.querySelectorAll(".calendar_calendar").forEach(element => new CalendarWidget(element));
