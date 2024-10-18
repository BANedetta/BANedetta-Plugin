-- #! mysql

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans_data (
			id INT AUTO_INCREMENT PRIMARY KEY,
			banned VARCHAR(255),
			`by` VARCHAR(255),
			reason TEXT,
			confirmed BOOLEAN,
			status VARCHAR(9) DEFAULT "waiting",
			unbanned BOOLEAN DEFAULT FALSE,
			vk_post INT,
			tg_post INT,
			tg_post_c INT,
			`trigger` BOOLEAN,
			created DATETIME DEFAULT CURRENT_TIMESTAMP
		);
	-- #}
-- #}

-- #{ bans
	-- #{ ban
		-- # :nickname string
		-- # :by string
		-- # :reason string
		-- # :confirmed bool
		INSERT INTO bans_data(banned, `by`, reason, confirmed)
		VALUES (:nickname, :by, :reason, :confirmed);
	-- #}

	-- #{ unban
		-- # :id int
		UPDATE bans_data
		SET unbanned = 1
		WHERE id = :id;
	-- #}

	-- #{ getLastDataByNickname
		-- # :nickname string
		SELECT * FROM bans_data
		WHERE banned = :nickname
		ORDER BY id DESC LIMIT 1;
	-- #}

	-- #{ getNotTriggeredBans
		SELECT * FROM bans_data
		WHERE `trigger` IS NULL AND status != "waiting" AND confirmed IS NULL;
	-- #}

	-- #{ trigger
		-- # :id int
		UPDATE bans_data
		SET `trigger` = TRUE
		WHERE id = :id;
	-- #}

-- #}
