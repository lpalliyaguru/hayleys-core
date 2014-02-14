<?php
class Transaction extends Controller {
	
	const TYPE_ADVANCE = 'ADVANCE';
	const TYPE_RECOVERY = 'RECOVERY';
	const TYPE_PAYMENT = 'PAYMENT';
	const TYPE_OTHER_RECOVERY = 'OTHER_RECOVERY';
	
	private $_data = null;
	private $_db = null;
	
	public function __construct($data=null) {
		$this->_db = Db::getInstance();
		$this->_data = !is_null($data) ? $data : array();
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('sp_transactions','*', 'id='.$id)->getResult();
		$trans = $result ? array_shift($result) : null;
		return !is_null($trans) ? new Transaction($trans) : null;
	}
	
	public function getSupplier() {
		return User::getUser($this->_data['supplier_id']);
	}
	
	public function getAmount() {
		return $this->_data['amount'];
	}
	/**
	 * 
	 * @param User $user
	 */
	public static function getTransactions($user) {
		$db = Db::getInstance();
		$result = $db->select('sp_transactions','*', 'supplier_id="'.$user->getUserName().'"','date ASC')->getResult();
		$transactions = array();
		if(!is_null($result)) { 
			foreach ($result as $transaction) {
				array_push($transactions, new Transaction($transaction));
			}
		}
		return $transactions;
	}

	public function setPaymentId($id) {
		$this->_data['payment_id'] = $id;
		return $this;
	}
	
	public function getId() {
		return $this->_data['id'];
	}
	
	public function getDate() {
		return $this->_data['date'];
	}
	
	public function getType() {
		return $this->_data['type'];
	}
	
	public function setSupplier($supplier) {
		$this->_data['supplier_id'] = $supplier->getUserName(); 
		return $this;
	}
	
	public function setAmount($amount) {
		$this->_data['amount'] = $amount;
		return $this;
	}
	
	public function setDate($date) {
		$this->_data['date'] = $date;
		return $this;
	}
	
	public function setType($type) {
		$this->_data['type'] = $type;
		return $this;
	}
	
	public function setRemarks($remarks) {
		$this->_data['remarks'] = $remarks;
		return $this;
	}
	
	public function getRemarks() {
		return $this->_data['remarks'];
	}
	
	public function save() {
		$insert = array(
					$this->_data['supplier_id'],
					$this->_data['amount'],
					$this->_data['date'],
					$this->_data['type'],
					isset($this->_data['payment_id']) ? $this->_data['payment_id'] : 0,
					isset($this->_data['remarks']) ? $this->_data['remarks'] : '' 	
				);
		
		$result = $this->_db->insert('sp_transactions', $insert, '`supplier_id`,`amount`,`date`,`type`,`payment_id`,`remarks`');
		
		if($result) {
			return true;
		}
		else {
			throw new Exception('Unable to save the transaction.');
		}
	}
	
	public function isAdvance() {
		return $this->_data['type'] == self::TYPE_ADVANCE ? true : false;
	}
	
	public function isRecovery() {
		return $this->_data['type'] == self::TYPE_RECOVERY ? true : false;
	}
	
	public function isPayment() {
		return $this->_data['type'] == self::TYPE_PAYMENT ? true : false;
	}
	
	public function remove() {
		$result = $this->_db->delete('sp_transactions','id='.$this->_data['id']);
		return $result;
	}
	
	public static function getTotalDebit($user) {
		$transactions = self::getTransactions($user);
		$debit = 0;
		foreach ($transactions as $transaction) {
			if($transaction->isAdvance() /*|| $transaction->isPayment()*/) { $debit += $transaction->getAmount(); }	
			else if($transaction->isRecovery()) { $debit -= $transaction->getAmount(); }	
		}
		return $debit;
	}
	
}
?>