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
		INSERT INTO bans(nickname, `by`, reason, message)
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
		WHERE nickname = :nickname;
	-- #}

	-- #{ getDataByNickname
		-- # :nickname string
		SELECT * FROM bans
		WHERE nickname = :nickname;
	-- #}

	-- #{ getDataByVkPostId
		-- # :post_id int
		SELECT * FROM bans
		WHERE post_id = :post_id
	-- #}

	-- #{ getDataByTgPostId
		-- # :post_id int
		SELECT * FROM bans
		WHERE post_id = :post_id
	-- #}

	-- #{ setPostIds
		-- # :nickname string
		-- # :vk_post_id int
		-- # :tg_post_id int
		UPDATE bans
		SET vk_post_id = :vk_post_id, tg_post_id = :tg_post_id
		WHERE nickname = :nickname;
	-- #}

	-- #{ remove
		-- # :nickname string
		DELETE FROM bans
		WHERE nickname = :nickname;
	-- #}

-- #}
