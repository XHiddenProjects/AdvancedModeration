----
-- phpLiteAdmin database dump (https://www.phpliteadmin.org/)
-- phpLiteAdmin version: 1.9.8.2
-- Exported: 11:31am on August 7, 2021 (EDT)
-- database file: ./AdvancedModeration
----
BEGIN TRANSACTION;

----
-- Table structure for players
----
CREATE TABLE 'players' ('user' TEXT PRIMARY KEY NOT NULL, 'joinedIP' TEXT, 'lastIP' TEXT, 'TempMuteTime' DATETIME, 'lastJoined'  DATETIME DEFAULT CURRENT_TIMESTAMP  , 'BannedTimeStamp' DATETIME, 'BannedType' TEXT, 'isBanned' BOOLEAN DEFAULT 0, 'isTempBanned' BOOLEAN DEFAULT 0, 'isMute' BOOLEAN DEFAULT 0, 'isTempMute' BOOLEAN DEFAULT 0);

----
-- Data dump for players, a total of 0 rows
----

----
-- structure for index sqlite_autoindex_players_1 on table players
----
;
COMMIT;
