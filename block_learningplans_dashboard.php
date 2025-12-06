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
use core_competency\user_competency;
use core_external\util as external_util;
use core_competency\api;
/**
 * Dashboard for viewing learning plans by cohorts and individuals
 * Commisioned by IUASR
 *
 * @package   block_learningplans_dashboard
 * @copyright 2025 Abdel Hamid Saib (corvo@arcadianflame.nl)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learningplans_dashboard extends block_base {
    function init() {
        $this->title = get_string('newlearningplansdashboardblock', 'block_learningplans_dashboard');
    }

    function get_content() {
        global $DB;
        global $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $assignedCompetencyPlanIDs = $DB->get_records('competency_plan', null, '', 'id');
        $userids = [];
        $users=[];
        //For now, focus on ONE specific LP (learning plan), the one that targets ALL competencies

        foreach ($assignedCompetencyPlanIDs as $planID) {
            $plan = new plan($planID->id);

            //All competencies associated with a plan (not with user, confusing)
            $pclist = api::list_plan_competencies($plan->get('id'));
            $proficientCount = 0;
            $competencyCount = 0;

            if(!in_array($plan->get('userid'), $userids)) {
                $userids= [...$userids, $plan->get('userid')];
                $newUser = $DB->get_record('user', array('id' => $plan->get('userid')));
                $newUser->plans = [];
                $users[$plan->get('userid')]=$newUser;
            }else{
                $newUser = $users[$plan->get('userid')];
            }

            $ucproperty = 'usercompetency';
            foreach ($pclist as $pc) {
                //add to total count
                $competencyCount++;

                //check if student = competent.
                $comp = $pc->competency;
                $usercomp = $pc->$ucproperty;

                if ($usercomp->get('proficiency')) {
                    $proficientCount++;
                }
            }

            $userPlan = ['name'=>$plan->get('name'), 'proficiency'=>$proficientCount, 'competencyCount'=>$competencyCount];
            //Add counts to competencyplan to user
            $newUser->plans = [...$newUser->plans, $userPlan];
            $newUser->proficiency = $proficientCount;
            $newUser->competency = $competencyCount;
            $newUser->editUrl = new moodle_url('/admin/tool/lp/plan.php', array('id' => $plan->get('id')));
        }

        foreach ($users as $user) {
            $user->picture = $OUTPUT->user_picture($user);
        }

        $data = [
            'users' => array_values($users),
        ];

        $this->content->text = $OUTPUT->render_from_template('block_learningplans_dashboard/main', $data);

        return $this->content;
    }

    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

}
