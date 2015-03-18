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

$channel = $_POST['channel_name'];
$user = $_POST['user_name'];

$userObj = User::load($user);
if ($userObj == null) {
	User::create($user);
	echo "created user";
	die;
}

$args = parse_text($_POST['text']);
//var_dump($args);
$command = array_shift($args);

switch ($command)
{
	case 'help':
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