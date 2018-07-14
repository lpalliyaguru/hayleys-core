<?php
class Payment {
	const ERROR_SAVE_APPROVAL = 515;
	const ERROR_REMOVE_APPROVAL = 516;
	const ERROR_REMOVE_PAYMENT = 517;
	
	private $_data = null;
	private $st_inData = null;
	private $_db = null;
	
	public function __construct($data=null) {
		$this->_data = $data;
		$this->_db = Db::getInstance();
	}
	
	public static function getPayment($id) {
		$db = Db::getInstance();
		$result = $db->select('sp_payment','*','id='.(int)$id)->getResult();
		$payment = $result ? array_shift($result) : null;
		return !is_null($payment) ? new Payment($payment) : null; 
	}
	
	public static function getPaymentByNumber($project, $number) {
		$db = Db::getInstance();
		$result = $db->select('sp_payment','*','payment_no='.(int)$id.' AND project='.(int)$project)->getResult();
		$payment = $result ? array_shift($result) : null;
		return !is_null($payment) ? new Payment($payment) : null;
	} 
	
	public static function getPaymentsByProject($project) {
		$db = Db::getInstance();
		$result = $db->select('sp_payment','*','project='.$project->getId())->getResult();
		$payments = array();
		if($result) {
			foreach ($result as $payment) { $payments[] = new Payment($payment); }
		}
	
		return $payments;
	}

	public static function getPaymentsByProjectAndSeason($project, $season, $isFinal = false) {
		$db = Db::getInstance();
		$where = sprintf('project=%s AND season=%s', $project->getId(), $season->getSeasonId());
		
		if($isFinal) {
			$where .= ' AND final_payment=1';
		}

		$result = $db->select(
			'sp_payment',
			'*',
			$where
		)->getResult();
		
		$payments = array();
		
		if($result) {
			foreach ($result as $payment) { $payments[] = new Payment($payment); }
		}
	
		return $payments;
	}	

	public function getId() {
		return $this->_data['id'];
	}
	
	public function getProject() {
		return Project::get($this->_data['project']);
	}
	
	public function getSeason() {
		return Season::get($this->_data['season']);
	}
	
	public function setSeason($season) {
		$this->_data['season'] = $season->getSeasonId(); 
		return $this;
	}

	public function setProject($project) {
		$this->_data['project'] = $project->getId(); 
		return $this;
	}
	
	public function setRejection($qty) {
		$this->_data['rejection'] = $qty;
		return $this;
	}
	
	public function setPaymentNumber($num) {
		$this->_data['payment_no'] = $num;
		return $this;
	}
	
	public function setWeekStart($tson) {
		$this->_data['week_start'] = $tson;
		return $this;
	}
	
	public function getWeekStart() {
		return $this->_data['week_start'];
	}
	
	public function setWeekEnd($tson) {
		$this->_data['week_end'] = $tson;
		return $this;
	}
	
	public function getWeekEnd() {
		return $this->_data['week_end'];
	}
	
	public function setStinData($data) {
		$this->st_inData = $data;
		return $this;
	}
	
