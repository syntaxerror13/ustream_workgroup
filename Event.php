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

	public function getLogMessage() {
		return "@" . $this->user . ($this->action == 'update' ? " logged: " : " ") . $this->message;
	}

	public static function create(Project $project, User $user, $action, $message) {
		$e = new Event(mktime(), $project->name, $user->name, $action, $message);
		
		DB::execute("INSERT INTO wg_log SET timestamp = NOW(), project_name = :project, user_name = :user, action = :action, message = :message",
			array(
				':project' => $project->name,
				':user' => $user->name,
				':action' => $action,
				':message' => $message
			));

		$hasRoom = !empty($project->slackroom);

		$slackmessage = $hasRoom ? '' : '[Project '.$project->name.']: ';
		$slackmessage .= $e->formatMessage();

		$channel = $hasRoom ? "#" . $project->slackroom : "@" . $project->owner;

		Slack::send($slackmessage, $channel);
	}

	public function formatMessage()
	{
		switch ($this->action) {
			case 'owner':
				return sprintf("@%s has made '@%s' the owner", $this->user, strtolower($this->message));
				break;
			
			case 'leave':
				return sprintf("@%s has left the project", $this->user);
				break;

			case 'join':
				return sprintf("@%s has joined the project", $this->user);
				break;

			case 'focus':
				return sprintf("@%s is focusing on the project", $this->user);
				break;

			case 'unfocus':
				return sprintf("@%s has stopped focusing on the project", $this->user);
				break;

			case 'slackroom':
				return sprintf("Project %s is now attached to #%s in Slack", $this->project, strtolower($this->message));
				break;

			case 'update':
				return sprintf("%s has logged: %s", $this->user, strtolower($this->message));
				break;

			case 'start':
				return sprintf("%s has started project '%s'", $this->user, $this->project);
				break;

			case 'ratio':
				return sprintf("%s says his recent focus ratio was %%s", $this->user, strtolower($this->message));
				break;

			default:
				return sprintf("%s: %s", $this->user, $this->message);
				break;
		}
	}
}