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
 * Tests of the participants filter.
 *
 * @package    tool_participantscustomfilter
 * @category   test
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\tool_participantscustomfilter\filter::class)]
final class filter_test extends \advanced_testcase {
    /** @var \stdClass Course. */
    private \stdClass $course;

    /** @var \stdClass Editing teacher of the course. */
    private \stdClass $teacher;

    /**
     * Creates a course with profile fields of every visibility and a teacher.
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->dirroot . '/user/profile/lib.php');
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $fields = [
            'city2' => PROFILE_VISIBLE_ALL,
            'team' => PROFILE_VISIBLE_TEACHERS,
            'secret' => PROFILE_VISIBLE_PRIVATE,
            'hidden' => PROFILE_VISIBLE_NONE,
        ];
        foreach ($fields as $shortname => $visible) {
            $generator->create_custom_profile_field([
                'datatype' => 'text',
                'shortname' => $shortname,
                'name' => ucfirst($shortname),
                'visible' => $visible,
            ]);
        }
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
    }

    /**
     * Creates a student of the course with the given value in the city2 field.
     *
     * @param string $city Value of the field.
     * @param string $lastname Last name, which sets the order of the results.
     * @param int $status Enrolment status (ENROL_USER_ACTIVE or ENROL_USER_SUSPENDED).
     * @return \stdClass
     */
    private function create_student(string $city, string $lastname, int $status = ENROL_USER_ACTIVE): \stdClass {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user(['lastname' => $lastname, 'profile_field_city2' => $city]);
        $generator->enrol_user($user->id, $this->course->id, 'student', 'manual', 0, 0, $status);
        return $user;
    }

    /**
     * A teacher may filter by fields visible to everyone or to teachers only.
     */
    public function test_get_custom_fields_teacher(): void {
        $this->setUser($this->teacher);
        $filter = new filter((int)$this->course->id);

        $shortnames = array_column($filter->get_custom_fields(), 'shortname');
        sort($shortnames);
        $this->assertSame(['city2', 'team'], $shortnames);
        $this->assertTrue($filter->is_valid_field('team'));
        $this->assertFalse($filter->is_valid_field('secret'));
        $this->assertFalse($filter->is_valid_field('missing'));
    }

    /**
     * An administrator may filter by every field.
     */
    public function test_get_custom_fields_admin(): void {
        $this->setAdminUser();
        $filter = new filter((int)$this->course->id);

        $this->assertCount(4, $filter->get_custom_fields());
    }

    /**
     * Matches are partial, case-insensitive and limited to active enrolments in the course.
     */
    public function test_get_filtered_participants(): void {
        $this->create_student('Natal', 'Bravo');
        $this->create_student('São Paulo', 'Alpha');
        $this->create_student('Parnamirim (near NATAL)', 'Alves');
        $this->create_student('Natal', 'Suspended', ENROL_USER_SUSPENDED);
        $other = $this->getDataGenerator()->create_user(['profile_field_city2' => 'Natal']);
        $this->getDataGenerator()->enrol_user($other->id, $this->getDataGenerator()->create_course()->id);

        $this->setUser($this->teacher);
        $result = (new filter((int)$this->course->id))->get_filtered_participants('city2', 'natal');

        $this->assertSame(2, $result['total']);
        $this->assertSame(['Alves', 'Bravo'], array_column($result['users'], 'lastname'));
        $this->assertSame(['email'], $result['identityfields']);
        $this->assertTrue(property_exists($result['users'][0], 'email'));
    }

    /**
     * Results are paged and the total counts every match.
     */
    public function test_get_filtered_participants_paging(): void {
        foreach (['A', 'B', 'C'] as $lastname) {
            $this->create_student('Natal', $lastname);
        }
        $this->setUser($this->teacher);
        $filter = new filter((int)$this->course->id);

        $page = $filter->get_filtered_participants('city2', 'Natal', 1, 2);
        $this->assertSame(3, $page['total']);
        $this->assertSame(['C'], array_column($page['users'], 'lastname'));
    }

    /**
     * Group restrictions limit the results to members of those groups.
     */
    public function test_get_filtered_participants_groups(): void {
        $member = $this->create_student('Natal', 'Member');
        $this->create_student('Natal', 'Outsider');
        $group = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, 'userid' => $member->id]);

        $this->setUser($this->teacher);
        $filter = new filter((int)$this->course->id);

        $result = $filter->get_filtered_participants('city2', 'Natal', 0, 20, [(int)$group->id]);
        $this->assertSame(1, $result['total']);
        $this->assertSame('Member', $result['users'][0]->lastname);
        $this->assertSame(0, $filter->get_filtered_participants('city2', 'Natal', 0, 20, [])['total']);
    }

    /**
     * Fields the user cannot see and empty values return nothing.
     */
    public function test_get_filtered_participants_not_allowed(): void {
        $user = $this->getDataGenerator()->create_user(['profile_field_secret' => 'x']);
        $this->getDataGenerator()->enrol_user($user->id, $this->course->id);

        $this->setUser($this->teacher);
        $filter = new filter((int)$this->course->id);

        $this->assertSame(0, $filter->get_filtered_participants('secret', 'x')['total']);
        $this->assertSame(0, $filter->get_filtered_participants('city2', '')['total']);
    }
}
