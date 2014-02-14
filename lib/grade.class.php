<?php
class Grade {
	private $data = null;
	private $db = null;
	
	public function __construct($data) {
		$this->db = Db::getInstance();
		$this->data = $data;
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('grade','*', 'gradeId="'.$id.'"')->getResult();
		$grade = $result ? array_shift($result) : false;
		return $grade ? new Grade($grade) : null;
	}
	
	public static function getGrades() {
		$db = Db::getInstance();
		$result = $db->select('grade','*',null, 'cate_id')->getResult();
		$grades = array();
		foreach ($result as $grade) { array_push($grades, new Grade($grade)); }
		return $grades;
	}
	
	public function getCategory() {
		return GradeCategory::get($this->data['cate_id']);
	}
	
	public function getFruitCount() {
		return $this->data['fruitCount'];	
	}
	
	public function getDiameter() {
		return $this->data['diameter'];
	}
	
	public function getSampleWeight() {
		return $this->data['sampleWeight'];
	}
	
	public function getOffGradeValue() {
		return $this->data['offgradereduce'];
	}
	
	public function getGradeId() {
		return $this->data['gradeId'];
	}
	
	public function isMainGrade() {
		return (bool)$this->data['maingrade'];
	}
	
	public function getRejectionRate($season) {
		$result = $this->db->select('sp_rejections','*', 'gradeid="'.$this->data['gradeId'].'" AND season="'.$season->getSeasonId().'"')->getResult();
		$rejecion = $result ? array_shift($result) : null;
		return !is_null($rejecion) ? $rejecion['rate'] : null; 	
	}
	
	public function updateRejection($season, $rate) {
		$result = $this->db->update('sp_rejections', 'rate='.$rate, 'gradeid="'.$this->data['gradeId'].'" AND season='.$season->getSeasonId());
		return $result;
	}
	
	public function setRejection($season, $rate) {
		$result = $this->db->insert('sp_rejections',array($this->data['gradeId'],$season->getSeasonId(),$rate),'gradeid,season,rate');
		return $result;
	}
	
	public function addRejectionRules($season, $rules) {
		foreach ($rules as $rule) {
			$this->db->insert('sp_reject_rules',array($season->getSeasonId(),$this->data['gradeId'],$rule['from'],$rule['to'],$rule['per'],time()),'`season`,`grade`,`from`,`to`,`percentage`,`tson`');
		}
		return true;
	}
	
