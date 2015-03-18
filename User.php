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
		$row = DB::getOne("SELECT * FROM wg_user WHERE user_name = :name", array(':name' => $name));
		if (!empty($row)) {
			return new User($row['user_name']);
		} else {
			return null;
		}
	}

	/**
	 * @return array of User objects
	 */
	public static function listAll() {
		$dbresult = DB::getAll("SELECT * FROM wg_user");
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = new User($row['user_name']);
		}
		return $result;
	}

	/**
	 * return format:
	 * [{"name" => "Consul", "focus" => true},{"name" => "Logging", "focus" => false}]
	 */
	public function getProjects() {
		$dbresult = DB::getAll("SELECT * FROM wg_member WHERE user_name = :name", array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = array('name' => $row['project_name'], 'focus' => ($row['is_focus'] == 1));
		}
		return $result;
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