<?php
   
   class Patient {
      var $name;
      var $gender;
      var $age;
      var $address;
      var $postcode;
      var $city;
      var $state;
      var $mobileno;
      var $status;
      var $admissiondate;
      var $icuadmissiondate;
      var $clinicaldeathdate;
      var $dischargedate;
   }

   class DbStatus {
      var $status;
      var $error;
      var $lastinsertid;
   }

   function time_elapsed_string($datetime, $full = false) {

      if ($datetime == '0000-00-00 00:00:00')
         return "none";

      if ($datetime == '0000-00-00')
         return "none";

      $now = new DateTime;
      $ago = new DateTime($datetime);
      $diff = $now->diff($ago);

      $diff->w = floor($diff->d / 7);
      $diff->d -= $diff->w * 7;

      $string = array(
         'y' => 'year',
         'm' => 'month',
         'w' => 'week',
         'd' => 'day',
         'h' => 'hour',
         'i' => 'minute',
         's' => 'second',
      );
      
      foreach ($string as $k => &$v) {
         if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
         } else {
            unset($string[$k]);
         }
      }

      if (!$full) $string = array_slice($string, 0, 1);
         return $string ? implode(', ', $string) . ' ago' : 'just now';
   }

	class Database {
 		protected $dbhost;
    	protected $dbuser;
    	protected $dbpass;
    	protected $dbname;
    	protected $db;

 		function __construct( $dbhost, $dbuser, $dbpass, $dbname) {
   		$this->dbhost = $dbhost;
   		$this->dbuser = $dbuser;
   		$this->dbpass = $dbpass;
   		$this->dbname = $dbname;

   		$db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
         $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
    		$this->db = $db;
   	}

      function beginTransaction() {
         try {
            $this->db->beginTransaction(); 
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function commit() {
         try {
            $this->db->commit();
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function rollback() {
         try {
            $this->db->rollback();
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function close() {
         try {
            $this->db = null;   
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      //insert patient
      function insertPatient($name, $gender, $age, $address, $postcode, $city, $state, $mobileno) {

         try {
            
            $sql = "INSERT INTO patients(name, gender, age, address, postcode, city, state, mobileno, admissiondate) 
                    VALUES (:name, :gender, :age, :address, :postcode, :city, :state, :mobileno, NOW())";

            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("name", $name);
            $stmt->bindParam("gender", $gender);
            $stmt->bindParam("age", $age);
            $stmt->bindParam("address", $address);
            $stmt->bindParam("postcode", $postcode);
            $stmt->bindParam("city", $city);
            $stmt->bindParam("state", $state);
            $stmt->bindParam("mobileno", $mobileno);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
         } 
      }

      //get all patients
      function getAllPatients() {
         $sql = "SELECT *
                 FROM patients";

         $stmt = $this->db->prepare($sql);
         $stmt->execute(); 
         $row_count = $stmt->rowCount();

         $data = array();

         if ($row_count)
         {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {

               $patient = new Patient();
               $patient->id = $row['id'];
               $patient->name = $row['name'];
               $patient->gender = $row['gender'];
               $patient->age = $row['age'];
               $patient->address = $row['address'];
               $patient->postcode = $row['postcode'];
               $patient->city = $row['city'];
               $patient->state = $row['state'];
               $patient->mobileno = $row['mobileno'];
               $patient->status = $row['status'];

               //$admissiondate = $row['admissiondate'];
               //$frontendadmissiondate = date("d-m-Y",strtotime($admissiondate));
               //$patient->admissiondate = $frontendadmissiondate;

               $admissiondate = $row['admissiondate'];
               $patient->admissiondate = time_elapsed_string($admissiondate);

               $icuadmissiondate = $row['icuadmissiondate'];
               $patient->icuadmissiondate = time_elapsed_string($icuadmissiondate); 

               $clinicaldeathdate = $row['clinicaldeathdate'];
               $patient->clinicaldeathdate = time_elapsed_string($clinicaldeathdate); 

               $dischargedate = $row['dischargedate'];
               $patient->dischargedate = time_elapsed_string($dischargedate); 

               array_push($data, $patient);
            }
         }

         return $data;
      }

      //get patient via id
      function getPatientViaId($id) {

         $sql = "SELECT *
                 FROM patients
                 WHERE id = :id";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute(); 
         $row_count = $stmt->rowCount();

         $patient = new Patient();

         if ($row_count)
         {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {

               $patient->id = $row['id'];
               $patient->name = $row['name'];
               $patient->gender = $row['gender'];
               $patient->age = $row['age'];
               $patient->address = $row['address'];
               $patient->postcode = $row['postcode'];
               $patient->city = $row['city'];
               $patient->state = $row['state'];
               $patient->mobileno = $row['mobileno'];
               $patient->status = $row['status'];

               //$admissiondate = $row['admissiondate'];
               //$frontendadmissiondate = date("d-m-Y",strtotime($admissiondate));
               //$patient->admissiondate = $frontendadmissiondate;

               $admissiondate = $row['admissiondate'];
               $patient->admissiondate = time_elapsed_string($admissiondate);

               $icuadmissiondate = $row['icuadmissiondate'];
               $patient->icuadmissiondate = time_elapsed_string($icuadmissiondate); 

               $clinicaldeathdate = $row['clinicaldeathdate'];
               $patient->clinicaldeathdate = time_elapsed_string($clinicaldeathdate);  

               $dischargedate = $row['dischargedate'];
               $patient->dischargedate = time_elapsed_string($dischargedate); 
            }
         }

         return $patient;
      }

      //update patient via id
      function updatePatientViaId($id, $name, $gender, $age, $address, $postcode, $city, $state, $mobileno) {

         $sql = "UPDATE patients
                 SET name = :name,
                     gender = :gender,
                     age = :age,
                     address = :address,
                     postcode = :postcode,
                     city = :city,
                     state = :state,
                     mobileno = :mobileno
                 WHERE id = :id";

         try {
            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("id", $id);
            $stmt->bindParam("name", $name);
            $stmt->bindParam("gender", $gender);
            $stmt->bindParam("age", $age);
            $stmt->bindParam("address", $address);
            $stmt->bindParam("postcode", $postcode);
            $stmt->bindParam("city", $city);
            $stmt->bindParam("state", $state);
            $stmt->bindParam("mobileno", $mobileno);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";

            return $dbs;
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
         } 
      }

      //update patient status via id
      function updatePatientStatusViaId($id, $status) {

         $sql = "";

         if (strcmp($status, "2") == 0) {
            $sql = "UPDATE patients
                    SET status = :status,
                        icuadmissiondate = NOW()
                    WHERE id = :id";
         }

         if (strcmp($status, "3") == 0) {
            $sql = "UPDATE patients
                    SET status = :status,
                        clinicaldeathdate = NOW()
                    WHERE id = :id";
         }

         if (strcmp($status, "4") == 0) {
            $sql = "UPDATE patients
                    SET status = :status,
                        dischargedate = NOW()
                    WHERE id = :id";
         }

         try {
            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("id", $id);
            $stmt->bindParam("status", $status);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";

            return $dbs;
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
         } 
      }
   }