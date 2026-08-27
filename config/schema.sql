-- ExMassTree product — auth schema only
CREATE DATABASE IF NOT EXISTS `xmasstree` CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER IF NOT EXISTS 'xmasstree'@'localhost' IDENTIFIED BY 'xmasstree';
GRANT ALL PRIVILEGES ON `xmasstree`.* TO 'xmasstree'@'localhost';
FLUSH PRIVILEGES;
USE `xmasstree`;

CREATE TABLE IF NOT EXISTS `t_a_group` (
  `n_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `isAdmin` int(11) NOT NULL,
  `desctopUrl` text NOT NULL,
  PRIMARY KEY (`n_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `t_a_user` (
  `n_id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `name` text NOT NULL,
  `password` mediumtext NOT NULL,
  `group_id` int(11) NOT NULL,
  `otp_secret` varchar(64) NOT NULL DEFAULT '',
  `otp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `otp_last_slot` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`n_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `t_a_user_group` (
  `n_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`n_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `t_a_group_component` (
  `n_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `componentName` varchar(50) NOT NULL,
  PRIMARY KEY (`n_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `t_a_actionlog` (
  `username` varchar(50) NOT NULL,
  `action` int(11) NOT NULL,
  `element` varchar(100) NOT NULL,
  `elementId` int(11) NOT NULL,
  `dataBefore` text NOT NULL,
  `dataAfter` text NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `t_a_group` (`n_id`, `title`, `isAdmin`, `desctopUrl`)
VALUES (1, 'Administrators', 1, '?personal')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `isAdmin`=VALUES(`isAdmin`), `desctopUrl`=VALUES(`desctopUrl`);

-- login: admin / password: admin
INSERT INTO `t_a_user` (`n_id`, `login`, `name`, `password`, `group_id`, `otp_secret`, `otp_enabled`, `otp_last_slot`)
VALUES (1, 'admin', 'Administrator', '*514FC2971F3E94BB16F25C396219DFDF01D02443', 1, '', 0, 0)
ON DUPLICATE KEY UPDATE
  `password`=VALUES(`password`),
  `group_id`=VALUES(`group_id`),
  `name`=VALUES(`name`);
