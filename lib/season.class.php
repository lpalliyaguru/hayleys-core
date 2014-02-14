<?php
class Season {
	private $data;
	private $_db;
	
	
	public static function getSeasons() {
		
		$db = Db::getInstance();
		$db->select('seasons','*',null,'flag DESC');
		$result = $db->getResult();
		
		if(!$result) { return array(); }
		$seasons = array();
		foreach ($result as $season) { array_push($seasons, new Season($season)); }
		return $seasons;
	}
	
	public function __construct($data) {
		$this->data = $data;
		$this->_db = Db::getInstance();
	}
	
	public function getProjects() {
		$list = $this->_db->select('area','*','season="'.$this->data['seasonId'].'"')->getResult();
		
		if(!$list) { return array(); }
		$projects = array();
		foreach ($list as $project) {
			array_push($projects, new Project($project));
		}
		return $projects;
		
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('seasons','*','seasonId="'.$id.'"')->getResult();
		$season_data = $result ? array_shift($result) : null;
		return $season_data ? new Season($season_data) : null;
		
	}
	
	public function getSeasonId() {
		return $this->data['seasonId'];
	}
	
	public function getSeasonName() {
		return $this->data['seasonName'];
	}
	
	public function getSeasonStartDate() {
		return $this->data['startDate'];
	}
	
	public function getSeasonEndDate() {
		return $this->data['endDate'];
	}
	
	public function getRemarks() {
		return $this->data['remarks'];
	}
	
	public function getFlag() {
		return $this->data['flag'];
	}
	
	public static function getPresentSeason() {
		$db = Db::getInstance();
		$result = $db->select('seasons','*','flag=1')->getResult();
		$seasonData = $result ? array_shift($result) : null;
		return $seasonData ? new Season($seasonData) : null;
	}
}
?>