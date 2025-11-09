-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2025 at 07:53 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dblearnit`
--

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `educatorID` int(11) NOT NULL,
  `topicID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `educatorID`, `topicID`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `quizfeedback`
--

CREATE TABLE `quizfeedback` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comments` text,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quizfeedback`
--

INSERT INTO `quizfeedback` (`id`, `quizID`, `rating`, `comments`, `date`) VALUES
(1, 1, 5, 'Great AI questions and coverage.', '2025-10-28 22:18:17'),
(2, 2, 3, 'IoT quiz okay but a bit short.', '2025-10-28 22:18:17'),
(3, 3, 4, 'Good balance of difficulty.', '2025-10-28 22:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `question` text NOT NULL,
  `questionFigureFileName` varchar(200) DEFAULT NULL,
  `answerA` varchar(300) NOT NULL,
  `answerB` varchar(300) NOT NULL,
  `answerC` varchar(300) NOT NULL,
  `answerD` varchar(300) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quizID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`) VALUES
(1, 1, 'What does supervised learning require?', 'q1-1-fig.png', 'Labeled data', 'Unlabeled data', 'No data', 'Only images', 'A'),
(2, 1, 'Which algorithm is commonly used for classification?', 'q1-2-fig.png', 'K-means', 'Linear Regression', 'Decision Tree', 'Apriori', 'C'),
(3, 1, 'What is overfitting?', NULL, 'Model fits training too well', 'Model too simple', 'No training needed', 'Random output', 'A'),
(4, 2, 'Which protocol is lightweight for IoT messaging?', 'q2-1-fig.png', 'HTTP', 'MQTT', 'FTP', 'SSH', 'B'),
(5, 2, 'A sensor node is typically…', NULL, 'Only a transceiver', 'Only a battery', 'Sensing + compute + comms', 'A router', 'C'),
(6, 2, 'Edge computing helps by…', NULL, 'Increasing latency', 'Processing near source', 'Removing devices', 'Encrypting passwords', 'B'),
(7, 3, 'Which is a form of social engineering?', NULL, 'Phishing', 'Firewall', 'VPN', 'IDS', 'A'),
(8, 3, 'Which encryption is symmetric?', NULL, 'RSA', 'AES', 'ECC', 'Diffie-Hellman', 'B'),
(9, 3, 'CIA triad stands for…', NULL, 'Confidentiality, Integrity, Availability', 'Control, Inspect, Audit', 'Code, Input, Access', 'Cloud, Identity, Auth', 'A'),
(10, 4, 'Which of the following best describes the purpose of the network shown in the diagram?\r\n', 'q4-1762631336.png', 'It provides a VPN connection', 'It separates traffic types to improve security.', 'It performs data encryption.', 'It monitors physical devices.', 'B'),
(11, 4, 'Which of the following best describes phishing?\r\n', NULL, 'A type of malware that encrypts files', 'A brute-force attack on passwords', ' A network scanning tool', 'A social engineering attack that tricks users into revealing sensitive information', 'D'),
(12, 4, 'Which security measure is used primarily to block unauthorized network traffic?\r\n', NULL, 'Firewall', 'Backup', 'Anti-virus', 'Keylogger', 'A'),
(13, 4, 'Based on the cybersecurity domains in the diagram, which pair of areas focuses primarily on protecting data both in use and in recovery?\r\n\r\n', 'q4-1762631517.png', 'Application Security and End-User Security. ', 'End-User Security and Network Security.', 'Network Security and Operational Security. ', 'Information Security and Disaster Recovery Planning. ', 'D');

-- --------------------------------------------------------

--
-- Table structure for table `recommendedquestion`
--

CREATE TABLE `recommendedquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `learnerID` int(11) NOT NULL,
  `question` text NOT NULL,
  `questionFigureFileName` varchar(200) DEFAULT NULL,
  `answerA` varchar(300) NOT NULL,
  `answerB` varchar(300) NOT NULL,
  `answerC` varchar(300) NOT NULL,
  `answerD` varchar(300) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL,
  `status` enum('pending','approved','disapproved') NOT NULL DEFAULT 'pending',
  `comments` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recommendedquestion`
--

INSERT INTO `recommendedquestion` (`id`, `quizID`, `learnerID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`, `status`, `comments`) VALUES
(1, 1, 2, 'Which metric evaluates classification performance?', NULL, 'MSE', 'Accuracy', 'SSE', 'Silhouette', 'B', 'pending', 'Please review for AI quiz'),
(2, 2, 3, 'IoT devices commonly connect via…', NULL, 'BLE', 'HDMI', 'SATA', 'PCIe', 'A', 'approved', 'Looks good'),
(3, 3, 2, 'Which is NOT part of CIA?', NULL, 'Confidentiality', 'Integrity', 'Availability', 'Auditability', 'D', 'disapproved', 'Out of scope');

-- --------------------------------------------------------

--
-- Table structure for table `takenquiz`
--

CREATE TABLE `takenquiz` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `takenquiz`
--

INSERT INTO `takenquiz` (`id`, `quizID`, `score`) VALUES
(1, 1, 85.00),
(2, 2, 60.00),
(3, 3, 95.00);

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(11) NOT NULL,
  `topicName` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `topicName`) VALUES
(1, 'Artificial Intelligence (AI)'),
(2, 'Internet of Things (IoT)'),
(3, 'Cyber Security');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(60) NOT NULL,
  `lastName` varchar(60) NOT NULL,
  `emailAddress` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoFileName` varchar(200) DEFAULT NULL,
  `userType` enum('learner','educator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`, `userType`) VALUES
(1, 'Nora', 'AlQahtani', 'nora.educator@ksu.edu.sa', '$2y$10$0WK8EdOfjmzD.PN747p0iux7Pno8kFvGi6wR.cCXjX7EVpaLyTu0y', 'u1-nora.png', 'educator'),
(2, 'Reem', 'AlOtaibi', 'reem.learner@ksu.edu.sa', '$2y$10$zBxH/b/rmgpKBVvqHw4t3Oj/j.HP1a4Hi0f6GC6x0m/dgcYdMuhuO', 'u2-reem.png', 'learner'),
(3, 'Lama', 'AlHarbi', 'lama.learner@ksu.edu.sa', '$2y$10$HZ5QuEUgJ31eex/uuMr5A.ZuUvr99cbm6XYUWAAGaqIdcy6TbhXpu', 'u3-lama.png', 'learner'),
(4, 'Razan', 'AlMutairi', 'razan.educator@ksu.edu.sa', '$2y$10$PQxqOYlDDoBw.jthA48kTO.VyQgEtgEQ/..FFlemOAa3xYQILc/Lm', 'default_profile.png', 'educator');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quiz_educator` (`educatorID`),
  ADD KEY `idx_quiz_topic` (`topicID`);

--
-- Indexes for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qf_quiz` (`quizID`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qq_quiz` (`quizID`);

--
-- Indexes for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rq_quiz` (`quizID`),
  ADD KEY `idx_rq_learner` (`learnerID`);

--
-- Indexes for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tq_quiz` (`quizID`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `takenquiz`
--
ALTER TABLE `takenquiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `fk_quiz_educator` FOREIGN KEY (`educatorID`) REFERENCES `user` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quiz_topic` FOREIGN KEY (`topicID`) REFERENCES `topic` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD CONSTRAINT `fk_qf_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD CONSTRAINT `fk_rq_learner` FOREIGN KEY (`learnerID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rq_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD CONSTRAINT `fk_tq_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
