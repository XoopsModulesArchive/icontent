CREATE TABLE `icontent_shortcuts` (
    `id`      INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`    VARCHAR(255)    NOT NULL DEFAULT '',
    `page`    INT(5)          NOT NULL,
    `submenu` INT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `index` (`id`)
)
    ENGINE = ISAM;

CREATE TABLE `icontent_directories` (
    `id`       INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pid`      INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `name`     VARCHAR(255)    NOT NULL DEFAULT '',
    `url`      VARCHAR(255)    NOT NULL DEFAULT '',
    `homePage` INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `hits`     INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `access`   VARCHAR(255)    NOT NULL DEFAULT '0',
    `hidden`   INT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `index` (`id`)
)
    ENGINE = ISAM;

CREATE TABLE `icontent_pages` (
    `id`              INT(5) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(255)     NOT NULL DEFAULT '',
    `url`             VARCHAR(255)     NOT NULL DEFAULT '',
    `directory`       INT(5) UNSIGNED  NOT NULL DEFAULT 0,
    `content`         LONGTEXT         NOT NULL DEFAULT '',
    `comments`        INT(5) UNSIGNED  NOT NULL DEFAULT 0,
    `hits`            INT(5) UNSIGNED  NOT NULL DEFAULT 0,
    `access`          VARCHAR(255)     NOT NULL DEFAULT '0',
    `hidden`          INT(1) UNSIGNED  NOT NULL DEFAULT 0,
    `lastUpdate`      INT(10)          NOT NULL DEFAULT 0,
    `rating`          DOUBLE(6, 4)     NOT NULL DEFAULT '0.0000',
    `votes`           INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `submitter`       INT(5) UNSIGNED  NOT NULL DEFAULT 1,
    `commentsEnabled` INT(1) UNSIGNED  NOT NULL DEFAULT 1,
    `ratingEnabled`   INT(1) UNSIGNED  NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `index` (`id`)
)
    ENGINE = ISAM;

CREATE TABLE `icontent_votedata` (
    `ratingid`        INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `page`            INT(11) UNSIGNED    NOT NULL DEFAULT '0',
    `ratinguser`      INT(11)             NOT NULL DEFAULT '0',
    `rating`          TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
    `ratinghostname`  VARCHAR(60)         NOT NULL DEFAULT '',
    `ratingtimestamp` INT(10)             NOT NULL DEFAULT '0',
    PRIMARY KEY (`ratingid`),
    KEY `ratinguser` (`ratinguser`),
    KEY `ratinghostname` (`ratinghostname`),
    KEY `page` (`page`)
)
    ENGINE = ISAM;
