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
 * Import users page.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/importqueue/importqueue_form.php');

require_login(null, false);

$PAGE->set_url('/blocks/importqueue/importusers.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('importuserstitle', 'block_importqueue'));
$mode = optional_param('mode', 'create', PARAM_TEXT);
$importqueueform = new importqueue_form($context, 0, $mode);

echo $OUTPUT->header();

$prefix = 'importusers';
if ($mode == 'update') {
    $prefix = 'update';
}
$options = array('class' => 'importqueue'.$mode);
echo html_writer::tag('h3', get_string($prefix.'heading', 'block_importqueue'), $options);

echo html_writer::tag('div', get_config('block_importqueue', 'menu'));

if ($importqueueform->is_submitted() && $importqueueform->is_validated()) {
    $queueid = $importqueueform->process($mode);
    if (empty($queueid)) {
        echo $importqueueform->geterror();
    } else {
        echo html_writer::tag('h3', get_string($prefix.'success', 'block_importqueue'));
        echo $importqueueform->geterror();
    }
} else {
    $importqueueform->display();
}

if ($count = $DB->count_records('dhimport_importqueue', array('userid' => $USER->id))) {
    $options = array('class' => 'uploadstatustext');
    echo html_writer::tag('p', get_string('importusersqueue', 'block_importqueue', $count), $options);
    $link = new moodle_url('/blocks/importqueue/queuestatus.php');
    $options = array("onclick" => 'window.location=\''.$link->out()."'", 'class' => 'uploadstatusbutton');
    echo html_writer::tag('button', get_string('importusersviewqueue', 'block_importqueue'), $options);
}

echo $OUTPUT->footer();
