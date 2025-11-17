<?php
header('Content-Type: application/json');
require_once 'connect.php'; 

if (!$conn) { echo json_encode([]); exit; }

$topicID = isset($_GET['topicID']) ? (int)$_GET['topicID'] : 0;
if ($topicID <= 0) { echo json_encode([]); exit; }

$sql = "
  SELECT DISTINCT u.id, CONCAT(u.firstName, ' ', u.lastName) AS name
  FROM quiz q
  JOIN user u ON q.educatorID = u.id
  WHERE q.topicID = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) { echo json_encode([]); exit; }
$stmt->bind_param('i', $topicID);
$stmt->execute();
$res = $stmt->get_result();

$educators = [];
while ($row = $res->fetch_assoc()) {
  $educators[] = ['id' => (int)$row['id'], 'name' => $row['name']];
}
$stmt->close();

echo json_encode($educators);
