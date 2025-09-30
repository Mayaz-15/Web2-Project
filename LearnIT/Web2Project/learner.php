<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Learners HomePage </title>

     <!-- <link rel="stylesheet" href="Style_fatema.css"> -->
    <link rel="stylesheet" href="style.css">

    <style>
      .question-answers {
        padding-left: 1rem;
      }

      .learneredu {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        height: 100%;
      }

      .learneredu img {
        width: 3rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 0.3rem;
      }

      .learneredu p {
        margin: 0;
      }

      .learneredu2 {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        height: 100%;
      }

      .learneredu2 img {
        width: 2rem;
        height: 2rem;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 0.3rem;
      }

      .learneredu2 p {
        margin: 0;
      }

      .correct {
        background-color: #d1f7d1;   /* light green highlight */
        font-weight: bold;
        border-radius: 0.3125rem;
        padding: 0.125rem 0.25rem;
      }
	  
	  
	  
	  
	  
	  
	  /* ===========================
   HEADER / GLOBAL LAYOUT
   =========================== */

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

header h2 {
  padding-left: .525rem;
}

header p {
  padding-right: .525rem;
  font-weight: bold;
  font-size: 1.25rem;
}

.logOutF {
  text-align: right;
}

.Logout {
  float: right;
  font-size: 1.25rem;
  font-weight: bold;
}

.learnerh2, .EducatorH2 {
  clear: left;
}


/* ===========================
   DASHBOARD / PROFILE CARD
   =========================== */

.dashboardF {
  display: flex;
  gap: 1.875rem;
  align-items: flex-start;
}

.welcomeF {
  flex: 1;
}

.infoF {
  padding-top: .625rem;
  padding-left: .625rem;
  border: .125rem solid black;
  flex: 1;
  clear: left;
  background-color: #f5fafc;
}

.infoF p {
  padding-bottom: .625rem;
}

.pfp-F {
  width: 6rem;
  height: 6rem;
  float: right;
  padding-right: 1.25rem;
  padding-bottom: 1.45rem;
}


/* ===========================
   QUIZ HEADERS / CONTROLS
   =========================== */

.quiz-headerAvailableQizzes {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: .625rem;
}

.quiz-headerAvailableQizzes h3 {
  margin: 0; /* remove default margin for clean alignment */
}

.QuizheaderContainer2 {
  display: flex;
  align-items: center;
  justify-content: center;
}

#QTDropDownF {
  width: 6.5rem;
  height: 2rem;
  margin-top: 1rem;
  margin-right: 1rem;
  border-radius: .5rem;
  justify-content: center;
  align-items: center;
  padding: .5rem;
 
}

.filter {
  align-items: center;
  width: 5rem;
  height: 2rem;
  margin-top: 1rem;
  padding: 0 .9375rem;
  align-items: center;
  justify-content: center;
  
  
}


/* ===========================
   TABLES (AVAILABLE / REC QS)
   =========================== */

.Available_Quizzes-F, .recQS-F {
  border: .0625rem solid black;
  width: 100%;
  min-width: 31.25rem;
}



thead {
  background-color: #34495e;
  color: white;
}

th, td {
  border: .0625rem solid black;
  padding: .625rem;
  color:#0
}

td {
  color:#00224B;
}

td {
 background-color: #f5fafc;
}



.Available_Quizzes-F td:nth-child(2),
.Available_Quizzes-F td:nth-child(4) {
  text-align: center;          
  vertical-align: middle;      
  white-space: nowrap;         
}

/* Keep the little 2x2 stats table visually */
.Available_Quizzes-F td:nth-child(3) table {
  margin: 0 auto;              
}
.Available_Quizzes-F td:nth-child(3) td {
  text-align: center;
  padding: .25rem .5rem;
}

/* Make the Comments link look a bit larger/clickable */
.Available_Quizzes-F td:nth-child(4) a {
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: underline;
}

/* ===== Available Quizzes: center "Number Of Questions" + "Quiz Feedback" ===== */
.Available_Quizzes-F th:nth-child(3),
.Available_Quizzes-F th:nth-child(4),
.Available_Quizzes-F td:nth-child(3),
.Available_Quizzes-F td:nth-child(4) {
  text-align: center;      
  vertical-align: middle;  
}

