<?php
class GradeCategory {
	const TYPE_LARGE = 'large';
	const TYPE_SMALL = 'small';
	
	private $data = null;
	private $db = null;
	
	public function __construct($data) {
		$this->db = Db::getInstance();
		$this->data = $data;
	}
	
	public static function get($id) {
		$db = Db::getInstance();
		$result = $db->select('gradeCategory','*', 'id="'.$id.'"')->getResult();
		$gradeCategory = $result ? array_shift($result) : false;
		return $gradeCategory ? new GradeCategory($gradeCategory) : null;
	}
	
	public static function getCategories() {
		$db = Db::getInstance();
		$result = $db->select('gradeCategory','*')->getResult();
		$categories = array();
		if($result) {
			foreach ($result as $category) {
				array_push($categories, new GradeCategory($category));
			}
		}
		return $categories;
	}
	
	public function getName() {
		return $this->data['name'];
	}
	
	public function getId() {
		return $this->data['id'];
	}
	
	public function getSubGrades() {
		return Grade::getGradesByCategory($this->data['id']);
	}
	
	/**
	 * @return Grade
	 */
	public function getMainGrade() {
		foreach ($this->getSubGrades() as $grade) {
			if($grade->isMainGrade()) { return $grade; }
		} 
		return null;
	}
	
	public function isSmall() {
		if($this->data['id'] == 5) { return true; }
		return false;
	}
	
	public function isLarge() {
		return !$this->isSmall();
	}
	
	public function setAveragePrice($season, $price, $allowance) {
		$result = $this->db->insert('sp_average_prices', array($season->getSeasonId(),$this->data['id'], $price,$allowance),'`season`,`category`,`value`,`allowance`');
		return $result;
	}
	
	public function updateAveragePrice($season, $price, $allowance) {
		$result = $this->db->update('sp_average_prices', '`value`='.$price.',`allowance`='.$allowance, '`season`='.$season->getSeasonId().' AND `category`='.$this->data['id']);
		return $result;
	}

	public function getAveragePrice($season) {
		$result = $this->db->select('sp_average_prices','*','season='.$season->getSeasonId().' AND category='.$this->data['id'])->getResult();
		$priceRow = $result ? array_shift($result) : null;
		return !is_null($priceRow) ? array('value' => $priceRow['value'],'allowance' => $priceRow['allowance']) : null;
	}
	
	public function setSchemeValues($season, $values) {
		
		foreach ($this->getSubGrades() as $grade) {
			$value = isset($values[$grade->getGradeId()]) ? $values[$grade->getGradeId()] : 0;
			if(!is_null($grade->getSchemeValue($season))) {
				$grade->updateSchemeValue($season, $value);
			}
			else {
				$grade->setSchemeValue($season, $value);
			}
			
		}
		return true;
	}
}
?>