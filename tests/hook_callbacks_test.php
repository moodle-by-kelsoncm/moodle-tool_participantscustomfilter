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

/**
 * Tests of the hook callbacks.
 *
 * @package    tool_participantscustomfilter
 * @category   test
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\tool_participantscustomfilter\hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    /**
     * Runs the callback on a page and returns the HTML it added.
     *
     * @param string $pagetype Page type.
     * @param \context_course $context Page context.
     * @return string
     */
    private function run_callback(string $pagetype, \context_course $context): string {
        global $PAGE;
        $PAGE = new \moodle_page();
        $PAGE->set_url('/user/index.php', ['id' => $context->instanceid]);
        $PAGE->set_context($context);
        $PAGE->set_pagetype($pagetype);
        $hook = new \core\hook\output\after_standard_main_region_html_generation($PAGE->get_renderer('core'));
        hook_callbacks::after_standard_main_region_html_generation($hook);
        return $hook->get_output();
    }

    /**
     * The panel shows only on the participants page of a course, to users who can view participants.
     */
    public function test_panel_only_on_participants_page(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = \context_course::instance($course->id);
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $student = $generator->create_and_enrol($course, 'student');

        $this->setUser($teacher);
        $this->assertStringContainsString(
            'data-region="tool-participantscustomfilter"',
            $this->run_callback(hook_callbacks::PAGETYPE, $context)
        );
        $this->assertSame('', $this->run_callback('course-view-topics', $context));
        $this->assertSame('', $this->run_callback(hook_callbacks::PAGETYPE, \context_course::instance(SITEID)));

        $studentrole = $this->getDataGenerator()->create_role();
        assign_capability('moodle/course:viewparticipants', CAP_PROHIBIT, $studentrole, $context->id);
        role_assign($studentrole, $student->id, $context->id);
        $this->setUser($student);
        $this->assertSame('', $this->run_callback(hook_callbacks::PAGETYPE, $context));
    }

    /**
     * Teachers without access to all groups are limited to their groups in separate groups mode.
     */
    public function test_get_group_restriction(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['groupmode' => SEPARATEGROUPS, 'groupmodeforce' => 1]);
        $context = \context_course::instance($course->id);
        $teacher = $generator->create_and_enrol($course, 'teacher');
        $group = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $group->id, 'userid' => $teacher->id]);

        $this->setUser($teacher);
        $this->assertSame([(int)$group->id], hook_callbacks::get_group_restriction($course, $context));

        $this->setAdminUser();
        $this->assertNull(hook_callbacks::get_group_restriction($course, $context));
    }
}
