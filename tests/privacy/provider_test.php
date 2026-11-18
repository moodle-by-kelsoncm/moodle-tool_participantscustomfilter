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

namespace tool_participantscustomfilter\privacy;

/**
 * Tests of the privacy provider.
 *
 * @package    tool_participantscustomfilter
 * @category   test
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\tool_participantscustomfilter\privacy\provider::class)]
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * The plugin declares that it stores no personal data.
     */
    public function test_get_reason(): void {
        $this->assertInstanceOf(\core_privacy\local\metadata\null_provider::class, new provider());
        $this->assertNotEmpty(get_string(provider::get_reason(), 'tool_participantscustomfilter'));
    }
}
