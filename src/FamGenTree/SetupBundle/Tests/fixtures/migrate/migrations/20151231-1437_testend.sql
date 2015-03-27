CREATE TABLE IF NOT EXISTS `###PREFIX###schema_updates` (
  id_schema_updates INTEGER(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  patch_id          VARCHAR(100)                        NOT NULL,
  patched_at        TIMESTAMP                           NOT NULL DEFAULT NOW(),
  PRIMARY KEY (id_schema_updates),
  UNIQUE KEY `idx_patch_id` (patch_id)
)
  COLLATE utf8_unicode_ci
  ENGINE = InnoDB;

INSERT INTO `###PREFIX###schema_updates` (patch_id, patched_at) VALUES ('20151231-1437', NOW());