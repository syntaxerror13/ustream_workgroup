<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 18/03/15
 * Time: 11:29
 */

class User {
	public $name;

	public function __construct($name) {
		$this->name = $name;
	}

	public static function load($name) {
		//
		return new User($name);
	}

	public static function listAll() {
		//
	}

	/**
	 * return format:
	 * [{"name" => "Consul", "focus" => true},{"name" => "Logging", "focus" => false}]
	 */
	public function getProjects() {
		//
	}

	public function setFocus(Project $project) {
		//
	}

	public function joinProject(Project $project) {
		//
	}

	public function leaveProject(Project $project) {
		//
	}
}