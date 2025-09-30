<?php
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topic_id = intval($_POST['topic']);
    $educator_id = intval($_POST['educator']);
    $question = trim($_POST['question']);

    if (!empty($question)) {
        $stmt = $conn->prepare("INSERT INTO recommended_questions (topic_id, educator_id, question_text, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iis", $topic_id, $educator_id, $question);
        $stmt->execute();
        $stmt->close();

        
        header("Location: learner.php");
        exit();
    }
}


$topics = [];
$result = $conn->query("SELECT id, name FROM topics");
while ($row = $result->fetch_assoc()) {
    $topics[] = $row;
}

// --- Fetch educators ---
$educators = [];
$result = $conn->query("SELECT id, name FROM educators");
while ($row = $result->fetch_assoc()) {
    $educators[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recommend-Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
    h2 { text-align: center; font-size:2.5rem; }
    form {
      display: flex; flex-direction: column; gap: 0.938em;
      margin: 1.875em auto; padding: 1.25em;
      background-color: white; border-radius: 0.5em;
      box-shadow: 0 0.125em 0.312em rgba(0,0,0,0.1);
    }
	
    fieldset { border: 0.062em solid #ddd; padding: 1.25em; border-radius: 0.5em; }
	
    legend { font-size: 2em; text-align: center; margin-bottom: 1.25em; font-weight: bold; }
	
    label { display: block; margin: 0.625em 0 0.312em; }
	
    select, textarea, input {
      width: 100%; padding: 0.625em; border-radius: 0.312em; border: 0.062em solid #ccc;
    }
	
    textarea { min-height: 5.0em; resize: vertical; }
  </style>
  
</head>


<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="learner.php">Home</a>
    </nav>
  </header>

  <main>
    <div class="card-container">
	
      <h2>Recommend a Question</h2>
      <form action="" method="POST">
        <fieldset>
          <legend>Question Details</legend>

          <label for="topic">Select Topic:</label>
          <select name="topic" id="topic" required>
            <option value="">-- Choose Topic --</option>
            <?php foreach ($topics as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
          </select>

          <label for="educator">Select Educator:</label>
          <select name="educator" id="educator" required>
            <option value="">-- Choose Educator --</option>
            <?php foreach ($educators as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
            <?php endforeach; ?>
          </select>

          <label for="question">Your Suggested Question:</label>
          <textarea name="question" id="question" required></textarea>

          <button type="submit">Submit Recommendation</button>
        </fieldset>
      </form>
	  
    </div>
	
  </main>

</body>
</html>
