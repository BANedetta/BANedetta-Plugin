-- #! mysql

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans_data (
			id INT AUTO_INCREMENT PRIMARY KEY,
			banned VARCHAR(255),
			`by` VARCHAR(255),
			reason TEXT,
			confirmed BOOL,
			status VARCHAR(9) DEFAULT "waiting",
			unbanned BOOL DEFAULT FALSE,
			vk_post INT,
			tg_post INT,
			tg_post_c INT,
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
		INSERT INTO bans_data(banned, by, reason, confirmed)
		VALUES (:nickname, :by, :reason, :confirmed);
	-- #}

	-- #{ getDataByNickname
		-- # :nickname string
		SELECT * FROM bans_data
		WHERE banned = :nickname
		ORDER BY id DESC LIMIT 1;
	-- #}

	-- #{ unban
		-- # :nickname string
		UPDATE bans_data
		SET unbanned = TRUE
		WHERE banned = :nickname
		ORDER BY id DESC
		LIMIT 1;
	-- #}

-- #}
