-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Mar 20 Septembre 2011 à 11:30
-- Version du serveur: 5.1.49
-- Version de PHP: 5.3.3-1ubuntu9.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `test`
--

-- --------------------------------------------------------

--
-- Structure de la table `amenities`
--

CREATE TABLE IF NOT EXISTS `amenities` (
  `amenity_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`amenity_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `amenities`
--

INSERT INTO `amenities` (`amenity_id`, `type`) VALUES
(1, 'Test #1'),
(2, 'Test #2'),
(3, 'Test #3');

-- --------------------------------------------------------

--
-- Structure de la table `authors`
--

CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_author_id` int(11) DEFAULT NULL,
  `name` varchar(25) NOT NULL DEFAULT 'default_name',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `some_date` date DEFAULT NULL,
  `some_time` time DEFAULT NULL,
  `some_text` text,
  `some_enum` enum('a','b','c') DEFAULT NULL,
  `encrypted_password` varchar(50) DEFAULT NULL,
  `mixedCaseField` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `authors`
--

INSERT INTO `authors` (`author_id`, `parent_author_id`, `name`, `updated_at`, `created_at`, `some_date`, `some_time`, `some_text`, `some_enum`, `encrypted_password`, `mixedCaseField`) VALUES
(1, 3, 'Tito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 2, 'George W. Bush', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, 'Bill Clinton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 2, 'Uncle Bob', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `awesome_people`
--

CREATE TABLE IF NOT EXISTS `awesome_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) DEFAULT NULL,
  `is_awesome` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `awesome_people`
--

INSERT INTO `awesome_people` (`id`, `author_id`, `is_awesome`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1);

-- --------------------------------------------------------

--
-- Structure de la table `books`
--

CREATE TABLE IF NOT EXISTS `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `Author_Id` int(11) DEFAULT NULL,
  `secondary_author_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `numeric_test` varchar(10) DEFAULT '0',
  `special` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`book_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `books`
--

INSERT INTO `books` (`book_id`, `Author_Id`, `secondary_author_id`, `name`, `numeric_test`, `special`) VALUES
(1, 1, 2, 'New book name', '0', '0.00'),
(2, 2, 2, 'Another Book', '0', '0.00');

-- --------------------------------------------------------

--
-- Structure de la table `employees`
--

CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `nick_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `nick_name`) VALUES
(1, 'michio', 'kaku', 'kakz'),
(2, 'jacques', 'fuentes', 'jax'),
(3, 'kien', 'la', 'kla');

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `title` varchar(60) NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Contenu de la table `events`
--

INSERT INTO `events` (`id`, `venue_id`, `host_id`, `title`, `description`, `type`) VALUES
(1, 1, 1, 'Monday Night Music Club feat. The Shivers', '', 'Music'),
(2, 2, 2, 'Yeah Yeah Yeahs', '', 'Music'),
(3, 2, 3, 'Love Overboard', '', 'Music'),
(5, 6, 4, '1320 Records Presents A \\"Live PA Set\\" By STS9 with', '', 'Music'),
(6, 500, 4, 'Kla likes to dance to YMCA', '', 'Music'),
(7, 9, 4, 'Blah', '', 'Blah');

-- --------------------------------------------------------

--
-- Structure de la table `hosts`
--

CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `hosts`
--

INSERT INTO `hosts` (`id`, `name`) VALUES
(1, 'David Letterman'),
(2, 'Billy Crystal'),
(3, 'Jon Stewart'),
(4, 'Funny Guy');

-- --------------------------------------------------------

--
-- Structure de la table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `newsletters`
--

INSERT INTO `newsletters` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Structure de la table `nom_de_table`
--

CREATE TABLE IF NOT EXISTS `nom_de_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `confirmation_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `nom_de_table`
--


-- --------------------------------------------------------

--
-- Structure de la table `nom_de_table_2`
--

CREATE TABLE IF NOT EXISTS `nom_de_table_2` (
  `id` int(11) NOT NULL,
  `x` varchar(50) NOT NULL,
  UNIQUE KEY `x` (`x`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `nom_de_table_2`
--


-- --------------------------------------------------------

--
-- Structure de la table `positions`
--

CREATE TABLE IF NOT EXISTS `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `active` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `positions`
--

INSERT INTO `positions` (`id`, `employee_id`, `title`, `active`) VALUES
(3, 1, 'physicist', 0),
(2, 2, 'programmer', 1),
(1, 3, 'programmer', 1);

-- --------------------------------------------------------

--
-- Structure de la table `property`
--

CREATE TABLE IF NOT EXISTS `property` (
  `property_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`property_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28842 ;

--
-- Contenu de la table `property`
--

INSERT INTO `property` (`property_id`) VALUES
(28840),
(28841);

-- --------------------------------------------------------

--
-- Structure de la table `property_amenities`
--

CREATE TABLE IF NOT EXISTS `property_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amenity_id` int(11) NOT NULL DEFAULT '0',
  `property_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=257121 ;

--
-- Contenu de la table `property_amenities`
--

INSERT INTO `property_amenities` (`id`, `amenity_id`, `property_id`) VALUES
(257117, 1, 28840),
(257118, 2, 28840),
(257119, 2, 28841),
(257120, 3, 28841);

-- --------------------------------------------------------

--
-- Structure de la table `rm-bldg`
--

CREATE TABLE IF NOT EXISTS `rm-bldg` (
  `rm-id` int(11) NOT NULL,
  `rm-name` varchar(10) NOT NULL,
  `space out` varchar(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `rm-bldg`
--

INSERT INTO `rm-bldg` (`rm-id`, `rm-name`, `space out`) VALUES
(1, 'name', 'x');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Structure de la table `user_newsletters`
--

CREATE TABLE IF NOT EXISTS `user_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `newsletter_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `user_newsletters`
--

INSERT INTO `user_newsletters` (`id`, `user_id`, `newsletter_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `venues`
--

CREATE TABLE IF NOT EXISTS `venues` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `name` (`name`,`address`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Contenu de la table `venues`
--

INSERT INTO `venues` (`Id`, `name`, `city`, `state`, `address`, `phone`) VALUES
(1, 'Blender Theater at Gramercy', 'New York', 'NY', '127 East 23rd Street', '2127776800'),
(2, 'Warner Theatre', 'Washington', 'DC', '1299 Pennsylvania Ave NW', '2027834000'),
(6, 'The Note - West Chester', 'West Chester', 'PA', '142 E. Market St.', '0000000000'),
(7, 'The National', 'Richmond', 'VA', '708 East Broad Street', '1112223333'),
(8, 'Hampton Coliseum', 'Hampton', 'VA', '1000 Coliseum Dr', '2223334444'),
(9, 'YMCA', 'Washington', 'DC', '1234 YMCA Way', '2222222222');
