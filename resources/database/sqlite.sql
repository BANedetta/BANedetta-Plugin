-- #! sqlite

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans (
			vk_post_id INTEGER DEFAULT NULL,
			tg_post_id INTEGER DEFAULT NULL,
			nickname TEXT,
			by TEXT,
			reason TEXT,
			confirmed INTEGER CHECK (confirmed IN (0, 1)) DEFAULT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			message TEXT
		);
	-- #}
-- #}

-- #{ bans
	-- #{ add
		-- # :nickname string
		-- # :by string
		-- # :reason string
		-- # :message string
		INSERT INTO bans(nickname, by, reason, message)
		VALUES (:nickname, :by, :reason, :message);
	-- #}

	-- #{ confirm
		-- # :nickname string
		-- # :confirmed bool
		-- # :message string
		UPDATE bans
		SET confirmed = :confirmed, message = :message
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
