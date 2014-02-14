<?php
abstract class StockAbstract {
	const TYPE_DQ = 'dq';
	const TYPE_PQ = 'pq';
	const TYPE_RQ = 'rq';
	
	/**
	 * 
	 * @var Project $project 
	 */
	protected $project = null;
	protected $vehicle = null;
	protected $date = null;
	protected $type = null;
	protected $data = array();
	
	public abstract function getDQ();
	public abstract function getPQ();
	public abstract function getRQ();
	public abstract function getMainGradeDQ();
	public abstract function getMainGradePQ();
	public abstract function getMainGradeRQ();
	
	public function __construct($data) {
		$this->data = $data;
	}

	public function setType($type) {
		if(in_array($type, array(GradeCategory::TYPE_LARGE, GradeCategory::TYPE_SMALL))) {
			$this->type = $type;
			return $this;
		}
		throw new Exception('Not a supported type');
	}
	
	public function getProject() {
		return $this->project;
	} 

	public function getVehicleNumber() {
		return $this->vehicle;
	}
	
	public function getDate() {
		return $this->date;
	}
	
	public function setProject($project) {
		$this->project = $project;
		return $this;
	}
	
	public function setVehicleNumber($number) {
		$this->vehicle = $number;
		return $this;
	}
	
	public function setDate($date) {
		$this->date = $date;
		return $this;
	}
	
} 
?>