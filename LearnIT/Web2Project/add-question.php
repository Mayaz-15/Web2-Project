<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add-Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
/* Scoped to quiz area only */

.quiz-scope{
  --qz-gap-1: 0.5rem;
  --qz-gap-2: 1rem;
  --qz-gap-3: 1.5rem;
  --qz-gap-4: 2rem;
  --qz-radius: 0.8rem;
  --qz-input-h: 2.75rem;

  --qz-text: inherit;
  --qz-muted: color-mix(in oklab, currentColor 55%, transparent);
  --qz-accent: currentColor;
  --qz-panel-bg: var(--surface-1, #ffffff);
  --qz-panel-bd: color-mix(in oklab, currentColor 85%, transparent);
  --qz-input-bg: var(--surface-2, #ffffff);
  --qz-input-bd: color-mix(in oklab, currentColor 80%, transparent);
  --qz-ok-bd: color-mix(in oklab, #22c55e 40%, transparent);
  --qz-ok-bg: color-mix(in oklab, #22c55e 12%, transparent);
}


    .quiz-scope h2 {
      text-align: center;
	  font-size:2.5rem;
    }


    form.flex-container {
      display: flex;
      flex-direction: column;
      gap: 0.938em;
    }

    fieldset {
      border: 0.062em solid #ddd;
      padding: 1.25em;
      border-radius: 0.5em;
    }

    legend {
      font-weight: bold;
      padding: 0 0.625em;
    }
	


.quiz-scope h3{
  margin: var(--qz-gap-3) 0 var(--qz-gap-2);
  font-size: 1.125rem;
}

/* Utilities */
.quiz-scope .q-help{ font-size: 0.9rem; color: var(--qz-muted); }
.quiz-scope .q-inline{ display:flex; align-items:center; gap: 0.75rem; flex-wrap: wrap; }



/* Form layout & inputs */
.quiz-scope .q-form{ display:grid; gap: var(--qz-gap-2); }
.quiz-scope .q-row{ display:grid; gap: 0.5rem; }
.quiz-scope label{ font-size: 0.95rem; color: var(--qz-muted); }

.quiz-scope input[type="text"],
.quiz-scope input[type="number"],
.quiz-scope input[type="email"],
.quiz-scope input[type="file"],
.quiz-scope select,
.quiz-scope textarea{
  width: 100%;
  height: var(--qz-input-h);
  padding: 0 var(--qz-gap-2);
  background: var(--qz-input-bg);
  border: 0.08rem solid var(--qz-input-bd);
  border-radius: var(--qz-radius);
  color: inherit;
  outline: none;
}
.quiz-scope textarea{
  height: auto;
  min-height: 8rem;
  padding: var(--qz-gap-2);
  resize: vertical;
}

/* Choice grid (A–D) */
.quiz-scope .q-choices{
  display:grid;
  grid-template-columns: repeat(2, minmax(0,1fr));
  gap: var(--qz-gap-2);
}

/* Responsive */
@media (max-width: 40rem){
  .quiz-scope .q-choices{ grid-template-columns: 1fr; }
}

</style>

</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="educator.php">Home</a>
    </nav>
  </header>
  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2>Add Question</h2>

        <div class="q-inline" style="margin-bottom: 1rem;">
          <a class="takeHome" href="quiz.php">Back to Quiz</a>
        </div>

        <form class="flex-container" action="quiz.php" method="post" enctype="multipart/form-data">
		 <fieldset>
		  <legend>Question Details</legend>
          <div class="q-row">
            <label for="topic">Topic</label>
            <select id="topic" name="topic" required>
                  <option value="">— Select a topic —</option>
                    <option value="ai-python101" selected>AI - Python101</option>
                    <option value="cybersecurity-concepts">Cybersecurity - Cybersecurity Concepts</option>
                    <option value="iot-network-layers">IoT - The Network Layers</option>
             </select>
          </div>

          <div class="q-row">
            <label for="qtext">Question Text</label>
            <textarea id="qtext" name="qtext" required placeholder="Write the question..."></textarea>
          </div>

          <div class="q-row">
            <label for="qfigure">Optional Figure</label>
            <input id="qfigure" name="qfigure" type="file" accept="image/*" />
            <p class="q-help">If no file is selected, the question will have no figure.</p>
          </div>

          <h3>Choices</h3>
          <div class="q-choices">
            <div class="q-row">
              <label for="c1">Choice A</label>
              <input id="c1" name="c1" type="text" required />
            </div>
            <div class="q-row">
              <label for="c2">Choice B</label>
              <input id="c2" name="c2" type="text" required />
            </div>
            <div class="q-row">
              <label for="c3">Choice C</label>
              <input id="c3" name="c3" type="text" required />
            </div>
            <div class="q-row">
              <label for="c4">Choice D</label>
              <input id="c4" name="c4" type="text" required />
            </div>
          </div>

          <div class="q-row">
            <label for="correct">Correct Answer</label>
            <select id="correct" name="correct" required>
              <option value="">— Choose correct option —</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </div>
		   </fieldset>

          <div class="q-inline">
            <button class="submit" type="submit" onClick="re()">Save Question</button>
            <a class="takeHome" href="quiz.php">Cancel</a>
          </div>
        </form>
      </section>
    </div>
  </main>
  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
