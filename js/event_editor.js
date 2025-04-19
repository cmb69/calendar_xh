/**
 * Copyright (c) Christoph M. Becker
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

(function () {
    const form = document.querySelector("form.calendar_input");
    if (!(form instanceof HTMLFormElement)) return;
    const fullDay = form.elements.namedItem("full_day");
    if (!(fullDay instanceof HTMLInputElement)) return;
    const dateStart = form.elements.namedItem("datestart");
    const dateEnd = form.elements.namedItem("dateend");
    if (!(dateStart instanceof HTMLInputElement && dateEnd instanceof HTMLInputElement)) return;
    const recur = form.elements.namedItem("recur");
    if (!(recur instanceof HTMLSelectElement)) return;
    const until = form.elements.namedItem("until");
    if (!(until instanceof HTMLInputElement)) return;
    let date = "";
    let [, dateStartTime] = dateStart.value.split("T", 2);
    let [, dateEndTime] = dateEnd.value.split("T", 2);
    const convert = () => {
        if (fullDay.checked) {
            [date, dateStartTime] = dateStart.value.split("T", 2);
            dateStart.value = "";
            dateStart.type = "date";
            dateStart.value = date;
            [date, dateEndTime] = dateEnd.value.split("T", 2);
            dateEnd.value = "";
            dateEnd.type = "date";
            dateEnd.value = date;
        } else if (dateStart.type === "date") {
            date = dateStart.value;
            dateStart.value = "";
            dateStart.type = "datetime-local";
            dateStart.value = date + "T" + dateStartTime;
            date = dateEnd.value;
            dateEnd.value = "";
            dateEnd.type = "datetime-local";
            dateEnd.value = date + "T" + dateEndTime;
        }
    }
    fullDay.onclick = convert;
    convert();
    if (recur.value === "none") {
        until.style.display = "none";
        if (until.previousElementSibling instanceof HTMLSpanElement) {
            until.previousElementSibling.style.display = "none";
        }
    }
    recur.oninput = location.oninput = () => {
        const display = recur.value === "none" ? "none" : "";
        until.style.display = display;
        if (until.previousElementSibling instanceof HTMLSpanElement) {
            until.previousElementSibling.style.display = display;
        }
    }
})();
