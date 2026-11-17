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

namespace tool_participantscustomfilter\output;

use core_user\fields;
use moodle_url;
use paging_bar;
use renderable;
use renderer_base;
use templatable;
use tool_participantscustomfilter\filter;

/**
 * Filter form and results shown on the course participants page.
 *
 * @package    tool_participantscustomfilter
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panel implements renderable, templatable {
    /** @var string Name of the URL parameter with the field shortname. */
    public const PARAM_FIELD = 'filtercustomfield';

    /** @var string Name of the URL parameter with the value. */
    public const PARAM_VALUE = 'filtervalue';

    /** @var string Name of the URL parameter with the results page. */
    public const PARAM_PAGE = 'filtercustompage';

    /** @var filter Filter of the course. */
    protected filter $filter;

    /** @var int Course id. */
    protected int $courseid;

    /** @var string Selected field shortname. */
    protected string $field;

    /** @var string Value to look for. */
    protected string $value;

    /** @var int Results page, starting at 0. */
    protected int $page;

    /** @var int[]|null Groups the results are restricted to (null: no restriction). */
    protected ?array $groupids;

    /**
     * Constructor.
     *
     * @param filter $filter Filter of the course.
     * @param int $courseid Course id.
     * @param string $field Selected field shortname ('' for none).
     * @param string $value Value to look for ('' for none).
     * @param int $page Results page, starting at 0.
     * @param int[]|null $groupids Restrict results to these groups (null: no restriction).
     */
    public function __construct(
        filter $filter,
        int $courseid,
        string $field,
        string $value,
        int $page = 0,
        ?array $groupids = null
    ) {
        $this->filter = $filter;
        $this->courseid = $courseid;
        $this->field = $field;
        $this->value = $value;
        $this->page = $page;
        $this->groupids = $groupids;
    }

    /**
     * Exports the data for the panel template.
     *
     * @param renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $fields = [];
        foreach ($this->filter->get_custom_fields() as $field) {
            $fields[] = $field + ['selected' => $field['shortname'] === $this->field];
        }

        $baseurl = new moodle_url('/user/index.php', ['id' => $this->courseid]);
        $isfiltered = $this->field !== '' && $this->value !== '';
        $data = [
            'courseid' => $this->courseid,
            'actionurl' => $baseurl->out(false),
            'clearurl' => $baseurl->out(false),
            'fields' => $fields,
            'hasfields' => !empty($fields),
            'currentvalue' => $this->value,
            'isfiltered' => $isfiltered,
            'invalidfield' => false,
            'results' => null,
        ];
        if (!$isfiltered) {
            return $data;
        }
        if (!$this->filter->is_valid_field($this->field)) {
            $data['invalidfield'] = true;
            return $data;
        }

        $found = $this->filter->get_filtered_participants(
            $this->field,
            $this->value,
            $this->page,
            filter::PERPAGE,
            $this->groupids
        );
        $headers = [];
        foreach ($found['identityfields'] as $identityfield) {
            $headers[] = ['label' => fields::get_display_name($identityfield)];
        }
        $users = [];
        foreach ($found['users'] as $user) {
            $identity = [];
            foreach ($found['identityfields'] as $identityfield) {
                $identity[] = ['value' => (string)($user->{$identityfield} ?? '')];
            }
            $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $this->courseid]);
            $users[] = [
                'fullname' => fullname($user),
                'profileurl' => $profileurl->out(false),
                'identity' => $identity,
            ];
        }

        $pageurl = new moodle_url($baseurl, [self::PARAM_FIELD => $this->field, self::PARAM_VALUE => $this->value]);
        $data['results'] = [
            'total' => $found['total'],
            'hasusers' => !empty($users),
            'headers' => $headers,
            'users' => $users,
            'pagingbar' => $output->render(
                new paging_bar($found['total'], $this->page, filter::PERPAGE, $pageurl, self::PARAM_PAGE)
            ),
        ];
        return $data;
    }
}
