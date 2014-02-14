<?php
class Center {

	private $_data;
	private $_db;
	
	public function __construct($data=null) {
		if(!is_null($data)) {
			$this->_data = $data;
		}
		$this->_db = Db::getInstance();
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('qa_center','*','centerId='.$id)->getResult();
		$center = !is_null($result) ? array_shift($result) : null;
		return !is_null($center) ? new Center($center) : null;  
	}
	
	public function getId() {
		return $this->_data['centerId'];
	}
	
	public function getName() {
		return $this->_data['centerName'];
	}
	
	public function getProject() {
		return Project::get($this->_data['areaId']);
	}
	
	public static function getCenters($project) {
		$db = Db::getInstance();
		$result = $db->select('qa_center','*','areaId='.$project->getId())->getResult();
		
		if(is_null($result)) { return array(); }
		$centers = array();
		foreach ($result as $center) {
			$centers[] = new Center($center);
		}
		return $centers;
	}
}
?>