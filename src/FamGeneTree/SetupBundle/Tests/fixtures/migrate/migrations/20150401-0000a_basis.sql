CREATE TABLE IF NOT EXISTS `###PREFIX###gedcom` (
  gedcom_id   INTEGER AUTO_INCREMENT NOT NULL,
  gedcom_name VARCHAR(255)           NOT NULL,
  sort_order  INTEGER                NOT NULL DEFAULT 0,
  PRIMARY KEY (gedcom_id),
  UNIQUE KEY `###PREFIX###gedcom_ix1` (gedcom_name),
  KEY `###PREFIX###gedcom_ix2` (sort_order)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###site_setting` (
  setting_name  VARCHAR(32)  NOT NULL,
  setting_value VARCHAR(255) NOT NULL,
  PRIMARY KEY (setting_name)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###gedcom_setting` (
  gedcom_id     INTEGER      NOT NULL,
  setting_name  VARCHAR(32)  NOT NULL,
  setting_value VARCHAR(255) NOT NULL,
  PRIMARY KEY (gedcom_id, setting_name),
  FOREIGN KEY `###PREFIX###gedcom_setting_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###user` (
  user_id   INTEGER AUTO_INCREMENT NOT NULL,
  user_name VARCHAR(32)            NOT NULL,
  real_name VARCHAR(64)            NOT NULL,
  email     VARCHAR(64)            NOT NULL,
  password  VARCHAR(128)           NOT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY `###PREFIX###user_ix1` (user_name),
  UNIQUE KEY `###PREFIX###user_ix2` (email)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###user_setting` (
  user_id       INTEGER      NOT NULL,
  setting_name  VARCHAR(32)  NOT NULL,
  setting_value VARCHAR(255) NOT NULL,
  PRIMARY KEY (user_id, setting_name),
  FOREIGN KEY `###PREFIX###user_setting_fk1` (user_id) REFERENCES `###PREFIX###user` (user_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###user_gedcom_setting` (
  user_id       INTEGER      NOT NULL,
  gedcom_id     INTEGER      NOT NULL,
  setting_name  VARCHAR(32)  NOT NULL,
  setting_value VARCHAR(255) NOT NULL,
  PRIMARY KEY (user_id, gedcom_id, setting_name),
  FOREIGN KEY `###PREFIX###user_gedcom_setting_fk1` (user_id) REFERENCES `###PREFIX###user` (user_id) /* ON DELETE CASCADE */,
  FOREIGN KEY `###PREFIX###user_gedcom_setting_fk2` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###log` (
  log_id      INTEGER AUTO_INCREMENT                                              NOT NULL,
  log_time    TIMESTAMP                                                           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_type    ENUM('auth', 'config', 'debug', 'edit', 'error', 'media', 'search') NOT NULL,
  log_message TEXT                                                                NOT NULL,
  ip_address  VARCHAR(40)                                                         NOT NULL,
  user_id     INTEGER                                                             NULL,
  gedcom_id   INTEGER                                                             NULL,
  PRIMARY KEY (log_id),
  KEY `###PREFIX###log_ix1` (log_time),
  KEY `###PREFIX###log_ix2` (log_type),
  KEY `###PREFIX###log_ix3` (ip_address),
  FOREIGN KEY `###PREFIX###log_fk1` (user_id) REFERENCES `###PREFIX###user` (user_id) /* ON DELETE SET NULL */,
  FOREIGN KEY `###PREFIX###log_fk2` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE SET NULL */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###change` (
  change_id   INTEGER AUTO_INCREMENT                  NOT NULL,
  change_time TIMESTAMP                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status      ENUM('accepted', 'pending', 'rejected') NOT NULL DEFAULT 'pending',
  gedcom_id   INTEGER                                 NOT NULL,
  xref        VARCHAR(20)                             NOT NULL,
  old_gedcom  MEDIUMTEXT                              NOT NULL,
  new_gedcom  MEDIUMTEXT                              NOT NULL,
  user_id     INTEGER                                 NOT NULL,
  PRIMARY KEY (change_id),
  KEY `###PREFIX###change_ix1` (gedcom_id, status, xref),
  FOREIGN KEY `###PREFIX###change_fk1` (user_id) REFERENCES `###PREFIX###user` (user_id) /* ON DELETE RESTRICT */,
  FOREIGN KEY `###PREFIX###change_fk2` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###message` (
  message_id INTEGER AUTO_INCREMENT NOT NULL,
  sender     VARCHAR(64)            NOT NULL, -- username or email address
  ip_address VARCHAR(40)            NOT NULL, -- long enough for IPv6
  user_id    INTEGER                NOT NULL,
  subject    VARCHAR(255)           NOT NULL,
  body       TEXT                   NOT NULL,
  created    TIMESTAMP              NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  FOREIGN KEY `###PREFIX###message_fk1` (user_id) REFERENCES `###PREFIX###user` (user_id) /* ON DELETE RESTRICT */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###default_resn` (
  default_resn_id INTEGER AUTO_INCREMENT                             NOT NULL,
  gedcom_id       INTEGER                                            NOT NULL,
  xref            VARCHAR(20)                                        NULL,
  tag_type        VARCHAR(15)                                        NULL,
  resn            ENUM ('none', 'privacy', 'confidential', 'hidden') NOT NULL,
  comment         VARCHAR(255)                                       NULL,
  updated         TIMESTAMP                                          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (default_resn_id),
  UNIQUE KEY `###PREFIX###default_resn_ix1` (gedcom_id, xref, tag_type),
  FOREIGN KEY `###PREFIX###default_resn_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id)
)
  ENGINE = InnoDB
  COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `###PREFIX###individuals` (
  i_id     VARCHAR(20)         NOT NULL,
  i_file   INTEGER             NOT NULL,
  i_rin    VARCHAR(20)         NOT NULL,
  i_sex    ENUM('U', 'M', 'F') NOT NULL,
  i_gedcom MEDIUMTEXT          NOT NULL,
  PRIMARY KEY (i_id, i_file),
  UNIQUE KEY `###PREFIX###individuals_ix1` (i_file, i_id)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###families` (
  f_id      VARCHAR(20) NOT NULL,
  f_file    INTEGER     NOT NULL,
  f_husb    VARCHAR(20) NULL,
  f_wife    VARCHAR(20) NULL,
  f_gedcom  MEDIUMTEXT  NOT NULL,
  f_numchil INTEGER     NOT NULL,
  PRIMARY KEY (f_id, f_file),
  UNIQUE KEY `###PREFIX###families_ix1` (f_file, f_id),
  KEY `###PREFIX###families_ix2` (f_husb),
  KEY `###PREFIX###families_ix3` (f_wife)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###places` (
  p_id          INTEGER AUTO_INCREMENT NOT NULL,
  p_place       VARCHAR(150)           NULL,
  p_parent_id   INTEGER                NULL,
  p_file        INTEGER                NOT NULL,
  p_std_soundex TEXT                   NULL,
  p_dm_soundex  TEXT                   NULL,
  PRIMARY KEY (p_id),
  KEY `###PREFIX###places_ix1` (p_file, p_place),
  UNIQUE KEY `###PREFIX###places_ix2` (p_parent_id, p_file, p_place)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###placelinks` (
  pl_p_id INTEGER     NOT NULL,
  pl_gid  VARCHAR(20) NOT NULL,
  pl_file INTEGER     NOT NULL,
  PRIMARY KEY (pl_p_id, pl_gid, pl_file),
  KEY `###PREFIX###placelinks_ix1` (pl_p_id),
  KEY `###PREFIX###placelinks_ix2` (pl_gid),
  KEY `###PREFIX###placelinks_ix3` (pl_file)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###dates` (
  d_day        TINYINT                                                                                                    NOT NULL,
  d_month      CHAR(5)                                                                                                    NULL,
  d_mon        TINYINT                                                                                                    NOT NULL,
  d_year       SMALLINT                                                                                                   NOT NULL,
  d_julianday1 MEDIUMINT                                                                                                  NOT NULL,
  d_julianday2 MEDIUMINT                                                                                                  NOT NULL,
  d_fact       VARCHAR(15)                                                                                                NOT NULL,
  d_gid        VARCHAR(20)                                                                                                NOT NULL,
  d_file       INTEGER                                                                                                    NOT NULL,
  d_type       ENUM ('@#DGREGORIAN@', '@#DJULIAN@', '@#DHEBREW@', '@#DFRENCH R@', '@#DHIJRI@', '@#DROMAN@', '@#DJALALI@') NOT NULL,
  KEY `###PREFIX###dates_ix1` (d_day),
  KEY `###PREFIX###dates_ix2` (d_month),
  KEY `###PREFIX###dates_ix3` (d_mon),
  KEY `###PREFIX###dates_ix4` (d_year),
  KEY `###PREFIX###dates_ix5` (d_julianday1),
  KEY `###PREFIX###dates_ix6` (d_julianday2),
  KEY `###PREFIX###dates_ix7` (d_gid),
  KEY `###PREFIX###dates_ix8` (d_file),
  KEY `###PREFIX###dates_ix9` (d_type),
  KEY `###PREFIX###dates_ix10` (d_fact, d_gid)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###media` (
  m_id       VARCHAR(20)  NOT NULL,
  m_ext      VARCHAR(6)   NULL,
  m_type     VARCHAR(20)  NULL,
  m_titl     VARCHAR(255) NULL,
  m_filename VARCHAR(512) NULL,
  m_file     INTEGER      NOT NULL,
  m_gedcom   MEDIUMTEXT   NULL,
  PRIMARY KEY (m_file, m_id),
  UNIQUE KEY `###PREFIX###media_ix1` (m_id, m_file),
  KEY `###PREFIX###media_ix2` (m_ext, m_type),
  KEY `###PREFIX###media_ix3` (m_titl)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###next_id` (
  gedcom_id   INTEGER     NOT NULL,
  record_type VARCHAR(15) NOT NULL,
  next_id     DECIMAL(20) NOT NULL,
  PRIMARY KEY (gedcom_id, record_type),
  FOREIGN KEY `###PREFIX###next_id_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###other` (
  o_id     VARCHAR(20) NOT NULL,
  o_file   INTEGER     NOT NULL,
  o_type   VARCHAR(15) NOT NULL,
  o_gedcom MEDIUMTEXT  NULL,
  PRIMARY KEY (o_id, o_file),
  UNIQUE KEY `###PREFIX###other_ix1` (o_file, o_id)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###sources` (
  s_id     VARCHAR(20)  NOT NULL,
  s_file   INTEGER      NOT NULL,
  s_name   VARCHAR(255) NOT NULL,
  s_gedcom MEDIUMTEXT   NOT NULL,
  PRIMARY KEY (s_id, s_file),
  UNIQUE KEY `###PREFIX###sources_ix1` (s_file, s_id),
  KEY `###PREFIX###sources_ix2` (s_name)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###link` (
  l_file INTEGER     NOT NULL,
  l_from VARCHAR(20) NOT NULL,
  l_type VARCHAR(15) NOT NULL,
  l_to   VARCHAR(20) NOT NULL,
  PRIMARY KEY (l_from, l_file, l_type, l_to),
  UNIQUE KEY `###PREFIX###link_ix1` (l_to, l_file, l_type, l_from)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###name` (
  n_file             INTEGER      NOT NULL,
  n_id               VARCHAR(20)  NOT NULL,
  n_num              INTEGER      NOT NULL,
  n_type             VARCHAR(15)  NOT NULL,
  n_sort             VARCHAR(255) NOT NULL, -- e.g. “GOGH,VINCENT WILLEM”
  n_full             VARCHAR(255) NOT NULL, -- e.g. “Vincent Willem van GOGH”
                -- These fields are only used for INDI records
  n_surname          VARCHAR(255) NULL, -- e.g. “van GOGH”
  n_surn             VARCHAR(255) NULL, -- e.g. “GOGH”
  n_givn             VARCHAR(255) NULL, -- e.g. “Vincent Willem”
  n_soundex_givn_std VARCHAR(255) NULL,
  n_soundex_surn_std VARCHAR(255) NULL,
  n_soundex_givn_dm  VARCHAR(255) NULL,
  n_soundex_surn_dm  VARCHAR(255) NULL,
  PRIMARY KEY (n_id, n_file, n_num),
  KEY `###PREFIX###name_ix1` (n_full, n_id, n_file),
  KEY `###PREFIX###name_ix2` (n_surn, n_file, n_type, n_id),
  KEY `###PREFIX###name_ix3` (n_givn, n_file, n_type, n_id)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###module` (
  module_name   VARCHAR(32)                 NOT NULL,
  status        ENUM('enabled', 'disabled') NOT NULL DEFAULT 'enabled',
  tab_order     INTEGER                     NULL,
  menu_order    INTEGER                     NULL,
  sidebar_order INTEGER                     NULL,
  PRIMARY KEY (module_name)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###module_setting` (
  module_name   VARCHAR(32) NOT NULL,
  setting_name  VARCHAR(32) NOT NULL,
  setting_value MEDIUMTEXT  NOT NULL,
  PRIMARY KEY (module_name, setting_name),
  FOREIGN KEY `###PREFIX###module_setting_fk1` (module_name) REFERENCES `###PREFIX###module` (module_name) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###module_privacy` (
  module_name  VARCHAR(32)                                                         NOT NULL,
  gedcom_id    INTEGER                                                             NOT NULL,
  component    ENUM('block', 'chart', 'menu', 'report', 'sidebar', 'tab', 'theme') NOT NULL,
  access_level TINYINT                                                             NOT NULL,
  PRIMARY KEY (module_name, gedcom_id, component),
  FOREIGN KEY `###PREFIX###module_privacy_fk1` (module_name) REFERENCES `###PREFIX###module` (module_name) /* ON DELETE CASCADE */,
  FOREIGN KEY `###PREFIX###module_privacy_fk2` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id)   /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###block` (
  block_id    INTEGER AUTO_INCREMENT NOT NULL,
  gedcom_id   INTEGER                NULL,
  user_id     INTEGER                NULL,
  xref        VARCHAR(20)            NULL,
  location    ENUM('main', 'side')   NULL,
  block_order INTEGER                NOT NULL,
  module_name VARCHAR(32)            NOT NULL,
  PRIMARY KEY (block_id),
  FOREIGN KEY `###PREFIX###block_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id), /* ON DELETE CASCADE */
  FOREIGN KEY `###PREFIX###block_fk2` (user_id) REFERENCES `###PREFIX###user` (user_id), /* ON DELETE CASCADE */
  FOREIGN KEY `###PREFIX###block_fk3` (module_name) REFERENCES `###PREFIX###module` (module_name) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###block_setting` (
  block_id      INTEGER     NOT NULL,
  setting_name  VARCHAR(32) NOT NULL,
  setting_value TEXT        NOT NULL,
  PRIMARY KEY (block_id, setting_name),
  FOREIGN KEY `###PREFIX###block_setting_fk1` (block_id) REFERENCES `###PREFIX###block` (block_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###hit_counter` (
  gedcom_id      INTEGER     NOT NULL,
  page_name      VARCHAR(32) NOT NULL,
  page_parameter VARCHAR(32) NOT NULL,
  page_count     INTEGER     NOT NULL,
  PRIMARY KEY (gedcom_id, page_name, page_parameter),
  FOREIGN KEY `###PREFIX###hit_counter_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###session` (
  session_id   CHAR(128)   NOT NULL,
  session_time TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  user_id      INTEGER     NOT NULL,
  ip_address   VARCHAR(32) NOT NULL,
  session_data MEDIUMBLOB  NOT NULL,
  PRIMARY KEY (session_id),
  KEY `###PREFIX###session_ix1` (session_time),
  KEY `###PREFIX###session_ix2` (user_id, ip_address)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###gedcom_chunk` (
  gedcom_chunk_id INTEGER AUTO_INCREMENT NOT NULL,
  gedcom_id       INTEGER                NOT NULL,
  chunk_data      MEDIUMBLOB             NOT NULL,
  imported        BOOLEAN                NOT NULL DEFAULT FALSE,
  PRIMARY KEY (gedcom_chunk_id),
  KEY `###PREFIX###gedcom_chunk_ix1` (gedcom_id, imported),
  FOREIGN KEY `###PREFIX###gedcom_chunk_fk1` (gedcom_id) REFERENCES `###PREFIX###gedcom` (gedcom_id) /* ON DELETE CASCADE */
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `###PREFIX###site_access_rule` (
  site_access_rule_id INTEGER                                   NOT NULL AUTO_INCREMENT,
  ip_address_start    INTEGER UNSIGNED                          NOT NULL DEFAULT 0,
  ip_address_end      INTEGER UNSIGNED                          NOT NULL DEFAULT 4294967295,
  user_agent_pattern  VARCHAR(255)                              NOT NULL,
  rule                ENUM('allow', 'deny', 'robot', 'unknown') NOT NULL DEFAULT 'unknown',
  comment             VARCHAR(255)                              NOT NULL,
  updated             TIMESTAMP                                 NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (site_access_rule_id),
  UNIQUE KEY `###PREFIX###site_access_rule_ix1` (ip_address_end, ip_address_start, user_agent_pattern, rule),
  KEY `###PREFIX###site_access_rule_ix2` (rule)
)
  ENGINE = InnoDB
  COLLATE = utf8_unicode_ci;


INSERT IGNORE INTO `###PREFIX###site_access_rule` (`user_agent_pattern`, `rule`, `comment`) VALUES
  ('Mozilla/5.0 (%) Gecko/% %/%', 'allow', 'Gecko-based browsers'),
  ('Mozilla/5.0 (%) AppleWebKit/% (KHTML, like Gecko)%', 'allow', 'WebKit-based browsers'),
  ('Opera/% (%) Presto/% Version/%', 'allow', 'Presto-based browsers'),
  ('Mozilla/% (compatible; MSIE %', 'allow', 'Trident-based browsers'),
  ('Mozilla/% (Windows%; Trident%; rv:%) like Gecko', 'allow', 'Modern Internet Explorer'),
  ('Mozilla/5.0 (% Konqueror/%', 'allow', 'Konqueror browser');


INSERT IGNORE INTO `###PREFIX###site_setting` (setting_name, setting_value) VALUES
  ('WT_SCHEMA_VERSION', '-2'),
  ('INDEX_DIRECTORY', 'data/'),
  ('USE_REGISTRATION_MODULE', '1'),
  ('REQUIRE_ADMIN_AUTH_REGISTRATION', '1'),
  ('ALLOW_USER_THEMES', '1'),
  ('ALLOW_CHANGE_GEDCOM', '1'),
  ('SESSION_TIME', '7200'),
  ('SMTP_ACTIVE', 'internal'),
  ('SMTP_HOST', 'localhost'),
  ('SMTP_PORT', '25'),
  ('SMTP_AUTH', '1'),
  ('SMTP_AUTH_USER', ''),
  ('SMTP_AUTH_PASS', ''),
  ('SMTP_SSL', 'none'),
  ('SMTP_HELO', 'helo'),
  ('SMTP_FROM_NAME', 'from');


INSERT IGNORE INTO `###PREFIX###gedcom` (`gedcom_id`, `gedcom_name`) VALUES (-1, 'DEFAULT_TREE');

-- Create the default settings for new users/family trees
INSERT INTO `###PREFIX###block` (user_id, location, block_order, module_name)
VALUES
  (-1, 'main', 1, 'todays_events'),
  (-1, 'main', 2, 'user_messages'),
  (-1, 'main', 3, 'user_favorites'),
  (-1, 'side', 1, 'user_welcome'),
  (-1, 'side', 2, 'random_media'),
  (-1, 'side', 3, 'upcoming_events'),
  (-1, 'side', 4, 'logged_in');

INSERT INTO `###PREFIX###block` (gedcom_id, location, block_order, module_name)
VALUES
  (-1, 'main', 1, 'gedcom_stats'),
  (-1, 'main', 2, 'gedcom_news'),
  (-1, 'main', 3, 'gedcom_favorites'),
  (-1, 'main', 4, 'review_changes'),
  (-1, 'side', 1, 'gedcom_block'),
  (-1, 'side', 2, 'random_media'),
  (-1, 'side', 3, 'todays_events'),
  (-1, 'side', 4, 'logged_in');
