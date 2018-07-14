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
	
	public static function getProjectsByUser(User $user, $season=null, $gradeCategory = null) {
		$db = Db::getInstance();
		$where = 'userId="'.$user->getUserName().'"';
		
		if(!is_null($season)) {
			$where .= ' AND season="'.$season->getSeasonId().'"';	
		}
		

		if(!is_null($gradeCategory)) {
			$where .= ' AND cate_id="'.$gradeCategory->getId().'"';	
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
	
	public function getFinalPayment($season) {
		$result = $this->_db->select('sp_payment','*','project='.$this->data['areaId'].' AND season='.$season->getSeasonId() . ' AND final_payment=1','id DESC')->getResult();
		$payment = !is_null($result) ? array_shift($result) : false; 
		return $payment ? new Payment($payment) : null;
	}

	public function getPayments() {
		return Payment::getPaymentsByProject($this);
	}
	
	public function getCenters() {
		return Center::getCenters($this);
	}

	public function getAveragePrice($season) {
		$result = $this->_db->select('sp_average_prices','*','season='.$season->getSeasonId().' AND project='.$this->data['areaId'])->getResult();
		$priceRow = $result ? array_shift($result) : null;
		return !is_null($priceRow) ? array(
			'value' 		=> $priceRow['value'], 
			'allowance_st' 	=> $priceRow['allowance_st'],
			'allowance_wl' 	=> $priceRow['allowance_wl']
		) : null;
	}

	public function setAveragePrice($season, $price, $allowance_st, $allowance_wl) {
		$result = $this->_db->insert('sp_average_prices', array($season->getSeasonId(),$this->data['areaId'], $price,$allowance_st, $allowance_wl),'`season`,`project`,`value`,`allowance_st`, `allowance_wl`');
		return $result;
	}
	
	public function updateAveragePrice($season, $price, $allowance_st, $allowance_wl) {
		$result = $this->_db->update('sp_average_prices', '`value`='.$price.',`allowance_st`='.$allowance_st . ',`allowance_wl`='.$allowance_wl, '`season`='.$season->getSeasonId().' AND `project`='.$this->data['areaId']);
		return $result;
	}
	
}
?>