	public function setMainGradeCumulativeQuntity($type, $qty) {
		if(!in_array($type, array(StockAbstract::TYPE_AQ, StockAbstract::TYPE_DQ, StockAbstract::TYPE_PQ, StockAbstract::TYPE_RQ))) { throw new Exception('Not a supported type.'); }
		
		if($type == StockAbstract::TYPE_DQ) {
			$this->_data['g1_cum_qty_dq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_PQ) {
			$this->_data['g1_cum_qty_pq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_RQ) {
			$this->_data['g1_cum_qty_rq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_AQ) {
			$this->_data['g1_cum_qty_aq'] = $qty;
		}
		return $this;
	}
	
	public function getMainGradeCumulativeQuntity($type=false) {
		if($type == StockAbstract::TYPE_DQ) {
			return $this->_data['g1_cum_qty_dq'];
		}
		else if($type == StockAbstract::TYPE_PQ) {
			return $this->_data['g1_cum_qty_pq'];
		}
		else if($type == StockAbstract::TYPE_AQ) {
			return $this->_data['g1_cum_qty_aq'];
		}
		else if($type == StockAbstract::TYPE_RQ) {
			return $this->_data['g1_cum_qty_rq'];
		}
		return false;
	}
	
	public function setCumulativeTotalQuantity($type, $qty) {
		if($type == StockAbstract::TYPE_DQ) {
			$this->_data['cum_total_dq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_PQ) {
			$this->_data['cum_total_pq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_AQ) {
			$this->_data['cum_total_aq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_RQ) {
			$this->_data['cum_total_rq'] = $qty;
		}
		return $this;
	}
	
	public function getCumulativeTotalQuantity($type=false) {
		if($type == StockAbstract::TYPE_DQ) {
			return $this->_data['cum_total_dq'];
		}
		else if($type == StockAbstract::TYPE_PQ) {
			return $this->_data['cum_total_pq'];
		}
		else if($type == StockAbstract::TYPE_AQ) {
			return $this->_data['cum_total_aq'];
		}
		else if($type == StockAbstract::TYPE_RQ) {
			return $this->_data['cum_total_rq'];
		}
		return false;
	}
	
	public function setWeeklyTotalQuantity($type=false, $qty) {
		if($type == StockAbstract::TYPE_DQ) {
			$this->_data['week_total_dq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_PQ) {
			$this->_data['week_total_pq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_AQ) {
			$this->_data['week_total_aq'] = $qty;
		}
		else if($type == StockAbstract::TYPE_RQ) {
			$this->_data['week_total_rq'] = $qty;
		}
		return $this;
	}
	
	public function getCumulativePayment() {
		return $this->_data['cum_payment'];
	}
	
	public function setCumulativePayment($payment) {
		$this->_data['cum_payment'] = $payment;
		return $this;
	}
	
	public function setFinalPayment($flag) {
		$this->_data['final_payment'] = $flag;
		return $this;
	}
	
	public function getFinalPayment() {
		return $this->_data['final_payment'];
	}

	public function setNetPayment($payment) {
		$this->_data['net_payment'] = $payment;
		return $this;
	}
	
	public function getNetPayment() {
		return $this->_data['net_payment'];
	}
	
	public function getPaymentNumber() {
		return $this->_data['payment_no'];
	}
	
	public function getProcessedDate() {
		return $this->_data['tson'];
	}
	
	public function setDoneBy(User $user) {
		$this->_data['done_by'] = $user->getUserName();
		return $this;
	}
	
	public function getDoneByUser() {
		return User::getUser($this->_data['done_by']);
	}
	
	public function save() {
		$insert = array(
				$this->_data['payment_no'],
				$this->_data['project'],
				$this->_data['season'],
				$this->_data['week_start'],
				$this->_data['week_end'],
				$this->_data['g1_cum_qty_aq'],
				$this->_data['g1_cum_qty_pq'],
				$this->_data['g1_cum_qty_rq'],
				$this->_data['week_total_aq'],
				$this->_data['week_total_pq'],
				$this->_data['week_total_rq'],
				$this->_data['cum_total_aq'],
				$this->_data['cum_total_pq'],
				$this->_data['cum_total_rq'],
				$this->_data['cum_payment'],
				$this->_data['net_payment'],
				$this->_data['final_payment'],
				time(),
				$this->_data['rejection'],
				$this->_data['done_by']
				);
		$fields = '`payment_no`,`project`,`season`,`week_start`,`week_end`,`g1_cum_qty_aq`,`g1_cum_qty_pq`,`g1_cum_qty_rq`,`week_total_aq`,`week_total_pq`,`week_total_rq`,`cum_total_aq`,`cum_total_pq`,`cum_total_rq`,`cum_payment`,`net_payment`,`final_payment`,`tson`,`rejection`,`done_by`';
		$this->_db->startTransaction();
		if($id = $this->_db->insert('sp_payment', $insert, $fields)){
			try {
				$this->sendForApproval($id, $this->_data['done_by']);
				$this->saveStinData($id);
				$this->_db->commitTransaction();
			}
			catch (Exception $e) {
				$this->_db->rollBackTransaction();
			}
			
			$this->_data['id'] = $id;
			return $id;
		}
		else {
			$this->_db->rollBackTransaction();
			Log::put('Payment save error: '.$this->_db->getError());
			throw new Exception('Unable to save the payment. You may try again.');
		}
	}
	
	private function saveStinData($id) {
		foreach ($this->st_inData->qty as $center => $value) {
			$out = property_exists($this->st_inData->qty_out, $center) ? $this->st_inData->qty_out->$center : 0;
			$grn = property_exists($this->st_inData->qty_grn, $center) ? $this->st_inData->qty_grn->$center : 0;

			$out = $out != '' ? $out : 0;
			$grn = $grn != '' ? $grn : 0;
			$value = $value != '' ? $value : 0;
			
			error_log('out :' . json_encode(array($out, $grn)));

			$insert = array($id,$center,$value,$out,$grn);
			
			if($this->_db->insert('sp_stin_set', $insert,'`paymentid`,`center`,`in`,`out`,`grn`')) {
			}
			else {
				throw new Exception('Cannot update stin data.',1002);
			}
		}
	}
	
	public function savePaymentSheet($html) {
		$db = Factory::getMongo();
		if($db->sheets->update(array('id' => $this->_data['id']),array('$set' => array('sheet' => $html)),array('upsert' => true))) {
			return true;
		}
		else {
			$this->removePayment($this->_data['id']);
			throw new Exception('Unable to save the payment sheet. You may try again.');
		}
	}
	
	public function removePayment($id) {
		if($this->_db->delete('sp_payment','id='.$id)) {
			return true;
		}
		else {
			throw new Exception('Error in payment remove',self::ERROR_REMOVE_PAYMENT);
		}
	}
	
	public function remove() {
		$this->_db->startTransaction();
		try {
			$this->removePayment($this->_data['id']);
			$this->removeApprovals($this->_data['id']);
			$this->removeStData();
			$this->_db->commitTransaction();
			$this->removePaymentSheetHtml();
			return true;
		}
		catch (Exception $e) {
			$this->_db->rollBackTransaction();
		}
	
	}
	
	public function sendForApproval($id, $by) {
		$insert = array($id, 0, $by);	
		$rows = '`id`,`approved`,`by`';
		
		if($this->_db->insert('sp_payment_approvals', $insert, $rows)){
			return true;
		}
		else {
			throw new Exception('Unable to send the payment for approval.Cancelling the payment.', self::ERROR_SAVE_APPROVAL);
		} 
	}
	
	public function removeApprovals($id) {
		if($this->_db->delete('sp_payment_approvals', 'id='.$id)){
			return true;
		}
		else {
			throw new Exception('Unable remove approval.', self::ERROR_REMOVE_APPROVAL);
		}
	}
	
	public function getPaymentSheetHtml() {
		$db = Factory::getMongo();
		$result = $db->sheets->findOne(array('id' => (int)$this->_data['id']));
		if($result) {
			$html = $result['sheet'];
			return $html;
		}
		return false;
	}
	
	public function removePaymentSheetHtml() {
		$db = Factory::getMongo();
		$result = $db->sheets->remove(array('id' => (int)$this->_data['id']));
		if($result) {
			return true;
		}
		return false;
	}
	
	public function getStData() {
		$data = $this->_db->select('sp_stin_set','*','paymentid='.$this->_data['id'])->getResult();
		return $data;
	}
	
	public function removeStData() {
		if($this->_db->delete('sp_stin_set','paymentid='.$this->_data['id'])) {
			return true;
		}
		throw new Exception('Unable to remove st data');
	}
}
?>