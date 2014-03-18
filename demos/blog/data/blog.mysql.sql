-- MySQL dump 10.13  Distrib 5.5.19, for Win64 (x86)
--
-- Host: localhost    Database: blog
-- ------------------------------------------------------
-- Server version	5.5.19-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES gbk */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `blog`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `blog` /*!40100 DEFAULT CHARACTER SET gbk */;

USE `blog`;

--
-- Table structure for table `bjhaze_blog`
--

DROP TABLE IF EXISTS `bjhaze_blog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = gbk */;
CREATE TABLE `bjhaze_blog` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) CHARACTER SET gbk NOT NULL,
  `category_id` tinyint(3) unsigned NOT NULL,
  `content` text CHARACTER SET gbk NOT NULL,
  `addtime` datetime NOT NULL,
  `intro` varchar(255) CHARACTER SET gbk NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=gbk;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bjhaze_blog`
--

LOCK TABLES `bjhaze_blog` WRITE;
/*!40000 ALTER TABLE `bjhaze_blog` DISABLE KEYS */;
INSERT INTO `bjhaze_blog` VALUES (1,'BJHaze Wiki',1,'https://code.csdn.net/vn700/bjhaze','2014-03-15 14:26:03','https://code.csdn.net/vn700/bjhaze'),(2,'应用配置(Application)',1,'https://code.csdn.net/vn700/bjhaze/wikis/%E5%BA%94%E7%94%A8%E9%85%8D%E7%BD%AE%28Application%29-','2014-03-15 14:52:11','https://code.csdn.net/vn700/bjhaze/wikis/%E5%BA%94%E7%94%A8%E9%8');
/*!40000 ALTER TABLE `bjhaze_blog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bjhaze_category`
--

DROP TABLE IF EXISTS `bjhaze_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = gbk */;
CREATE TABLE `bjhaze_category` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` char(32) CHARACTER SET gbk NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=gbk;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bjhaze_category`
--

LOCK TABLES `bjhaze_category` WRITE;
/*!40000 ALTER TABLE `bjhaze_category` DISABLE KEYS */;
INSERT INTO `bjhaze_category` VALUES (1,'BJHaze');
/*!40000 ALTER TABLE `bjhaze_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bjhaze_comment`
--

DROP TABLE IF EXISTS `bjhaze_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = gbk */;
CREATE TABLE `bjhaze_comment` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` smallint(5) unsigned NOT NULL,
  `content` varchar(255) CHARACTER SET gbk NOT NULL,
  `addtime` datetime NOT NULL,
  `user_ip` char(32) CHARACTER SET gbk NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=gbk;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bjhaze_comment`
--

LOCK TABLES `bjhaze_comment` WRITE;
/*!40000 ALTER TABLE `bjhaze_comment` DISABLE KEYS */;
INSERT INTO `bjhaze_comment` VALUES (1,1,'wiki is here : https://code.csdn.net/vn700/bjhaze/wikis','2014-03-15 14:26:45','127.0.0.1');
/*!40000 ALTER TABLE `bjhaze_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bjhaze_user`
--

DROP TABLE IF EXISTS `bjhaze_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = gbk */;
CREATE TABLE `bjhaze_user` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(16) CHARACTER SET gbk NOT NULL,
  `password` char(32) CHARACTER SET gbk NOT NULL,
  `last_login_time` datetime NOT NULL,
  `last_login_ip` char(16) CHARACTER SET gbk NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=gbk;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bjhaze_user`
--

LOCK TABLES `bjhaze_user` WRITE;
/*!40000 ALTER TABLE `bjhaze_user` DISABLE KEYS */;
INSERT INTO `bjhaze_user` VALUES (1,'admin','admin','2014-03-15 14:51:58','127.0.0.1');
/*!40000 ALTER TABLE `bjhaze_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-03-15 14:52:38
