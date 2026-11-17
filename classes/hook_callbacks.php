<?php
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

namespace tool_participantscustomfilter;

use context_course;
use core\hook\output\after_standard_main_region_html_generation;
use tool_participantscustomfilter\output\panel;

/**
 * Hook callbacks of tool_participantscustomfilter.
 *
 * @package    tool_participantscustomfilter
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /** @var string Page type of /user/index.php. */
    public const PAGETYPE = 'course-view-participants';

    /**
     * Adds the custom profile field filter to the course participants page.
     *
     * The panel is printed at the end of the main region; the AMD module moves it
     * above the participants table.
     *
     * @param after_standard_main_region_html_generation $hook
     */
    public static function after_standard_main_region_html_generation(after_standard_main_region_html_generation $hook): void {
        global $PAGE;

        if (during_initial_install() || $PAGE->pagetype !== self::PAGETYPE) {
            return;
        }
        $context = $PAGE->context;
        if (!($context instanceof context_course) || (int)$context->instanceid === SITEID) {
            return;
        }
        if (!has_capability('moodle/course:viewparticipants', $context)) {
            return;
        }

        $courseid = (int)$context->instanceid;
        $panel = new panel(
            new filter($courseid),
            $courseid,
            optional_param(panel::PARAM_FIELD, '', PARAM_ALPHANUMEXT),
            trim(optional_param(panel::PARAM_VALUE, '', PARAM_TEXT)),
            max(0, optional_param(panel::PARAM_PAGE, 0, PARAM_INT)),
            self::get_group_restriction($PAGE->course, $context)
        );

        $output = $hook->renderer;
        $hook->add_html($output->render_from_template(
            'tool_participantscustomfilter/panel',
            $panel->export_for_template($output)
        ));
        $PAGE->requires->js_call_amd('tool_participantscustomfilter/filter', 'init');
    }

    /**
     * Returns the groups the current user is limited to, as on the participants page.
     *
     * @param \stdClass $course Course record.
     * @param context_course $context Course context.
     * @return int[]|null Group ids, or null when the user can see every participant.
     */
    public static function get_group_restriction(\stdClass $course, context_course $context): ?array {
        global $USER;

        $separate = groups_get_course_groupmode($course) == SEPARATEGROUPS;
        if (!$separate || has_capability('moodle/site:accessallgroups', $context)) {
            return null;
        }
        return array_map('intval', array_keys(groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid)));
    }
}
