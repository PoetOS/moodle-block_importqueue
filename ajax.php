<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Import queue.  This file processes AJAX actions and returns JSON for autocomplete.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

$query = required_param('usrset', PARAM_RAW);
$query = preg_replace('/[^A-Za-z0-9_-\s]/i', '', $query);
$query = trim($query);
$callback = optional_param('callback', '', PARAM_TEXT);

$PAGE->set_url(new moodle_url('/blocks/importqueue/ajax.php', array('usrset' => $query)));

require_login(null, false);
$context = context_system::instance();
if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
    // For system adminstrator or role with sitewide access select from all usersets.
    $like = $DB->sql_like('name', '?', false);
    $sql = "SELECT *
              FROM {local_elisprogram_uset}
             WHERE depth in (2, 3)
                   AND {$like}
          ORDER BY name ASC
             LIMIT 50";
    $param = array('%'.$query.'%');
    $records = $DB->get_recordset_sql($sql, $param);
} else if (has_capability('block/importqueue:upload', $context, $USER->id)) {
    // Get solution id for user.
    $auth = get_auth_plugin('kronosportal');
    $solutionidfield = $auth->config->solutionid;
    $solutionid = $auth->get_user_solution_id($USER->id);
    $sql = "SELECT ctx.id context, uset.id id, uset.name
              FROM {local_elisprogram_uset} uset
              JOIN {local_eliscore_field_clevels} fldctx on fldctx.fieldid = ?
              JOIN {context} ctx ON ctx.instanceid = uset.id AND ctx.contextlevel = fldctx.contextlevel
              JOIN {local_eliscore_fld_data_char} fldchar ON fldchar.contextid = ctx.id AND fldchar.fieldid = fldctx.fieldid
             WHERE uset.depth = 2
                   AND fldchar.data = ?";
    $usersetcontextandname = $DB->get_record_sql($sql, array($solutionidfield, $solutionid));
    // Show roles and solution id userset.
    $like = $DB->sql_like('name', '?', false);
    $sql = "SELECT *
              FROM {local_elisprogram_uset}
             WHERE depth in (2, 3)
                   AND (parent = ? OR id = ?)
                   AND {$like}
          ORDER BY name ASC
             LIMIT 50";
    if (empty($usersetcontextandname->id)) {
        print_error('nopermissiontoshow');
    }
    $param = array($usersetcontextandname->id, $usersetcontextandname->id, '%'.$query.'%');
    $records = $DB->get_recordset_sql($sql, $param);
} else {
    print_error('nopermissiontoshow');
}

$formattedresult = array('result' => array());

foreach ($records as $record) {
    if ($record->depth == 3) {
        $parent = $DB->get_record('local_elisprogram_uset', array('id' => $record->parent));
        $record->name = $parent->name.'-'.$record->name;
    }
    $formattedresult['result'][] = $record;
}

$records->close();

echo $callback.'('.json_encode($formattedresult).')';
die();