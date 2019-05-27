<?php 
	class Database{
		private $hostdb = "localhost";
		private $userdb = "root";
		private $passdb = "root";
		private $dbname = "db_lr";
		public $pdo;

		public function __construct(){
			if(!isset($this->pdo)){
				try{
					$link = new PDO("mysql:host=".$this->hostdb."; dbname=".$this->dbname, $this->userdb,$this->passdb);
					$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$link->exec("SET CHARACTER SET utf8");
					$this->pdo = $link;
				}catch(PDOException $ex){
					die("Failed to connect with Database.".$ex->getMessage());
				}
			}
		}
	}
?>