.Available_Quizzes-F td:nth-child(3) table {
  margin: 0 auto;
}

.Available_Quizzes-F td:nth-child(4) a {
  display: inline-block;
}

/* ===== Recommend Questions: center "Status" + "Comments" ===== */
.recQS-F th:nth-child(4),
.recQS-F th:nth-child(5),
.recQS-F td:nth-child(4),
.recQS-F td:nth-child(5) {
  text-align: center;      
  vertical-align: middle;  
  white-space: nowrap;     
}

caption {
  font-weight: bold;
  font-size: 1.20rem;
  text-align: left;
  padding-bottom: .3125rem;
}


/* ===========================
   RECOMMENDATION HEADER BAR
   =========================== */

.RECQS {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: .625rem;
}


/* ===========================
   FORM ELEMENTS
   =========================== */

textarea {
  width: 15rem;
  height: 2rem;
  resize: none;
  overflow-y: auto;
}

input[type="radio"] {
  vertical-align: middle; 
  position: relative;
  top: -.45rem; 
}


/* ===========================
   MISC / UTILITIES
   =========================== */

.opt {
  flex: 1;
}

	  
	  
	  
    </style>
  </head>

	<body> 


<!--		<header> 
		
			<h2> Welcome [name] </h2>
			
			<p class= "logOutF" > Log-out</p>
		
		
		</header>      -->
		
		
		 <header>
		 
		<div class="logo">
		
		  <img src="images/logo.png" alt="LearnIT Logo">
		  <h1>LearnIT</h1>
		  
		</div>
		<nav>
		
		  <a href="learner.php">Home</a>
		 
		 
		</nav>
  </header>
		
		

		
		  <main class = "card-container">
				<div class= "Logout">
				
				<a href= "index.php">Log-out </a>
				
				</div>
		  
				<h2 class="learnerh2">Learner</h2>
				
				
				
				
				<div class= "dashboardF">
				
				
				<div class= "welcomeF">
				
				
				
				<h4>Welcome [Name] </h4>
				
				<br>
				<p>Learner dashboard with available quizzes and recommended questions.</p>
	
				</div>
				
				
				
	<br>
	
	
				


			
			
			<div class= "infoF" >
			
			<p>First Name : Leen</p>
			<img src= "images/pfp.png" alt= "Profile Picture" class= "pfp-F" >
			<p>Last Name : AlMutairi</p>
			<p>Email : LeenM@gmail.com</p>
			
			


			
		</div>
		
		
		



				</div>
				
				
				
	
		<table class= "Available_Quizzes-F">  <!-- QUIZZZEESS -->
		
				
			
			
			
			
			<div class="quiz-headerAvailableQizzes">
  <h3>All Available Quizzes</h3>
  <div class=QuizheaderContainer2>
  
  <select name="QuizzesTopic" id="QTDropDownF">
	<option>All topics</option>
    <option>AI - python101</option>
    <option>Cybersecurity -Cybersecurity Concepts</option>
    <option>IoT - The Network Layer</option>
    <
  </select>
  <button class= "filter" > Filter </button>
</div>
  </div>

			
		
		
				<thead>
				
					<tr>
						<th> Topic </th>
						<th> Educator </th>
						<th> Number Of Questions </th>
						<th>  </th>
					
					
					</tr>
					
					</thead>
					
					<tbody>
					
					<tr>
						<td>AI - python101</td>
						<td> 
							<div class= "learneredu2">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Sara</p> 
							</div>
							</td>
						 <td> 15 QS</td>
						<td> <a href= "take-quiz.php" > Take Quiz</a></td>
					
					
					
					</tr>
					
					
					<tr>
						<td> Cybersecurity -Cybersecurity Concepts</td>
						<td> 
							<div class= "learneredu2">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Hessah</p> 
							</div>
							
							</td>
						<td> 10 QS </td>
						<td> <a href= "take-quiz.php" > Take Quiz</a></td>
					
					
					
					</tr>
					
					<tr>
						<td> IoT - The Network Layer </td>
						<td>
							<div class= "learneredu2">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Aryam</p> 
							</div>
							</td>
						<td> 20 QS</td>
						<td> <a href= "take-quiz.php" > Take Quiz</a></td>
					
					
					
					</tr>
					
					
				
				
				</tbody>


		</table>			<!-- QUIZZZEESS -->

				

