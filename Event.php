<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 18/03/15
 * Time: 11:38
 */

class Event {
	public $time;
	public $project;
	public $user;
	public $action;
	public $message;

	public function __construct($time, $project, $user, $action, $message) {
		$this->time = $time;
		$this->project = $project;
		$this->user = $user;
		$this->action = $action;
		$this->message = $message;
	}

	public static function create($project, $user, $action, $message) {
		DB::execute("INSERT INTO wg_log SET timestamp = NOW(), project_name = :project, user_name = :user, action = :action, message = :message",
			array(
				':project' => $project,
				':user' => $user,
				':action' => $action,
				':message' => $message
			));
	}
}