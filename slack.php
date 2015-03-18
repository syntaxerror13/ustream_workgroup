<?php
class Slack
{

	public static function httpPost($url, $params)
	{
		$postData = '';

		foreach($params as $k => $v) 
		{ 
			$postData .= $k . '='.$v.'&'; 
		}
		rtrim($postData, '&');

		$ch = curl_init();  

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    

		$output = curl_exec($ch);

		curl_close($ch);
		return $output;
	}

	public static function send($text, $channel = false)
	{
		$payload = array();
		$payload['text'] = $text;
		$payload['link_names'] = 1;
		if ($channel) $payload['channel'] = $channel;

		Slack::httpPost('https://hooks.slack.com/services/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', array('payload' => json_encode($payload)) );
	}	

}

?>