<?php
class StockLarge extends StockAbstract {
	
	public function __construct($data) {
		$this->data = $data;
		$this->setType(GradeCategory::TYPE_LARGE);
	}
	
	public static function get($p,$v,$d) {
		$db = Db::getInstance();
		$project = Project::get($p);
		$dataSet = array('stock' => null,'grade-stock' => null,'sample' =>  null,'images' => null,'c-quantity' => null);
		//setting stocks
		$stock = $db->select('stock','*','id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();
		
		if($stock) {
			$dataSet['stock'] = array_shift($stock);
			unset($dataSet['stock']['id']);
			unset($dataSet['stock']['vehicleNo']);
			unset($dataSet['stock']['date']);
		}
		else {
			throw new Exception('Stock not found'); 
		}
		$grade_stock = $db->select('gradeStock','*','id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();

		if($grade_stock) {
			foreach ($grade_stock as $grade_data) {
				unset($grade_data['id']); unset($grade_data['vehicleNo']); unset($grade_data['date']); unset($grade_data['project']); 
				$dataSet['grade-stock'][] = $grade_data;
			}
		}
		else {
			throw new Exception('Grade data not found');
		}
		
		$center_qtys =  $db->select('centerQuantity','*','id="'.$project->getId().'" AND vehicleNo="'.$v.'" AND date="'.$d.'"')->getResult();
		if($center_qtys) {
			foreach ($center_qtys as $tm_row) {
				unset($tm_row['id']); unset($tm_row['date']); unset($tm_row['vehicleNo']); unset($tm_row['project']);
				$dataSet['c-quantity'][] = $tm_row;
			}
		}
		
		$stockInstance  = new StockLarge($dataSet); 
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
		
		$stocksUpdates = $db->select('stockUpdates','*',$where)->getResult();
		$stocks = array();
		
		if($stocksUpdates) {
			foreach ($stocksUpdates as $set) {
				$stock = StockLarge::get($project->getId(), $set['vehicleNo'], $set['date']);
				array_push($stocks, $stock);
			}
		}
		return $stocks;
		
	}
	
	/**
	* Returns actual quantity
	*/
	public function getAQ() {
		$aq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			$aq += $gradeStock['trueWeight'];
		}
		return $aq;
	}

	public function getDQ() {
		$dq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			$dq += $gradeStock['notedWeight'];
		}
		return $dq;
	}
	
	public function getPQ() {
		$pq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			$pq += $gradeStock['payableQuantity'];
		}
		return $pq;
	}
	
	public function getRQ() {
		$rq = 0;
		return $this->getAQ() - $this->getPQ();
	}
	
	public function getMainGradeAQ() {
		$aq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $this->project->getGradeCategory()->getMainGrade()->getGradeId()) {
				$aq += $gradeStock['trueWeight'];
			}
		}
		return $aq;
	}
	

	public function getMainGradeDQ() {
		$dq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $this->project->getGradeCategory()->getMainGrade()->getGradeId()) {
				$dq += $gradeStock['notedWeight'];
			}
		}
		return $dq;
	}
	
	public function getMainGradePQ() {
		$pq = 0;
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $this->project->getGradeCategory()->getMainGrade()->getGradeId()) {
				$pq += $gradeStock['payableQuantity'];
			}
		}
		return $pq;
	}
	
	public function  getMainGradeRQ() {
		return $this->getMainGradeAQ() - $this->getMainGradePQ();
	}
	
	public function getAqByGrade($grade) {
		/* Going through for loop. For better performence this should be avoided */
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $grade->getGradeId()) {
				return $gradeStock['trueWeight'];
			}
		}
	}
	
	public function getDqByGrade($grade) {
		/* Going through for loop. For better performence this should be avoided */
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $grade->getGradeId()) {
				return $gradeStock['notedWeight'];
			}
		}
	}
	
	public function getPqByGrade($grade) {
		/* Going through for loop. For better performence this should be avoided */
		foreach ($this->data['grade-stock'] as $gradeStock) {
			if($gradeStock['gradeId'] == $grade->getGradeId()) {
				return $gradeStock['payableQuantity'];
			}
		}
	}
	
	public function getRqByGrade($grade) {
		/* Going through for loop. For better performence this should be avoided */
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
				return ($tm_row['grade1'] + $tm_row['grade2'] + $tm_row['grade3'] + $tm_row['grade4'] + $tm_row['grade5']);
			}
		}
		return 0;
	}
}
?>