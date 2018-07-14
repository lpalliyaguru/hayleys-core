<?php
class StockSmall extends StockAbstract {
	
	public function __construct($data) {
		$this->data = $data;
		$this->setType(GradeCategory::TYPE_LARGE);
	}
	
	public static function get($p,$v,$d) {
		$db = Db::getInstance();
		$project = Project::get($p);
		$dataSet = array('crop' => null,'belongs' => null,'sample' =>  null,'images' => null,'c-quantity' => null);
		$crop = $db->select('small_crop','*','id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();
		/* stock crop */
		if($crop) {
			$dataSet['crop'] = array_shift($crop);
			unset($dataSet['crop']['id']);
			unset($dataSet['crop']['vehicleNo']);
			unset($dataSet['crop']['date']);
		}
		else {
			throw new Exception('Stock not found');
		}
		
		$belongs = $db->select('small_belongs','*','id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();
		if($belongs) {
			foreach ($belongs as $belong) {
				unset($belong['id']); unset($belong['vehicleNo']); unset($belong['date']); unset($belong['project']);
				$dataSet['belongs'][] = $belong;
			}
		}
		else {
			throw new Exception('Stock belong data not found');
		}
		
		$center_qtys =  $db->select('centerQuantitySmall', '*', 'id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();
		
		error_log('Getting data for Center TM : found ' . count($center_qtys) . ' records');
		if($center_qtys) {
			foreach ($center_qtys as $qty_row) {
				unset($qty_row['id']); unset($qty_row['date']); unset($qty_row['vehicleNo']); unset($qty_row['project']);
				$dataSet['c-quantity'][] = $qty_row;
			}
		}
		
		$stockInstance  = new StockSmall($dataSet);
		$stockInstance->setProject($project)
					->setDate($d)
					->setVehicleNumber($v);
		return $stockInstance;
		
	}
	
	public static function getStocks($project, $from , $to, $station=false) {
		$db = Db::getInstance();
		if($station) {
			$where = 'areaId='.$project->getId().' AND stationId='.$station.' AND date<="'.$to.'" AND date>"'.$from.'"';
		}
		else {
			$where = 'areaId='.$project->getId().' AND date<="'.$to.'" AND date>"'.$from.'"';
		}
	
		$stocksUpdates = $db->select('stockUpdates_small','*',$where)->getResult();
		$stocks = array();
	
		if($stocksUpdates) {
			foreach ($stocksUpdates as $set) {
				$stock = StockSmall::get($project->getId(), $set['vehicleNo'], $set['date']);
				array_push($stocks, $stock);
			}
		}
		return $stocks;
	
	}

	public function getAQ() {
		return $this->data['crop']['total_AQ'];
	}

	public function getDQ() {
		return $this->data['crop']['total_DQ'];
	}
	
	public function getPQ() {
		$pq = $this->data['crop']['11-14Q'] + $this->data['crop']['14-17Q'] + $this->data['crop']['17-29Q'] + $this->data['crop']['29-44Q'] + $this->data['crop']['crs'];
		return $pq;
	
	}
	
	public function getRQ() {
		$rq = $this->getAQ() - $this->getPQ();
		return $rq;
	}
	
	public function getMapper() {
		return array(
					49 => '11-14Q',
					50 => '14-17Q',
					51 => '17-29Q',
					52 => '29-44Q',
					53 => 'crs'	
				);
	}
	
	public function getMapperV2() {
		$subgrades = Grade::getGradesByCategory(5);
		return array(
				49 => '11-14',
				50 => '14-17',
				51 => '17-29',
				52 => '29-44',
				53 => 'CRS'
		);
	}
	
	public function getMainGradeAQ() {
		$pq = 0; $mapper = $this->getMapperV2(); 
		foreach ($this->data['belongs'] as $gradeBelong) {
			if($gradeBelong['gradeName'] == $mapper[$this->project->getGradeCategory()->getMainGrade()->getGradeId()]) {
				return $gradeBelong['AQ'];
			}
		}
	
	}
	
	public function getMainGradeDQ() {
		$pq = 0; $mapper = $this->getMapperV2(); 
		foreach ($this->data['belongs'] as $gradeBelong) {
			if($gradeBelong['gradeName'] == $mapper[$this->project->getGradeCategory()->getMainGrade()->getGradeId()]) {
				return $gradeBelong['DQ'];
			}
		}
	
	}
	
	public function getMainGradePQ() {
		$pq = 0; $mapper = $this->getMapperV2();
		foreach ($this->data['belongs'] as $gradeBelong) {
			$pq += $gradeBelong[$mapper[$this->project->getGradeCategory()->getMainGrade()->getGradeId()]];
		}
		return $pq;
		
	}
	
	public function getMainGradeRQ() {
		return ($this->getMainGradeAQ() - $this->getMainGradePQ());
	}

	/**
	 * 
	 * @param Grade $grade
	 * @return unknown
	 */
	public function getAqByGrade($grade) {
		$mapper = $this->getMapperV2(); 
		foreach ($this->data['belongs'] as $gradeBelong) {
			if($gradeBelong['gradeName'] == $mapper[$grade->getGradeId()]) {
				return $gradeBelong['AQ'];
			}
		}
	}
	
	/**
	 * 
	 * @param Grade $grade
	 * @return unknown
	 */
	public function getDqByGrade($grade) {
		$mapper = $this->getMapperV2(); 
		foreach ($this->data['belongs'] as $gradeBelong) {
			if($gradeBelong['gradeName'] == $mapper[$grade->getGradeId()]) {
				return $gradeBelong['DQ'];
			}
		}
	}
	
	public function getPqByGrade($grade) {
		$pq = 0; $mapper = $this->getMapperV2();
		foreach ($this->data['belongs'] as $gradeBelong) {
			$pq += $gradeBelong[$mapper[$grade->getGradeId()]];
		}
		return $pq;
	
	}
	
	public function getRqByGrade($grade) {
		return ($this->getAqByGrade($grade) - $this->getPqByGrade($grade));
	}
	
	public function getTmQty() {
		$sum = 0;
		foreach ($this->data['c-quantity'] as $tm_row) {
			$sum += ($tm_row['grade1'] + $tm_row['grade2'] + $tm_row['grade3'] + $tm_row['grade4']+ $tm_row['grade5']);
		}
		return $sum;
	}
	
	public function getCenterQuantity(Center $center) {
		foreach ($this->data['c-quantity'] as $tm_row) {
			if($tm_row['center'] == $center->getName()) {
				return ($tm_row['grade1'] + $tm_row['grade2'] + $tm_row['grade3'] + $tm_row['grade4']+ $tm_row['grade5']);
			}
		}
		return 0;
	}
	
}
?>