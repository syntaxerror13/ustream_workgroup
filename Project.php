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
	public $members;
	public $focusingMembers;
	public $unfocusingMembers;

	public function __construct($name, $desc, $owner, $slackroom = "") {
		$this->name = $name;
		$this->desc = $desc;
		$this->owner = $owner;
		$this->slackroom = $slackroom;
	}

	/**
	 * @param $name
	 * @return null|Project
	 */
	public static function load($name) {
		$row = DB::getOne("SELECT * FROM wg_project WHERE project_name = :name", array(':name' => $name));
		if (!empty($row)) {
			return new Project($row['project_name'], $row['description'], $row['owner_name'], $row['slack_room']);
		} else {
			return null;
		}
	}

	/**
	 * @param $name
	 * @param $desc
	 * @param $owner
	 * @param string $slackroom
	 * @return Project
	 */
	public static function create($name, $desc, $owner, $slackroom = "") {
		DB::execute("INSERT INTO wg_project SET project_name = :name, description = :desc, owner_name = :owner, slack_room = :slackroom",
			array(
				':name' => $name,
				':desc' => $desc,
				':owner' => $owner->name,
				':slackroom' => $slackroom
			));
		$project = new Project($name, $desc, $owner->name, $slackroom);
		Event::create($project, $owner, 'start', 'Started project');
		return $project;
	}

	/**
	 * @return Project[]
	 */
	public static function listAll() {
		$dbresult = DB::getAll("SELECT p.project_name, p.description, p.owner_name, p.slack_room, count(m.user_name) AS members, sum(m.is_focus) AS fmembers ".
			"FROM wg_project p JOIN wg_member m ON p.project_name = m.project_name ".
			"GROUP BY p.project_name, p.description, p.owner_name, p.slack_room");
		$result = array();
		foreach ($dbresult as $row) {
			$p = new Project($row['project_name'], $row['description'], $row['owner_name'], $row['slack_room']);
			$p -> members = $row['members'];
			$p -> focusingMembers = $row['fmembers'];
			$result[] = $p;
		}
		return $result;
	}

	/**
	 * return format:
	 * [{"name" => "Jozsi", "focus" => true},{"name" => "Geza", "focus" => false}]
	 *
	 * @return array
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
	 * @param $limit
	 * @return Event[]
	 */
	public function getLog($limit = false) {
		$sql = "SELECT * FROM wg_log WHERE project_name = :name ORDER BY timestamp DESC";
		if ($limit) {
			$sql .= " LIMIT " . $limit;
		}
		$dbresult = DB::getAll($sql, array(':name' => $this->name));
		$result = array();
		foreach ($dbresult as $row) {
			$result[] = new Event($row['timestamp'], $row['project_name'], $row['user_name'], $row['action'], $row['message']);
		}
		return $result;
	}

	public function setRoom($slackroom, User $user) {
		DB::execute("UPDATE wg_project SET slack_room = :slackroom WHERE project_name = :name",
			array(
				':name' => $this->name,
				':slackroom' => $slackroom
			));
		Event::create($this, $user, 'slackroom', $slackroom);
	}

	public function loadStats()
	{
		//get focusing and non focusing member count
		$dbresult = DB::getAll("SELECT is_focus, count(user_name) AS members FROM wg_member WHERE project_name = :name GROUP BY is_focus", 
			array(':name' => $this->name));
		$this -> focusingMembers = 0;
		$this -> unfocusingMembers = 0; 
		foreach ($dbresult as $row) {
			if ($row['is_focus'] == 1) $this -> focusingMembers = $row['members'];
			else $this -> unfocusingMembers = $row['members'];
		}
		$this -> members = $this -> focusingMembers + $this -> unfocusingMembers;

		
	}

	public function setOwner(User $owner, User $user) {
		DB::execute("UPDATE wg_project SET owner_name = :owner WHERE project_name = :name",
			array(
				':name' => $this->name,
				':owner' => $owner->name
			));
		Event::create($this, $user, 'owner', $owner->name);
	}
}