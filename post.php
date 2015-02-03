<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 03/02/15
 * Time: 10:49
 */

$token = "xETbF5kv5xhklaHduU95dqle";
//var_dump($_POST);

if ($token != $_POST['token']) {
	echo "invalid";
	die;
}

$channel = $_POST['channel_name'];
$user = $_POST['user_name'];

$args = parse_text($_POST['text']);
var_dump($args);

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
			$quote = !$quote;
		if ($char == " " && !$quote)
		{
			$ret[] = $word;
			$word = "";
		}
	}
	if (!empty($word))
		$ret[] = $word;
	return $ret;
}

?>