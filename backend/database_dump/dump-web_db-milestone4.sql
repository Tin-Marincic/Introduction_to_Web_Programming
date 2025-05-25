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
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `availabilitycalendar`
--

LOCK TABLES `availabilitycalendar` WRITE;
/*!40000 ALTER TABLE `availabilitycalendar` DISABLE KEYS */;
INSERT INTO `availabilitycalendar` VALUES (73,109,'2025-05-26','active'),(74,109,'2025-05-27','active'),(75,109,'2025-05-28','active'),(76,109,'2025-05-29','active'),(77,109,'2025-05-30','active'),(78,109,'2025-05-25','active'),(79,109,'2025-05-24','not_active');
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (2,107,109,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,'2025-05-29',3,'11:00:00','pending'),(3,107,110,3,'Private_instruction',NULL,NULL,NULL,NULL,NULL,1,NULL,2,NULL,NULL,'2025-05-26',4,'12:00:00','confirmed'),(32,114,108,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-24',1,'10:00:00','confirmed'),(33,114,108,3,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-24',1,'12:00:00','confirmed'),(34,114,112,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-24',1,'11:00:00','confirmed'),(35,114,108,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-24',1,'11:00:00','confirmed'),(36,114,108,2,'Private_instruction',NULL,'week1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-30',1,'10:00:00','confirmed'),(37,114,NULL,4,'Ski_school',3,'week1',1,1,1,1,1,1,1,'Allergic to nuts',NULL,NULL,NULL,NULL),(38,114,109,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-30',1,'12:00:00','confirmed'),(39,114,NULL,4,'Ski_school',2,'week1',2,0,0,2,0,0,1,'',NULL,NULL,NULL,NULL),(40,114,NULL,4,'Ski_school',4,'week1',4,0,0,4,0,0,4,'rgsedrfg',NULL,NULL,NULL,NULL),(41,114,110,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-28',2,'12:00:00','confirmed'),(42,114,NULL,4,'Ski_school',5,'week1',5,0,0,5,0,0,0,'',NULL,NULL,NULL,NULL),(43,114,NULL,4,'Ski_school',3,'week3',1,1,1,1,1,1,3,'sta ba ovo ???',NULL,NULL,NULL,NULL),(44,114,113,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-24',3,'10:00:00','confirmed'),(45,114,NULL,4,'Ski_school',1,'week1',1,0,0,1,0,0,0,'',NULL,NULL,NULL,NULL),(60,114,NULL,4,'Ski_school',4,'week2',2,2,0,2,2,0,1,'',NULL,NULL,NULL,NULL),(61,114,109,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-27',3,'11:00:00','confirmed'),(62,114,109,2,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-27',1,'10:00:00','confirmed'),(63,125,NULL,4,'Ski_school',4,'week2',4,0,0,0,4,0,0,'',NULL,NULL,NULL,NULL),(64,125,109,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-27',1,'14:00:00','confirmed'),(65,125,109,1,'Private_instruction',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-25',2,'12:00:00','confirmed');
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
  `booking_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `grade` enum('1','2','3','4','5') DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (79,NULL,114,'4','fwsfaws'),(80,NULL,114,'4','dghdfg'),(81,NULL,114,'4','fgjdf'),(82,NULL,114,'3','ghdfrghj'),(83,NULL,114,'4','gthdg'),(84,NULL,114,'3','dfhgfdhg'),(85,NULL,114,'4','ehgserhegr'),(86,NULL,114,'4','asjkuhgdgujas');
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
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'One on One','Personalized instruction',60,'2026-01-01','2026-03-20'),(2,'One on Two','good',100,'2026-01-01','2026-03-20'),(3,'One on Three','Ideal for small groups',150,'2026-01-01','2026-03-20'),(4,'Ski school week 1','Comprehensive training',500,'2026-01-01','2026-01-07'),(5,'Ski school week 2','nez',500,'2026-01-08','2026-01-14'),(6,'Ski school week 3','nezzz',500,'2026-01-15','2026-01-21'),(7,'Ski school week 4','neznam',500,'2026-01-22','2026-01-28');
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
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (104,'Malik','Selimovic',NULL,'tin@gmail.com','$2y$10$YsvLJAt4HbSUbvNRztWcfuEujONVjCl6YZOweLrV8MwpVn/EHPQs2',NULL,'user',NULL),(106,'admin',NULL,NULL,'semi@gmail.com','$2y$10$KnEc9jg.ir5gsH9ZkIJHgOVVRlz/DN1HVzgvA.MXZiHmUJw9iLheW',NULL,'admin',NULL),(107,'Halima','zdenko',NULL,'user@gmail.com','$2y$10$e.SiePpbFHekDqxuLkGkD.LLBofnACdQgrc2.okChChf/.IjK/TJS',NULL,'user',NULL),(108,'Haris','Saric','U2','unisportharis.saric@gmail.com','$2y$10$MFmOFN.Zw86E0fRxlW98jeQMuFLnO7agk6ScOhf43SFbzraK6VuLy',NULL,'instructor',NULL),(109,'Vedad','Saric','U1','unisportvedad.saric@gmail.com','$2y$10$Jgdik.JEQFGCKd9m/G0rkOwmY1zgbhFl5d8uevyYLqdgslsOZvvVm',NULL,'instructor',NULL),(110,'Muhamed','Saric','U3','unisportmuhamed.saric@gmail.com','$2y$10$D3h.omqn4DXKWmvg1pNeBuKlECaGYqSDNgPeOHht0Je83FyNInrEW',NULL,'instructor',NULL),(111,'Iman','Sijercic','U1','unisportiman.sijercic@gmail.com','$2y$10$Tc/2U17A8uikqliBOUINiedlv8vPgVmJFDE6.tc7IwK08PQ7W2mGa',NULL,'instructor',NULL),(112,'Ilma','Catic','U2','unisportilma.catic@gmail.com','$2y$10$8yRGjpfrf0liPJMgvw3RMOsw5FmICbjb.9tQZMWHFo0TC5/OzztH2',NULL,'instructor',NULL),(113,'Tin','Marincic','U1','unisporttin.marincic@gmail.com','$2y$10$lzuCGRAexJwF0APBISC9l.iIqjt3ARaQhYcg52UvCGcZIvozEPMBW',NULL,'instructor',NULL),(114,NULL,NULL,NULL,'laga@gmail.com','$2y$10$kqSR379iX3pyImhhe7bJceblLaw2HU7WLpyx.hhD.Q1WXxDqCM/2O',NULL,'user',NULL),(119,'Ja','Ja',NULL,'ja@gmail.com','$2y$10$svZxkNIRImi.rNTw7Tn7oOemyuolngB/TqAQFMzmugFYocWmHNMk.',NULL,'user',NULL),(122,'lagson','lalak',NULL,'djwhvvjd@gmail.com','$2y$10$Ist1e4S59t5HjF6xplN/Z.5BonfdyyLBi5zhN6Fhw.U252bnCSRQO',NULL,'user',NULL),(123,'lagsonnnnn','lalak',NULL,'djwhvvjddd@gmail.com','$2y$10$/9e44W4IZRgD/1lpR2iDAO4/rxbAD0r9QzFjQAMCGN6FlKEAZh3WC',NULL,'user',NULL),(125,'Maja','Majic',NULL,'majamajic@gmail.com','$2y$10$sCKdde3a6P./nGgtuS1G6OJHRfY4tPLA1xQebbg0A4i/j53yUL3nm',NULL,'user',NULL);
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

-- Dump completed on 2025-05-25 22:31:10
