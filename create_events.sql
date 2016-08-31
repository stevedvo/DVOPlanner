CREATE TABLE IF NOT EXISTS events
(
	event_id		INT UNSIGNED NOT NULL AUTO_INCREMENT,
	type			ENUM("Task","Meeting","Appointment") NOT NULL,
	description		VARCHAR(60) NOT NULL,
	duration		INT UNSIGNED,
	PRIMARY KEY (event_id)
);