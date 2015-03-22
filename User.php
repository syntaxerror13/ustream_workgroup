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

	/**
	 * @param $name
	 * @return null|User
	 */
	public static function load($name) {
		$row = DB::getOne("SELECT * FROM wg_user WHERE user_name = :name", array(':name' => $name));
		if (!empty($row)) {
			return new User($row['user_name']);
		} else {
			return null;
		}
	}

	/**
	 * @param $name
	 * @return User
	 */
	public static function create($name) {
		DB::execute("INSERT INTO wg_user SET user_name = :name", array(':name' => $name));
		return new User($name);
	}

	/**
	 * @return User[]
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
	 *
	 * @return array
	 */
	public function getProjects() {
		$dbresult = DB::getAll("SELECT m.project_name, m.is_focus, count(mm.user_name) as members, sum(mm.is_focus) AS fmembers ".
			"FROM wg_member m JOIN wg_member mm ON m.project_name=mm.project_name ".
			"WHERE m.user_name = :name GROUP BY m.project_name, m.is_focus", array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = array('name' => $row['project_name'], 'focus' => ($row['is_focus'] == 1));
		}
		return $result;
	}

	/**
	 * @return Project[]
	 */
	public function getProjectsWithFocus() {
		$dbresult = DB::getAll("SELECT * FROM wg_member WHERE user_name = :name AND is_focus = 1", array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = Project::load($row['project_name']);
		}
		return $result;
	}

	/**
	 * @return int|null
	 */
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

	/**
	 * @param Project $project
	 * @return bool
	 */
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