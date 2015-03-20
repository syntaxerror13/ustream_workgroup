CREATE TABLE `wg_user` (
  `user_name` varchar(100) NOT NULL,
  PRIMARY KEY (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wg_project` (
  `project_name` varchar(100) NOT NULL,
  `description` varchar(1000) DEFAULT "",
  `owner_name` varchar(100) NOT NULL,
  PRIMARY KEY (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wg_member` (
  `project_name` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `is_focus` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`project_name`, `user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `wg_log` (
  `id` int auto_increment not null,
  `timestamp` datetime not null default "2000-01-01 00:00:00",
  `project_name` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `message` varchar(1000) DEFAULT "",
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table wg_log add column action varchar(32) after user_name;

alter table wg_project add column slack_room varchar(32) after owner_name;
