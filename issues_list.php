<?php
require_once "db.php";

$sql = "
SELECT 
  i.id,
  i.title,
  i.category,
  i.location,
  i.description,
  i.created_at,
  u.full_name,
  u.points,
  IFNULL(AVG(r.rating),0) AS avg_rating,
  COUNT(r.id) AS rating_count
FROM issues i
JOIN users u ON u.id = i.user_id
LEFT JOIN issue_ratings r ON r.issue_id = i.id
GROUP BY i.id
ORDER BY i.id DESC
LIMIT 50
";

$res = $conn->query($sql);

$rows = [];
while($row = $res->fetch_assoc()){
  $rows[] = $row;
}

header("Content-Type: application/json");
echo json_encode($rows);
?>
