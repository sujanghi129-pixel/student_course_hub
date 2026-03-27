-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 11:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_course_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `staff_id`) VALUES
(3, 'Rajan', 'acharyarajan063@gmail.com', '$2y$10$GlWr6JOOncktqgnm42KJWegkeeu/wYN5QQQT0p4Yv4CSaVuELPgNa', 'admin', NULL),
(6, 'Dr Alice', 'staff1@university.com', '$2y$10$3bA4yYxTcOvMduxLcatEIeZgLtwxenx1BpfVV6np8/4XIa4XYlcSG', 'staff', 1),
(7, 'Dr Brian', 'staff2@university.com', '$2y$10$nYRZzdhBCqls6kAJC.VIJeq8nXRdCCXrXDSRruiFtCv38XP1/Ki/i', 'staff', 2),
(8, 'Dr Carol White', 'staff3@university.com', '$2y$10$8o3sV10CmmJAU5ADCRtN7.2WZ.ynC/eJBoOVmjgxxCI/1qZeVpUfq', 'staff', 3),
(9, 'Dr David Green', 'staff4@university.com', '$2y$10$ym8MZmLcbdn5ScOStqMr3eFlCazskTqA2fEO0q0g2HyBctYjNcLki', 'staff', 4),
(10, 'Dr Emma Scott', 'staff5@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 5),
(11, 'Dr Frank Moore', 'staff6@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 6),
(12, 'Dr Grace Adams', 'staff7@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 7),
(13, 'Dr Henry Clark', 'staff8@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 8),
(14, 'Dr Irene Hall', 'staff9@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 9),
(15, 'Dr James Wright', 'staff10@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 10),
(16, 'Dr Sophia Miller', 'staff11@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 11),
(17, 'Dr Benjamin Carter', 'staff12@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 12),
(18, 'Dr Chloe Thompson', 'staff13@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 13),
(19, 'Dr Daniel Robinson', 'staff14@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 14),
(20, 'Dr Emily Davis', 'staff15@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 15),
(21, 'Dr Nathan Hughes', 'staff16@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 16),
(22, 'Dr Olivia Martin', 'staff17@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 17),
(23, 'Dr Samuel Anderson', 'staff18@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 18),
(24, 'Dr Victoria Hall', 'staff19@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 19),
(25, 'Dr William Scott', 'staff20@university.com', '$2y$10$zjQ4UXXRymfLEo8irFsck.ken7u/7ok4SrMcZgyqXVlZFYXCIfoZe', 'staff', 20);

-- --------------------------------------------------------

--
-- Table structure for table `interestedstudents`
--

CREATE TABLE `interestedstudents` (
  `InterestID` int(11) NOT NULL,
  `ProgrammeID` int(11) NOT NULL,
  `StudentName` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `RegisteredAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interestedstudents`
--

INSERT INTO `interestedstudents` (`InterestID`, `ProgrammeID`, `StudentName`, `Email`, `RegisteredAt`) VALUES
(1, 1, 'John Doe', 'john.doe@example.com', '2026-03-24 14:26:00'),
(2, 4, 'Jane Smith', 'jane.smith@example.com', '2026-03-24 14:26:00'),
(3, 6, 'Alex Brown', 'alex.brown@example.com', '2026-03-24 14:26:00'),
(4, 9, 'Priya Patel', 'priya.patel@example.com', '2026-03-24 14:26:00'),
(10, 4, 'Rajani Shrestha', 'rajani.shrestha34@swissedu.co.uk', '2026-03-25 18:48:21');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `LevelID` int(11) NOT NULL,
  `LevelName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`LevelID`, `LevelName`) VALUES
(1, 'Undergraduate'),
(2, 'Postgraduate');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `ModuleID` int(11) NOT NULL,
  `ModuleName` text NOT NULL,
  `ModuleLeaderID` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`ModuleID`, `ModuleName`, `ModuleLeaderID`, `Description`, `Image`) VALUES
(1, 'Introduction to Programming', 1, 'Covers the fundamentals of programming using Python and Java.', NULL),
(2, 'Mathematics for Computer Science', 2, 'Teaches discrete mathematics, linear algebra, and probability theory.', NULL),
(3, 'Computer Systems & Architecture', 3, 'Explores CPU design, memory management, and assembly language.', NULL),
(4, 'Databases', 4, 'Covers SQL, relational database design, and NoSQL systems.', NULL),
(5, 'Software Engineering', 5, 'Focuses on agile development, design patterns, and project management.', NULL),
(6, 'Algorithms & Data Structures', 6, 'Examines sorting, searching, graphs, and complexity analysis.', NULL),
(7, 'Cyber Security Fundamentals', 7, 'Provides an introduction to network security, cryptography, and vulnerabilities.', NULL),
(8, 'Artificial Intelligence', 8, 'Introduces AI concepts such as neural networks, expert systems, and robotics.', NULL),
(9, 'Machine Learning', 9, 'Explores supervised and unsupervised learning, including decision trees and clustering.', NULL),
(10, 'Ethical Hacking', 10, 'Covers penetration testing, security assessments, and cybersecurity laws.', NULL),
(11, 'Computer Networks', 1, 'Teaches TCP/IP, network layers, and wireless communication.', NULL),
(12, 'Software Testing & Quality Assurance', 2, 'Focuses on automated testing, debugging, and code reliability.', NULL),
(13, 'Embedded Systems', 3, 'Examines microcontrollers, real-time OS, and IoT applications.', NULL),
(14, 'Human-Computer Interaction', 4, 'Studies UI/UX design, usability testing, and accessibility.', NULL),
(15, 'Blockchain Technologies', 5, 'Covers distributed ledgers, consensus mechanisms, and smart contracts.', NULL),
(16, 'Cloud Computing', 6, 'Introduces cloud services, virtualization, and distributed systems.', NULL),
(17, 'Digital Forensics', 7, 'Teaches forensic investigation techniques for cybercrime.', NULL),
(18, 'Final Year Project', 8, 'A major independent project where students develop a software solution.', NULL),
(19, 'Advanced Machine Learning', 11, 'Covers deep learning, reinforcement learning, and cutting-edge AI techniques.', NULL),
(20, 'Cyber Threat Intelligence', 12, 'Focuses on cybersecurity risk analysis, malware detection, and threat mitigation.', NULL),
(21, 'Big Data Analytics', 13, 'Explores data mining, distributed computing, and AI-driven insights.', NULL),
(22, 'Cloud & Edge Computing', 14, 'Examines scalable cloud platforms, serverless computing, and edge networks.', NULL),
(23, 'Blockchain & Cryptography', 15, 'Covers decentralized applications, consensus algorithms, and security measures.', NULL),
(24, 'AI Ethics & Society', 16, 'Analyzes ethical dilemmas in AI, fairness, bias, and regulatory considerations.', NULL),
(25, 'Quantum Computing', 17, 'Introduces quantum algorithms, qubits, and cryptographic applications.', NULL),
(26, 'Cybersecurity Law & Policy', 18, 'Explores digital privacy, GDPR, and international cyber law.', NULL),
(27, 'Neural Networks & Deep Learning', 19, 'Delves into convolutional networks, GANs, and AI advancements.', NULL),
(28, 'Human-AI Interaction', 20, 'Studies AI usability, NLP systems, and social robotics.', NULL),
(29, 'Autonomous Systems', 11, 'Focuses on self-driving technology, robotics, and intelligent agents.', NULL),
(30, 'Digital Forensics & Incident Response', 12, 'Teaches forensic analysis, evidence gathering, and threat mitigation.', NULL),
(31, 'Postgraduate Dissertation', 13, 'A major research project where students explore advanced topics in computing.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `programmemodules`
--

CREATE TABLE `programmemodules` (
  `ProgrammeModuleID` int(11) NOT NULL,
  `ProgrammeID` int(11) DEFAULT NULL,
  `ModuleID` int(11) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programmemodules`
--

INSERT INTO `programmemodules` (`ProgrammeModuleID`, `ProgrammeID`, `ModuleID`, `Year`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 2, 1, 1),
(6, 2, 2, 1),
(7, 2, 3, 1),
(8, 2, 4, 1),
(9, 3, 1, 1),
(10, 3, 2, 1),
(11, 3, 3, 1),
(12, 3, 4, 1),
(13, 4, 1, 1),
(14, 4, 2, 1),
(15, 4, 3, 1),
(16, 4, 4, 1),
(17, 5, 1, 1),
(18, 5, 2, 1),
(19, 5, 3, 1),
(20, 5, 4, 1),
(21, 1, 5, 2),
(22, 1, 6, 2),
(23, 1, 7, 2),
(24, 1, 8, 2),
(25, 2, 5, 2),
(26, 2, 6, 2),
(27, 2, 12, 2),
(28, 2, 14, 2),
(29, 3, 5, 2),
(30, 3, 9, 2),
(31, 3, 8, 2),
(32, 3, 10, 2),
(33, 4, 7, 2),
(34, 4, 10, 2),
(35, 4, 11, 2),
(36, 4, 17, 2),
(37, 5, 5, 2),
(38, 5, 6, 2),
(39, 5, 9, 2),
(40, 5, 16, 2),
(41, 1, 11, 3),
(42, 1, 13, 3),
(43, 1, 15, 3),
(44, 1, 18, 3),
(45, 2, 13, 3),
(46, 2, 15, 3),
(47, 2, 16, 3),
(48, 2, 18, 3),
(49, 3, 13, 3),
(50, 3, 15, 3),
(51, 3, 16, 3),
(52, 3, 18, 3),
(53, 4, 15, 3),
(54, 4, 16, 3),
(55, 4, 17, 3),
(56, 4, 18, 3),
(57, 5, 9, 3),
(58, 5, 14, 3),
(59, 5, 16, 3),
(60, 5, 18, 3),
(61, 6, 19, 1),
(62, 6, 24, 1),
(63, 6, 27, 1),
(64, 6, 29, 1),
(65, 6, 31, 1),
(66, 7, 20, 1),
(67, 7, 26, 1),
(68, 7, 30, 1),
(69, 7, 23, 1),
(70, 7, 31, 1),
(71, 8, 21, 1),
(72, 8, 22, 1),
(73, 8, 27, 1),
(74, 8, 28, 1),
(75, 8, 31, 1),
(76, 9, 19, 1),
(77, 9, 24, 1),
(78, 9, 28, 1),
(79, 9, 29, 1),
(80, 9, 31, 1),
(81, 10, 23, 1),
(82, 10, 22, 1),
(83, 10, 25, 1),
(84, 10, 26, 1),
(85, 10, 31, 1);

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `ProgrammeID` int(11) NOT NULL,
  `ProgrammeName` text NOT NULL,
  `LevelID` int(11) DEFAULT NULL,
  `ProgrammeLeaderID` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programmes`
--

INSERT INTO `programmes` (`ProgrammeID`, `ProgrammeName`, `LevelID`, `ProgrammeLeaderID`, `Description`, `Image`) VALUES
(1, 'BSc Computer Science', 1, 1, 'A broad computer science degree covering programming, AI, cybersecurity, and software engineering.', 'bsc computer science.webp'),
(2, 'BSc Software Engineering', 1, 2, 'A specialized degree focusing on the development and lifecycle of software applications.', 'bsc software engineering.jpg'),
(3, 'BSc Artificial Intelligence', 1, 3, 'Focuses on machine learning, deep learning, and AI applications.', 'bsc artificial intelligence.webp'),
(4, 'BSc Cyber Security', 1, 4, 'Explores network security, ethical hacking, and digital forensics.', 'BSC Cybersecurity.jpg'),
(5, 'BSc Data Science', 1, 5, 'Covers big data, machine learning, and statistical computing.', 'bsc data science.jpeg'),
(6, 'MSc Machine Learning', 2, 11, 'A postgraduate degree focusing on deep learning, AI ethics, and neural networks.', 'msc machine learning.avif'),
(7, 'MSc Cyber Security', 2, 12, 'A specialized programme covering digital forensics, cyber threat intelligence, and security policy.', 'msc cyber security.jpg'),
(8, 'MSc Data Science', 2, 13, 'Focuses on big data analytics, cloud computing, and AI-driven insights.', 'msc data science.jpg'),
(9, 'MSc Artificial Intelligence', 2, 14, 'Explores autonomous systems, AI ethics, and deep learning technologies.', 'msc artificial intelligence.jpg'),
(10, 'MSc Software Engineering', 2, 15, 'Emphasizes software design, blockchain applications, and cutting-edge methodologies.', 'msc software engineering.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `Name` text NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `Name`, `email`) VALUES
(1, 'Dr. Alice Johnson', 'staff1@university.com'),
(2, 'Dr. Brian Lee', 'staff2@university.com'),
(3, 'Dr. Carol White', 'staff3@university.com'),
(4, 'Dr. David Green', 'staff4@university.com'),
(5, 'Dr. Emma Scott', 'staff5@university.com'),
(6, 'Dr. Frank Moore', 'staff6@university.com'),
(7, 'Dr. Grace Adams', 'staff7@university.com'),
(8, 'Dr. Henry Clark', 'staff8@university.com'),
(9, 'Dr. Irene Hall', 'staff9@university.com'),
(10, 'Dr. James Wright', 'staff10@university.com'),
(11, 'Dr. Sophia Miller', 'staff11@university.com'),
(12, 'Dr. Benjamin Carter', 'staff12@university.com'),
(13, 'Dr. Chloe Thompson', 'staff13@university.com'),
(14, 'Dr. Daniel Robinson', 'staff14@university.com'),
(15, 'Dr. Emily Davis', 'staff15@university.com'),
(16, 'Dr. Nathan Hughes', 'staff16@university.com'),
(17, 'Dr. Olivia Martin', 'staff17@university.com'),
(18, 'Dr. Samuel Anderson', 'staff18@university.com'),
(19, 'Dr. Victoria Hall', 'staff19@university.com'),
(20, 'Dr. William Scott', 'staff20@university.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `interestedstudents`
--
ALTER TABLE `interestedstudents`
  ADD PRIMARY KEY (`InterestID`),
  ADD KEY `ProgrammeID` (`ProgrammeID`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`LevelID`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`ModuleID`),
  ADD KEY `ModuleLeaderID` (`ModuleLeaderID`);

--
-- Indexes for table `programmemodules`
--
ALTER TABLE `programmemodules`
  ADD PRIMARY KEY (`ProgrammeModuleID`),
  ADD KEY `ProgrammeID` (`ProgrammeID`),
  ADD KEY `ModuleID` (`ModuleID`);

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`ProgrammeID`),
  ADD KEY `LevelID` (`LevelID`),
  ADD KEY `ProgrammeLeaderID` (`ProgrammeLeaderID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `interestedstudents`
--
ALTER TABLE `interestedstudents`
  MODIFY `InterestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `programmemodules`
--
ALTER TABLE `programmemodules`
  MODIFY `ProgrammeModuleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `ProgrammeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `interestedstudents`
--
ALTER TABLE `interestedstudents`
  ADD CONSTRAINT `interestedstudents_ibfk_1` FOREIGN KEY (`ProgrammeID`) REFERENCES `programmes` (`ProgrammeID`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`ModuleLeaderID`) REFERENCES `staff` (`StaffID`);

--
-- Constraints for table `programmemodules`
--
ALTER TABLE `programmemodules`
  ADD CONSTRAINT `programmemodules_ibfk_1` FOREIGN KEY (`ProgrammeID`) REFERENCES `programmes` (`ProgrammeID`),
  ADD CONSTRAINT `programmemodules_ibfk_2` FOREIGN KEY (`ModuleID`) REFERENCES `modules` (`ModuleID`);

--
-- Constraints for table `programmes`
--
ALTER TABLE `programmes`
  ADD CONSTRAINT `programmes_ibfk_1` FOREIGN KEY (`LevelID`) REFERENCES `levels` (`LevelID`),
  ADD CONSTRAINT `programmes_ibfk_2` FOREIGN KEY (`ProgrammeLeaderID`) REFERENCES `staff` (`StaffID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
