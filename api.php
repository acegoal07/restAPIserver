<?php

class API
{
   private $conn;

   public function __construct()
   {
      $servername = "localhost";
      $port = "3306";
      $username = "root";
      $password = "";
      $dbname = "apidatabase";
      $this->conn = new mysqli($servername, $username, $password, $dbname, $port);
   }

   public function handleRequest()
   {
      if ($this->conn->connect_error) {
         http_response_code(500);
      } else {
         switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
               if (!isset($_POST['oid']) ||
               strlen($_POST['oid']) > 32 ||
               !isset($_POST['name']) ||
               strlen($_POST['name']) > 64 ||
               !isset($_POST['comment']) ||
               strlen($_POST['comment']) === 0) {
                  http_response_code(400);
               } else {
                  $this->handlePost();
               }
               break;
            case 'GET':
               if (!isset($_GET['oid']) || strlen($_GET['oid']) > 32) {
                  http_response_code(400);
               } else {
                  $this->handleGet();
               }
               break;
            default:
               http_response_code(400);
               break;
         }
      }
   }

   public function handlePost()
   {
      try {
         $oid = $_POST['oid'];
         $name = $_POST['name'];
         $comment = $_POST['comment'];
         $id = null;
         if ($this->conn->query("INSERT INTO `comments`(`oid`, `name`, `comment`) VALUES ('$oid', '$name','$comment')")) {
            $id = $this->conn->insert_id;
         }
         $response["id"] = $id;
         if (http_response_code(201)) {
            echo json_encode($response);
         }
      } catch (Exception $e) {
         http_response_code(500);
      }
   }

   public function handleGet()
   {
      try {
         $oid = $_GET['oid'];
         $result = $this->conn->query("SELECT `id`, `date`, `name`, `comment` FROM comments where `oid` = $oid");
         $data = array();
         if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
               array_push($data, $row);
            }
         }
         $result->close();
         $response["oid"] = $oid;
         $response["comments"] = $data;
         if (http_response_code(201)) {
            echo json_encode($response);
         }
      } catch (Exception $e) {
         http_response_code(500);
      }
   }
}

$api = new API();
$api->handleRequest();
