<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 18/03/15
 * Time: 12:27
 */

class DB {
	private static $instance = null;

	public function __construct($config) {
		//
	}

	public static function init($config) {
		self::$instance = new DB($config);
	}

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
}