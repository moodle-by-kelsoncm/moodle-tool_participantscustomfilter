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

/**
 * Upgrade steps of tool_participantscustomfilter.
 *
 * @package    tool_participantscustomfilter
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrades the plugin.
 *
 * @param int $oldversion Version installed before this upgrade.
 * @return bool
 */
function xmldb_tool_participantscustomfilter_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026111000) {
        // Versions up to 1.0.3 created a placeholder table that was never used.
        $table = new xmldb_table('tool_participantscustomfilter');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        upgrade_plugin_savepoint(true, 2026111000, 'tool', 'participantscustomfilter');
    }

    return true;
}
