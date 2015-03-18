<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 18/03/15
 * Time: 12:27
 */

class DB {
	private static $instance = null;

	public static function init($config) {
		self::$instance = new PDO('mysql:host='.$config['host'].';dbname='.$config['name'].';charset=utf8', $config['user'], $config['pass']);
	}

	/**
	 * @param null $config
	 * @return PDO
	 */
	public static function getInstance($config = null) {
		if (self::$instance != null) {
			return self::$instance;
		} else if ($config != null) {
			self::init($config);
			return self::$instance;
		} else {
			return null;
		}
	}

	public static function getOne($sql, $params = array())
	{
		$db = self::getInstance();
		$stmt = $db->prepare($sql);
		foreach ($params as $pname => $pvalue) {
			$stmt->bindParam($pname, $pvalue);
		}
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return isset($results[0]) ? $results[0] : null;
	}

	public static function getAll($sql, $params = array())
	{
		$db = self::getInstance();
		$stmt = $db->prepare($sql);
		foreach ($params as $pname => $pvalue) {
			$stmt->bindParam($pname, $pvalue);
		}
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	public static function execute($sql, $params = array())
	{
		$db = self::getInstance();
		$stmt = $db->prepare($sql);
		foreach ($params as $pname => $pvalue) {
			$stmt->bindParam($pname, $pvalue);
		}
		$stmt->execute();
	}
}