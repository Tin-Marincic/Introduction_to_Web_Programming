-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: web_db
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `availabilitycalendar`
--

DROP TABLE IF EXISTS `availabilitycalendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `availabilitycalendar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `instructor_id` int NOT NULL,
  `date` date DEFAULT NULL,
  `status` enum('active','not_active') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instructor_id` (`instructor_id`),
  CONSTRAINT `availabilitycalendar_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `availabilitycalendar`
--

LOCK TABLES `availabilitycalendar` WRITE;
/*!40000 ALTER TABLE `availabilitycalendar` DISABLE KEYS */;
INSERT INTO `availabilitycalendar` VALUES (5,2,'2025-05-06','active'),(6,2,'2025-05-12','active'),(7,2,'2025-05-07','not_active'),(26,2,'2025-05-07','active'),(28,2,'2025-04-22','active'),(32,2,'2025-04-20','active'),(36,2,'2025-04-22','active'),(37,2,'2025-04-22','active'),(38,2,'2025-04-22','active'),(39,2,'2025-04-24','active'),(41,4,'2025-05-05','active'),(43,4,'2025-05-06','active'),(45,4,'2025-05-06','active');
/*!40000 ALTER TABLE `availabilitycalendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `instructor_id` int DEFAULT NULL,
  `service_id` int NOT NULL,
  `session_type` enum('Private_instruction','Ski_school') DEFAULT NULL,
  `num_of_spots` int DEFAULT NULL,
  `week` enum('week1','week2','week3','week4') DEFAULT NULL,
  `age_group_child` int DEFAULT NULL,
  `age_group_teen` int DEFAULT NULL,
  `age_group_adult` int DEFAULT NULL,
  `ski_level_b` int DEFAULT NULL,
  `ski_level_i` int DEFAULT NULL,
  `ski_level_a` int DEFAULT NULL,
  `veg_count` int DEFAULT NULL,
  `other` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `num_of_hours` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `status` enum('confirmed','pending','cancelled') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,1,2,1,'Ski_school',10,'week1',2,6,2,10,0,0,2,'None',NULL,NULL,NULL,'confirmed'),(2,2,2,2,'Private_instruction',2,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,'2026-10-01',3,'10:00:00','confirmed'),(3,2,5,1,'Private_instruction',1,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,'2026-01-02',1,'12:00:00','confirmed'),(4,3,5,1,'Private_instruction',2,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,'2025-04-02',3,'11:00:00','confirmed'),(6,3,7,2,'Private_instruction',1,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,'2025-05-05',4,'10:00:00','confirmed'),(7,1,3,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-10',2,'10:00:00','confirmed'),(8,1,3,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-10',2,'15:00:00','confirmed');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `grade` enum('1','2','3','4','5') DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,1,'5','Great service!'),(74,8,1,'4','Great session!');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Ski Lesson','Beginner ski lesson',50,'2025-01-01','2025-12-31'),(2,'Advanced Lesson','Advanced ski techniques',100,'2025-01-01','2025-12-31');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `licence` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','instructor','admin') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Mak','Dalilic','','Makac','password1','1234567890','user','2025-04-06 22:54:07'),(2,'Alica','Smajic','U2','alicaaa','password2','0987654321','instructor','2025-04-06 22:54:07'),(3,'Tin','Admin','','tin','password3','1122334455','admin','2025-04-06 22:54:07'),(4,'Iman','Sijercic','U1','Sijera','password4','12345337890','instructor','2025-04-06 22:54:07'),(5,'Vedad','Saric','U2','Vedoo','password5','0987635324321','instructor','2025-04-06 22:54:07'),(6,'Ilma','Catic','','Ilmicaa','password6','1234562327890','user','2025-04-06 22:54:07'),(7,'Haris','Sakic','U2','haree','password7','093487654321','instructor','2025-04-06 22:54:07'),(9,'Regular','User',NULL,'user_4103','$2y$10$lqR5IImy3ffUOFlC0wnV5eeke5eLtZIn5wse1OGegAHXLWGOtmRZm',NULL,'user','2025-04-06 22:54:41'),(11,'Regular','User',NULL,'user_6848','$2y$10$pkDyPjPtBfilMZFd70Cd4O03gBNz6iwZdZV.zKmnbL9cla9W1gu7i',NULL,'user','2025-04-06 22:56:58'),(13,'Regular','User',NULL,'user_8329','$2y$10$rR/BUI7W5xpb.xqZCF0ILOm5gZM2Oqz.FgnC5QSvjeHbN5Vzhg2Oa',NULL,'user','2025-04-06 22:58:00'),(15,'Regular','User',NULL,'user_2455','$2y$10$FGjLQM8MlwAaYA.6wz/Gc.my3TEKhi6IZ7RhW4CmTMF5ih0zytnI2',NULL,'user','2025-04-06 23:01:42'),(17,'Regular','User',NULL,'user_6675','$2y$10$uaRPnb/hc1BxauUN/tjmBOj8Xyod8N0JA2zHwv0j3z8jhAa/vXn2y',NULL,'user','2025-04-06 23:01:59'),(19,'Regular','User',NULL,'user_2258','$2y$10$QqPgsdN8PvTBGMxyd6pqxeUAk.jeTBxIXD8YOkOpMASQxg6s9GQ3e',NULL,'user','2025-04-06 23:03:06'),(21,'Regular','User',NULL,'user_6060','$2y$10$pnjYfQsDIQSwCiB2Avik4.NzFTEJMwOBbY6i0Fa7Pp7ncU1oQhcbC',NULL,'user','2025-04-06 23:05:49'),(23,'Regular','User',NULL,'user_6505','$2y$10$HEd7CA24XxbdN1DIP/xej.8ItmZSevdhMhJMjKulyWR0srDJcjNJ6',NULL,'user','2025-04-06 23:06:51'),(25,'Regular','User',NULL,'user_4242','$2y$10$yjp/UfYB5EIlDju9F0nlme24uua9.WH8cmQuzajb1ZkLIIX3kp/Qa',NULL,'user','2025-04-06 23:12:53'),(27,'Regular','User',NULL,'user_3055','$2y$10$SGULTM/VWUksnkcEF.Hcj.Eyfu091V2MbYah3sUzmN3ydx7yIsppe',NULL,'user','2025-04-06 23:13:18'),(29,'Regular','User',NULL,'user_8397','$2y$10$0Tjp85Jx1mC6TJSOZ.vnJeSAkOpBzh6VfrAzPbe0WT3bWJRNRnxF2',NULL,'user','2025-04-06 23:13:53'),(31,'Regular','User',NULL,'user_2314','$2y$10$5ONqSUCwZIzFP2gWQJ47e.WSSz8GEn147HvZU65C/.55qu8WZ7aKm',NULL,'user','2025-04-06 23:17:36'),(33,'Regular','User',NULL,'user_4948','$2y$10$Wm9.BjZw9TNXdQvuiluks.WAu2oTsN3JLKl/7YraI1YXRLAqvQOLu',NULL,'user','2025-04-06 23:19:32'),(35,'Regular','User',NULL,'user_6946','$2y$10$IxEZ1kz04bNrrJwuFgCtzePcVBCno3ckW2yZf8kqTrDIGSpljhgqW',NULL,'user','2025-04-06 23:20:17'),(37,'Regular','User',NULL,'user_9762','$2y$10$SFRscWzaH9k9dbaChTPfwe1PRdI2aEgYANBO9ks4D3H/ePAjDBmjm',NULL,'user','2025-04-06 23:20:26'),(39,'Regular','User',NULL,'user_8456','$2y$10$1YbYr1Pg5U0BsQPOy0lWQeA6LYLiw2BDpMbvS8NnZsXYktna9Zd6.',NULL,'user','2025-04-06 23:37:46'),(41,'Regular','User',NULL,'user_8119','$2y$10$tdMgNufyjJpR95ZY.9F6yOahPnpOxPIhrtedjyUejEJ4dmsAx8QZy',NULL,'user','2025-04-06 23:39:46'),(43,'Regular','User',NULL,'user_1880','$2y$10$gmCYJduFkvhqK9URvDRodOZCqgUcI0d.5vi7962Hw5xdKjepg7qu6',NULL,'user','2025-04-06 23:41:52'),(45,'Regular','User',NULL,'user_8192','$2y$10$Coh30Cw8tOELxoJu8.EMEOxLg5JTaWjTH7HtunnjIYyj/6a/JJrXe',NULL,'user','2025-04-06 23:43:30'),(47,'Regular','User',NULL,'user_3862','$2y$10$azBzpNtVKV2BQcZGwM/vZe2tSkP8xqVSgMBYW7HuLrnZOUEcwdmzi',NULL,'user','2025-04-06 23:43:44'),(49,'Regular','User',NULL,'user_1101','$2y$10$ocd./pQg9291E6NUn6Mf0.92GEy2fnLOY1QQyL1eq/mTo8YFU862a',NULL,'user','2025-04-06 23:53:38'),(51,'Regular','User',NULL,'user_4389','$2y$10$SoMHNRPfoz3LBRXFLV9RBOIJJs7og9L0lOUdB3Q//u8tFnt/hjOaS',NULL,'user','2025-04-06 23:54:09'),(53,'Regular','User',NULL,'user_6531','$2y$10$0YEkSPQ/ekxVeq6aeoKVreqTcBodxoPuQz8rtyXjYdjbEuzGleqyi',NULL,'user','2025-04-06 23:54:15'),(55,'Regular','User',NULL,'user_8578','$2y$10$KhUpD1.iHMVHTMRorLnAJOYBZOO1B7NMGZ1s5uAeP.w7ZzEWcufF.',NULL,'user','2025-04-06 23:55:43'),(57,'Regular','User',NULL,'user_8993','$2y$10$dtcymgy3ZomlI/FxzPg0qOA5ekqC3oPaeldG.riETcSpX/cew0UmC',NULL,'user','2025-04-06 23:57:29'),(59,'Regular','User',NULL,'user_4993','$2y$10$4SoV.2lm2eVRTbl7PD.W8euMIpXZ5QezW9SG8bab78ByTv9IOUhpu',NULL,'user','2025-04-06 23:57:33'),(61,'Regular','User',NULL,'user_2057','$2y$10$ZXe6PmBaWak9s568ERJeCu4HJLUy4weG4lYjpjJCkA0QHAfHAhtu6',NULL,'user','2025-04-07 00:00:36'),(63,'Regular','User',NULL,'user_3572','$2y$10$BjIPV7D0s51wJG1VHOhEwuUUSeUaBKTc8PCDNFNLEfaxmg01yugSO',NULL,'user','2025-04-07 00:01:00'),(65,'Regular','User',NULL,'user_2560','$2y$10$rjyKePcrNfU6.BIVO1N/3O95cHLlBPXiUj08b8frVyMGAtv/WTS/i',NULL,'user','2025-04-07 00:05:18'),(67,'Regular','User',NULL,'user_4446','$2y$10$Ez8/eJ7sqFOngkZk5j8Cd.OE.mheR8apXx/7pwqG0m.YaJEcyZWcy',NULL,'user','2025-04-07 00:06:23'),(69,'Regular','User',NULL,'user_5413','$2y$10$3w8L1svlQnAKg.unndV2mORFIM45n.jbOkBFoGc0t3tEMQcxxMuou',NULL,'user','2025-04-07 00:06:52'),(71,'Regular','User',NULL,'user_5757','$2y$10$ayKuvCIfPon34AcO0FN5S.GNMKlZa6YRevF/K9nE9ZR3wqaW4jMma',NULL,'user','2025-04-07 00:07:41'),(73,'Regular','User',NULL,'user_3422','$2y$10$H/KEf1KcLXp8n3Sf23lQvuignEg22SIasTGd7zAgeJTVVfZfSl8Y.',NULL,'user','2025-04-07 00:12:30'),(75,'Regular','User',NULL,'user_4910','$2y$10$mPxQo0IoOV/9oHYB9CA2eeHY1/cMAqLS3TWAXLgmZViQ9.yJHy9fa',NULL,'user','2025-04-07 00:20:34'),(77,'Regular','User',NULL,'user_5817','$2y$10$msmYfgfpnFt9al.kri.ivec2cncJ3GqbRqtMskOwq7uz2lRWsJ8qS',NULL,'user','2025-04-07 00:23:12'),(79,'Regular','User',NULL,'user_4521','$2y$10$gIzhOCFDbJ3daO/8ImJr9OsBJoL48Fa16sev9mqQBjOz.Z3KYkmbS',NULL,'user','2025-04-07 00:23:52'),(81,'Regular','User',NULL,'user_1423','$2y$10$DQpdhAtUb7BS7OAi3BDCVemxib4wBYi7D9FFlWJuJhY4LbILon4Zm',NULL,'user','2025-04-07 00:25:08'),(83,'Regular','User',NULL,'user_4681','$2y$10$kHFWV63NK2yIgfaqJq9vUO5l1xqlMqKdbiAofySHHEiKZlx6Pn/Ay',NULL,'user','2025-04-07 00:28:20'),(85,'Regular','User',NULL,'user_1737','$2y$10$25q84UzwZAcFROMKXNTUCeousNYc0GMqPHZqJWmFPikIM1rftYLFu',NULL,'user','2025-04-07 00:28:44'),(87,'Regular','User',NULL,'user_5790','$2y$10$ItzCGOg.HEwy9P/SRlMLP.OtHawCmSAzHYXUmFnFCOCgIHvS90RMy',NULL,'user','2025-04-07 00:29:45'),(89,'Regular','User',NULL,'user_8232','$2y$10$7odBCao8y5cM/U0waivytet39JlVpycLz7K6zQILW/KHpnw6v7k3.',NULL,'user','2025-04-07 00:30:59'),(91,'Regular','User',NULL,'user_4611','$2y$10$KMZmTAbjxjiMlp.YzmVyt.Lopl103rsJMrCCZgWKoiAj7RLcUoj7K',NULL,'user','2025-04-07 00:33:46'),(93,'Regular','User',NULL,'user_6883','$2y$10$.bB998HmDeh15Ci82ffhJuDomMA2pEX89Pws3imN7mfgvaYbanTa2',NULL,'user','2025-04-07 00:36:09'),(95,'Regular','User',NULL,'user_8561','$2y$10$9s3dZipj9/.sfiU/dYU3..8pdZK6FXeVbZ3/gboA/ZW7XpmDA6Eca',NULL,'user','2025-04-07 00:36:31'),(97,'Regular','User',NULL,'user_2747','$2y$10$TAIShTtesVBvgjXlDj5Q4OoVjwVJVKuXIpixvfq0Ma0o9ggtAQtYy',NULL,'user','2025-04-07 00:37:18'),(99,'Regular','User',NULL,'user_7556','$2y$10$D81UZBxmxM8ILGVzfI.r.uDUa3KuzhvfvFMswQZK5YanGbCGFNGhy',NULL,'user','2025-04-07 00:37:28'),(101,'Regular','User',NULL,'user_5494','$2y$10$YYGFivxKuon5fEl7fiPFV.7Jm0M9GRtWRBKGt4Y038exGZAUc6ms2',NULL,'user','2025-04-09 08:27:15'),(103,'Regular','User',NULL,'user_5250','$2y$10$greplZ.5IW/2AozusyycQu5m.4zjRzEpv0XfPaT3i.aixI1tHiI.K',NULL,'user','2025-04-09 08:46:12');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'web_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-04 23:09:39
