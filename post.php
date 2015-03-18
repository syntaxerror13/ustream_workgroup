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

* start [project name] ['project description']
  Starts a new project with the specified name and description. The current user joins the specified project.

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
  Log an update for the specified project

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
		$project = Project::create($name, $desc, $user);
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
		echo "not implemented\n";
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
		Slack::send("this is a test", $workgroups_webhook_url, "#".$channel);
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