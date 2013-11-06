-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Host: mysql.project-files.net
-- Generation Time: Jun 14, 2012 at 05:32 PM
-- Server version: 5.1.53
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `cti_corporate_engagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `coach_id` mediumint(9) NOT NULL,
  `organization_id` mediumint(9) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `start_date` date NOT NULL,
  `sessions_allotment` int(11) NOT NULL DEFAULT '0',
  `sessions_frequency` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `focus_area` text NOT NULL,
  `success_metrics` text NOT NULL,
  `organization_level` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `coach_id` (`coach_id`),
  KEY `organization_id` (`organization_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `coach_id`, `organization_id`, `user_id`, `start_date`, `sessions_allotment`, `sessions_frequency`, `tags`, `focus_area`, `success_metrics`, `organization_level`) VALUES
(1, 4, 1, 4, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(2, 20, 1, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(3, 1, 3, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(4, 2, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(5, 1, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(6, 2, 5, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(7, 2, 7, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(8, 20, 2, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(9, 5, 1, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(10, 15, 3, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(11, 18, 4, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(12, 6, 4, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(13, 3, 4, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(14, 9, 5, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(15, 7, 5, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(16, 13, 5, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(17, 17, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(18, 16, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(19, 14, 7, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(20, 13, 5, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(21, 8, 4, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(22, 4, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(23, 20, 7, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(24, 4, 3, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President'),
(25, 2, 6, 0, '2012-06-14', 16, 'Weekly', 'SanJose', 'Becoming a better leader', 'A new level in sales and employee engagement.', 'Executive Vice President');

-- --------------------------------------------------------

--
-- Table structure for table `coachs`
--

CREATE TABLE IF NOT EXISTS `coachs` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL,
  `schedule_url` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `expertise` text NOT NULL,
  `bio_complete` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `coachs`
--

INSERT INTO `coachs` (`id`, `user_id`, `schedule_url`, `bio`, `expertise`, `bio_complete`) VALUES
(1, 3, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(2, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(3, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(4, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(5, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(6, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(7, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(8, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(9, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(10, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(11, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(12, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(13, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(14, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(15, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(16, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(17, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(18, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(19, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(20, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(21, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(22, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(23, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(24, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', ''),
(25, 0, 'http://www.timetrade.com', 'Here is my bio...', 'Many, many things', '');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE IF NOT EXISTS `documents` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `client_id` mediumint(9) NOT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `documentTemplate_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_id` (`client_id`,`documentTemplate_id`),
  KEY `documentTemplate_id` (`documentTemplate_id`),
  KEY `client_id_2` (`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=78 ;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `client_id`, `title`, `url`, `documentTemplate_id`) VALUES
(53, 1, 'Quisque tincidunt pede', 'http://www.mydocumentlink.com', 1),
(54, 13, 'ante dictum cursus. Nunc mauris', 'http://www.mydocumentlink.com', 1),
(56, 9, 'luctus aliquet odio. Etiam', 'http://www.mydocumentlink.com', 1),
(57, 6, 'non massa', 'http://www.mydocumentlink.com', 1),
(58, 25, 'ultricies dignissim lacus.', 'http://www.mydocumentlink.com', 1),
(59, 20, 'fermentum metus.', 'http://www.mydocumentlink.com', 1),
(60, 8, 'et nunc. Quisque ornare tortor', 'http://www.mydocumentlink.com', 1),
(61, 24, 'faucibus lectus, a sollicitudin orci', 'http://www.mydocumentlink.com', 1),
(62, 5, 'enim nec', 'http://www.mydocumentlink.com', 1),
(64, 10, 'ultrices posuere', 'http://www.mydocumentlink.com', 1),
(65, 2, 'semper tellus id nunc', 'http://www.mydocumentlink.com', 1),
(66, 21, 'mus. Proin', 'http://www.mydocumentlink.com', 1),
(67, 11, 'Suspendisse aliquet molestie', 'http://www.mydocumentlink.com', 1),
(69, 12, 'nunc, ullamcorper eu, euismod', 'http://www.mydocumentlink.com', 1),
(73, 4, 'odio. Phasellus at augue id', 'http://www.mydocumentlink.com', 1),
(74, 22, 'enim, sit', 'http://www.mydocumentlink.com', 1),
(75, 16, 'Quisque ornare tortor', 'http://www.mydocumentlink.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `documentTemplates`
--

CREATE TABLE IF NOT EXISTS `documentTemplates` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `readonly` bit(1) NOT NULL DEFAULT b'1',
  `confidential` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `documentTemplates`
--

INSERT INTO `documentTemplates` (`id`, `title`, `url`, `readonly`, `confidential`) VALUES
(1, 'facilisi. Sed neque. Sed', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(2, 'eu, ultrices sit', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(3, 'ligula. Aenean gravida nunc sed', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(4, 'sed pede. Cum sociis natoque', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(5, 'nec, diam.', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(6, 'ridiculus mus.', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(7, 'tincidunt. Donec', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(8, 'condimentum eget, volutpat', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(9, 'porttitor interdum. Sed auctor', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(10, 'tellus faucibus leo,', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(11, 'dis parturient montes, nascetur', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(12, 'Donec tempus, lorem fringilla ornare', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(13, 'nulla. Cras eu tellus eu', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(14, 'velit justo nec ante.', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(15, 'suscipit nonummy. Fusce fermentum', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(16, 'faucibus ut, nulla.', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(17, 'congue, elit sed', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(18, 'enim, condimentum eget, volutpat ornare,', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(19, 'erat, in', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(20, 'eros turpis non enim.', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(21, 'est. Nunc', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(22, 'arcu iaculis enim, sit amet', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(23, 'Aliquam ultrices iaculis', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(24, 'sed tortor. Integer aliquam', 'http://www.mydocumentlink.com/template', '\0', '\0'),
(25, 'netus et malesuada fames', 'http://www.mydocumentlink.com/template', '\0', '\0');

-- --------------------------------------------------------

--
-- Table structure for table `link_organizations_coachs`
--

CREATE TABLE IF NOT EXISTS `link_organizations_coachs` (
  `organization_id` mediumint(9) NOT NULL,
  `coach_id` mediumint(9) NOT NULL,
  KEY `organization_id` (`organization_id`,`coach_id`),
  KEY `coach_id` (`coach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `link_organizations_coachs`
--

INSERT INTO `link_organizations_coachs` (`organization_id`, `coach_id`) VALUES
(1, 5),
(1, 15),
(1, 20),
(1, 24),
(2, 2),
(2, 7),
(2, 10),
(2, 17),
(3, 5),
(4, 10),
(4, 13),
(4, 24),
(5, 11),
(5, 14),
(5, 14),
(5, 19),
(5, 24),
(6, 5),
(6, 8),
(6, 14),
(6, 18),
(6, 21),
(7, 3),
(7, 12),
(7, 16);

-- --------------------------------------------------------

--
-- Table structure for table `link_organizations_documentTemplates`
--

CREATE TABLE IF NOT EXISTS `link_organizations_documentTemplates` (
  `organization_id` mediumint(9) NOT NULL,
  `documentTemplate_id` mediumint(9) NOT NULL,
  KEY `organization_id` (`organization_id`,`documentTemplate_id`),
  KEY `documentTemplate_id` (`documentTemplate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `link_organizations_documentTemplates`
--

INSERT INTO `link_organizations_documentTemplates` (`organization_id`, `documentTemplate_id`) VALUES
(1, 4),
(1, 4),
(1, 14),
(1, 19),
(1, 23),
(2, 14),
(2, 20),
(2, 24),
(3, 7),
(3, 25),
(3, 25),
(4, 3),
(4, 6),
(4, 8),
(4, 12),
(4, 15),
(4, 17),
(4, 17),
(5, 11),
(5, 22),
(6, 10),
(6, 23),
(7, 1),
(7, 8),
(7, 22);

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE IF NOT EXISTS `organizations` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `organization_name` varchar(100) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `addr_street` varchar(200) NOT NULL,
  `addr_city` varchar(200) NOT NULL,
  `addr_state` varchar(50) NOT NULL,
  `addr_zip` varchar(25) NOT NULL,
  `notes` text NOT NULL,
  `budget` int(11) NOT NULL COMMENT 'in dollars',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `organization_name`, `user_id`, `addr_street`, `addr_city`, `addr_state`, `addr_zip`, `notes`, `budget`) VALUES
(1, 'Microsoft', 2, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(2, 'Google', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(3, 'Borland', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(4, 'Adobe', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(5, 'Chami', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(6, 'Lavasoft', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000),
(7, 'Macromedia', 0, '4667 Mission Street', 'San Francisco', 'CA', '94110', 'some notes on this company', 50000);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `client_id` mediumint(9) NOT NULL,
  `coach_id` mediumint(9) NOT NULL,
  `session_datetime` datetime NOT NULL,
  `confidential_notes` text NOT NULL,
  `progress_notes` text NOT NULL,
  `progress_notes_approved` bit(1) NOT NULL DEFAULT b'0',
  `status_code` int(11) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT 'in minutes',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`coach_id`),
  KEY `coach_id` (`coach_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `client_id`, `coach_id`, `session_datetime`, `confidential_notes`, `progress_notes`, `progress_notes_approved`, `status_code`, `duration`) VALUES
(1, 4, 2, '2013-01-01 06:16:29', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(2, 8, 2, '2012-02-19 08:01:38', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(3, 25, 23, '2013-05-19 05:14:17', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(4, 10, 13, '2012-08-12 16:21:27', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(5, 3, 20, '2013-01-12 21:23:28', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(6, 15, 10, '2011-07-17 21:15:41', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(7, 20, 23, '2013-03-10 10:23:47', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(8, 2, 11, '2011-12-15 20:34:00', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(9, 6, 3, '2011-12-19 09:08:31', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(10, 2, 5, '2011-08-02 15:55:14', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(11, 11, 14, '2012-02-08 02:31:06', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(12, 25, 11, '2013-01-24 06:04:14', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(13, 6, 4, '2012-11-06 20:34:38', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(14, 22, 3, '2012-08-11 16:03:39', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(15, 23, 19, '2012-01-02 01:41:19', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(16, 3, 24, '2013-01-10 21:53:25', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(17, 8, 10, '2013-04-07 05:27:50', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(18, 24, 7, '2013-02-24 13:10:27', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(19, 8, 9, '2012-03-25 19:49:54', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(20, 20, 11, '2013-02-15 21:20:31', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(21, 3, 9, '2013-02-02 22:21:28', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(22, 20, 23, '2012-09-30 11:53:42', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(23, 7, 22, '2011-08-04 09:17:24', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(24, 8, 12, '2013-01-24 14:14:36', 'this is the confidential note', 'Here is my progress', '', 1, 40),
(25, 25, 10, '2012-02-02 16:42:37', 'this is the confidential note', 'Here is my progress', '', 1, 40);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `userType` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_code` varchar(255) NOT NULL,
  `last_login` datetime NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `userType`, `username`, `password`, `reset_code`, `last_login`, `first_name`, `last_name`, `phone`) VALUES
(1, 1, 'poorni@thecoaches.com', 'd41e98d1eafa6d6011d3a70f1a5b92f0', '', '0000-00-00 00:00:00', 'Poorni ', 'Bid', '415-555-5555'),
(2, 2, 'hr@nike.com', 'd41e98d1eafa6d6011d3a70f1a5b92f0', '', '0000-00-00 00:00:00', 'H.R.', 'Nike', '777-777-7777'),
(3, 3, 'jeremy@connectedadvantage.com', 'd41e98d1eafa6d6011d3a70f1a5b92f0', '', '0000-00-00 00:00:00', 'Jeremy', 'Stover', '415-935-1491'),
(4, 4, 'client@nike.com', 'd41e98d1eafa6d6011d3a70f1a5b92f0', '', '0000-00-00 00:00:00', 'Mrs.', 'Client', '555-555-5555'),
(5, 5, 'ellen@thecoaches.com', 'd41e98d1eafa6d6011d3a70f1a5b92f0', '', '0000-00-00 00:00:00', 'Ellen', 'Eva', '415-951-9999');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `coachs` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`documentTemplate_id`) REFERENCES `documentTemplates` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `link_organizations_coachs`
--
ALTER TABLE `link_organizations_coachs`
  ADD CONSTRAINT `link_organizations_coachs_ibfk_2` FOREIGN KEY (`coach_id`) REFERENCES `coachs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `link_organizations_coachs_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `link_organizations_documentTemplates`
--
ALTER TABLE `link_organizations_documentTemplates`
  ADD CONSTRAINT `link_organizations_documentTemplates_ibfk_2` FOREIGN KEY (`documentTemplate_id`) REFERENCES `documentTemplates` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `link_organizations_documentTemplates_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`coach_id`) REFERENCES `coachs` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
