-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 10:00 PM
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
-- Database: `zevnik_projekt`
--

-- --------------------------------------------------------

--
-- Table structure for table `albumi`
--

CREATE TABLE `albumi` (
  `id` int(11) NOT NULL,
  `Ime` varchar(20) NOT NULL,
  `Opis` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `albumi`
--

INSERT INTO `albumi` (`id`, `Ime`, `Opis`) VALUES
(13, 'Graduation', 'Kanye West'),
(14, 'Graduation', 'kanye west'),
(15, 'Jan Plestenjak sanje', 'mal'),
(16, 'xavier', '');

-- --------------------------------------------------------

--
-- Table structure for table `album_izvajalci`
--

CREATE TABLE `album_izvajalci` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `izvajalec_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `album_izvajalci`
--

INSERT INTO `album_izvajalci` (`id`, `album_id`, `izvajalec_id`) VALUES
(12, 13, 3),
(14, 15, 4),
(15, 16, 5);

-- --------------------------------------------------------

--
-- Table structure for table `izvajalci`
--

CREATE TABLE `izvajalci` (
  `id` int(11) NOT NULL,
  `Ime` varchar(20) NOT NULL,
  `Opis` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `izvajalci`
--

INSERT INTO `izvajalci` (`id`, `Ime`, `Opis`) VALUES
(1, 'Eminem', 'Ameriški reper, tekstopisec in producent.'),
(2, 'Modrijani', 'Slovenska narodno-zabavna skupina.'),
(3, 'Kanye West', 'Ameriški reper, producent in oblikovalec.'),
(4, 'Jan Plestenjak', 'Slovenski pop pevec in kantavtor.'),
(5, 'Xavier Wulf', 'Rapper');

-- --------------------------------------------------------

--
-- Table structure for table `pesem_izvajalci`
--

CREATE TABLE `pesem_izvajalci` (
  `id` int(11) NOT NULL,
  `pesem_id` int(11) NOT NULL,
  `izvajalec_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `pesem_izvajalci`
--

INSERT INTO `pesem_izvajalci` (`id`, `pesem_id`, `izvajalec_id`) VALUES
(11, 7, 3),
(19, 21, 5);

-- --------------------------------------------------------

--
-- Table structure for table `pesmi`
--

CREATE TABLE `pesmi` (
  `id` int(11) NOT NULL,
  `Ime` varchar(20) NOT NULL,
  `leto_izdaje` char(20) DEFAULT NULL,
  `Dolzina` varchar(20) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `zanr_id` int(11) DEFAULT NULL,
  `pod_do_pesmi` varchar(200) DEFAULT NULL,
  `pot_do_slike` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `pesmi`
--

INSERT INTO `pesmi` (`id`, `Ime`, `leto_izdaje`, `Dolzina`, `album_id`, `zanr_id`, `pod_do_pesmi`, `pot_do_slike`) VALUES
(7, 'Homecoming', '2008', '00:03:24', 13, 2, 'pesmi/Homecoming.mp3', 'slike_pesmi/OIP-1732803988.jpg'),
(21, 'First light', '2021', '00:03:15', 16, 2, 'pesmi/Xavier_Wulf_-__First_Light___Music_Video_.mp3', 'slike_pesmi/d76-398716719.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `uporabniki`
--

CREATE TABLE `uporabniki` (
  `id` int(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `password` varchar(200) NOT NULL,
  `vloga` varchar(20) DEFAULT 'uporabnik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `uporabniki`
--

INSERT INTO `uporabniki` (`id`, `user`, `password`, `vloga`) VALUES
(4, 'jan', '$2y$10$CR8HDL.hgHpPqjVU/dB3PuSH/aCSvn15nV/hBDBVVcu5fUJtGSNnK', 'admin'),
(5, 'user', '$2y$10$OSKCvYOg/.QEwWO8.KKitum9vC4gmCBEo7GCZNQamoiVi1Dfcmxpe', 'uporabnik');

-- --------------------------------------------------------

--
-- Table structure for table `uporabniki_like`
--

CREATE TABLE `uporabniki_like` (
  `id` int(11) NOT NULL,
  `uporabnik_id` int(11) NOT NULL,
  `pesem_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `uporabniki_like`
--

INSERT INTO `uporabniki_like` (`id`, `uporabnik_id`, `pesem_id`) VALUES
(68, 4, 21);

-- --------------------------------------------------------

--
-- Table structure for table `zanri`
--

CREATE TABLE `zanri` (
  `id` int(11) NOT NULL,
  `Ime` varchar(20) NOT NULL,
  `Opis` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `zanri`
--

INSERT INTO `zanri` (`id`, `Ime`, `Opis`) VALUES
(1, 'Rok', 'nekaksen opis'),
(2, 'Pop', 'popularna glasba');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albumi`
--
ALTER TABLE `albumi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `album_izvajalci`
--
ALTER TABLE `album_izvajalci`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IX_Relationship9` (`album_id`),
  ADD KEY `IX_Relationship10` (`izvajalec_id`);

--
-- Indexes for table `izvajalci`
--
ALTER TABLE `izvajalci`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesem_izvajalci`
--
ALTER TABLE `pesem_izvajalci`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IX_Relationship6` (`pesem_id`),
  ADD KEY `IX_Relationship7` (`izvajalec_id`);

--
-- Indexes for table `pesmi`
--
ALTER TABLE `pesmi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IX_Relationship4` (`album_id`),
  ADD KEY `IX_Relationship11` (`zanr_id`);

--
-- Indexes for table `uporabniki`
--
ALTER TABLE `uporabniki`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uporabniki_like`
--
ALTER TABLE `uporabniki_like`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IX_Relationship1` (`uporabnik_id`),
  ADD KEY `IX_Relationship3` (`pesem_id`);

--
-- Indexes for table `zanri`
--
ALTER TABLE `zanri`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albumi`
--
ALTER TABLE `albumi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `album_izvajalci`
--
ALTER TABLE `album_izvajalci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `izvajalci`
--
ALTER TABLE `izvajalci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pesem_izvajalci`
--
ALTER TABLE `pesem_izvajalci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pesmi`
--
ALTER TABLE `pesmi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `uporabniki`
--
ALTER TABLE `uporabniki`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `uporabniki_like`
--
ALTER TABLE `uporabniki_like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `zanri`
--
ALTER TABLE `zanri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `album_izvajalci`
--
ALTER TABLE `album_izvajalci`
  ADD CONSTRAINT `Relationship10` FOREIGN KEY (`izvajalec_id`) REFERENCES `izvajalci` (`id`),
  ADD CONSTRAINT `Relationship9` FOREIGN KEY (`album_id`) REFERENCES `albumi` (`id`);

--
-- Constraints for table `pesem_izvajalci`
--
ALTER TABLE `pesem_izvajalci`
  ADD CONSTRAINT `Relationship6` FOREIGN KEY (`pesem_id`) REFERENCES `pesmi` (`id`),
  ADD CONSTRAINT `Relationship7` FOREIGN KEY (`izvajalec_id`) REFERENCES `izvajalci` (`id`);

--
-- Constraints for table `pesmi`
--
ALTER TABLE `pesmi`
  ADD CONSTRAINT `Relationship11` FOREIGN KEY (`zanr_id`) REFERENCES `zanri` (`id`),
  ADD CONSTRAINT `Relationship4` FOREIGN KEY (`album_id`) REFERENCES `albumi` (`id`);

--
-- Constraints for table `uporabniki_like`
--
ALTER TABLE `uporabniki_like`
  ADD CONSTRAINT `Relationship1` FOREIGN KEY (`uporabnik_id`) REFERENCES `uporabniki` (`id`),
  ADD CONSTRAINT `Relationship3` FOREIGN KEY (`pesem_id`) REFERENCES `pesmi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
