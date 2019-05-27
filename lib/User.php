<?php 
	include_once 'Session.php';
	include 'Database.php';

class User{
	private $db;
	public function __construct()		{
		$this->db = new Database();
	}

	public function userRegistration($data){
		$name     = $data['name'];
		$username = $data['username'];
		$email    = $data['email'];
		$password = $data['password'];

		$chk_email = $this->emailCheck($email);

		if(strlen($password)<6){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Password length must be ateast 6 characters.</div>";
			return $msg;
		}

		if($name == "" || $username == "" || $email == "" || $password == ""){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Field must not be Empty</div>";
			return $msg;
		}
		if(strlen($username) < 3){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Username is too short.</div>";
			return $msg;	
		}
		elseif(preg_match('/[^a-z0-9_-]+/i',$username)){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Username must only contain alphanumerical, dashes and underscores.</div>";
			return $msg;	
		}

		if(filter_var($email,FILTER_VALIDATE_EMAIL) === false){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Email Address is not valid.</div>";
			return $msg;
		}
		if($chk_email == true){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Email Address already exists.</div>";
			return $msg;
		}

		$passwordMD5 = md5($password);
		
		$sql = "INSERT INTO tbl_user(name,username,email,password) VALUES(:name,:username,:email,:password)";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':name',$name);
		$query->bindValue(':username',$username);
		$query->bindValue(':email',$email);
		$query->bindValue(':password',$passwordMD5);
		$result = $query->execute();
		if ($result) {
			$msg = "<div class='alert alert-success'><strong>Success!</strong> Thank you have been Registered.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Sorry, we are unable to Register you. Please try again.</div>";
			return $msg;
		}
	}

	public function emailCheck($email){
		$sql = "SELECT email from tbl_user where email = :email";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':email',$email);
		$query->execute();
		if($query->rowCount() > 0){
			return true;
		}else{
			return false;
		}
	}

	public function getLoginUser($email,$passwordMD5){
		$sql = "SELECT * from tbl_user where email = :email AND password = :password LIMIT 1";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':email',$email);
		$query->bindValue(':password',$passwordMD5);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}

	public function userLogin($data){
		$email    = $data['email'];
		$password = $data['password'];

		$chk_email = $this->emailCheck($email);

		$passwordMD5 = md5($password);

		if($email == "" || $password == ""){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Field must not be Empty</div>";
			return $msg;
		}

		if(filter_var($email,FILTER_VALIDATE_EMAIL) === false){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Email Address is not valid.</div>";
			return $msg;
		}
		if($chk_email == false){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Email Address does not exists in our database.</div>";
			return $msg;
		}

		$result = $this->getLoginUser($email,$passwordMD5);
		if ($result) {
			Session::init();
			Session::set("login",true);
			Session::set("id",$result->id);
			Session::set("name",$result->name);
			Session::set("username",$result->username);
			Session::set("loginmsg", "<div class='alert alert-success'><strong>Success!</strong> You are logged In.</div>");
			header("Location: index.php");
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Data Not Found.</div>";
			return $msg;
		}
	}

	public function getUserData(){
		$sql = "SELECT * from tbl_user ORDER BY id DESC";
		$query = $this->db->pdo->prepare($sql);
		$query->execute();
		$result = $query->fetchAll();
		return $result;
	}

	public function getUserById($userid){
		$sql = "SELECT * from tbl_user where id= :id limit 1";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':id',$userid);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}


	public function userUpdateData($userid, $data){
		$name     = $data['name'];
		$username = $data['username'];
		$email    = $data['email'];

		if($name == "" || $username == "" || $email == ""){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Field must not be Empty</div>";
			return $msg;
		}
		if(strlen($username) < 3){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Username is too short.</div>";
			return $msg;	
		}
		elseif(preg_match('/[^a-z0-9_-]+/i',$username)){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Username must only contain alphanumerical, dashes and underscores.</div>";
			return $msg;	
		}

		if(filter_var($email,FILTER_VALIDATE_EMAIL) === false){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Email Address is not valid.</div>";
			return $msg;
		}

		$sql = "UPDATE tbl_user 
				SET name=:name,
					username= :username,
					email = :email
				where id= :id";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':name',$name);
		$query->bindValue(':username',$username);
		$query->bindValue(':email',$email);
		$query->bindValue(':id',$userid);
		$result = $query->execute();
		if ($result) {
			$msg = "<div class='alert alert-success'><strong>Success!</strong> User Data updated successfully.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Sorry, we are unable to update your information. Please try again.</div>";
			return $msg;
		}
	}

	private function checkPassword($userid, $oldpassword){
		$password = md5($oldpassword);
		$sql = "SELECT password from tbl_user where id=:id AND password = :password";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':id',$userid);
		$query->bindValue(':password',$password);
		$query->execute();
		if($query->rowCount() > 0){
			return true;
		}else{
			return false;
		}
	}
	public function userPassUpdate($userid, $data){
		$oldpassword = $data['oldpassword'];
		$newpassword = $data['newpassword'];
		$chk_pass = $this->checkPassword($userid, $oldpassword);

		if($oldpassword == "" OR $newpassword == ""){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Field must not be empty.</div>";
			return $msg;
		}
		if ($chk_pass == false) {
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Old Password does not exists.</div>";
			return $msg;
			}
		if(strlen($newpassword)<6){
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Password length must be ateast 6 characters.</div>";
			return $msg;
		}
		$password =md5($newpassword);
		$sql = "UPDATE tbl_user 
				SET password=:password
				where id= :id";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':password',$password);
		$query->bindValue(':id',$userid);
		$result = $query->execute();
		if ($result) {
			$msg = "<div class='alert alert-success'><strong>Success!</strong> User Password updated successfully.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error!</strong> Sorry, we are unable to update your Password. Please try again.</div>";
			return $msg;
		}
		}
	}
?> 