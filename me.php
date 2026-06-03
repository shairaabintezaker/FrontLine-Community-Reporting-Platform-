<?php
require_once "db.php";
header("Content-Type: application/json");

if(isset($_SESSION["user"])) echo json_encode(["ok"=>true]);
else echo json_encode(["ok"=>false]);
?>
