<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 03/02/15
 * Time: 10:49
 */

require_once('config.php');
require_once('slack.php');
require_once('User.php');
require_once('Project.php');
require_once('Event.php');
require_once('DB.php');

if ($token != $_POST['token']) {
	echo "invalid token\n";
	die;
}

if ($prod) {
	DB::init($dbconfig);
	Slack::init($workgroups_webhook_url);
}

$channel = $_POST['channel_name'];
$username = $_POST['user_name'];

$user = User::load($username);
if ($user == null) {
	User::create($username);
	echo "created user\n";
	die;
}

$args = parse_text($_POST['text']);
$command = array_shift($args);

switch ($command)
{
	case 'help':
		echo <<<EOF
Welcome to WorkGroups.
Available commands:

* start [project name] ['project description'] [slack room]
  Starts a new project with the specified name and description. The current user joins the specified project.
  Slack room is optional, and should be specified without '#' mark. If not specified, it will be the current channel. If the current channel is not set, it will be empty.

* join [project name]
  The current user joins the specified project

* leave [project name]
  The current user leaves the specified project

* focus [project name]
  The current user sets focus on the specified project

* unfocus [project name]
  The current user removes focus from the specified project

* projects
  Shows the projects where the current user is a member. [*] indicates projects with focus

* details [project name]
  Shows the details of the specified project:
  - Name
  - Description
  - Owner
  - Slack room
  - Latest logged update
  - Member list. [*] indicates members with focus

* members [project name]
  Shows the member list of the specified project. [*] indicates members with focus

* log [project name] ['update message']
* update [project name] ['update message']
  Log an update for the specified project. The two commands are identical.

* ratio [project name] [success ratio]
  Log the focus success ratio for a week for the specified project. Success ratio can be an integer between 0 and 100, or one of the following:
  'fail' (means 0), 'mixed' (means 50), 'success' (means 100)

* setowner [project name] [user name]
  Changes the owner of the specified project

* setroom [project name] [slack room]
  Changes the slack room of the specified project. Slack room should be specified without '#' mark

Note: [x] means a single word parameter, ['x'] is a parameter string in single quotes (')
EOF;

		break;
	case 'projects':
		$projects = $user->getProjects();
		echo "Total " . count($projects) . " projects: \n";
		foreach ($projects as $project) {
			echo $project['name'] . " " . ($project['focus'] ? "[*]" : "") . "\n";
		}
		break;
	case 'start':
		$name = array_shift($args);
		$desc = array_shift($args);
		$slackroom = array_shift($args);
		if (empty($slackroom) && !empty($channel)) {
			$slackroom = $channel;
		} else {
			$slackroom = "";
		}
		$project = Project::create($name, $desc, $user, $slackroom);
		echo "Project created\n";
		$user->joinProject($project);
		break;
	case 'join':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$user->joinProject($project);
			echo "Joined project " . $projectname . "\n";
		}
		break;
	case 'leave':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else if ($project->owner == $user->name) {
			echo "Owner can not leave the project\n";
		} else {
			$user->leaveProject($project);
			echo "Left project " . $projectname . "\n";
		}
		break;
	case 'focus':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$user->setFocus($project);
			echo "Set focus on " . $projectname . "\n";
			$count = $user->getFocusCount();
			if ($count > $focus_warn_limit) {
				echo "Warning: already focusing on " . $count . " projects!\n";
			}
		}
		break;
	case 'unfocus':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$user->removeFocus($project);
			echo "Removed focus from " . $projectname . "\n";
		}
		break;
	case 'details':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			echo "Project " . $project->name . " details:\n";
			echo "Description: " . $project->desc . "\n\n";
			echo "Owner: " . $project->owner . "\n";
			echo "Slack room: " . (!empty($project->slackroom) ? "#" . $project->slackroom : "not set") . "\n";
			$logs = $project->getLog(1);
			$event = $logs[0];
			echo "Last log: " . $event->getLogMessage() . "\n";
			$members = $project->getMembers();
			echo "Total " . count($members) . " members: \n";
			foreach ($members as $member) {
				echo $member['name'] . " " . ($member['focus'] ? "[*]" : "") . "\n";
			}
		}
		break;
		break;
	case 'members':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$members = $project->getMembers();
			echo "Total " . count($members) . " members: \n";
			foreach ($members as $member) {
				echo $member['name'] . " " . ($member['focus'] ? "[*]" : "") . "\n";
			}
		}
		break;
	case 'log':
		// fallthrough
	case 'update':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$message = array_shift($args);
			Event::create($project, $user, 'update', $message);
			echo "update logged successfully\n";
		}
		break;
	case 'setowner':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$ownername = array_shift($args);
			$owner = User::load($ownername);
			if (empty($owner)) {
				echo "User " . $ownername . " not found\n";
			} else {
				$owner->joinProject($project);
				$project->setOwner($owner, $user);
				echo "Project owner changed successfully\n";
			}
		}
		break;
	case 'setroom':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$slackroom = array_shift($args);
			$project->setRoom($slackroom, $user);
			echo "Project room updated to '" . $slackroom . "'\n";
		}
		break;
	case 'ratio':
		$projectname = array_shift($args);
		$project = Project::load($projectname);
		if (empty($project)) {
			echo "Project does not exist\n";
		} else {
			$ratio = array_shift($args);
			switch (strtolower($ratio)) {
				case 'fail':
					$ratio = 0;
					break;
				case 'mixed':
					$ratio = 50;
					break;
				case 'success':
					$ratio = 100;
					break;
				default:
					$ratio = intval($ratio);
					break;
			}
			Event::create($project, $user, 'ratio', $ratio);
			echo "Logged focus ratio of " . $ratio . " for project " . $project->name . "\n";
		}
		break;
	default:
		echo "unknown command, see help\n";
		die;
}

function parse_text($text)
{
	$ret = array();
	$quote = false;
	$word = "";
	$length = strlen($text);
	for ($i = 0; $i < $length; $i++)
	{
		$char = $text[$i];
		if ($char == "\"")
		{
			$quote = !$quote;
			continue;
		}
		if ($char == "'")
		{
			$quote = !$quote;
			continue;
		}
		if ($char == " " && !$quote)
		{
			$ret[] = $word;
			$word = "";
			continue;
		}
		if ($char == "\\") {
			continue;
		}
		$word .= $char;
	}
	if (!empty($word))
		$ret[] = $word;
	return $ret;
}

?>