-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2018 at 07:35 PM
-- Server version: 10.1.30-MariaDB
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `podcast`
--

-- --------------------------------------------------------

--
-- Table structure for table `podcasts`
--

CREATE TABLE `podcasts` (
  `id` int(5) UNSIGNED NOT NULL,
  `podcast_id` tinyint(2) UNSIGNED NOT NULL,
  `track` smallint(4) UNSIGNED NOT NULL,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `author` text COLLATE utf8_unicode_ci NOT NULL,
  `explicit` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `pub_date` datetime NOT NULL,
  `type` char(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full',
  `link` text COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `subtitle` text COLLATE utf8_unicode_ci NOT NULL,
  `duration` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '00:00:00',
  `keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `public` int(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `podcasts`
--

INSERT INTO `podcasts` (`id`, `podcast_id`, `track`, `title`, `author`, `explicit`, `pub_date`, `type`, `link`, `image`, `description`, `subtitle`, `duration`, `keywords`, `public`) VALUES
(1, 1, 0, '[PODCAST TITLE]', '[PODCAST AUTHORS]', 'no', '2018-01-01 00:00:00', 'episodic', '[PODCAST URL]', '[PODCAST IMAGE]', '[PODCAST DESCRIPTION]', '[PODCAST SUBTITLE]', '[NIL]', '[PODCAST CATEGORY]|[PODCAST SUBCATEGORY]|[PODCAST TAGS]', 1),
(2, 1, 1, '[EPISODE TITLE]', '[EPISODE AUTHORS]', 'no', '2018-01-01 00:00:00', 'full', '[EPISODE MP3 URL]', '[EPISODE IMAGE]', '[EPISODE DESCRIPTION]', '[EPISODE SUBTITLE]', '00:00:00', '[EPISODE TAGS]', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `podcasts`
--
ALTER TABLE `podcasts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `podcasts`
--
ALTER TABLE `podcasts`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
