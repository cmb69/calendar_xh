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

class OverviewWidget {
    /**
     * @param {Element} element
     */
    constructor (element) {
        this.splitButton = element.querySelector("button[value='edit_single']");
        this.editButton = element.querySelector("button[value='update']");
        this.deleteButton = element.querySelector("button[value='delete']");
        let radios = /** @type {NodeListOf<HTMLInputElement>}} */
            (element.querySelectorAll("tr input[type='radio']"));
        radios.forEach(radio => this.replaceRadiosWithButtons(radio));
        this.splitButton.parentNode.removeChild(this.splitButton);
        this.editButton.parentNode.removeChild(this.editButton);
        this.deleteButton.parentNode.removeChild(this.deleteButton);
        element.querySelectorAll("tr").forEach(tr =>
            tr.onclick = () => this.selectRow(tr)
        );
        element.querySelectorAll(".calendar_hidden").forEach(el => {
            el.parentElement.title = el.textContent;
            el.style.display = "none";
        });
    }

    /**
     * @param {HTMLInputElement} radio
     */
    replaceRadiosWithButtons(radio) {
        var col = radio.parentNode;
        if (col?.parentNode.dataset.recurring) {
            col.appendChild(this.splitButton.cloneNode(true));
        }
        col.appendChild(this.editButton.cloneNode(true));
        col.appendChild(this.deleteButton.cloneNode(true));
        radio.style.display = "none";
    }

    /**
     * @param {HTMLTableRowElement} tr
     */
    selectRow(tr) {
        let select = /** @type {HTMLInputElement} */
            (tr.querySelector("input[type='radio']"));
        select.checked = true;
    }
}

document.querySelectorAll(".calendar_overview").forEach(form => new OverviewWidget(form));
