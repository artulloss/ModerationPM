-- # !sqlite
-- # { moderation
-- #   { init
-- #     { players
CREATE TABLE IF NOT EXISTS players (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) NOT NULL UNIQUE,
  xuid VARCHAR(32) NOT NULL UNIQUE,
  device_id VARCHAR(64) NOT NULL UNIQUE,
  ip VARCHAR(64) NOT NULL NULL
);
-- #     }
-- #     { bans
CREATE TABLE IF NOT EXISTS bans (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { ip_bans
CREATE TABLE IF NOT EXISTS ip_bans (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { mutes
CREATE TABLE IF NOT EXISTS mutes (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { freezes
CREATE TABLE IF NOT EXISTS freezes (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { alias
CREATE TABLE IF NOT EXISTS alias (
   id INTEGER PRIMARY KEY AUTO_INCREMENT,
   name VARCHAR(16) NOT NULL,
   xuid VARCHAR(32) NOT NULL,
   device_id VARCHAR(64) NOT NULL,
   client_id VARCHAR(64) NOT NULL
);
-- #     }
-- #   }
-- #   { upsert
-- #     { players
-- #       :player_name string
-- #       :xuid string
-- #       :device_id string
-- #       :ip string
INSERT INTO players (name, xuid, device_id, ip) VALUES (:player_name, :xuid, :device_id, :ip) ON DUPLICATE KEY UPDATE name = :player_name, xuid = :xuid, device_id = :device_id, ip = :ip;
-- #     }
-- #     { bans
-- #       :player_name string
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO bans (player_id, staff_name, reason, until) VALUES (
     (SELECT id FROM players WHERE lower(name) = lower(:player_name)), :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { ip_bans
-- #       :player_name string
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO ip_bans (player_id, staff_name, reason, until) VALUES (
     (SELECT id FROM players WHERE lower(name) = lower(:player_name)), :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { mutes
-- #       :player_name string
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO mutes (player_id, staff_name, reason, until) VALUES (
    (SELECT id FROM players WHERE lower(name) = lower(:player_name)), :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { freezes
-- #       :player_name string
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO freezes (player_id, staff_name, reason, until) VALUES (
     (SELECT id FROM players WHERE lower(name) = lower(:player_name)), :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #   }
-- #   { get
-- #     { players
-- #       { all
SELECT * FROM players;
-- #       }
-- #       { player
-- #         :player_name string
SELECT * FROM players WHERE LOWER(name) = LOWER(:player_name);
-- #       }
-- #     }
-- #     { bans
-- #       { all
SELECT bans.*, players.* FROM bans
  INNER JOIN players ON bans.player_id = players.id;
-- #       }
-- #       { player
-- #         :player_name string
SELECT bans.*, players.name FROM bans
  INNER JOIN players ON bans.player_id = players.id
WHERE players.id = (SELECT id from players WHERE name = :player_name);
-- #       }
-- #     }
-- #     { ip_bans
-- #       { all
SELECT ip_bans.*, players.* FROM ip_bans
  RIGHT JOIN players ON ip_bans.player_id = players.id;
-- #       }
-- #       { player
-- #         :player_name string
SELECT ip_bans.*, players.* FROM ip_bans
  RIGHT JOIN players ON ip_bans.player_id = players.id
WHERE players.ip = (SELECT ip from players WHERE name = :player_name);
-- #       }
-- #     }
-- #     { mutes
-- #       { all
SELECT mutes.*, players.* FROM mutes
  INNER JOIN players ON mutes.player_id = players.id;
-- #       }
-- #       { player
-- #         :player_name string
SELECT mutes.*, players.name FROM mutes
  INNER JOIN players ON mutes.player_id = players.id
WHERE players.id = (SELECT id from players WHERE name = :player_name);
-- #       }
-- #     }
-- #     { freezes
-- #       { all
SELECT freezes.*, players.* FROM freezes
  INNER JOIN players ON freezes.player_id = players.id;
-- #       }
-- #       { player
-- #         :player_name string
SELECT freezes.*, players.name FROM freezes
  INNER JOIN players ON freezes.player_id = players.id
WHERE players.id = (SELECT id from players WHERE name = :player_name);
-- #       }
-- #     }
-- #   }
-- #   { delete
-- #     { bans
-- #       :player_name string
DELETE FROM bans WHERE player_id = (SELECT id FROM players WHERE name = :player_name);
-- #     }
-- #     { ip_bans
-- #       :player_name string
DELETE FROM ip_bans WHERE player_id = (SELECT id FROM players WHERE name = :player_name);
-- #     }
-- #     { mutes
-- #       :player_name string
DELETE FROM mutes WHERE player_id = (SELECT id FROM players WHERE name = :player_name);
-- #     }
-- #     { freezes
-- #       :player_name string
DELETE FROM freezes WHERE player_id = (SELECT id FROM players WHERE name = :player_name);
-- #     }
-- #   }
-- # }