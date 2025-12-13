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

use core_competency\plan;
use core_competency\api;

/**
 * Dashboard for viewing learning plans by cohorts and individuals
 * Commisioned by IUASR
 *
 * @package   block_learningplans_dashboard
 * @copyright 2025 Abdel Hamid Saib (corvo@arcadianflame.nl)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learningplans_dashboard extends block_base
{
    function init()
    {
        $this->title = get_string('newlearningplansdashboardblock', 'block_learningplans_dashboard');
    }

    function get_content()
    {
        global $DB;
        global $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $cohorts = $DB->get_records('cohort');
        $users = [];


        foreach ($cohorts as $cohort) {
            foreach ($users as $user) {
                if ($DB->record_exists('cohort_members', array('cohortid' => $cohort->id, 'userid' => $user->id))) {
                    if (!isset($cohort->users)) {
                        $cohort->users = [];
                    }
                    $cohort->users = [...$cohort->users, $user];
                }
            }
            $cohort->userCount = count($cohort->users);
        }
        $data = [
            'cohorts' => array_values($cohorts),
            'cohortView' => false
        ];

        $this->content->text = $OUTPUT->render_from_template('block_learningplans_dashboard/user_competency_overview', $data);
        return $this->content;
    }

    public function applicable_formats()
    {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

}
