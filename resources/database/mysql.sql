-- #! mysql

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans (
			vk_post_id INT DEFAULT NULL,
			tg_post_id INT DEFAULT NULL,
			nickname TEXT,
			`by` TEXT,
			reason TEXT,
			confirmed BOOLEAN DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			kick_screen TEXT DEFAULT NULL
		);
	-- #}
-- #}

-- #{ bans
	-- #{ add
		-- # :nickname string
		-- # :by string
		-- # :reason string
		INSERT INTO bans(nickname, by, reason)
		VALUES (:nickname, :by, :reason);
	-- #}

	-- #{ confirm
		-- # :nickname string
		-- # :confirmed bool
		UPDATE bans
		SET confirmed = :confirmed
		WHERE nickname = :nickname;
	-- #}

	-- #{ setKickScreen
		-- # :nickname string
		-- # :kick_screen string
		UPDATE bans
		SET kick_screen = :kick_screen
		WHERE nickname = :nickname;
	-- #}

	-- #{ getData
		-- # :nickname string
		SELECT * FROM bans
		WHERE nickname = :nickname
	-- #}

	-- #{ getDataByNickname
		-- # :nickname string
		SELECT * FROM bans
		WHERE nickname = :nickname
	-- #}

	-- #{ getDataByVkPostId
		-- # :post_id int
		SELECT * FROM bans
		WHERE vk_post_id = :post_id
	-- #}

	-- #{ getDataByTgPostId
		-- # :post_id int
		SELECT * FROM bans
		WHERE tg_post_id = :post_id
	-- #}

	-- #{ setVkPostId
		-- # :nickname string
		-- # :post_id int
		UPDATE bans
		SET vk_post_id = :post_id
		WHERE nickname = :nickname;
	-- #}

	-- #{ setTgPostId
		-- # :nickname string
		-- # :post_id int
		UPDATE bans
		SET tg_post_id = :post_id
		WHERE nickname = :nickname;
	-- #}

	-- #{ remove
		-- # :nickname string
		DELETE FROM bans
		WHERE nickname = :nickname;
	-- #}

-- #}
