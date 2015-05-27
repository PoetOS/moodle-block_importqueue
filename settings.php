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
 * Settings for the HTML block
 *
 * @package    blocks_importqueue 
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/datahub/importplugins/version1/lib.php');
require_once($CFG->dirroot.'/auth/kronosportal/lib.php');

if ($ADMIN->fulltree) {
    // Create list of fields which can be used in the import process.
    $fields = rlipimport_version1_get_mapping('user');
    $map = array();
    $excludefields = 'action,auth,username,idnumber,context,user_idnumber,user_username,user_email,inactive,password,';
    $excludefields .= 'firstname,lastname,email,city,country';
    $excludecolumns = preg_split('/,/', $excludefields);
    $auth = get_auth_plugin('kronosportal');
    if ($auth->is_configuration_valid()) {
        $excludecolumns[] = 'profile_field_'.kronosportal_get_solutionfield();
    }
    foreach ($fields as $src => $dest) {
        if (!in_array($src, $excludecolumns)) {
            $map[] = html_writer::tag('li', $dest);
        }
    }

    $settings->add(new admin_setting_configtext('block_importqueue/importcolumns', get_string('importcolumns', 'block_importqueue'),
                       get_string('importcolumnsdesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/allowedempty', get_string('allowedempty', 'block_importqueue'),
                       get_string('allowedemptydesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_heading('block_importqueue/importqueuefields', get_string('csvfieldsheading', 'block_importqueue'),
                       get_string('csvfieldsdesc', 'block_importqueue', join('', $map))));

    $settings->add(new admin_setting_configcheckbox('block_importqueue/learningpath_autocomplete', get_string('learningpath_autocomplete', 'block_importqueue'),
                       get_string('learningpath_autocompletedesc', 'block_importqueue'), 0));

    $settings->add(new admin_setting_configcheckbox('block_importqueue/learningpath_select', get_string('learningpath_select', 'block_importqueue'),
                       get_string('learningpath_selectdesc', 'block_importqueue'), 0));
}
