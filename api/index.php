<?php

   ini_set("date.timezone", "Asia/Kuala_Lumpur");

   //*
   header('Access-Control-Allow-Origin: *');

   // Allow from any origin
   if (isset($_SERVER['HTTP_ORIGIN'])) {
      // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
      // you want to allow, and if so:
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
   }

   // Access-Control headers are received during OPTIONS requests
   if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
         header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");         

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
         header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
   }
   //*/

   include_once("database_class.php");

   require_once 'vendor/autoload.php';

   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;

   use Slim\App;

   //functions /////////////////////////////////////////////start

   function getDatabase() {

      $dbhost = "127.0.0.1";
      $dbuser = "root";
      $dbpass = "";
      $dbname = "covid19"; //change dbname only

      $db = new Database($dbhost, $dbuser, $dbpass, $dbname);
      return $db;
   }
   //functions /////////////////////////////////////////////ends

   $config = [
      'settings' => [
         'displayErrorDetails' => true
      ]
   ];

   $app = new App($config);

   /**
     * Public route example
     */
   $app->get('/ping', function($request, $response){
      $output = ['msg' => 'RESTful API works, active and online!'];
      return $response->withJson($output, 200, JSON_PRETTY_PRINT);
   });

   $app->post('/patients', function($request, $response){

      //form data
      $json = json_decode($request->getBody());
      $name = $json->name;
      $gender = $json->gender;
      $age = $json->age;
      $address = $json->address;
      $postcode = $json->postcode;
      $city = $json->city;
      $state = $json->state;
      $mobileno = $json->mobileno;

      $db = getDatabase();
      $dbs = $db->insertPatient($name, $gender, $age, $address, $postcode, $city, $state, $mobileno);
      $db->close();

      $data = array(
         "insertStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );


      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   }); 

   $app->get('/patients', function($request, $response){

      $db = getDatabase();
      $data = $db->getAllPatients();
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   });

   $app->get('/patients/[{id}]', function($request, $response, $args){
      
      $id = $args['id'];

      $db = getDatabase();
      $data = $db->getPatientViaId($id);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   }); 

   $app->put('/patients/[{id}]', function($request, $response, $args){

      $id = $args['id'];

      //form data using json structure
      $json = json_decode($request->getBody());
      $name = $json->name;
      $gender = $json->gender;
      $age = $json->age;
      $address = $json->address;
      $postcode = $json->postcode;
      $city = $json->city;
      $state = $json->state;
      $mobileno = $json->mobileno;

      $db = getDatabase();
      $dbs = $db->updatePatientViaId($id, $name, $gender, $age, $address, $postcode, $city, $state, $mobileno);
      $db->close();

      $data = Array(
         "updateStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });  

   $app->put('/patients/status/[{id}]', function($request, $response, $args){
     
      //from url
      $id = $args['id'];

      //form data, from json data
      $json = json_decode($request->getBody());
      $status = $json->status;

      $db = getDatabase();

      $dbs = $db->updatePatientStatusViaId($id, $status);
      $db->close();

      $data = Array(
         "updateStatus" => $dbs->status,
         "errorMessage" => $dbs->error,
         "status" => $status
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   }); 

   $app->run();