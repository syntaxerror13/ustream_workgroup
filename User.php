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

	public static function create($name) {
		DB::execute("INSERT INTO wg_user SET user_name = :name", array(':name' => $name));
		return new User($name);
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

	public function getFocusCount() {
		$dbresult = DB::getOne("SELECT COUNT(1) AS cnt FROM wg_member WHERE user_name = :name AND is_focus = 1", array(':name' => $this->name));
		if (!empty($dbresult)) {
			return $dbresult['cnt'];
		} else {
			return null;
		}
	}

	public function setFocus(Project $project) {
		DB::execute("UPDATE wg_member SET is_focus = 1 WHERE project_name = :name AND user_name = :user",
			array(
				':name' => $project->name,
				':user' => $this->name
			));
		Event::create($project, $this, 'focus', 'Set focus');
	}

	public function removeFocus(Project $project) {
		DB::execute("UPDATE wg_member SET is_focus = 0 WHERE project_name = :name AND user_name = :user",
			array(
				':name' => $project->name,
				':user' => $this->name
			));
		Event::create($project, $this, 'unfocus', 'Removed focus');
	}

	public function isMemberOf(Project $project) {
		$dbresult = DB::getOne("SELECT * FROM wg_member WHERE project_name = :name AND user_name = :user",
			array(
				':name' => $project->name,
				':user' => $this->name
			));
		return !empty($dbresult);
	}

	public function joinProject(Project $project) {
		if ($this->isMemberOf($project)) {
			return;
		}
		DB::execute("INSERT INTO wg_member SET project_name = :name, user_name = :user, is_focus = 0",
			array(
				':name' => $project->name,
				':user' => $this->name
			));
		Event::create($project, $this, 'join', 'Joined project');
	}

	public function leaveProject(Project $project) {
		DB::execute("DELETE FROM wg_member WHERE project_name = :name AND user_name = :user",
			array(
				':name' => $project->name,
				':user' => $this->name
			));
		Event::create($project, $this, 'leave', 'Left project');
	}
}