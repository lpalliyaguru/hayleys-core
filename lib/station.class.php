<?php
class Station {
	private $data = null;
	private $db = null;
	
	public function __construct($data) {
		$this->data = $data;
		$this->db = Db::getInstance();
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('stations','*','id="'.$id.'"')->getResult();
		$station = $result ? array_shift($result) : null;
		return $station ? new Station($station) : null;
	}
	
	public static function getStations() {
		$db = Db::getInstance(); $stations = array();
		$result = $db->select('stations','*')->getResult();
		if($result) {
			foreach ($result as $station) {
				array_push($stations, new Station($station));
			}
		}
		return $stations;
	}
	
	public function getId() {
		return $this->data['id'];
	}
	
	public function getName() {
		return $this->data['name'];
	}
	
	public function setTransportRate($season, $project, $rate,$upate=false) {
		if(!$upate) {
			$result = $this->db->insert('sp_transport_rates',array($season->getSeasonId(),$project->getId(),$this->data['id'],$rate),'`season`,`project`,`station`,`rate`');
		}
		else {
			$result = $this->db->update('sp_transport_rates','`rate`='.$rate,'`season`='.$season->getSeasonId().' AND `project`='.$project->getId().' AND `station`='.$this->data['id']);
		}
			
		if($result) { return  true; } 
		else { throw new Exception('Unable to update data.'); }
		
	}
	
	public function getTransportRate($season, $project) {
		$result = $this->db->select('sp_transport_rates','*','season='.$season->getSeasonId().' AND project='.$project->getId().' AND station='.$this->data['id'])->getResult();
		$rate = $result ? array_shift($result) : null;
		return !is_null($rate) ? $rate['rate'] : null; 
	
	}
}
?>