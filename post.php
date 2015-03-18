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
	echo "invalid";
	die;
}

DB::init($dbconfig);

$channel = $_POST['channel_name'];
$username = $_POST['user_name'];

$user = User::load($username);
if ($user == null) {
	User::create($username);
	echo "created user\n";
	die;
}

$args = parse_text($_POST['text']);
//var_dump($args);
$command = array_shift($args);

switch ($command)
{
	case 'help':
		echo <<<EOF
Welcome to WorkGroups.
Available commands:
* start [project name] ["project description"]
* join [project name]
* leave [project name]
* focus [project name]
* unfocus [project name]
* log [project name] ["update message"]
* projects
EOF;

		break;
	case 'projects':
		$projects = $user->getProjects();
		foreach ($projects as $project) {
			echo $project['name'] . " " . ($project['focus'] ? "focus" : "") . "\n";
		}
		break;
	case 'start':
		$name = array_shift($args);
		$desc = array_shift($args);
		$project = Project::create($name, $desc, $user);
		echo "Project created\n";
		$user->joinProject($project);
		die;
		break;
	case 'update':
		Slack::send("this is a test", $workgroups_webhook_url, "#".$channel);
		break;
	case 'members':
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
		$word .= $char;
	}
	if (!empty($word))
		$ret[] = $word;
	return $ret;
}

?>