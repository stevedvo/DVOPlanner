ALTER TABLE `events` CHANGE `type` `type` ENUM('Task','Meeting','Appointment','Appt') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
UPDATE `events` SET `type`='Appt' WHERE type='Appointment';
ALTER TABLE `events` CHANGE `type` `type` ENUM('Task','Meeting','Appt') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;