	public function getRejectionRules($season) {
		$result = $this->db->select('sp_reject_rules','*','season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])->getResult();
		return $result;
	}
	
	public function getWeeklyIncentive($season) {
		$result = $this->db->select('sp_weekly_incentives','*','season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])->getResult();
		$incentive = $result ? array_shift($result) : null;
		return !is_null($incentive) ? $incentive['rate'] : null;
	}
	
	public function addWeeklyIncentive($season, $incentive_rate) {
		if($this->db->insert('sp_weekly_incentives',array($season->getSeasonId(),$this->data['gradeId'],$incentive_rate),'`season`,`grade`,`rate`')) {
			return true;	
		}
		throw new Exception('Unable to update the weekly incentives');
		
	}
	
	public function updateWeeklyIncentive($season, $incentive_rate) {
		if($this->db->update('sp_weekly_incentives','`rate`='.$incentive_rate,'season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])) {
			return true;
		}
		throw new Exception('Unable to update the weekly incentives');
	}
	
	public function addIncentives($season, $incentives) {
		foreach ($incentives as $incentive) {
			$this->db->insert('sp_incentive',array($season->getSeasonId(),$this->data['gradeId'],$incentive['from'],$incentive['to'],$incentive['per']),'`season`,`grade`,`from`,`to`,`rate`');
		}
		return true;
	}
	
	public function getIncentives($season) {
		$result = $this->db->select('sp_incentive','*','season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])->getResult();
		return $result;
	}
	
	public function updateIncentives($season, $incentives) {
		if($this->removeIncentives($season)) {
			foreach ($incentives as $incentive) {
				if(isset($incentive['id']) && !is_null($this->getIncentive($incentive['id']))) {
					$this->db->insert('sp_incentive',array($incentive['id'],$season->getSeasonId(),$this->data['gradeId'],$incentive['from'],$incentive['to'],$incentive['per']),'`id`,`season`,`grade`,`from`,`to`,`rate`');
				}
				else {
					$this->db->insert('sp_incentive',array($season->getSeasonId(),$this->data['gradeId'],$incentive['from'],$incentive['to'],$incentive['per']),'`season`,`grade`,`from`,`to`,`rate`');
				}
			}
			return true;
		}
		return false;
	}
	
	public function removeIncentives($season) {
		if($this->db->delete('sp_incentive','season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function getIncentivableRate($season,$qty) {
		$incentives = $this->getIncentives($season);
		if(is_null($incentives)) { return 0; }
		foreach ($incentives as $incentive) {
			if($qty > (float)$incentive['from'] && $qty <= (float)$incentive['to']) {
				return $qty * $incentive['rate'];
			}
		}
		return 0;
	}
	
	public function getRejectableRate($season,$percentage,$rate) {
		$rules = $this->getRejectionRules($season);
		
		if(is_null($rules)) { return $rate * 100/100; }
		
		foreach ($rules as $rule) {
			if($percentage > $rule['from'] && $percentage <= $rule['to']) {
				return ($rate * $rule['percentage']) / 100;
			}
		}
		return $rate * 100/100; 
	}
	
	public function updateRejectionRules($season, $rules) {
		$time = time();
		if($this->removeRules($season)) { 
			foreach ($rules as $rule) {
				if(isset($rule['id']) && !is_null($this->getRejectionRule($rule['id']))) {
					$this->db->insert('sp_reject_rules',array($rule['id'],$season->getSeasonId(),$this->data['gradeId'],$rule['from'],$rule['to'],$rule['per'],$time),'`id`,`season`,`grade`,`from`,`to`,`percentage`,`tson`');
				}
				else {
					$this->db->insert('sp_reject_rules',array($season->getSeasonId(),$this->data['gradeId'],$rule['from'],$rule['to'],$rule['per'],$time),'`season`,`grade`,`from`,`to`,`percentage`,`tson`');
				}
			}
		}
		return true;
	}

	
	public function removeRules($season) {
		if($this->db->delete('sp_reject_rules','season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])) {
			return true;
		}
		else {
			return false;
		}
	} 
	
	public function getRejectionRule($id) {
		$result = $this->db->select('sp_reject_rules','*','id='.$id)->getResult();
		return $result;
	}
	
	public function getIncentive($id) {
		$result = $this->db->select('sp_week_incentive','*','id='.$id)->getResult();
		return $result;
	}
	
	public function getSchemeValue($season) {
		$result = $this->db->select('sp_scheme_values','*', 'season='.$season->getSeasonId().' AND grade='.$this->data['gradeId'])->getResult();
		$value = $result ? array_shift($result) : null;
		return !is_null($value) ? $value['value'] : null; 
	}
	
	public function setSchemeValue($season, $value) {
		$result = $this->db->insert('sp_scheme_values', array($season->getSeasonId(), $this->data['gradeId'],$value),'`season`,`grade`,`value`');
		return $result ? true : false;
	}
	
	public function updateSchemeValue($season, $value) {
		
		$result = $this->db->update('sp_scheme_values', '`value`='.$value, 'season='.$season->getSeasonId().' AND grade='.$this->data['gradeId']);
		return $result ? true : false;
	}
	
	public static function getGradesByCategory($cateId) {
		$db = Db::getInstance();
		$result = $db->select('grade','*', 'cate_id="'.$cateId.'"','maingrade DESC')->getResult();
		$grades = array();
		foreach ($result as $grade) {
			array_push($grades, new Grade($grade));
		}
		return $grades ;
	}
}
?>