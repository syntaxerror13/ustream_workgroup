<?php
/**
 * Created by PhpStorm.
 * User: nemethzoltan
 * Date: 18/03/15
 * Time: 11:32
 */

class Project {
	public $name;
	public $desc;
	public $owner;
	public $slackroom;

	public function __construct($name, $desc, $owner, $slackroom = "") {
		$this->name = $name;
		$this->desc = $desc;
		$this->owner = $owner;
		$this->slackroom = $slackroom;
	}

	public static function load($name) {
		$row = DB::getOne("SELECT * FROM wg_project WHERE project_name = :name", array(':name' => $name));
		if (!empty($row)) {
			return new Project($row['project_name'], $row['description'], $row['owner_name'], $row['slack_room']);
		} else {
			return null;
		}
	}

	public static function create($name, $desc, $owner, $slackroom = "") {
		DB::execute("INSERT INTO wg_project SET project_name = :name, description = :desc, owner_name = :owner, slack_room = :slackroom",
			array(
				':name' => $name,
				':desc' => $desc,
				':owner' => $owner->name,
				':slackroom' => $slackroom
			));
		return new Project($name, $desc, $owner->name);
	}

	/**
	 * @return array of Project objects
	 */
	public static function listAll() {
		$dbresult = DB::getAll("SELECT * FROM wg_project");
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = new Project($row['project_name'], $row['description'], $row['owner_name'], $row['slack_room']);
		}
		return $result;
	}

	/**
	 * return format:
	 * [{"name" => "Jozsi", "focus" => true},{"name" => "Geza", "focus" => false}]
	 */
	public function getMembers() {
		$dbresult = DB::getAll("SELECT * FROM wg_member WHERE project_name = :name", array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = array('name' => $row['user_name'], 'focus' => ($row['is_focus'] == 1));
		}
		return $result;
	}

	/**
	 * @return array of Event objects
	 */
	public function getLog() {
		$dbresult = DB::getAll("SELECT * FROM wg_log WHERE project_name = :name", array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = new Event($row['timestamp'], $row['project_name'], $row['user_name'], $row['action'], $row['message']);
		}
		return $result;
	}
}