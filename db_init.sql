CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('Task','Meeting','Appointment') NOT NULL,
  `description` varchar(60) NOT NULL,
  `duration` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `instances` (
  `instance_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `travel_time` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('To Do','Complete','Cancelled','Postponed') NOT NULL,
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB;
