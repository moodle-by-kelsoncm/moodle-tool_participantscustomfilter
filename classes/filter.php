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
use context_system;
use core_user\fields;

/**
 * Finds the course participants whose custom profile field matches a value.
 *
 * @package    tool_participantscustomfilter
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter {
    /** @var int Default number of participants per page. */
    public const PERPAGE = 20;

    /** @var int Course id. */
    protected int $courseid;

    /** @var context_course Course context. */
    protected context_course $context;

    /**
     * Constructor.
     *
     * @param int $courseid Course id.
     */
    public function __construct(int $courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        $this->courseid = $courseid;
        $this->context = context_course::instance($courseid);
    }

    /**
     * Returns the custom profile fields the current user may filter by in this course.
     *
     * The rules follow {@see \profile_field_base::is_visible()} for other users' data:
     * fields visible to everyone, fields visible to teachers when the user can see
     * the identity of course participants, and private or hidden fields only with
     * moodle/user:viewalldetails at system level.
     *
     * @return array List of ['shortname' => string, 'name' => string], sorted by name.
     */
    public function get_custom_fields(): array {
        global $DB;

        $records = $DB->get_records('user_info_field', null, 'sortorder ASC, id ASC', 'id, shortname, name, visible');
        $fields = [];
        foreach ($records as $record) {
            if (!$this->can_filter_by($record->visible)) {
                continue;
            }
            $fields[] = [
                'shortname' => $record->shortname,
                'name' => format_string($record->name, true, ['context' => context_system::instance()]),
            ];
        }
        return $fields;
    }

    /**
     * Whether the current user may filter by a field with the given visibility.
     *
     * @param int $visible Value of user_info_field.visible.
     * @return bool
     */
    protected function can_filter_by(int $visible): bool {
        switch ($visible) {
            case PROFILE_VISIBLE_ALL:
                return true;
            case PROFILE_VISIBLE_TEACHERS:
                return has_capability('moodle/site:viewuseridentity', $this->context);
            default:
                return has_capability('moodle/user:viewalldetails', context_system::instance());
        }
    }

    /**
     * Whether the shortname is a profile field the current user may filter by.
     *
     * @param string $shortname Field shortname.
     * @return bool
     */
    public function is_valid_field(string $shortname): bool {
        foreach ($this->get_custom_fields() as $field) {
            if ($field['shortname'] === $shortname) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the participants with an active enrolment whose field value contains the given text.
     *
     * The match is case-insensitive and partial. Only active enrolments on enabled
     * enrolment methods count, and deleted users are left out.
     *
     * @param string $fieldshortname Shortname of the profile field.
     * @param string $filtervalue Text to look for.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Participants per page.
     * @param int[]|null $groupids Restrict to members of these groups (null: no restriction).
     * @return array ['total' => int, 'users' => \stdClass[], 'identityfields' => string[]]
     */
    public function get_filtered_participants(
        string $fieldshortname,
        string $filtervalue,
        int $page = 0,
        int $perpage = self::PERPAGE,
        ?array $groupids = null
    ): array {
        global $DB;

        $identityfields = fields::get_identity_fields($this->context, false);
        $empty = ['total' => 0, 'users' => [], 'identityfields' => $identityfields];
        if ($filtervalue === '' || !$this->is_valid_field($fieldshortname)) {
            return $empty;
        }
        if ($groupids !== null && empty($groupids)) {
            return $empty;
        }

        $groupjoin = '';
        $params = [
            'courseid' => $this->courseid,
            'shortname' => $fieldshortname,
            'value' => '%' . $DB->sql_like_escape($filtervalue) . '%',
            'activeue' => ENROL_USER_ACTIVE,
            'activee' => ENROL_INSTANCE_ENABLED,
            'now1' => time(),
            'now2' => time(),
        ];
        if ($groupids !== null) {
            [$ingroups, $groupparams] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'grp');
            $groupjoin = "JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid $ingroups";
            $params += $groupparams;
        }

        $from = "FROM {user} u
                 JOIN {user_info_field} uif ON uif.shortname = :shortname
                 JOIN {user_info_data} uid ON uid.fieldid = uif.id AND uid.userid = u.id
                 $groupjoin
                WHERE u.deleted = 0
                  AND " . $DB->sql_like('uid.data', ':value', false, false) . "
                  AND EXISTS (
                      SELECT 1
                        FROM {user_enrolments} ue
                        JOIN {enrol} e ON e.id = ue.enrolid
                       WHERE ue.userid = u.id
                         AND e.courseid = :courseid
                         AND e.status = :activee
                         AND ue.status = :activeue
                         AND ue.timestart <= :now1
                         AND (ue.timeend = 0 OR ue.timeend > :now2)
                  )";

        $total = (int)$DB->count_records_sql("SELECT COUNT(DISTINCT u.id) $from", $params);
        if ($total === 0) {
            return $empty;
        }

        $userfields = fields::for_name()->including(...$identityfields)->get_sql('u', false, '', '', false);
        $sql = "SELECT DISTINCT u.id, {$userfields->selects} $from ORDER BY u.lastname, u.firstname, u.id";
        $users = array_values($DB->get_records_sql($sql, $params + $userfields->params, $page * $perpage, $perpage));

        return ['total' => $total, 'users' => $users, 'identityfields' => $identityfields];
    }
}
