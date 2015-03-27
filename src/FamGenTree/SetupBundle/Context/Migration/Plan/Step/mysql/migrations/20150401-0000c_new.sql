ALTER TABLE `###PREFIX###user`
CHANGE COLUMN user_name user_name VARCHAR(100) NOT NULL,
ADD COLUMN username_canonical VARCHAR(255) NOT NULL
AFTER user_name,
CHANGE COLUMN real_name real_name VARCHAR(100) DEFAULT NULL,
CHANGE COLUMN email email VARCHAR(255) NOT NULL,
ADD COLUMN email_canonical VARCHAR(255) NOT NULL
AFTER email,
ADD COLUMN enabled TINYINT(1) NOT NULL,
CHANGE COLUMN password password VARCHAR(255) NOT NULL,
ADD COLUMN salt VARCHAR(255) NOT NULL
AFTER password,
ADD COLUMN password_algorithm VARCHAR(15) DEFAULT 'bcrypt_10' NOT NULL
AFTER password,
ADD COLUMN last_login DATETIME DEFAULT NULL,
ADD COLUMN locked TINYINT(1) NOT NULL,
ADD COLUMN expired TINYINT(1) NOT NULL,
ADD COLUMN expires_at DATETIME DEFAULT NULL,
ADD COLUMN confirmation_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN password_requested_at DATETIME DEFAULT NULL,
ADD COLUMN roles LONGTEXT NOT NULL
COMMENT '(DC2Type:array)',
ADD COLUMN credentials_expired TINYINT(1) NOT NULL,
ADD COLUMN credentials_expire_at DATETIME DEFAULT NULL,
ADD UNIQUE INDEX `###PREFIX###UNIQ_username_cano` (username_canonical),
ADD UNIQUE INDEX `###PREFIX###UNIQ_email_cano` (email_canonical),
DROP INDEX `###PREFIX###user_ix1`,
DROP INDEX `###PREFIX###user_ix2`;

CREATE TABLE `###PREFIX###config` (
  id_config  INT AUTO_INCREMENT NOT NULL,
  section    VARCHAR(50)        NOT NULL,
  config_key VARCHAR(255)       NOT NULL,
  value      LONGTEXT           DEFAULT NULL,
  PRIMARY KEY (id_config),
  UNIQUE KEY `###PREFIX###uniq_section_config_key` (section, config_key)
)
  DEFAULT CHARACTER SET utf8
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;