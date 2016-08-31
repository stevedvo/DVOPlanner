CREATE TABLE IF NOT EXISTS instances
(
	instance_id		INT UNSIGNED NOT NULL AUTO_INCREMENT,
	event_id		INT UNSIGNED NOT NULL,
	date			date NOT NULL,
	time			TIME,
	travel_time		INT UNSIGNED,
	status			ENUM("To Do","Complete","Cancelled","Postponed") NOT NULL,
	PRIMARY KEY (instance_id)
);