<br> <br> <br>


		<table class= "recQS-F"> <!-- REC QS-->
		
		
		<div class= "RECQS">
		
		
			<h3> Recommend Questions </h3>
			
			<a href="recommend-question.html" id="recqslink" >Recommend a Question </a>
			
		</div>
					<thead>
					
						<tr>
							<th> Topic </th>
							<th> Educator </th>
							<th> Question </th>
							<th> Status </th>
							<th> Comments </th>
						
						</tr>
						
						</thead>
						
						<tbody>
						
						<tr>
							<td>AI - python101</td>
							<td> 
							<div class= "learneredu">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Sara</p> 
							</div>
							</td>
					
							<td>
								<div class="question-photo">[photo]</div>
								<div class="question-text">Which of the following is an example of supervised learning?</div>
						
								<ol type="A" class="question-answers">
								  <li>Clustering</li>
								  <li class="correct">Regression</li>
								  <li>Association rules</li>
								  <li>Anomaly detection</li>
								</ol>
							
							</td>
							
							<td> Approved</td>
							 <td>Clear and relevant question for beginners.</td>
						
						
						
						</tr>
						
						
						<tr>
							<td> Cybersecurity -Cybersecurity Concepts</td>
							<td> 
							<div class= "learneredu">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Hessah</p> 
							</div>
							
							</td>
							<td> 
								<div class="question-photo">[photo]</div>
								<div class="question-text">What does HTTPS stand for?</div>
								<ol type="A" class="question-answers">
									<li>Hyper Text Transfer Secure</li>
									<li>Hyper Transfer Protocol Standard</li>
									<li class="correct">Hyper Text Transfer Protocol Secure</li>
									<li>Hyper Text Tracking Protocol System</li>
							  </ol>
							</td>
							<td> Dissaproved</td>
							<td> Great question! But it has already been recommended by another student.</td>
						
						
						
						</tr>
						
						<tr>
							<td> IoT - The Network Layer </td>
							<td>
							<div class= "learneredu">
							<img src= "images/learneredu.png" alt= "PFP">
							<p>Aryam</p> 
							</div>
							</td>
							
							<td>
							  <div class="question-photo">[photo]</div>
							  <div class="question-text"> Which protocol is most commonly used at the network layer in IoT devices</div>
							  <ol type="A" class="question-answers">
								<li>FTP</li>
								<li>HTTP</li>
								<li class="correct">IPv6</li>
								<li>SMTP</li>
							  </ol>
							</td>

							<td> Pending</td>
							<td>  </td>
						
						
						</tr>
						
					
				
					
					</tbody>


				</table>		<!-- REC QS-->
				
		</main>
				
			<footer>
				<p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
			</footer>


<script>
  (function () {
    // dropdown (ID)
    const filterSelect = document.getElementById('QTDropDownF');
    // filter button (class)
    const filterBtn = document.querySelector('.filter');
    // table (class)
    const table = document.querySelector('.Available_Quizzes-F');
    // all rows inside table body
    const rows = Array.from(table.querySelectorAll('tbody tr'));

    function normalize(str) {
      return (str || '').toLowerCase().trim();
    }

    function filterQuizzes() {
      const selected = normalize(filterSelect.value);
      rows.forEach(tr => {
        const topicText = normalize(tr.cells[0]?.textContent);
        // if "All topics" → show everything
        if (selected === 'all topics') {
          tr.style.display = '';
        } else {
          // otherwise show only rows where topic matches
          tr.style.display = topicText === selected ? '' : 'none';
        }
      });
    }

    // run only when Filter button is clicked
    filterBtn.addEventListener('click', filterQuizzes);

  })();
</script>












</body>


</html>