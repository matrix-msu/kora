-- MySQL dump 10.13  Distrib 5.7.20, for solaris11 (x86_64)
--
-- Host: localhost    Database: kora3
-- ------------------------------------------------------
-- Server version	5.7.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `kora3_associations`
--

DROP TABLE IF EXISTS `kora3_associations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_associations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dataForm` int(10) unsigned NOT NULL,
  `assocForm` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `associations_dataform_foreign` (`dataForm`),
  KEY `associations_assocform_foreign` (`assocForm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_associations`
--

LOCK TABLES `kora3_associations` WRITE;
/*!40000 ALTER TABLE `kora3_associations` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_associations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_associator_fields`
--

DROP TABLE IF EXISTS `kora3_associator_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_associator_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `associator_fields_rid_foreign` (`rid`),
  KEY `associator_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_associator_fields`
--

LOCK TABLES `kora3_associator_fields` WRITE;
/*!40000 ALTER TABLE `kora3_associator_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_associator_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_associator_support`
--

DROP TABLE IF EXISTS `kora3_associator_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_associator_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `record` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_associator_support`
--

LOCK TABLES `kora3_associator_support` WRITE;
/*!40000 ALTER TABLE `kora3_associator_support` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_associator_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_backup_overall_progress`
--

DROP TABLE IF EXISTS `kora3_backup_overall_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_backup_overall_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_backup_overall_progress`
--

LOCK TABLES `kora3_backup_overall_progress` WRITE;
/*!40000 ALTER TABLE `kora3_backup_overall_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_backup_overall_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_backup_partial_progress`
--

DROP TABLE IF EXISTS `kora3_backup_partial_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_backup_partial_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `backup_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_partial_progress_backup_id_foreign` (`backup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_backup_partial_progress`
--

LOCK TABLES `kora3_backup_partial_progress` WRITE;
/*!40000 ALTER TABLE `kora3_backup_partial_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_backup_partial_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_backup_support`
--

DROP TABLE IF EXISTS `kora3_backup_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_backup_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `hasRun` datetime DEFAULT NULL,
  `accessed` int(11) NOT NULL,
  `view` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_support_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_backup_support`
--

LOCK TABLES `kora3_backup_support` WRITE;
/*!40000 ALTER TABLE `kora3_backup_support` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_backup_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_combo_list_fields`
--

DROP TABLE IF EXISTS `kora3_combo_list_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_combo_list_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `combo_list_fields_rid_foreign` (`rid`),
  KEY `combo_list_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_combo_list_fields`
--

LOCK TABLES `kora3_combo_list_fields` WRITE;
/*!40000 ALTER TABLE `kora3_combo_list_fields` DISABLE KEYS */;
INSERT INTO `kora3_combo_list_fields` VALUES (1,38,3,14,'2018-05-03 15:48:00','2018-05-03 15:48:00');
/*!40000 ALTER TABLE `kora3_combo_list_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_combo_support`
--

DROP TABLE IF EXISTS `kora3_combo_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_combo_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `list_index` int(10) unsigned NOT NULL,
  `field_num` int(10) unsigned NOT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci,
  `number` decimal(65,30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search_sup` (`data`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_combo_support`
--

LOCK TABLES `kora3_combo_support` WRITE;
/*!40000 ALTER TABLE `kora3_combo_support` DISABLE KEYS */;
INSERT INTO `kora3_combo_support` VALUES (1,3,38,14,0,1,'Jeremy Dunn',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(2,3,38,14,0,2,'Farmer',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(3,3,38,14,1,1,'Jim',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(4,3,38,14,1,2,'Ranger',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(5,3,38,14,2,1,'asdf',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(6,3,38,14,2,2,'Ranger[!]Dad',NULL,'2018-05-03 15:48:00','2018-05-03 15:48:00');
/*!40000 ALTER TABLE `kora3_combo_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_dashboard_blocks`
--

DROP TABLE IF EXISTS `kora3_dashboard_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_dashboard_blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sec_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `options` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dashboard_blocks_sec_id_foreign` (`sec_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_dashboard_blocks`
--

LOCK TABLES `kora3_dashboard_blocks` WRITE;
/*!40000 ALTER TABLE `kora3_dashboard_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_dashboard_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_dashboard_sections`
--

DROP TABLE IF EXISTS `kora3_dashboard_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_dashboard_sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dashboard_sections_uid_foreign` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_dashboard_sections`
--

LOCK TABLES `kora3_dashboard_sections` WRITE;
/*!40000 ALTER TABLE `kora3_dashboard_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_dashboard_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_date_fields`
--

DROP TABLE IF EXISTS `kora3_date_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_date_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `circa` tinyint(1) NOT NULL,
  `month` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `era` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_object` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date_fields_month_index` (`month`),
  KEY `date_fields_day_index` (`day`),
  KEY `date_fields_year_index` (`year`),
  KEY `date_fields_rid_foreign` (`rid`),
  KEY `date_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_date_fields`
--

LOCK TABLES `kora3_date_fields` WRITE;
/*!40000 ALTER TABLE `kora3_date_fields` DISABLE KEYS */;
INSERT INTO `kora3_date_fields` VALUES (1,38,3,15,0,2,1,1901,'CE','1901-02-01','2018-05-03 15:48:00','2018-05-03 15:48:00');
/*!40000 ALTER TABLE `kora3_date_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_documents_fields`
--

DROP TABLE IF EXISTS `kora3_documents_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_documents_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `documents` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_fields_rid_foreign` (`rid`),
  KEY `documents_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_doc` (`documents`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_documents_fields`
--

LOCK TABLES `kora3_documents_fields` WRITE;
/*!40000 ALTER TABLE `kora3_documents_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_documents_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_download_trackers`
--

DROP TABLE IF EXISTS `kora3_download_trackers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_download_trackers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_download_trackers`
--

LOCK TABLES `kora3_download_trackers` WRITE;
/*!40000 ALTER TABLE `kora3_download_trackers` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_download_trackers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_exodus_overall_progress`
--

DROP TABLE IF EXISTS `kora3_exodus_overall_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_exodus_overall_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_exodus_overall_progress`
--

LOCK TABLES `kora3_exodus_overall_progress` WRITE;
/*!40000 ALTER TABLE `kora3_exodus_overall_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_exodus_overall_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_exodus_partial_progress`
--

DROP TABLE IF EXISTS `kora3_exodus_partial_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_exodus_partial_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `exodus_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exodus_partial_progress_exodus_id_foreign` (`exodus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_exodus_partial_progress`
--

LOCK TABLES `kora3_exodus_partial_progress` WRITE;
/*!40000 ALTER TABLE `kora3_exodus_partial_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_exodus_partial_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_failed_jobs`
--

DROP TABLE IF EXISTS `kora3_failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_failed_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_failed_jobs`
--

LOCK TABLES `kora3_failed_jobs` WRITE;
/*!40000 ALTER TABLE `kora3_failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_fields`
--

DROP TABLE IF EXISTS `kora3_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_fields` (
  `flid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` tinyint(1) NOT NULL,
  `searchable` tinyint(1) NOT NULL,
  `advsearch` tinyint(1) NOT NULL,
  `extsearch` tinyint(1) NOT NULL,
  `viewable` tinyint(1) NOT NULL,
  `viewresults` tinyint(1) NOT NULL,
  `extview` tinyint(1) NOT NULL,
  `default` text COLLATE utf8_unicode_ci,
  `options` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`flid`),
  UNIQUE KEY `fields_slug_unique` (`slug`),
  KEY `fields_pid_fid_foreign` (`pid`,`fid`),
  KEY `fields_page_id_foreign` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_fields`
--

LOCK TABLES `kora3_fields` WRITE;
/*!40000 ALTER TABLE `kora3_fields` DISABLE KEYS */;
INSERT INTO `kora3_fields` VALUES (1,2,3,3,0,'Text','Text field','newField','This is the new field',0,1,0,0,1,1,0,'','[!Regex!][!Regex!][!MultiLine!]1[!MultiLine!]','2018-05-02 14:56:48','2018-05-02 20:33:27'),(17,2,3,3,8,'Documents','Document','document','This is the document',0,1,0,0,1,1,0,'','[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]','2018-05-02 20:41:59','2018-05-02 20:41:59'),(2,2,3,3,1,'Geolocator','Geolocator','geolocator','geolocator',0,1,0,0,1,1,0,'[Desc]My House[Desc][LatLon]42.749564519733,-84.487606937304[LatLon][UTM]16T:705616.00798845,4736066.0637048[UTM][Address]220 E Pointe Ln, East Lansing, MI. 48823[Address]','[!Map!]Yes[!Map!][!DataView!]LatLon[!DataView!]','2018-05-02 18:07:57','2018-05-02 18:22:58'),(3,8,5,5,0,'List','Section','Section_137_806','Specifies type of record and controls where content displays on website.',0,1,0,1,1,1,1,'','[!Options!]About-Overview[!]Sources[!]Contributors[!]Copyright[!]Guidebook[!]Courts[!Options!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(4,8,5,5,1,'Text','Order','Order_137_806','Number determining the sorting sequence for similar records display. 1 displays first; 2 displays second, etc.\r\n\r\nFor Mali, use for \"Highlight\" records on the Home page.',0,1,0,1,1,1,1,'','[!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(5,8,5,5,2,'Text','Title','Title_137_806','Label for the page or heading for Highlight record.',0,1,0,1,1,1,1,'','[!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(6,8,5,5,3,'Text','Description','Description_137_806','Text explaining the purpose of the page or contextualizing a highlighted material.\r\n\r\nIf pasting text into this field follow UTF-8 instructions',0,1,0,1,1,1,1,'','[!Regex!][!Regex!][!MultiLine!]1[!MultiLine!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(7,8,5,5,4,'Date','Date','Date_137_806','Date\r\n\r\n',0,1,0,1,1,1,1,'[M]0[M][D]0[D][Y]0[Y]','[!Circa!]No[!Circa!][!Start!]1920[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(8,8,5,5,5,'Gallery','Thumbnail','Thumbnail_137_806','For Liberated Africans Image for About Page.',0,1,0,1,1,1,1,'','[!FieldSize!]0[!FieldSize!][!ThumbSmall!]125x125[!ThumbSmall!][!ThumbLarge!]250x250[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]image/jpeg[!]image/gif[!]image/png[!]image/bmp[!FileTypes!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(9,8,5,5,6,'List','Display','Display_137_806','Tick \"True\" to display record on the website.',0,0,0,0,1,1,1,'True','[!Options!]True[!]False[!Options!]','2018-05-02 18:09:15','2018-05-02 18:09:15'),(10,2,3,3,2,'Rich Text','Rich Text','richText','richtext',0,1,0,0,1,1,0,'Hello','','2018-05-02 18:26:42','2018-05-02 18:26:42'),(11,2,3,3,3,'List','List','list','This is the list field',0,1,0,0,1,1,0,'Cheese','[!Options!]Cheese[!]Fish[!]Just meat in general[!Options!]','2018-05-02 19:00:16','2018-05-02 19:00:16'),(14,2,3,3,5,'Combo List','Combo List','combo','This is the Combo List',0,1,0,0,1,1,0,'','[!Field1!][Type]Text[Type][Name]Name[Name][Options][!Regex!][!Regex!][!MultiLine!][!MultiLine!][Options][!Field1!][!Field2!][Type]Multi-Select List[Type][Name]Occupation[Name][Options][!Options!]Farmer[!]Ranger[!]Dad[!Options!][Options][!Field2!]','2018-05-02 19:55:23','2018-05-03 15:38:46'),(13,2,3,3,4,'Multi-Select List','Multi Select List','multiselect','this is the Multi Select List',0,1,0,0,1,1,0,'','[!Options!][!Options!]','2018-05-02 19:37:47','2018-05-02 19:37:55'),(15,2,3,3,6,'Date','Date Field','Thisisdate','This is the Date Field',0,1,0,0,1,1,0,'[M][M][D][D][Y][Y]','[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]','2018-05-02 20:11:04','2018-05-02 20:11:04'),(16,2,3,3,7,'Schedule','Schedule','schedule','This is the Schedule Field',0,1,0,0,1,1,0,'New Years Day: 01/01/2018 - 01/01/2018[!]Martin Luther King Day: 01/15/2018 - 01/15/2018[!]Presidents\' Day: 02/19/2018 - 02/19/2018[!]Mother\'s Day: 05/13/2018 - 05/13/2018[!]Father\'s Day: 06/17/2018 - 06/17/2018[!]Independence Day: 07/04/2018 - 07/04/2018[!]Labor Day: 09/03/2018 - 09/03/2018[!]Columbus Day: 10/08/2018 - 10/08/2018[!]Veterans Day: 11/12/2018 - 11/12/2018[!]Thanksgiving: 11/22/2018 - 11/22/2018[!]Christmas: 12/25/2018 - 12/25/2018','[!Start!]1900[!Start!][!End!]2020[!End!][!Calendar!]No[!Calendar!]','2018-05-02 20:14:21','2018-05-02 21:30:10'),(19,2,3,3,9,'Gallery','Gallery','gallery','This is the Gallery',0,1,0,0,1,1,0,'','[!FieldSize!]0[!FieldSize!][!ThumbSmall!]150x150[!ThumbSmall!][!ThumbLarge!]300x300[!ThumbLarge!]\n        [!MaxFiles!]0[!MaxFiles!][!FileTypes!]image/jpeg[!]image/gif[!]image/png[!]image/bmp[!FileTypes!]','2018-05-02 20:46:29','2018-05-02 20:46:29'),(20,2,3,3,10,'Playlist','Playlist','playlist','ThisIstheplaylist',0,1,0,0,1,1,0,'','[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]audio/mp3[!]audio/wav[!]audio/ogg[!FileTypes!]','2018-05-02 21:10:37','2018-05-02 21:10:37'),(21,2,3,3,11,'Video','Video','thisIstheVideo','This is the Video',0,1,0,0,1,1,0,'','[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]video/mp4[!]video/ogg[!FileTypes!]','2018-05-02 21:11:22','2018-05-02 21:11:22'),(22,2,3,3,12,'3D-Model','3D Model','model','This is the 3D Model',0,1,0,0,1,1,0,'','[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]obj[!]stl[!]image/jpeg[!]image/png[!]application/octet-stream[!FileTypes!][!ModelColor!]#CAA618[!ModelColor!][!BackColorOne!]#ffffff[!BackColorOne!]\n        [!BackColorTwo!]#383840[!BackColorTwo!]','2018-05-02 21:21:53','2018-05-02 21:21:53'),(23,2,3,3,13,'Associator','Associator','associateMe','This is the Associator',0,1,0,0,1,1,0,'','[!SearchForms!][!SearchForms!]','2018-05-02 21:24:15','2018-05-02 21:24:15');
/*!40000 ALTER TABLE `kora3_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_form_custom`
--

DROP TABLE IF EXISTS `kora3_form_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_form_custom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_custom_uid_foreign` (`uid`),
  KEY `form_custom_pid_foreign` (`pid`),
  KEY `form_custom_fid_foreign` (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_form_custom`
--

LOCK TABLES `kora3_form_custom` WRITE;
/*!40000 ALTER TABLE `kora3_form_custom` DISABLE KEYS */;
INSERT INTO `kora3_form_custom` VALUES (1,5,8,1,0,'2018-04-30 18:34:34','2018-04-30 18:34:34'),(2,9,8,1,0,'2018-04-30 18:34:34','2018-04-30 18:34:34'),(3,1,8,1,0,'2018-04-30 18:34:34','2018-04-30 18:34:34'),(4,2,8,1,0,'2018-04-30 18:34:34','2018-04-30 18:34:34'),(5,3,8,1,0,'2018-04-30 18:34:34','2018-04-30 18:34:34'),(6,12,8,1,0,'2018-04-30 18:39:39','2018-04-30 18:39:39'),(7,13,8,1,0,'2018-04-30 18:39:39','2018-04-30 18:39:39'),(8,4,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(9,1,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(10,2,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(11,3,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(12,5,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(13,9,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(14,12,3,2,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(15,6,8,1,0,'2018-05-02 00:22:38','2018-05-02 00:22:38'),(16,6,3,2,0,'2018-05-02 00:22:38','2018-05-02 00:22:38'),(17,2,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(18,1,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(19,3,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(20,5,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(21,6,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(22,9,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(23,12,2,3,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(24,3,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(25,5,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(26,9,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(27,1,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(28,2,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(29,6,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(30,12,8,4,1,'2018-05-02 17:31:33','2018-05-02 17:31:33'),(31,3,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(32,5,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(33,9,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(34,1,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(35,2,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(36,6,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(37,12,8,5,2,'2018-05-02 18:09:15','2018-05-02 18:09:15');
/*!40000 ALTER TABLE `kora3_form_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_form_group_user`
--

DROP TABLE IF EXISTS `kora3_form_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_form_group_user` (
  `form_group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  KEY `form_group_user_form_group_id_index` (`form_group_id`),
  KEY `form_group_user_user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_form_group_user`
--

LOCK TABLES `kora3_form_group_user` WRITE;
/*!40000 ALTER TABLE `kora3_form_group_user` DISABLE KEYS */;
INSERT INTO `kora3_form_group_user` VALUES (1,5),(1,9),(1,12),(1,13),(3,4),(5,2),(7,3),(7,5),(7,9),(9,3),(9,5),(9,9);
/*!40000 ALTER TABLE `kora3_form_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_form_groups`
--

DROP TABLE IF EXISTS `kora3_form_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_form_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `create` tinyint(1) NOT NULL,
  `edit` tinyint(1) NOT NULL,
  `delete` tinyint(1) NOT NULL,
  `ingest` tinyint(1) NOT NULL,
  `modify` tinyint(1) NOT NULL,
  `destroy` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_groups_fid_foreign` (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_form_groups`
--

LOCK TABLES `kora3_form_groups` WRITE;
/*!40000 ALTER TABLE `kora3_form_groups` DISABLE KEYS */;
INSERT INTO `kora3_form_groups` VALUES (10,'Sections Default Group',5,0,0,0,0,0,0,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(3,'test Admin Group',2,1,1,1,1,1,1,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(4,'test Default Group',2,0,0,0,0,0,0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(5,'This is the Form Name Admin Group',3,1,1,1,1,1,1,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(6,'This is the Form Name Default Group',3,0,0,0,0,0,0,'2018-05-02 14:56:05','2018-05-02 14:56:05'),(9,'Sections Admin Group',5,1,1,1,1,1,1,'2018-05-02 18:09:15','2018-05-02 18:09:15');
/*!40000 ALTER TABLE `kora3_form_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_forms`
--

DROP TABLE IF EXISTS `kora3_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_forms` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `adminGID` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `preset` tinyint(1) NOT NULL,
  `public_metadata` tinyint(1) NOT NULL,
  `lod_resource` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`fid`),
  UNIQUE KEY `forms_slug_unique` (`slug`),
  KEY `forms_pid_foreign` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_forms`
--

LOCK TABLES `kora3_forms` WRITE;
/*!40000 ALTER TABLE `kora3_forms` DISABLE KEYS */;
INSERT INTO `kora3_forms` VALUES (5,8,9,'Sections','Sections1','Metadata fields used to display materials on the public site',0,0,'','2018-05-02 18:09:15','2018-05-02 18:09:15'),(2,3,3,'test','test','asdf',0,0,'','2018-05-01 14:33:37','2018-05-01 14:33:37'),(3,2,5,'This is the Form Name','thisistheuniqueId','This is the description',0,0,'','2018-05-02 14:56:05','2018-05-02 14:56:05');
/*!40000 ALTER TABLE `kora3_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_gallery_fields`
--

DROP TABLE IF EXISTS `kora3_gallery_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_gallery_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `images` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_fields_rid_foreign` (`rid`),
  KEY `gallery_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_gal` (`images`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_gallery_fields`
--

LOCK TABLES `kora3_gallery_fields` WRITE;
/*!40000 ALTER TABLE `kora3_gallery_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_gallery_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_generated_list_fields`
--

DROP TABLE IF EXISTS `kora3_generated_list_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_generated_list_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `options` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `generated_list_fields_rid_foreign` (`rid`),
  KEY `generated_list_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_gen` (`options`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_generated_list_fields`
--

LOCK TABLES `kora3_generated_list_fields` WRITE;
/*!40000 ALTER TABLE `kora3_generated_list_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_generated_list_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_geolocator_fields`
--

DROP TABLE IF EXISTS `kora3_geolocator_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_geolocator_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `geolocator_fields_rid_foreign` (`rid`),
  KEY `geolocator_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_geolocator_fields`
--

LOCK TABLES `kora3_geolocator_fields` WRITE;
/*!40000 ALTER TABLE `kora3_geolocator_fields` DISABLE KEYS */;
INSERT INTO `kora3_geolocator_fields` VALUES (1,34,3,2,'2018-05-02 18:22:21','2018-05-02 18:22:21'),(2,35,3,2,'2018-05-02 19:01:28','2018-05-02 19:01:28'),(3,36,3,2,'2018-05-02 19:49:31','2018-05-02 19:49:31'),(4,37,3,2,'2018-05-02 19:49:31','2018-05-02 19:49:31'),(5,38,3,2,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(6,39,3,2,'2018-05-03 15:51:32','2018-05-03 15:51:32');
/*!40000 ALTER TABLE `kora3_geolocator_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_geolocator_support`
--

DROP TABLE IF EXISTS `kora3_geolocator_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_geolocator_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` double(10,7) NOT NULL,
  `lon` double(10,7) NOT NULL,
  `zone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `easting` double(10,3) NOT NULL,
  `northing` double(10,3) NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search_geo_desc` (`desc`),
  FULLTEXT KEY `search_geo_address` (`address`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_geolocator_support`
--

LOCK TABLES `kora3_geolocator_support` WRITE;
/*!40000 ALTER TABLE `kora3_geolocator_support` DISABLE KEYS */;
INSERT INTO `kora3_geolocator_support` VALUES (1,3,34,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-02 18:22:21','2018-05-02 18:22:21'),(2,3,35,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-02 19:01:28','2018-05-02 19:01:28'),(3,3,36,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-02 19:49:31','2018-05-02 19:49:31'),(4,3,37,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-02 19:49:31','2018-05-02 19:49:31'),(5,3,38,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-03 15:48:00','2018-05-03 15:48:00'),(6,3,39,2,'My House',42.7495645,-84.4876069,'16T',705616.008,4736066.064,'220 E Pointe Ln, East Lansing, MI. 48823','2018-05-03 15:51:32','2018-05-03 15:51:32');
/*!40000 ALTER TABLE `kora3_geolocator_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_global_cache`
--

DROP TABLE IF EXISTS `kora3_global_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_global_cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `html` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `global_cache_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_global_cache`
--

LOCK TABLES `kora3_global_cache` WRITE;
/*!40000 ALTER TABLE `kora3_global_cache` DISABLE KEYS */;
INSERT INTO `kora3_global_cache` VALUES (3,2,'<li><a href=\"http://kora3.matrix.msu.edu/globalSearch?keywords=ethan&method=2&projects%5B%5D=ALL\">ethan</a></li>',NULL,NULL);
/*!40000 ALTER TABLE `kora3_global_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_jobs`
--

DROP TABLE IF EXISTS `kora3_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_jobs`
--

LOCK TABLES `kora3_jobs` WRITE;
/*!40000 ALTER TABLE `kora3_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_list_fields`
--

DROP TABLE IF EXISTS `kora3_list_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_list_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `option` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `list_fields_rid_foreign` (`rid`),
  KEY `list_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search` (`option`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_list_fields`
--

LOCK TABLES `kora3_list_fields` WRITE;
/*!40000 ALTER TABLE `kora3_list_fields` DISABLE KEYS */;
INSERT INTO `kora3_list_fields` VALUES (1,35,3,11,'Cheese','2018-05-02 19:01:28','2018-05-02 19:01:28'),(2,36,3,11,'Cheese','2018-05-02 19:49:31','2018-05-02 19:49:31'),(3,37,3,11,'Cheese','2018-05-02 19:49:31','2018-05-02 19:49:31'),(4,38,3,11,'Cheese','2018-05-03 15:48:00','2018-05-03 15:48:00'),(5,39,3,11,'Cheese','2018-05-03 15:51:32','2018-05-03 15:51:32');
/*!40000 ALTER TABLE `kora3_list_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_metadatas`
--

DROP TABLE IF EXISTS `kora3_metadatas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_metadatas` (
  `flid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `primary` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `metadatas_flid_foreign` (`flid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_metadatas`
--

LOCK TABLES `kora3_metadatas` WRITE;
/*!40000 ALTER TABLE `kora3_metadatas` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_metadatas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_migrations`
--

DROP TABLE IF EXISTS `kora3_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_migrations`
--

LOCK TABLES `kora3_migrations` WRITE;
/*!40000 ALTER TABLE `kora3_migrations` DISABLE KEYS */;
INSERT INTO `kora3_migrations` VALUES (1,'2015_00_00_000000_create_combo_list_fields_table',1),(2,'2015_00_00_000001_create_datefields_table',1),(3,'2015_00_00_000002_create_documentsfields_table',1),(4,'2015_00_00_000003_create_galleryfields_table',1),(5,'2015_00_00_000004_create_generatedlistfields_table',1),(6,'2015_00_00_000005_create_geolocatorfields_table',1),(7,'2015_00_00_000006_create_listfields_table',1),(8,'2015_00_00_000007_create_modelfields_table',1),(9,'2015_00_00_000008_create_multiselectlistfields_table',1),(10,'2015_00_00_000009_create_numberfields_table',1),(11,'2015_00_00_000010_create_playlistfields_table',1),(12,'2015_00_00_000011_create_richtextfields_table',1),(13,'2015_00_00_000012_create_schedulefields_table',1),(14,'2015_00_00_000013_create_textfields_table',1),(15,'2015_00_00_000014_create_videofields_table',1),(16,'2015_04_03_151510_CreateProjectsTable',1),(17,'2015_04_03_151648_create_password_resets_table',1),(18,'2015_04_03_152745_CreateFormsTable',1),(19,'2015_05_01_140126_CreateFieldsTable',1),(20,'2015_05_18_173954_create_records_table',1),(21,'2015_06_17_134524_CreateUsersTable',1),(22,'2015_06_19_152400_CreateTokensTable',1),(23,'2015_07_01_122103_CreateMetadataTable',1),(24,'2015_07_09_171601_create_project_groups_table',1),(25,'2015_07_15_172743_create_form_groups_table',1),(26,'2015_07_23_181833_create_revisions_table',1),(27,'2015_08_21_154839_create_recordpresets_table',1),(28,'2015_08_21_194838_create_associatorfields_table',1),(29,'2015_09_14_201213_create_optionpresets_table',1),(30,'2015_10_07_175909_create_associations_table',1),(31,'2015_11_23_193021_create_versions_table',1),(32,'2016_05_10_205219_backup_support',1),(33,'2016_05_20_204314_create_backup_progress_tables',1),(34,'2016_05_23_204821_create_jobs_table',1),(35,'2016_05_23_204859_create_failed_jobs_table',1),(36,'2016_08_16_180221_create_plugins_table',1),(37,'2016_09_23_162317_create_download_trackers_table',1),(38,'2016_10_27_193957_CreateRestoreProgressTables',1),(39,'2016_12_08_171347_CreateExodusProgressTables',1),(40,'2017_01_12_190618_CreateDashboardTables',1),(41,'2017_03_28_185021_CreatePagesTable',1),(42,'2017_08_10_175125_CreateGlobalCacheTable',1),(43,'2017_08_25_171638_CreateProjectCustomTable',1),(44,'2017_08_30_171426_CreateFormCustomTable',1),(45,'9999_99_99_999999_create_scripts_table',1);
/*!40000 ALTER TABLE `kora3_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_model_fields`
--

DROP TABLE IF EXISTS `kora3_model_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_model_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `model` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `model_fields_rid_foreign` (`rid`),
  KEY `model_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_mdl` (`model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_model_fields`
--

LOCK TABLES `kora3_model_fields` WRITE;
/*!40000 ALTER TABLE `kora3_model_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_model_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_multi_select_list_fields`
--

DROP TABLE IF EXISTS `kora3_multi_select_list_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_multi_select_list_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `options` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `multi_select_list_fields_rid_foreign` (`rid`),
  KEY `multi_select_list_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_msl` (`options`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_multi_select_list_fields`
--

LOCK TABLES `kora3_multi_select_list_fields` WRITE;
/*!40000 ALTER TABLE `kora3_multi_select_list_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_multi_select_list_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_number_fields`
--

DROP TABLE IF EXISTS `kora3_number_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_number_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `number` decimal(65,30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `number_fields_number_index` (`number`),
  KEY `number_fields_rid_foreign` (`rid`),
  KEY `number_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_number_fields`
--

LOCK TABLES `kora3_number_fields` WRITE;
/*!40000 ALTER TABLE `kora3_number_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_number_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_option_presets`
--

DROP TABLE IF EXISTS `kora3_option_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_option_presets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `preset` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `option_presets_pid_foreign` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_option_presets`
--

LOCK TABLES `kora3_option_presets` WRITE;
/*!40000 ALTER TABLE `kora3_option_presets` DISABLE KEYS */;
INSERT INTO `kora3_option_presets` VALUES (1,NULL,'Text','URL_URI','/^(http|ftp|https):\\/\\//',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(2,NULL,'List','Boolean','Yes[!]No',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(3,NULL,'List','Countries','United States[!]United Nations[!]Canada[!]Mexico[!]Afghanistan[!]Albania[!]Algeria[!]American Samoa[!]Andorra[!]Angola[!]Anguilla[!]Antarctica[!]Antigua and Barbuda[!]Argentina[!]Armenia[!]Aruba[!]Australia[!]Austria[!]Azerbaijan[!]Bahamas[!]Bahrain[!]Bangladesh[!]Barbados[!]Belarus[!]Belgium[!]Belize[!]Benin[!]Bermuda[!]Bhutan[!]Bolivia[!]Bosnia and Herzegowina[!]Botswana[!]Bouvet Island[!]Brazil[!]British Indian Ocean Terr.[!]Brunei Darussalam[!]Bulgaria[!]Burkina Faso[!]Burundi[!]Cambodia[!]Cameroon[!]Cape Verde[!]Cayman Islands[!]Central African Republic[!]Chad[!]Chile[!]China[!]Christmas Island[!]Cocos (Keeling) Islands[!]Colombia[!]Comoros[!]Congo[!]Cook Islands[!]Costa Rica[!]Cote d`Ivoire[!]Croatia (Hrvatska)[!]Cuba[!]Cyprus[!]Czech Republic[!]Denmark[!]Djibouti[!]Dominica[!]Dominican Republic[!]East Timor[!]Ecuador[!]Egypt[!]El Salvador[!]Equatorial Guinea[!]Eritrea[!]Estonia[!]Ethiopia[!]Falkland Islands/Malvinas[!]Faroe Islands[!]Fiji[!]Finland[!]France[!]France, Metropolitan[!]French Guiana[!]French Polynesia[!]French Southern Terr.[!]Gabon[!]Gambia[!]Georgia[!]Germany[!]Ghana[!]Gibraltar[!]Greece[!]Greenland[!]Grenada[!]Guadeloupe[!]Guam[!]Guatemala[!]Guinea[!]Guinea-Bissau[!]Guyana[!]Haiti[!]Heard &amp; McDonald Is.[!]Honduras[!]Hong Kong[!]Hungary[!]Iceland[!]India[!]Indonesia[!]Iran[!]Iraq[!]Ireland[!]Israel[!]Italy[!]Jamaica[!]Japan[!]Jordan[!]Kazakhstan[!]Kenya[!]Kiribati[!]Korea, North[!]Korea, South[!]Kuwait[!]Kyrgyzstan[!]Lao People`s Dem. Rep.[!]Latvia[!]Lebanon[!]Lesotho[!]Liberia[!]Libyan Arab Jamahiriya[!]Liechtenstein[!]Lithuania[!]Luxembourg[!]Macau[!]Macedonia[!]Madagascar[!]Malawi[!]Malaysia[!]Maldives[!]Mali[!]Malta[!]Marshall Islands[!]Martinique[!]Mauritania[!]Mauritius[!]Mayotte[!]Micronesia[!]Moldova[!]Monaco[!]Mongolia[!]Montserrat[!]Morocco[!]Mozambique[!]Myanmar[!]Namibia[!]Nauru[!]Nepal[!]Netherlands[!]Netherlands Antilles[!]New Caledonia[!]New Zealand[!]Nicaragua[!]Niger[!]Nigeria[!]Niue[!]Norfolk Island[!]Northern Mariana Is.[!]Norway[!]Oman[!]Pakistan[!]Palau[!]Panama[!]Papua New Guinea[!]Paraguay[!]Peru[!]Philippines[!]Pitcairn[!]Poland[!]Portugal[!]Puerto Rico[!]Qatar[!]Reunion[!]Romania[!]Russian Federation[!]Rwanda[!]Saint Kitts and Nevis[!]Saint Lucia[!]St. Vincent &amp; Grenadines[!]Samoa[!]San Marino[!]Sao Tome &amp; Principe[!]Saudi Arabia[!]Senegal[!]Seychelles[!]Sierra Leone[!]Singapore[!]Slovakia (Slovak Republic)[!]Slovenia[!]Solomon Islands[!]Somalia[!]South Africa[!]S.Georgia &amp; S.Sandwich Is.[!]Spain[!]Sri Lanka[!]St. Helena[!]St. Pierre &amp; Miquelon[!]Sudan[!]Suriname[!]Svalbard &amp; Jan Mayen Is.[!]Swaziland[!]Sweden[!]Switzerland[!]Syrian Arab Republic[!]Taiwan[!]Tajikistan[!]Tanzania[!]Thailand[!]Togo[!]Tokelau[!]Tonga[!]Trinidad and Tobago[!]Tunisia[!]Turkey[!]Turkmenistan[!]Turks &amp; Caicos Islands[!]Tuvalu[!]Uganda[!]Ukraine[!]United Arab Emirates[!]United Kingdom[!]U.S. Minor Outlying Is.[!]Uruguay[!]Uzbekistan[!]Vanuatu[!]Vatican (Holy See)[!]Venezuela[!]Viet Nam[!]Virgin Islands (British)[!]Virgin Islands (U.S.)[!]Wallis &amp; Futuna Is.[!]Western Sahara[!]Yemen[!]Yugoslavia[!]Zaire[!]Zambia[!]Zimbabwe',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(4,NULL,'List','Languages','Abkhaz[!]Achinese[!]Acoli[!]Adangme[!]Adygei[!]Afar[!]Afrihili (Artificial language)[!]Afrikaans[!]Afroasiatic (Other)[!]Akan[!]Akkadian[!]Albanian[!]Aleut[!]Algonquian (Other)[!]Altaic (Other)[!]Amharic[!]Apache languages[!]Arabic[!]Aragonese Spanish[!]Aramaic[!]Arapaho[!]Arawak[!]Armenian[!]Artificial (Other)[!]Assamese[!]Athapascan (Other)[!]Australian languages[!]Austronesian (Other)[!]Avaric[!]Avestan[!]Awadhi[!]Aymara[!]Azerbaijani[!]Bable[!]Balinese[!]Baltic (Other)[!]Baluchi[!]Bambara[!]Bamileke languages[!]Banda[!]Bantu (Other)[!]Basa[!]Bashkir[!]Basque[!]Batak[!]Beja[!]Belarusian[!]Bemba[!]Bengali[!]Berber (Other)[!]Bhojpuri[!]Bihari[!]Bikol[!]Bislama[!]Bosnian[!]Braj[!]Breton[!]Bugis[!]Bulgarian[!]Buriat[!]Burmese[!]Caddo[!]Carib[!]Catalan[!]Caucasian (Other)[!]Cebuano[!]Celtic (Other)[!]Central American Indian (Other)[!]Chagatai[!]Chamic languages[!]Chamorro[!]Chechen[!]Cherokee[!]Cheyenne[!]Chibcha[!]Chinese[!]Chinook jargon[!]Chipewyan[!]Choctaw[!]Church Slavic[!]Chuvash[!]Coptic[!]Cornish[!]Corsican[!]Cree[!]Creek[!]Creoles and Pidgins (Other)[!]Creoles and Pidgins, English-based (Other)[!]Creoles and Pidgins, French-based (Other)[!]Creoles and Pidgins, Portuguese-based (Other)[!]Crimean Tatar[!]Croatian[!]Cushitic (Other)[!]Czech[!]Dakota[!]Danish[!]Dargwa[!]Dayak[!]Delaware[!]Dinka[!]Divehi[!]Dogri[!]Dogrib[!]Dravidian (Other)[!]Duala[!]Dutch[!]Dutch, Middle (ca. 1050-1350)[!]Dyula[!]Dzongkha[!]Edo[!]Efik[!]Egyptian[!]Ekajuk[!]Elamite[!]English[!]English, Middle (1100-1500)[!]English, Old (ca. 450-1100)[!]Esperanto[!]Estonian[!]Ethiopic[!]Ewe[!]Ewondo[!]Fang[!]Fanti[!]Faroese[!]Fijian[!]Finnish[!]Finno-Ugrian (Other)[!]Fon[!]French[!]French, Middle (ca. 1400-1600)[!]French, Old (ca. 842-1400)[!]Frisian[!]Friulian[!]Fula[!]Galician[!]Ganda[!]Gayo[!]Gbaya[!]Georgian[!]German[!]German, Middle High (ca. 1050-1500)[!]German, Old High (ca. 750-1050)[!]Germanic (Other)[!]Gilbertese[!]Gondi[!]Gorontalo[!]Gothic[!]Grebo[!]Greek, Ancient (to 1453)[!]Greek, Modern (1453- )[!]Guarani[!]Gujarati[!]Gwich\'in[!]Gã[!]Haida[!]Haitian French Creole[!]Hausa[!]Hawaiian[!]Hebrew[!]Herero[!]Hiligaynon[!]Himachali[!]Hindi[!]Hiri Motu[!]Hittite[!]Hmong[!]Hungarian[!]Hupa[!]Iban[!]Icelandic[!]Ido[!]Igbo[!]Ijo[!]Iloko[!]Inari Sami[!]Indic (Other)[!]Indo-European (Other)[!]Indonesian[!]Ingush[!]Interlingua (International Auxiliary Language Association)[!]Interlingue[!]Inuktitut[!]Inupiaq[!]Iranian (Other)[!]Irish[!]Irish, Middle (ca. 1100-1550)[!]Irish, Old (to 1100)[!]Iroquoian (Other)[!]Italian[!]Japanese[!]Javanese[!]Judeo-Arabic[!]Judeo-Persian[!]Kabardian[!]Kabyle[!]Kachin[!]Kalmyk[!]Kalâtdlisut[!]Kamba[!]Kannada[!]Kanuri[!]Kara-Kalpak[!]Karen[!]Kashmiri[!]Kawi[!]Kazakh[!]Khasi[!]Khmer[!]Khoisan (Other)[!]Khotanese[!]Kikuyu[!]Kimbundu[!]Kinyarwanda[!]Komi[!]Kongo[!]Konkani[!]Korean[!]Kpelle[!]Kru[!]Kuanyama[!]Kumyk[!]Kurdish[!]Kurukh[!]Kusaie[!]Kutenai[!]Kyrgyz[!]Ladino[!]Lahnda[!]Lamba[!]Lao[!]Latin[!]Latvian[!]Letzeburgesch[!]Lezgian[!]Limburgish[!]Lingala[!]Lithuanian[!]Low German[!]Lozi[!]Luba-Katanga[!]Luba-Lulua[!]Luiseño[!]Lule Sami[!]Lunda[!]Luo (Kenya and Tanzania)[!]Lushai[!]Macedonian[!]Madurese[!]Magahi[!]Maithili[!]Makasar[!]Malagasy[!]Malay[!]Malayalam[!]Maltese[!]Manchu[!]Mandar[!]Mandingo[!]Manipuri[!]Manobo languages[!]Manx[!]Maori[!]Mapuche[!]Marathi[!]Mari[!]Marshallese[!]Marwari[!]Masai[!]Mayan languages[!]Mende[!]Micmac[!]Minangkabau[!]Miscellaneous languages[!]Mohawk[!]Moldavian[!]Mon-Khmer (Other)[!]Mongo-Nkundu[!]Mongolian[!]Mooré[!]Multiple languages[!]Munda (Other)[!]Nahuatl[!]Nauru[!]Navajo[!]Ndebele (South Africa)[!]Ndebele (Zimbabwe)[!]Ndonga[!]Neapolitan Italian[!]Nepali[!]Newari[!]Nias[!]Niger-Kordofanian (Other)[!]Nilo-Saharan (Other)[!]Niuean[!]Nogai[!]North American Indian (Other)[!]Northern Sami[!]Northern Sotho[!]Norwegian[!]Norwegian (Bokmål)[!]Norwegian (Nynorsk)[!]Nubian languages[!]Nyamwezi[!]Nyanja[!]Nyankole[!]Nyoro[!]Nzima[!]Occitan (post-1500)[!]Ojibwa[!]Old Norse[!]Old Persian (ca. 600-400 B.C.)[!]Oriya[!]Oromo[!]Osage[!]Ossetic[!]Otomian languages[!]Pahlavi[!]Palauan[!]Pali[!]Pampanga[!]Pangasinan[!]Panjabi[!]Papiamento[!]Papuan (Other)[!]Persian[!]Philippine (Other)[!]Phoenician[!]Polish[!]Ponape[!]Portuguese[!]Prakrit languages[!]Provençal (to 1500)[!]Pushto[!]Quechua[!]Raeto-Romance[!]Rajasthani[!]Rapanui[!]Rarotongan[!]Romance (Other)[!]Romani[!]Romanian[!]Rundi[!]Russian[!]Salishan languages[!]Samaritan Aramaic[!]Sami[!]Samoan[!]Sandawe[!]Sango (Ubangi Creole)[!]Sanskrit[!]Santali[!]Sardinian[!]Sasak[!]Scots[!]Scottish Gaelic[!]Selkup[!]Semitic (Other)[!]Serbian[!]Serer[!]Shan[!]Shona[!]Sichuan Yi[!]Sidamo[!]Sign languages[!]Siksika[!]Sindhi[!]Sinhalese[!]Sino-Tibetan (Other)[!]Siouan (Other)[!]Skolt Sami[!]Slave[!]Slavic (Other)[!]Slovak[!]Slovenian[!]Sogdian[!]Somali[!]Songhai[!]Soninke[!]Sorbian languages[!]Sotho[!]South American Indian (Other)[!]Southern Sami[!]Spanish[!]Sukuma[!]Sumerian[!]Sundanese[!]Susu[!]Swahili[!]Swazi[!]Swedish[!]Syriac[!]Tagalog[!]Tahitian[!]Tai (Other)[!]Tajik[!]Tamashek[!]Tamil[!]Tatar[!]Telugu[!]Temne[!]Terena[!]Tetum[!]Thai[!]Tibetan[!]Tigrinya[!]Tigré[!]Tiv[!]Tlingit[!]Tok Pisin[!]Tokelauan[!]Tonga (Nyasa)[!]Tongan[!]Truk[!]Tsimshian[!]Tsonga[!]Tswana[!]Tumbuka[!]Tupi languages[!]Turkish[!]Turkish, Ottoman[!]Turkmen[!]Tuvaluan[!]Tuvinian[!]Twi[!]Udmurt[!]Ugaritic[!]Uighur[!]Ukrainian[!]Umbundu[!]Undetermined[!]Urdu[!]Uzbek[!]Vai[!]Venda[!]Vietnamese[!]Volapük[!]Votic[!]Wakashan languages[!]Walamo[!]Walloon[!]Waray[!]Washo[!]Welsh[!]Wolof[!]Xhosa[!]Yakut[!]Yao (Africa)[!]Yapese[!]Yiddish[!]Yoruba[!]Yupik languages[!]Zande[!]Zapotec[!]Zenaga[!]Zhuang[!]Zulu[!]Zuni',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(5,NULL,'List','US States','Alabama[!]Alaska[!]Arizona[!]Arkansas[!]California[!]Colorado[!]Connecticut[!]Delaware[!]District of Columbia[!]Florida[!]Georgia[!]Hawaii[!]Idaho[!]Illinois[!]Indiana[!]Iowa[!]Kansas[!]Kentucky[!]Louisiana[!]Maine[!]Maryland[!]Massachusetts[!]Michigan[!]Minnesota[!]Mississippi[!]Missouri[!]Montana[!]Nebraska[!]Nevada[!]New Hampshire[!]New Jersey[!]New Mexico[!]New York[!]North Carolina[!]North Dakota[!]Ohio[!]Oklahoma[!]Oregon[!]Pennsylvania[!]Rhode Island[!]South Carolina[!]South Dakota[!]Tennessee[!]Texas[!]Utah[!]Vermont[!]Virginia[!]Washington[!]West Virginia[!]Wisconsin[!]Wyoming',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(6,NULL,'Schedule','US Holidays 2018','New Years Day: 01/01/2018 - 01/01/2018[!]Martin Luther King Day: 01/15/2018 - 01/15/2018[!]Presidents\' Day: 02/19/2018 - 02/19/2018[!]Mother\'s Day: 05/13/2018 - 05/13/2018[!]Father\'s Day: 06/17/2018 - 06/17/2018[!]Independence Day: 07/04/2018 - 07/04/2018[!]Labor Day: 09/03/2018 - 09/03/2018[!]Columbus Day: 10/08/2018 - 10/08/2018[!]Veterans Day: 11/12/2018 - 11/12/2018[!]Thanksgiving: 11/22/2018 - 11/22/2018[!]Christmas: 12/25/2018 - 12/25/2018',0,'2018-04-30 17:33:53','2018-04-30 17:33:53'),(7,NULL,'Geolocator','US Capitols','[Desc]Montgomery, Alabama[Desc][LatLon]32.361538,-86.279118[LatLon][UTM]16S:567823.38838923,3580738.9844514[UTM][Address] East 5th Street Montgomery Alabama[Address][!][Desc]Juneau, Alaska[Desc][LatLon]58.301935,-134.419740[LatLon][UTM]8V:534009.26096904,6462472.8464997[UTM][Address]709 West 9th Street Juneau Alaska[Address][!][Desc]Phoenix, Arizona[Desc][LatLon]33.448457,-112.073844[LatLon][UTM]12S:400194.21279718,3701520.2757013[UTM][Address]Suite 1400 North Central Avenue Phoenix Arizona[Address][!][Desc]Little Rock, Arkansas[Desc][LatLon]34.736009,-92.331122[LatLon][UTM]15S:561232.09562153,3843971.7628186[UTM][Address] West 18th Street  Arkansas[Address][!][Desc]Sacramento, California[Desc][LatLon]38.555605,-121.468926[LatLon][UTM]10S:633407.27512251,4268574.590979[UTM][Address] X Street Y Street Alley Sacramento California[Address][!][Desc]Denver, Colorado[Desc][LatLon]39.7391667,-104.984167[LatLon][UTM]13S:501356.62832259,4398808.0467364[UTM][Address]200 East Colfax Avenue Denver Colorado[Address][!][Desc]Hartford, Connecticut[Desc][LatLon]41.767,-72.677[LatLon][UTM]18T:693091.61449858,4626515.1509541[UTM][Address] Haynes Street City Of Hartford Connecticut[Address][!][Desc]Dover, Delaware[Desc][LatLon]39.161921,-75.526755[LatLon][UTM]18S:454491.37347078,4334877.4920692[UTM][Address] North Bradford Street Dover Delaware[Address][!][Desc]Tallahassee, Florida[Desc][LatLon]30.4518,-84.27277[LatLon][UTM]16R:761883.81679029,3372010.5037012[UTM][Address]902 Martin Street Tallahassee Florida[Address][!][Desc]Atlanta, Georgia[Desc][LatLon]33.76,-84.39[LatLon][UTM]16S:741735.79582188,3738606.7627897[UTM][Address]196 Ted Turner Drive Northwest Atlanta Georgia[Address][!][Desc]Honolulu, Hawaii[Desc][LatLon]21.30895,-157.826182[LatLon][UTM]4Q:621747.01926081,2356793.8454331[UTM][Address] Mamane Place Honolulu Hawaii[Address][!][Desc]Boise, Idaho[Desc][LatLon]43.613739,-116.237651[LatLon][UTM]11T:561515.86953992,4829255.4444125[UTM][Address] Gage Street Boise City Idaho[Address][!][Desc]Springfield, Illinois[Desc][LatLon]39.783250,-89.650373[LatLon][UTM]16S:273036.44670432,4407060.8758545[UTM][Address] East Laurel Street Springfield Illinois[Address][!][Desc]Indianapolis, Indiana[Desc][LatLon]39.790942,-86.147685[LatLon][UTM]16S:572975.22719286,4404901.6314715[UTM][Address] East 17th Street Indianapolis Indiana[Address][!][Desc]Des Moines, Iowa[Desc][LatLon]41.590939,-93.620866[LatLon][UTM]15T:448253.23858349,4604546.3882494[UTM][Address] 2nd Avenue Des Moines Iowa[Address][!][Desc]Topeka, Kansas[Desc][LatLon]39.04,-95.69[LatLon][UTM]15S:267181.5390966,4324659.2616614[UTM][Address]1018 Southwest 15th Street Topeka Kansas[Address][!][Desc]Frankfort, Kentucky[Desc][LatLon]38.197274,-84.86311[LatLon][UTM]16S:687119.84422167,4229861.6492636[UTM][Address] East Main Street Frankfort Kentucky[Address][!][Desc]Baton Rouge, Louisiana[Desc][LatLon]30.45809,-91.140229[LatLon][UTM]15R:678556.42591707,3371016.4979645[UTM][Address] North Foster Drive Baton Rouge Louisiana[Address][!][Desc]Augusta, Maine[Desc][LatLon]44.323535,-69.765261[LatLon][UTM]19T:438980.21801941,4908092.9149464[UTM][Address] Park Street Augusta Maine[Address][!][Desc]Annapolis, Maryland[Desc][LatLon]38.972945,-76.501157[LatLon][UTM]18S:369959.55240987,4314845.850535[UTM][Address]128 Archwood Avenue Annapolis Maryland[Address][!][Desc]Boston, Massachusetts[Desc][LatLon]42.2352,-71.0275[LatLon][UTM]19T:332703.5972572,4677880.84088[UTM][Address] Wesson Avenue  Massachusetts[Address][!][Desc]Lansing, Michigan[Desc][LatLon]42.7335,-84.5467[LatLon][UTM]16T:700831.4265761,4734139.7225832[UTM][Address] East Michigan Avenue Lansing Michigan[Address][!][Desc]Saint Paul, Minnesota[Desc][LatLon]44.95,-93.094[LatLon][UTM]15T:492584.92142831,4977400.3559376[UTM][Address] Robert Street North Saint Paul Minnesota[Address][!][Desc]Jackson, Mississippi[Desc][LatLon]32.320,-90.207[LatLon][UTM]15S:762938.35266383,3579334.2537806[UTM][Address] Carver Street Jackson Mississippi[Address][!][Desc]Jefferson City, Missouri[Desc][LatLon]38.572954,-92.189283[LatLon][UTM]15S:570621.96606259,4269700.1089196[UTM][Address] Edmonds Street Jefferson City Missouri[Address][!][Desc]Helana, Montana[Desc][LatLon]46.595805,-112.027031[LatLon][UTM]12T:421332.72882339,5160761.2480163[UTM][Address] Helena Avenue Helena Montana[Address][!][Desc]Lincoln, Nebraska[Desc][LatLon]40.809868,-96.675345[LatLon][UTM]14T:696075.72531413,4520251.3423297[UTM][Address] J Street Lincoln Nebraska[Address][!][Desc]Carson City, Nevada[Desc][LatLon]39.160949,-119.753877[LatLon][UTM]11S:262059.41187024,4338250.1156982[UTM][Address] East 5th Street Carson City Nevada[Address][!][Desc]Concord, New Hampshire[Desc][LatLon]43.220093,-71.549127[LatLon][UTM]19T:292963.65070103,4788411.2231967[UTM][Address] Curtice Avenue Concord New Hampshire[Address][!][Desc]Trenton, New Jersey[Desc][LatLon]40.221741,-74.756138[LatLon][UTM]18T:520748.50944052,4452397.286396[UTM][Address]450 Ewing Street Trenton New Jersey[Address][!][Desc]Santa Fe, New Mexico[Desc][LatLon]35.667231,-105.964575[LatLon][UTM]13S:412700.06116234,3947469.0280147[UTM][Address] Young Street Santa Fe New Mexico[Address][!][Desc]Albany, New York[Desc][LatLon]42.659829,-73.781339[LatLon][UTM]18T:599877.87894873,4723760.3512143[UTM][Address] Yates Street Albany New York[Address][!][Desc]Raleigh, North Carolina[Desc][LatLon]35.771,-78.638[LatLon][UTM]17S:713514.51156294,3961122.9545538[UTM][Address] South Wilmington Street Raleigh North Carolina[Address][!][Desc]Bismarck, North Dakota[Desc][LatLon]48.813343,-100.779004[LatLon][UTM]14U:369396.37872121,5408232.4739295[UTM][Address] County Road 33  North Dakota[Address][!][Desc]Columbus, Ohio[Desc][LatLon]39.962245,-83.000647[LatLon][UTM]17S:329125.20731903,4425483.3406726[UTM][Address] East Broad Street Columbus Ohio[Address][!][Desc]Oklahoma City, Oklahoma[Desc][LatLon]35.482309,-97.534994[LatLon][UTM]14S:632899.82618685,3927517.786144[UTM][Address] North Mckinley Avenue Oklahoma City Oklahoma[Address][!][Desc]Salem, Oregon[Desc][LatLon]44.931109,-123.029159[LatLon][UTM]10T:497699.07245835,4975297.9434202[UTM][Address]  Salem Oregon[Address][!][Desc]Harrisburg, Pennsylvania[Desc][LatLon]40.269789,-76.875613[LatLon][UTM]18T:340525.35775351,4459389.4206946[UTM][Address] Forster Street Harrisburg Pennsylvania[Address][!][Desc]Providence, Rhode Island[Desc][LatLon]41.82355,-71.422132[LatLon][UTM]19T:298844.83645536,4633021.6717855[UTM][Address] Newton Street Providence Rhode Island[Address][!][Desc]Columbia, South Carolina[Desc][LatLon]34.000,-81.035[LatLon][UTM]17S:496767.82579737,3762156.5296628[UTM][Address]1115 Assembly Street Columbia South Carolina[Address][!][Desc]Pierre, South Dakota[Desc][LatLon]44.367966,-100.336378[LatLon][UTM]14T:393521.24858888,4913611.737334[UTM][Address] East Robinson Avenue Pierre South Dakota[Address][!][Desc]Nashville, Tennessee[Desc][LatLon]36.165,-86.784[LatLon][UTM]16S:519426.94669423,4002271.2269574[UTM][Address] 7th Avenue North Nashville-Davidson Tennessee[Address][!][Desc]Austin, Texas[Desc][LatLon]30.266667,-97.75[LatLon][UTM]14R:620240.70200607,3348995.9735886[UTM][Address]607 West 3rd Street Austin Texas[Address][!][Desc]Salt Lake City, Utah[Desc][LatLon]40.7547,-111.892622[LatLon][UTM]12T:424651.03790536,4511910.1988511[UTM][Address] 700 South Salt Lake City Utah[Address][!][Desc]Montpelier, Vermont[Desc][LatLon]44.26639,-72.57194[LatLon][UTM]18T:693796.0175554,4904327.9430711[UTM][Address]15 Winter Street Montpelier Vermont[Address][!][Desc]Richmond, Virginia[Desc][LatLon]37.54,-77.46[LatLon][UTM]18S:282659.10446272,4157622.9853212[UTM][Address] Lakeview Avenue Richmond City Virginia[Address][!][Desc]Olympia, Washington[Desc][LatLon]47.042418,-122.893077[LatLon][UTM]10T:508122.44663845,5209883.4331402[UTM][Address] Plum Street Southeast Olympia Washington[Address][!][Desc]Charleston, West Virginia[Desc][LatLon]38.349497,-81.633294[LatLon][UTM]17S:444663.13148926,4244783.347957[UTM][Address] Hale Street Charleston West Virginia[Address][!][Desc]Madison, Wisconsin[Desc][LatLon]43.074722,-89.384444[LatLon][UTM]16T:305879.7197932,4771872.0721079[UTM][Address]2 East Main Street Madison Wisconsin[Address][!][Desc]Cheyenne, Wyoming[Desc][LatLon]41.145548,-104.802042[LatLon][UTM]13T:516611.89796343,4554933.3575248[UTM][Address]1525 East Pershing Boulevard Cheyenne Wyoming[Address]',0,'2018-04-30 17:33:53','2018-04-30 17:33:53');
/*!40000 ALTER TABLE `kora3_option_presets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_pages`
--

DROP TABLE IF EXISTS `kora3_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pages_fid_foreign` (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_pages`
--

LOCK TABLES `kora3_pages` WRITE;
/*!40000 ALTER TABLE `kora3_pages` DISABLE KEYS */;
INSERT INTO `kora3_pages` VALUES (5,5,'Section',0,'2018-05-02 18:09:15','2018-05-02 18:09:15'),(2,2,'test Default Page',0,'2018-05-01 14:33:37','2018-05-01 14:33:37'),(3,3,'thisistheuniqueId Default Page',0,'2018-05-02 14:56:05','2018-05-02 14:56:05');
/*!40000 ALTER TABLE `kora3_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_password_resets`
--

DROP TABLE IF EXISTS `kora3_password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_password_resets`
--

LOCK TABLES `kora3_password_resets` WRITE;
/*!40000 ALTER TABLE `kora3_password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_playlist_fields`
--

DROP TABLE IF EXISTS `kora3_playlist_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_playlist_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `audio` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `playlist_fields_rid_foreign` (`rid`),
  KEY `playlist_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_ply` (`audio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_playlist_fields`
--

LOCK TABLES `kora3_playlist_fields` WRITE;
/*!40000 ALTER TABLE `kora3_playlist_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_playlist_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_plugin_menus`
--

DROP TABLE IF EXISTS `kora3_plugin_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_plugin_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_menus_plugin_id_foreign` (`plugin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_plugin_menus`
--

LOCK TABLES `kora3_plugin_menus` WRITE;
/*!40000 ALTER TABLE `kora3_plugin_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_plugin_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_plugin_settings`
--

DROP TABLE IF EXISTS `kora3_plugin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_plugin_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_settings_plugin_id_foreign` (`plugin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_plugin_settings`
--

LOCK TABLES `kora3_plugin_settings` WRITE;
/*!40000 ALTER TABLE `kora3_plugin_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_plugin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_plugin_users`
--

DROP TABLE IF EXISTS `kora3_plugin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_plugin_users` (
  `plugin_id` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `plugin_users_gid_foreign` (`gid`),
  KEY `plugin_users_plugin_id_gid_index` (`plugin_id`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_plugin_users`
--

LOCK TABLES `kora3_plugin_users` WRITE;
/*!40000 ALTER TABLE `kora3_plugin_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_plugin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_plugins`
--

DROP TABLE IF EXISTS `kora3_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugins_pid_foreign` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_plugins`
--

LOCK TABLES `kora3_plugins` WRITE;
/*!40000 ALTER TABLE `kora3_plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_project_custom`
--

DROP TABLE IF EXISTS `kora3_project_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_project_custom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_custom_uid_foreign` (`uid`),
  KEY `project_custom_pid_foreign` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_project_custom`
--

LOCK TABLES `kora3_project_custom` WRITE;
/*!40000 ALTER TABLE `kora3_project_custom` DISABLE KEYS */;
INSERT INTO `kora3_project_custom` VALUES (1,3,1,0,'2018-04-30 17:42:24','2018-04-30 17:42:24'),(2,1,1,0,'2018-04-30 17:42:24','2018-04-30 17:42:24'),(3,2,2,0,'2018-04-30 17:42:30','2018-04-30 17:42:30'),(4,1,2,1,'2018-04-30 17:42:30','2018-04-30 17:42:30'),(5,4,3,0,'2018-04-30 17:43:13','2018-04-30 17:43:13'),(6,1,3,2,'2018-04-30 17:43:13','2018-04-30 17:43:13'),(7,3,2,1,'2018-04-30 17:44:31','2018-04-30 17:44:31'),(8,3,3,2,'2018-04-30 17:44:31','2018-04-30 17:44:31'),(9,2,1,1,'2018-04-30 17:44:40','2018-04-30 17:44:40'),(10,2,3,2,'2018-04-30 17:44:40','2018-04-30 17:44:40'),(14,1,4,3,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(13,5,4,0,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(15,2,4,3,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(16,3,4,3,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(96,6,10,9,NULL,NULL),(18,1,5,4,'2018-04-30 17:50:04','2018-04-30 17:50:04'),(19,2,5,4,'2018-04-30 17:50:04','2018-04-30 17:50:04'),(20,3,5,4,'2018-04-30 17:50:04','2018-04-30 17:50:04'),(21,5,1,1,'2018-04-30 18:20:07','2018-04-30 18:20:07'),(22,5,2,2,'2018-04-30 18:20:07','2018-04-30 18:20:07'),(23,5,3,3,'2018-04-30 18:20:07','2018-04-30 18:20:07'),(24,5,5,4,'2018-04-30 18:20:07','2018-04-30 18:20:07'),(25,9,6,0,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(26,1,6,5,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(27,2,6,5,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(28,3,6,5,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(29,5,6,5,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(35,5,8,7,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(31,1,7,6,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(32,2,7,6,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(33,3,7,6,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(34,5,7,6,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(36,9,8,1,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(37,1,8,7,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(38,2,8,7,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(39,3,8,7,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(40,13,9,0,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(41,1,9,8,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(42,2,9,8,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(43,3,9,8,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(44,5,9,8,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(45,12,10,0,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(46,1,10,9,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(47,2,10,9,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(48,3,10,9,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(49,5,10,9,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(50,13,8,1,'2018-04-30 18:39:49','2018-04-30 18:39:49'),(51,9,1,2,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(52,9,2,3,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(53,9,3,4,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(54,9,4,5,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(55,9,5,6,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(56,9,7,7,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(57,9,9,8,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(58,9,10,9,'2018-04-30 18:40:49','2018-04-30 18:40:49'),(59,12,1,1,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(60,12,2,2,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(61,12,3,3,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(62,12,4,4,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(63,12,5,5,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(64,12,6,6,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(65,12,7,7,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(66,12,8,8,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(67,12,9,9,'2018-04-30 18:40:55','2018-04-30 18:40:55'),(95,6,9,8,NULL,NULL),(94,6,8,7,NULL,NULL),(93,6,7,6,NULL,NULL),(92,6,6,5,NULL,NULL),(91,6,4,4,NULL,NULL),(90,6,3,3,NULL,NULL),(89,6,2,2,NULL,NULL),(88,6,1,1,NULL,NULL),(87,6,5,0,NULL,NULL);
/*!40000 ALTER TABLE `kora3_project_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_project_group_user`
--

DROP TABLE IF EXISTS `kora3_project_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_project_group_user` (
  `project_group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  KEY `project_group_user_project_group_id_index` (`project_group_id`),
  KEY `project_group_user_user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_project_group_user`
--

LOCK TABLES `kora3_project_group_user` WRITE;
/*!40000 ALTER TABLE `kora3_project_group_user` DISABLE KEYS */;
INSERT INTO `kora3_project_group_user` VALUES (1,3),(3,2),(5,4),(7,5),(9,6),(11,9),(15,5),(15,9),(15,5),(17,13),(19,12);
/*!40000 ALTER TABLE `kora3_project_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_project_groups`
--

DROP TABLE IF EXISTS `kora3_project_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_project_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `create` tinyint(1) NOT NULL,
  `edit` tinyint(1) NOT NULL,
  `delete` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_groups_pid_foreign` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_project_groups`
--

LOCK TABLES `kora3_project_groups` WRITE;
/*!40000 ALTER TABLE `kora3_project_groups` DISABLE KEYS */;
INSERT INTO `kora3_project_groups` VALUES (1,'ZZTest anthony.donofrio Admin Group',1,1,1,1,'2018-04-30 17:42:24','2018-04-30 17:42:24'),(2,'ZZTest anthony.donofrio Default Group',1,0,0,0,'2018-04-30 17:42:24','2018-04-30 17:42:24'),(3,'Austin\'s Project Admin Group',2,1,1,1,'2018-04-30 17:42:30','2018-05-02 15:12:51'),(4,'Austin\'s Project Default Group',2,0,0,0,'2018-04-30 17:42:30','2018-05-02 15:12:51'),(5,'ZZTest goekesmi Admin Group',3,1,1,1,'2018-04-30 17:43:13','2018-04-30 17:43:13'),(6,'ZZTest goekesmi Default Group',3,0,0,0,'2018-04-30 17:43:13','2018-04-30 17:43:13'),(7,'ZZTest seilage Admin Group',4,1,1,1,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(8,'ZZTest seilage Default Group',4,0,0,0,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(9,'Ethan\'s Super Awesome Test Project.   Admin Group',5,1,1,1,'2018-04-30 17:50:04','2018-05-02 11:19:43'),(10,'Ethan\'s Super Awesome Test Project.   Default Group',5,0,0,0,'2018-04-30 17:50:04','2018-05-02 11:19:43'),(11,'ZZTest asheill Admin Group',6,1,1,1,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(12,'ZZTest asheill Default Group',6,0,0,0,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(13,'ZZTest bob_diller.steve Admin Group',7,1,1,1,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(14,'ZZTest bob_diller.steve Default Group',7,0,0,0,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(15,'Hutchins Biographies Admin Group',8,1,1,1,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(16,'Hutchins Biographies Default Group',8,0,0,0,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(17,'ZZTest cartyrya Admin Group',9,1,1,1,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(18,'ZZTest cartyrya Default Group',9,0,0,0,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(19,'ZZTest catherine.foley Admin Group',10,1,1,1,'2018-04-30 18:37:37','2018-04-30 18:37:37'),(20,'ZZTest catherine.foley Default Group',10,0,0,0,'2018-04-30 18:37:37','2018-04-30 18:37:37');
/*!40000 ALTER TABLE `kora3_project_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_project_token`
--

DROP TABLE IF EXISTS `kora3_project_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_project_token` (
  `project_pid` int(10) unsigned NOT NULL,
  `token_id` int(10) unsigned NOT NULL,
  KEY `project_token_project_pid_index` (`project_pid`),
  KEY `project_token_token_id_index` (`token_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_project_token`
--

LOCK TABLES `kora3_project_token` WRITE;
/*!40000 ALTER TABLE `kora3_project_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_project_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_projects`
--

DROP TABLE IF EXISTS `kora3_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_projects` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `adminGID` int(10) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `projects_slug_unique` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_projects`
--

LOCK TABLES `kora3_projects` WRITE;
/*!40000 ALTER TABLE `kora3_projects` DISABLE KEYS */;
INSERT INTO `kora3_projects` VALUES (1,'ZZTest anthony.donofrio','ZZTest_anthony_donofrio','Test project for user, anthony.donofrio',1,1,'2018-04-30 17:42:24','2018-04-30 18:24:11'),(2,'Austin\'s Project','austintruchan','Test project for Austin. Updating description works, can confirm.',3,1,'2018-04-30 17:42:30','2018-05-03 14:54:35'),(3,'ZZTest goekesmi','ZZTest_goekesmi','Test project for user, goekesmi',5,1,'2018-04-30 17:43:13','2018-04-30 17:43:13'),(4,'ZZTest seilage','ZZTest_seilage','Test project for user, seilage',7,1,'2018-04-30 17:49:42','2018-04-30 17:49:42'),(5,'Ethan\'s Super Awesome Test Project.  ','ZZTest_watrall','This is a test project so that Ethan can screw around with the beta release version of K3',9,1,'2018-04-30 17:50:04','2018-05-02 11:19:43'),(6,'ZZTest asheill','ZZTest_asheill','Test project for user, asheill',11,1,'2018-04-30 18:26:24','2018-04-30 18:26:24'),(7,'ZZTest bob_diller.steve','ZZTest_bob_diller.steve','Test project for user, bob_diller.steve',13,1,'2018-04-30 18:27:25','2018-04-30 18:27:25'),(8,'Hutchins Biographies','UbOqni649EmiHHTN1vF7gvYj','Test Hutchins Biographies',15,1,'2018-04-30 18:31:38','2018-04-30 18:31:38'),(9,'ZZTest cartyrya','ZZTest_cartyrya','Test project for user, cartyrya',17,1,'2018-04-30 18:36:53','2018-04-30 18:36:53'),(10,'ZZTest catherine.foley','ZZTest_catherine.foley','Test project for user, catherine.foley',19,1,'2018-04-30 18:37:37','2018-04-30 18:37:37');
/*!40000 ALTER TABLE `kora3_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_record_presets`
--

DROP TABLE IF EXISTS `kora3_record_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_record_presets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `preset` blob NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_presets_fid_foreign` (`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_record_presets`
--

LOCK TABLES `kora3_record_presets` WRITE;
/*!40000 ALTER TABLE `kora3_record_presets` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_record_presets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_records`
--

DROP TABLE IF EXISTS `kora3_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_records` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `legacy_kid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `isTest` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`rid`),
  KEY `records_pid_fid_foreign` (`pid`,`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_records`
--

LOCK TABLES `kora3_records` WRITE;
/*!40000 ALTER TABLE `kora3_records` DISABLE KEYS */;
INSERT INTO `kora3_records` VALUES (1,'2-3-1',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(2,'2-3-2',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(3,'2-3-3',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(4,'2-3-4',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(5,'2-3-5',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(6,'2-3-6',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(7,'2-3-7',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(8,'2-3-8',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(9,'2-3-9',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(10,'2-3-10',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(11,'2-3-11',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(12,'2-3-12',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(13,'2-3-13',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(14,'2-3-14',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(15,'2-3-15',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(16,'2-3-16',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(17,'2-3-17',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(18,'2-3-18',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(19,'2-3-19',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(20,'2-3-20',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(21,'2-3-21',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(22,'2-3-22',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(23,'2-3-23',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(24,'2-3-24',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(25,'2-3-25',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(26,'2-3-26',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(27,'2-3-27',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(28,'2-3-28',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(29,'2-3-29',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(30,'2-3-30',NULL,2,3,2,0,'2018-05-02 14:57:08','2018-05-02 14:57:08'),(31,'2-3-31',NULL,2,3,2,0,'2018-05-02 15:32:12','2018-05-02 15:32:12'),(32,'2-3-32',NULL,2,3,2,0,'2018-05-02 15:32:12','2018-05-02 15:32:12'),(33,'2-3-33',NULL,2,3,2,0,'2018-05-02 15:32:12','2018-05-02 15:32:12'),(34,'2-3-34',NULL,2,3,2,0,'2018-05-02 18:22:21','2018-05-02 18:22:21'),(35,'2-3-35',NULL,2,3,2,0,'2018-05-02 19:01:28','2018-05-02 19:01:28'),(36,'2-3-36',NULL,2,3,2,0,'2018-05-02 19:49:31','2018-05-02 19:49:31'),(37,'2-3-37',NULL,2,3,2,0,'2018-05-02 19:49:31','2018-05-02 19:49:31'),(38,'2-3-38',NULL,2,3,2,0,'2018-05-03 15:48:00','2018-05-03 15:48:00'),(39,'2-3-39',NULL,2,3,2,0,'2018-05-03 15:51:32','2018-05-03 15:51:32');
/*!40000 ALTER TABLE `kora3_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_restore_overall_progress`
--

DROP TABLE IF EXISTS `kora3_restore_overall_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_restore_overall_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_restore_overall_progress`
--

LOCK TABLES `kora3_restore_overall_progress` WRITE;
/*!40000 ALTER TABLE `kora3_restore_overall_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_restore_overall_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_restore_partial_progress`
--

DROP TABLE IF EXISTS `kora3_restore_partial_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_restore_partial_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `progress` int(11) NOT NULL,
  `overall` int(11) NOT NULL,
  `restore_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restore_partial_progress_restore_id_foreign` (`restore_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_restore_partial_progress`
--

LOCK TABLES `kora3_restore_partial_progress` WRITE;
/*!40000 ALTER TABLE `kora3_restore_partial_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_restore_partial_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_revisions`
--

DROP TABLE IF EXISTS `kora3_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_revisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `owner` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` blob NOT NULL,
  `oldData` blob NOT NULL,
  `rollback` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `revisions_fid_foreign` (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_revisions`
--

LOCK TABLES `kora3_revisions` WRITE;
/*!40000 ALTER TABLE `kora3_revisions` DISABLE KEYS */;
INSERT INTO `kora3_revisions` VALUES (1,3,1,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(2,3,2,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(3,3,3,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(4,3,4,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(5,3,5,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(6,3,6,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(7,3,7,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(8,3,8,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(9,3,9,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(10,3,10,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(11,3,11,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(12,3,12,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(13,3,13,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(14,3,14,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(15,3,15,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(16,3,16,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(17,3,17,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(18,3,18,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(19,3,19,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(20,3,20,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(21,3,21,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(22,3,22,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(23,3,23,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(24,3,24,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(25,3,25,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(26,3,26,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(27,3,27,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(28,3,28,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(29,3,29,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(30,3,30,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody\"}}}','',0,'2018-05-02 14:57:08','2018-05-03 15:57:12'),(31,3,31,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody I\'m jeremy\"}}}','',0,'2018-05-02 15:32:12','2018-05-03 15:57:12'),(32,3,32,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody I\'m jeremy\"}}}','',0,'2018-05-02 15:32:12','2018-05-03 15:57:12'),(33,3,33,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"New field\",\"data\":\"Hi Everybody I\'m jeremy\"}}}','',0,'2018-05-02 15:32:12','2018-05-03 15:57:12'),(34,3,36,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"Text field\",\"data\":\"Hi Everybody\"}},\"Geolocator\":{\"2\":{\"name\":\"Geolocator\",\"data\":[\"[Desc]My House[Desc][LatLon]42.7495645,-84.4876069[LatLon][UTM]16T:705616.008,4736066.064[UTM][Address]220 E Pointe Ln, East Lansing, MI. 48823[Address]\"]}},\"Rich Text\":{\"10\":{\"name\":\"Rich Text\",\"data\":\"Hello\"}},\"List\":{\"11\":{\"name\":\"List\",\"data\":\"Cheese\"}},\"Multi-Select List\":{\"13\":{\"name\":\"Multi Select List\",\"data\":null}}}','',0,'2018-05-02 19:49:31','2018-05-03 15:57:12'),(35,3,37,2,'austintruchan','create','{\"Text\":{\"1\":{\"name\":\"Text field\",\"data\":\"Hi Everybody\"}},\"Geolocator\":{\"2\":{\"name\":\"Geolocator\",\"data\":[\"[Desc]My House[Desc][LatLon]42.7495645,-84.4876069[LatLon][UTM]16T:705616.008,4736066.064[UTM][Address]220 E Pointe Ln, East Lansing, MI. 48823[Address]\"]}},\"Rich Text\":{\"10\":{\"name\":\"Rich Text\",\"data\":\"Hello\"}},\"List\":{\"11\":{\"name\":\"List\",\"data\":\"Cheese\"}},\"Multi-Select List\":{\"13\":{\"name\":\"Multi Select List\",\"data\":null}}}','',0,'2018-05-02 19:49:31','2018-05-03 15:57:12');
/*!40000 ALTER TABLE `kora3_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_rich_text_fields`
--

DROP TABLE IF EXISTS `kora3_rich_text_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_rich_text_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `rawtext` longtext COLLATE utf8_unicode_ci NOT NULL,
  `searchable_rawtext` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rich_text_fields_rid_foreign` (`rid`),
  KEY `rich_text_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search` (`searchable_rawtext`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_rich_text_fields`
--

LOCK TABLES `kora3_rich_text_fields` WRITE;
/*!40000 ALTER TABLE `kora3_rich_text_fields` DISABLE KEYS */;
INSERT INTO `kora3_rich_text_fields` VALUES (1,35,10,3,'Hello','Hello','2018-05-02 19:01:28','2018-05-02 19:01:28'),(2,36,10,3,'Hello','Hello','2018-05-02 19:49:31','2018-05-02 19:49:31'),(3,37,10,3,'Hello','Hello','2018-05-02 19:49:31','2018-05-02 19:49:31'),(4,38,10,3,'Hello','Hello','2018-05-03 15:48:00','2018-05-03 15:48:00'),(5,39,10,3,'Hello','Hello','2018-05-03 15:51:32','2018-05-03 15:51:32');
/*!40000 ALTER TABLE `kora3_rich_text_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_schedule_fields`
--

DROP TABLE IF EXISTS `kora3_schedule_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_schedule_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_fields_rid_foreign` (`rid`),
  KEY `schedule_fields_flid_foreign` (`flid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_schedule_fields`
--

LOCK TABLES `kora3_schedule_fields` WRITE;
/*!40000 ALTER TABLE `kora3_schedule_fields` DISABLE KEYS */;
INSERT INTO `kora3_schedule_fields` VALUES (1,38,3,16,'2018-05-03 15:48:00','2018-05-03 15:48:00');
/*!40000 ALTER TABLE `kora3_schedule_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_schedule_support`
--

DROP TABLE IF EXISTS `kora3_schedule_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_schedule_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `begin` datetime NOT NULL,
  `end` datetime NOT NULL,
  `allday` tinyint(1) NOT NULL,
  `desc` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search_supp` (`desc`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_schedule_support`
--

LOCK TABLES `kora3_schedule_support` WRITE;
/*!40000 ALTER TABLE `kora3_schedule_support` DISABLE KEYS */;
INSERT INTO `kora3_schedule_support` VALUES (1,3,38,16,'2018-01-01 15:48:00','2018-01-01 15:48:00',1,'New Years Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(2,3,38,16,'2018-01-15 15:48:00','2018-01-15 15:48:00',1,'Martin Luther King Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(3,3,38,16,'2018-02-19 15:48:00','2018-02-19 15:48:00',1,'Presidents\' Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(4,3,38,16,'2018-05-13 15:48:00','2018-05-13 15:48:00',1,'Mother\'s Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(5,3,38,16,'2018-06-17 15:48:00','2018-06-17 15:48:00',1,'Father\'s Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(6,3,38,16,'2018-07-04 15:48:00','2018-07-04 15:48:00',1,'Independence Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(7,3,38,16,'2018-09-03 15:48:00','2018-09-03 15:48:00',1,'Labor Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(8,3,38,16,'2018-10-08 15:48:00','2018-10-08 15:48:00',1,'Columbus Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(9,3,38,16,'2018-11-12 15:48:00','2018-11-12 15:48:00',1,'Veterans Day','2018-05-03 15:48:00','2018-05-03 15:48:00'),(10,3,38,16,'2018-11-22 15:48:00','2018-11-22 15:48:00',1,'Thanksgiving','2018-05-03 15:48:00','2018-05-03 15:48:00'),(11,3,38,16,'2018-12-25 15:48:00','2018-12-25 15:48:00',1,'Christmas','2018-05-03 15:48:00','2018-05-03 15:48:00');
/*!40000 ALTER TABLE `kora3_schedule_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_scripts`
--

DROP TABLE IF EXISTS `kora3_scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_scripts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hasRun` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scripts_filename_unique` (`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_scripts`
--

LOCK TABLES `kora3_scripts` WRITE;
/*!40000 ALTER TABLE `kora3_scripts` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_scripts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_text_fields`
--

DROP TABLE IF EXISTS `kora3_text_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_text_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text_fields_rid_foreign` (`rid`),
  KEY `text_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search` (`text`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_text_fields`
--

LOCK TABLES `kora3_text_fields` WRITE;
/*!40000 ALTER TABLE `kora3_text_fields` DISABLE KEYS */;
INSERT INTO `kora3_text_fields` VALUES (1,1,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(2,2,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(3,3,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(4,4,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(5,5,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(6,6,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(7,7,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(8,8,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(9,9,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(10,10,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(11,11,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(12,12,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(13,13,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(14,14,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(15,15,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(16,16,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(17,17,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(18,18,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(19,19,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(20,20,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(21,21,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(22,22,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(23,23,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(24,24,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(25,25,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(26,26,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(27,27,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(28,28,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(29,29,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(30,30,3,1,'Hi Everybody','2018-05-02 14:57:08','2018-05-02 14:57:08'),(31,31,3,1,'Hi Everybody I\'m jeremy','2018-05-02 15:32:12','2018-05-02 15:32:12'),(32,32,3,1,'Hi Everybody I\'m jeremy','2018-05-02 15:32:12','2018-05-02 15:32:12'),(33,33,3,1,'Hi Everybody I\'m jeremy','2018-05-02 15:32:12','2018-05-02 15:32:12'),(34,34,3,1,'Hi Everybody this is jesse','2018-05-02 18:22:21','2018-05-02 18:22:21'),(35,35,3,1,'Hi Everybody','2018-05-02 19:01:28','2018-05-02 19:01:28'),(36,36,3,1,'Hi Everybody','2018-05-02 19:49:31','2018-05-02 19:49:31'),(37,37,3,1,'Hi Everybody','2018-05-02 19:49:31','2018-05-02 19:49:31');
/*!40000 ALTER TABLE `kora3_text_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_tokens`
--

DROP TABLE IF EXISTS `kora3_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `search` tinyint(1) NOT NULL,
  `create` tinyint(1) NOT NULL,
  `edit` tinyint(1) NOT NULL,
  `delete` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_tokens`
--

LOCK TABLES `kora3_tokens` WRITE;
/*!40000 ALTER TABLE `kora3_tokens` DISABLE KEYS */;
INSERT INTO `kora3_tokens` VALUES (1,'UbOqni649EmiHHTN1vF7gvYj','Hutchins',1,1,1,1,'2018-04-30 18:30:02','2018-04-30 18:30:02');
/*!40000 ALTER TABLE `kora3_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_users`
--

DROP TABLE IF EXISTS `kora3_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `organization` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `regtoken` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dash` tinyint(1) NOT NULL DEFAULT '0',
  `locked_out` tinyint(1) NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_users`
--

LOCK TABLES `kora3_users` WRITE;
/*!40000 ALTER TABLE `kora3_users` DISABLE KEYS */;
INSERT INTO `kora3_users` VALUES (1,1,1,'msumatrix','Matrix','MSU',NULL,'matrix@msu.edu','$2y$10$m6YlHXijMo1Hj5QZucbYQ.i5eyCWP7C1GOgK1R/d4PBsCnVcnsnW2','Matrix','en','',0,0,'akukufNMXzfZTjgAZv4mBsiyJFDqn9e9F6dIt2e1inYny8gRbkttFM4Oksw1','2018-04-30 17:33:53','2018-04-30 17:33:53'),(2,1,1,'austintruchan','Austin','Truchan','austin_truchan.jpg','austintruchan@me.com','$2y$10$dymV/i.zbw9W/a3PePQJvewi9VcjUA.3kCFCje0xhxdYZ5.SV/a4u','Matrix','en','cBfvmRHoNeZAnC4ukfz3K0AWLG5ZbhX',0,0,'rraSDuH2wOc4lZAbZk4cCzoilbzlurCWFuL40ssIOrgg7ygDjsZjuq3WfPwd','2018-04-30 17:38:42','2018-04-30 17:44:40'),(3,1,1,'anthony.donofrio','Anthony','Marco D\'Onofrio','anthony_donofrio_DSC6625.jpg','anthon70@msu.edu','$2y$10$XI2stGFf8aAQVvSeYgKEyu.PvAaSno3csBoSlUWrk53.jSVYvnmVq','Matrix Kora Dev','en','Yrptkd0ZPmGV7iSfJLQOSTzBSvU1YAc',0,0,'XHl8UyvQNb8NZRB8o68TogLSF6DGW0HIXTK1W0tdnQLLToOr5wIOm2YoWD8W','2018-04-30 17:38:52','2018-04-30 18:18:42'),(4,0,1,'goekesmi','Jeff','Goeke-Smith',NULL,'goekesmi@msu.edu','$2y$10$GeXNQm7jwpuvzPgmHjSIo.RE07HeUMdZjd3QI2BPbXhQBmiw./PQ6','Matrix','en','IGEAjVKjRJ0LmzuSBUCdKvadhOorkZq',0,0,'TYG8nNCqRWtpzWm8tx6pDn1dV9nFtwbOyTB4MRLCGSVixjy0JI1PlLd8GVVv','2018-04-30 17:41:00','2018-04-30 17:44:47'),(5,1,1,'seilage','Seila','Gonzalez Estrecha','playa-los-lances-Tarifa.jpg','seilage@msu.edu','$2y$10$XJmhw6LYbiyBEjko9CFn8OtPVtgN8w3pIWL9PjPxhpN/zPDu29ysW','Matrix','en','ZQq6CmS1bhRM2i4ozollfBMpBzp1bmv',0,0,'1ZOq6ccwFwYu2Dwoc0aNt4cBFWa3Qty31bYMYB7aWsNr6cfKSjd6ufD47mQj','2018-04-30 17:47:18','2018-04-30 18:20:11'),(6,1,1,'watrall','Ethan','Watrall',NULL,'watrall@msu.edu','$2y$10$shwFmIhu5In1dzi4Dcw8rudxIb3I6RIxNKZrpVgRYW.kZsQ0D2vye','MATRIX','en','NUkzAEf5QJqNN0SuvSEN3DHUyyRq0jU',0,0,'GlzzowuiUHCnN0dJ1kKI0eAVFTppqowjgXBXQtpdDHofaKHTsDMPZj2kfWmf','2018-04-30 17:47:38','2018-05-02 00:22:38'),(9,1,1,'asheill','Alicia','Sheill',NULL,'asheill@msu.edu','$2y$10$1pW3czrj4Og4VQrfOjmEi.RVPiKQ2Gwb23dkdrexsqrXvWEq3Xugq','Matrix','en','2jVmXHYRQKfgo8qfn8n4t6GQ3gDAg9E',0,0,'7ihYB6xpv3p9mrqqTeq49kT8TDdhzAgXXWVX8qEsTk5nKJkHYP5x5mLGCkfO','2018-04-30 18:25:04','2018-04-30 18:40:49'),(12,1,1,'catherine.foley','Catherine','Foley',NULL,'foleyc@msu.edu','$2y$10$c.LAs/tnjLBc8ONTLaWCgOFf6s9a6QtfGLav29tfz7M4fSjiU1DV2','MATRIX','en','DRPz35Fxg9TC0gAodUYqFjxEctOxAuw',0,0,'QFbSNXGzi2GlzHO3f2L7wJ1K6cwAG3s3wVQ5nwGd57lsVy3o8ZeMdcGATCXk','2018-04-30 18:34:39','2018-04-30 18:40:55'),(11,0,1,'bob_diller.steve','Bob','Diller',NULL,'bobdiller15@gmail.com','$2y$10$NtxO/sEFjVWEMLSD9uqKuepOA1c0uxw/pqn1xwOTe7rnMsErJyHWS','Bob Diller','en','ntVKMAYubqfQEjDw1F532lHt0a10lmo',0,0,'hQx1vp1IgITGBuaywOyNwMD6E47RZo3abicYSpFa32CJzxYVC7LN6ZLx7WLD','2018-04-30 18:31:23','2018-04-30 18:31:46'),(13,0,1,'cartyrya','Ryan','Carty',NULL,'cartyrya@msu.edu','$2y$10$mt55JnSmKlTfsTmZZLBAd.UyfUzaeJ/yhdZTj9/juQg8m1PUCiBei','','en','a2Pnx7vqPYHus4BzVTLWYGkIoo4EfKc',0,0,NULL,'2018-04-30 18:35:26','2018-04-30 18:36:52');
/*!40000 ALTER TABLE `kora3_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_versions`
--

DROP TABLE IF EXISTS `kora3_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_versions`
--

LOCK TABLES `kora3_versions` WRITE;
/*!40000 ALTER TABLE `kora3_versions` DISABLE KEYS */;
INSERT INTO `kora3_versions` VALUES (1,'3.0','2018-04-30 17:33:53','2018-04-30 17:33:53');
/*!40000 ALTER TABLE `kora3_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kora3_video_fields`
--

DROP TABLE IF EXISTS `kora3_video_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kora3_video_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `flid` int(10) unsigned NOT NULL,
  `video` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `video_fields_rid_foreign` (`rid`),
  KEY `video_fields_flid_foreign` (`flid`),
  FULLTEXT KEY `search_vid` (`video`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kora3_video_fields`
--

LOCK TABLES `kora3_video_fields` WRITE;
/*!40000 ALTER TABLE `kora3_video_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kora3_video_fields` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-05-03 20:15:03
