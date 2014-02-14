<?php
class Project {
	private $data; 
	private $_db;	
	
	public function __construct($data) {
		$this->data = $data;
		$this->_db = Db::getInstance();
	}
	
	public function getName() {
		return $this->data['areaName'];
	}
	
	public function getId() {
		return $this->data['areaId'];
	
	}
	
	public function getSeasonId() {
		return $this->data['season'];
	}
	
	public function getAreaType() {
		return $this->data['areaType'];
	}
	
	public function getIncharge() {
		return User::getUser($this->data['userId']);
	}
	
	public function getGradeCategory() {
		return GradeCategory::get($this->data['cate_id']);
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('qa_area','*', 'areaId="'.$id.'"')->getResult();
		$project_data = $result ? array_shift($result) : null;
		return $project_data ? new Project($project_data) : null;
	
	}
	
	public static function getProjectsByUser(User $user, $season=null) {
		$db = Db::getInstance();
		$where = 'userId="'.$user->getUserName().'"';
		
		if(!is_null($season)) {
			$where .= ' AND season="'.$season->getSeasonId().'"';	
		}
		
		$result = $db->select('qa_area','*', $where)->getResult();
		$projects = array();
		if($result) {
			foreach ($result as $project) { $projects[] = new Project($project); } 
		}
		return $projects;
				
	}
	
	public function getMainGrade() {
		return $this->getGradeCategory()->getMainGrade();
	}
	
	public function getStocks($from, $to, $station=false) {
		if($this->getGradeCategory()->isLarge()) {
			return StockLarge::getStocks($this, $from, $to, $station);
		}
		else {
			return StockSmall::getStocks($this, $from, $to, $station);
		}
	}
	
	public function hasLastPayment($season) {
		$result = $this->_db->select('sp_payment','*','project='.$this->data['areaId'].' AND season='.$season->getSeasonId())->getResult();
		return !is_null($result) ? true : false; 
	}
	
	public function getLastPayment($season) {
		$result = $this->_db->select('sp_payment','*','project='.$this->data['areaId'].' AND season='.$season->getSeasonId(),'id DESC')->getResult();
		$payment = !is_null($result) ? array_shift($result) : false; 
		return $payment ? new Payment($payment) : null;
	}
	
	public function getPayments() {
		return Payment::getPaymentsByProject($this);
	}
	
	public function getCenters() {
		return Center::getCenters($this);
	}
	
}
?>