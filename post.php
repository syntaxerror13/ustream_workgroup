<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 03/02/15
 * Time: 10:49
 */

require_once('config.php');

if ($token != $_POST['token']) {
	echo "invalid";
	die;
}

$channel = $_POST['channel_name'];
$user = $_POST['user_name'];

$args = parse_text($_POST['text']);
//var_dump($args);
$command = array_shift($args);

switch ($command)
{
	case 'help':
		break;
	case 'update':
		webhook_post($workgroups_webhook_url, "this is a test");
		break;
	case 'members':
		break;
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

function webhook_post($url, $text)
{
	$data = array('payload' => json_encode(array('text' => $text)));

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HEADER, 0);

	curl_exec($ch);

	curl_close($ch);
}

?>