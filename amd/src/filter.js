// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Moves the custom profile field filter above the participants table.
 *
 * The panel is printed by the server at the end of the main region; without
 * JavaScript it stays there and still works.
 *
 * @module     tool_participantscustomfilter/filter
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const PANEL = '[data-region="tool-participantscustomfilter"]';

/**
 * Places the panel right after the core participants filter, or before the list.
 */
export const init = () => {
    const panel = document.querySelector(PANEL);
    if (!panel || panel.dataset.moved) {
        return;
    }
    const corefilter = document.querySelector('#region-main [id^="core-filter-"]');
    const userlist = document.querySelector('#region-main .userlist');
    if (corefilter) {
        corefilter.after(panel);
    } else if (userlist) {
        userlist.before(panel);
    } else {
        return;
    }
    panel.dataset.moved = '1';
};
