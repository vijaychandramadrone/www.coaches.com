
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- event
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `event`;


CREATE TABLE `event`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`fmid` INTEGER,
	`name` TEXT,
	`location` TEXT,
	`course_type_id` VARCHAR(32),
	`start_date` VARCHAR(32),
	`end_date` VARCHAR(32),
	`max_enrollment` INTEGER,
	`current_enrollment` INTEGER,
	`max_waitlist` INTEGER,
	`current_waitlist` INTEGER,
	`max_assisting_enrollment` INTEGER,
	`current_assisting_enrollment` INTEGER,
	`max_assisting_waitlist` INTEGER,
	`current_assisting_waitlist` INTEGER,
	`booking_link` TEXT,
	`leader_name` TEXT,
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`extra1` TEXT,
	`extra2` TEXT,
	`extra3` TEXT,
	`extra4` TEXT,
	`extra5` TEXT,
	`extra6` TEXT,
	`extra7` TEXT,
	`extra8` TEXT,
	`extra9` TEXT,
	`extra10` TEXT,
	`extra11` TEXT,
	`extra12` TEXT,
	`extra13` TEXT,
	`extra14` TEXT,
	`extra15` TEXT,
	`extra16` TEXT,
	`extra17` TEXT,
	`extra18` TEXT,
	`extra19` TEXT,
	`extra20` TEXT,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- student
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `student`;


CREATE TABLE `student`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`fmid` INTEGER,
	`first_name` VARCHAR(100),
	`last_name` VARCHAR(100),
	`email` VARCHAR(100),
	`reset_key` VARCHAR(100),
	`new_email` VARCHAR(100),
	`new_email_request_time` DATETIME,
	`home_address` TEXT,
	`city` TEXT,
	`state_prov` TEXT,
	`country` TEXT,
	`zip_postal` TEXT,
	`home_phone` VARCHAR(100),
	`cell_phone` VARCHAR(100),
	`business_phone` VARCHAR(100),
	`level` INTEGER,
	`password` VARCHAR(100),
	`salt` VARCHAR(100),
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`extra1` TEXT,
	`extra2` TEXT,
	`extra3` TEXT,
	`extra4` TEXT,
	`extra5` TEXT,
	`extra6` TEXT,
	`extra7` TEXT,
	`extra8` TEXT,
	`extra9` TEXT,
	`extra10` TEXT,
	`extra11` TEXT,
	`extra12` TEXT,
	`extra13` TEXT,
	`extra14` TEXT,
	`extra15` TEXT,
	`extra16` TEXT,
	`extra17` TEXT,
	`extra18` TEXT,
	`extra19` TEXT,
	`extra20` TEXT,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- enrollment
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `enrollment`;


CREATE TABLE `enrollment`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`student_id` INTEGER,
	`event_id` INTEGER,
	`date` DATETIME,
	`start_date` VARCHAR(32),
	`end_date` VARCHAR(32),
	`type` VARCHAR(32),
	`updated_at` DATETIME,
	`created_at` DATETIME,
	`extra1` TEXT,
	`extra2` TEXT,
	`extra3` TEXT,
	`extra4` TEXT,
	`extra5` TEXT,
	`extra6` TEXT,
	`extra7` TEXT,
	`extra8` TEXT,
	`extra9` TEXT,
	`extra10` TEXT,
	`extra11` TEXT,
	`extra12` TEXT,
	`extra13` TEXT,
	`extra14` TEXT,
	`extra15` TEXT,
	`extra16` TEXT,
	`extra17` TEXT,
	`extra18` TEXT,
	`extra19` TEXT,
	`extra20` TEXT,
	PRIMARY KEY (`id`),
	INDEX `enrollment_FI_1` (`student_id`),
	CONSTRAINT `enrollment_FK_1`
		FOREIGN KEY (`student_id`)
		REFERENCES `student` (`id`)
		ON DELETE CASCADE,
	INDEX `enrollment_FI_2` (`event_id`),
	CONSTRAINT `enrollment_FK_2`
		FOREIGN KEY (`event_id`)
		REFERENCES `event` (`id`)
)Type=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
