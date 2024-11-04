-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2024 at 07:14 PM
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
(1, 'fluky11731@gmail.com', 'fluky', '1aacb4b30884c1382163955d95d9d89ea47badfa29e9be2be092c008cd36e737', NULL, 1, NULL),
(8, 'phonchaiphikulkhaw@gmail.com', 'Wave', 'cabb4ab725e2f4a6c7b3aa8307f8e103a791c493f5f71ea2760d79a033af54ec', NULL, 1, NULL),
(9, 'jirayut.ch2002@gmail.com', 'Full', '74af5b19f0515dbd47e5009c6a4251e72be45ecd0152030fbf93cd19aa72efb9', NULL, 1, NULL),
(12, 'projectes.webapp@gmail.com', 'Admin', '8b3987887195c5dfbac358a94da70a038b9594d42067ab6f392ec1814387da90', NULL, 1, NULL),
(13, 'temttt032545@gmail.com', 'Full2', 'd15807472096bef9871038f6ecae5efe75f5a7c4b818b0829b6db0f94bd31f75', NULL, NULL, 'dadcb992782396a6d161');

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
(1, 'detectFreq', 33),
(2, 'sitLimit', 8),
(3, 'sitLimitFreq', 12);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `itemID` int(11) NOT NULL,
  `groupID` int(11) DEFAULT NULL,
  `item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`itemID`, `groupID`, `item`) VALUES
(7, 2, '20'),
(8, 2, '30'),
(9, 2, '40'),
(10, 2, '50'),
(11, 3, '3'),
(12, 3, '5'),
(29, 1, '15'),
(33, 1, '5'),
(34, 1, '10'),
(38, 3, '10');

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
  `newNotification` tinyint(1) NOT NULL DEFAULT 1
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
(32, '2024-08-01', '2024-12-31', 'ประกาศลบประวัติการตรวจจับ : เรียนผู้ใช้งานทุกท่าน ทางเราจะทำการลบประวัติการตรวจจับที่มีวันที่ก่อนปี 2024 เพื่อปรับปรุงพื้นที่จัดเก็บข้อมูล เรียนมาเพื่อทราบ'),
(33, '2024-10-01', '2024-10-18', 'ประกาศแจ้งปิดปรับปรุงระบบ : การปิดปรับปรุงเซิร์ฟเวอร์ใน วันที่ 18 ตุลาคม 2024 เพื่อปรับปรุงและเพิ่มประสิทธิภาพการทำงานของระบบ ในระหว่างนี้ ระบบทั้งหมดจะไม่สามารถใช้งานได้ชั่วคราว ขออภัยในความไม่สะดวก');

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
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `accountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
