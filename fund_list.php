<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

// total
$total = 0;
$r = $conn->query("SELECT IFNULL(SUM(amount),0) AS total FROM fund_donations");
if($r && $row = $r->fetch_assoc()) $total = (int)$row["total"];

// purpose summary
$purpose = [];
$r2 = $conn->query("SELECT purpose, IFNULL(SUM(amount),0) AS sum
                    FROM fund_donations
                    GROUP BY purpose
                    ORDER BY sum DESC");
if($r2){
  while($x = $r2->fetch_assoc()){
    $purpose[] = ["purpose" => $x["purpose"], "sum" => (int)$x["sum"]];
  }
}

// recent donations
$recent = [];
$sql = "SELECT d.amount, d.purpose, d.created_at, u.full_name
        FROM fund_donations d
        JOIN users u ON u.id = d.user_id
        ORDER BY d.created_at DESC
        LIMIT 10";
$r3 = $conn->query($sql);
if($r3){
  while($x = $r3->fetch_assoc()){
    $recent[] = [
      "amount" => (int)$x["amount"],
      "purpose" => $x["purpose"],
      "full_name" => $x["full_name"],
      "created_at" => $x["created_at"]
    ];
  }
}

echo json_encode([
  "total" => $total,
  "purpose" => $purpose,
  "recent" => $recent
]);
