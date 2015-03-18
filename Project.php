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

	public function __construct($name, $desc, $owner) {
		$this->name = $name;
		$this->desc = $desc;
		$this->owner = $owner;
	}

	public static function load($name) {
		//
		return new Project($name, $desc, $owner);
	}

	public function getMembers() {
		//
	}

	public function getLog() {
		//
	}
}