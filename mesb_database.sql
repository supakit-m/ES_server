-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2024 at 06:04 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mesb_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `email` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `changeEmailDT` datetime DEFAULT NULL,
  `usable` tinyint(1) DEFAULT NULL,
  `token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `email`, `username`, `password`, `changeEmailDT`, `usable`, `token`) VALUES
(1, 'fluky11731@gmail.com', 'fluky', 'e7433fe0aea99a10a2613e3477097c7e32b986527140b5dfe6b68d543e7f78de', NULL, 1, NULL),
(3, 'sss@gmail.com', 'sss', '$2y$10$DZ8jvSYGqooXQTWtYvyUvOEQ7WkI2jhG2fJvGj92QCo6vlh2iF.7y', NULL, 0, NULL),
(5, 'ggg@mail.com', 'g', '$2y$10$we2IbXztz0ZBporMpDBjFeBd5bwtRzexBfDfE2jEPAYJ1dOYH9uZy', NULL, 0, NULL),
(6, 'fff@mail.com', 'f', '$2y$10$08b6p5v/ro/XG1SBKI1QuuvMrrBy7ENOh89hIxuzWxdev4cROLW4e', NULL, 0, NULL),
(7, 'test000@mail.test', '0000', '9af15b336e6a9619928537df30b2e6a2376569fcf9d7e773eccede65606529a0', NULL, 1, 'tokenTest'),
(8, '1111@gmail.com', '1111', '0ffe1abd1a08215353c233d6e009613e95eec4253832a761af28ff37ac5a150c', NULL, NULL, NULL),
(14, 'sinonblackpan@gmail.com', 'spk', '2be597ea2af55525ee0861a12012848cab61fa5b2af97f678a51cb7f116a62c7', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dailyreport`
--

CREATE TABLE `dailyreport` (
  `reportID` int(11) NOT NULL,
  `accountID` int(11) DEFAULT NULL,
  `detectDate` date DEFAULT NULL,
  `detectAmount` int(11) DEFAULT NULL,
  `head` int(11) DEFAULT NULL,
  `arm` int(11) DEFAULT NULL,
  `back` int(11) DEFAULT NULL,
  `leg` int(11) DEFAULT NULL,
  `sitDuration` int(11) DEFAULT NULL,
  `amountSitOverLimit` int(11) DEFAULT NULL,
  `sitLimitOnDay` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detected`
--

CREATE TABLE `detected` (
  `detectedID` int(11) NOT NULL,
  `accountID` int(11) DEFAULT NULL,
  `detectDT` datetime DEFAULT NULL,
  `incorrectPoint` text DEFAULT NULL,
  `evidence` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `groupID` int(11) NOT NULL,
  `groupName` text NOT NULL,
  `defaultItemID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`groupID`, `groupName`, `defaultItemID`) VALUES
(1, 'detectFreq', 2),
(2, 'sitLimit', 9),
(3, 'sitLimitFreq', 14);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `itemID` int(11) NOT NULL,
  `groupID` int(11) DEFAULT NULL,
  `itemName` text DEFAULT NULL,
  `item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`itemID`, `groupID`, `itemName`, `item`) VALUES
(1, 1, NULL, '3'),
(2, 1, NULL, '5'),
(3, 1, NULL, '7'),
(4, 1, NULL, '9'),
(5, 1, NULL, '12'),
(6, 2, NULL, '10'),
(7, 2, NULL, '20'),
(8, 2, NULL, '30'),
(9, 2, NULL, '40'),
(10, 2, NULL, '50'),
(11, 3, NULL, '3'),
(12, 3, NULL, '5'),
(13, 3, NULL, '7'),
(14, 3, NULL, '9'),
(15, 3, NULL, '12'),
(24, 3, NULL, '20');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `accountID` int(11) NOT NULL,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `calibratedDT` datetime DEFAULT NULL,
  `detectFreq` int(11) DEFAULT NULL,
  `sitLimit` int(11) DEFAULT NULL,
  `sitLimitAlarmFreq` int(11) DEFAULT NULL,
  `lastLoginDT` datetime DEFAULT NULL,
  `lastDetectDT` datetime DEFAULT NULL,
  `newNotification` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notificationID` int(11) NOT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notificationID`, `startDate`, `endDate`, `message`) VALUES
(1, '2024-06-10', NULL, 'hello world'),
(4, '2024-06-08', '2024-06-29', 'dfzgsgfjskdf;dfgdsgfjsdf'),
(5, '2024-06-06', '2028-06-15', 'test on web'),
(6, '2024-06-13', '2024-06-07', 'test on web'),
(7, '2024-06-13', '2024-06-07', 'test on web'),
(8, '2024-06-13', '2024-06-07', 'test on web'),
(9, '2024-06-13', '2024-06-07', 'test on web'),
(24, '2024-08-27', '2024-09-13', 'Test 01-09'),
(25, '2024-09-04', '2024-09-27', 'test new notification'),
(26, '2024-08-26', '2024-09-28', 'test new notification v.2');

-- --------------------------------------------------------

--
-- Table structure for table `profilesaxis`
--

CREATE TABLE `profilesaxis` (
  `accountID` int(11) NOT NULL,
  `headDegree` float DEFAULT NULL,
  `armDegree` float DEFAULT NULL,
  `backDegree` float DEFAULT NULL,
  `legDegree` float DEFAULT NULL,
  `profileImgPath` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `dailyreport`
--
ALTER TABLE `dailyreport`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `accountID` (`accountID`);

--
-- Indexes for table `detected`
--
ALTER TABLE `detected`
  ADD PRIMARY KEY (`detectedID`),
  ADD KEY `accountID` (`accountID`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `defaultItemID` (`defaultItemID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `groupID` (`groupID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`accountID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notificationID`);

--
-- Indexes for table `profilesaxis`
--
ALTER TABLE `profilesaxis`
  ADD PRIMARY KEY (`accountID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dailyreport`
--
ALTER TABLE `dailyreport`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detected`
--
ALTER TABLE `detected`
  MODIFY `detectedID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `groupID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `accountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dailyreport`
--
ALTER TABLE `dailyreport`
  ADD CONSTRAINT `dailyreport_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `member` (`accountID`);

--
-- Constraints for table `detected`
--
ALTER TABLE `detected`
  ADD CONSTRAINT `detected_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `member` (`accountID`);

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`defaultItemID`) REFERENCES `items` (`itemID`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`);

--
-- Constraints for table `profilesaxis`
--
ALTER TABLE `profilesaxis`
  ADD CONSTRAINT `profilesaxis_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `member` (`accountID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
