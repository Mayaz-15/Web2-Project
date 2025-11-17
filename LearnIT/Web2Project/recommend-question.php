<?php
session_start();
include 'connect.php'; // should define $conn

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- Session mapping (for consistency) ---
if (isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['user_id'];
}
if (isset($_SESSION['user_type']) && !isset($_SESSION['userType'])) {
    $_SESSION['userType'] = $_SESSION['user_type'];
}

// Check if user is logged in and is a learner
if (!isset($_SESSION['id']) || !isset($_SESSION['userType'])) {
    header("Location: index.php?error=unauthorized");
    exit;
}
if ($_SESSION['userType'] !== 'learner') {
    header("Location: index.php?error=unauthorized");
    exit;
}

// --- Fetch topics ---
$topics = [];
$result = $conn->query("SELECT id, topicName FROM topic");
while ($row = $result->fetch_assoc()) $topics[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recommend a Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
    h2 { text-align: center; font-size:2.5rem; }
    form {
      display: flex; flex-direction: column; gap: 0.938em;
      margin: 1.875em auto; padding: 1.25em;
      background-color: white; border-radius: 0.5em;
      box-shadow: 0 0.125em 0.312em rgba(0,0,0,0.1);
      width: 80%; max-width: 600px;
    }
    fieldset { border: 0.062em solid #ddd; padding: 1.25em; border-radius: 0.5em; }
    legend { font-size: 2em; text-align: center; margin-bottom: 1.25em; font-weight: bold; }
    label { display: block; margin: 0.625em 0 0.312em; }
    select, textarea, input {
      width: 100%; padding: 0.625em; border-radius: 0.312em; border: 0.062em solid #ccc;
    }
    textarea { min-height: 5.0em; resize: vertical; }
    #q { display:flex; align-items:center; gap: 0.75rem; flex-wrap: wrap; }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(document).ready(function() {
      $('#topic').on('change', function() {
          var topicID = $(this).val();
          if (!topicID) {
              $('#prof').html('<option value="">Select topic first</option>');
              return;
          }

          $.ajax({
              url: 'get-educators.php',
              method: 'GET',
              data: { topicID: topicID },
              dataType: 'json',
              success: function(response) {
                  $('#prof').empty();
                  if (response.length === 0) {
                      $('#prof').append('<option value="">No educators found</option>');
                  } else {
                      $('#prof').append('<option value="" disabled selected>Select educator</option>');
                      $.each(response, function(index, educator) {
                          $('#prof').append(
                              $('<option>', { value: educator.id, text: educator.name })
                          );
                      });
                  }
              },
              error: function(xhr, status, error) {
                  console.log("AJAX Error:", error);
                  alert('Error retrieving educators.');
              }
          });
      });
  });
  </script>
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

      <form action="submit-recommendation.php" method="POST" enctype="multipart/form-data">
        <fieldset>
          <label>Select a Topic:</label>
          <select id="topic" name="topic" required>
            <option value="" disabled selected>Select topic</option>
            <?php foreach ($topics as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['topicName']) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Choose a Professor:</label>
          <select id="prof" name="prof" required>
            <option value="">Select topic first</option>
          </select>

          <label>Write your Question here:</label>
          <textarea placeholder="Type your question here.." name="Q" required></textarea>

          <label>Upload figure (optional)</label>
          <input type="file" name="pic">

          <label>Answer A</label>
          <input type="text" name="a" required>

          <label>Answer B</label>
          <input type="text" name="b" required>

          <label>Answer C</label>
          <input type="text" name="c" required>

          <label>Answer D</label>
          <input type="text" name="d" required>

          <label>Select The right answer:</label>
          <select id="rightAns" name="rightAns" required>
            <option value="" disabled selected>Select right answer</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
          </select>
        </fieldset>

        <div id="q">
          <button type="submit" class="submit">Submit Question</button>
          <a href="learner.php" class="takeHome">Cancel</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
