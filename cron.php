<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 20/03/15
 * Time: 12:41
 */

require_once('config.php');
require_once('slack.php');
require_once('User.php');
require_once('Project.php');
require_once('Event.php');
require_once('DB.php');

if ($prod) {
	DB::init($dbconfig);
	Slack::init($workgroups_webhook_url);
}

// task is first command line parameter:
// 'clear' remove focus from everyone and send them a direct message
// 'collect' ask if the focus was successful and wait for an answer

$task = isset($argv[1]) ? $argv[1] : "";

switch ($task) {
	case 'clear':
		$users = User::listAll();
		foreach ($users as $user) {
			$projects = $user->getProjectsWithFocus();
			foreach ($projects as $project) {
				$message = "Weekly cleanup is removing your focus from project " . $project->name . "\nSet it again with the 'focus' command if needed.";
				$channel = "@" . $user->name;
				Slack::send($message, $channel);
			}
		}
		break;
	case 'collect':
		$users = User::listAll();
		foreach ($users as $user) {
			$projects = $user->getProjectsWithFocus();
			foreach ($projects as $project) {
				$message = "How successfully could you focus on project " . $project->name . "?\nPlease respond with the 'result' command.";
				$channel = "@" . $user->name;
				Slack::send($message, $channel);
			}
		}
		break;
	default:
		break;
}
