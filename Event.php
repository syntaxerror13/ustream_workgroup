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
	public $message;

	public function __construct($time, $project, $user, $message) {
		$this->time = $time;
		$this->project = $project;
		$this->user = $user;
		$this->message = $message;
	}
}