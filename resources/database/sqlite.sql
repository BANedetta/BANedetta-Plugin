-- #! sqlite

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans (
			postId INTEGER DEFAULT NULL,
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
		SET confirmed = :confirmed
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

	-- #{ getDataByPostId
		-- # :postId int
		SELECT * FROM bans
		WHERE postId = :postId
	-- #}

	-- #{ setPostId
		-- # :nickname string
		-- # :postId int
		UPDATE bans
		SET postId = :postId
		WHERE nickname = :nickname;
	-- #}

	-- #{ remove
		-- # :nickname string
		DELETE FROM bans
		WHERE nickname = :nickname;
	-- #}

	-- #{ getAllData
		-- # :page int
		SELECT *
		FROM bans
		LIMIT 30 OFFSET (:page - 1) * 30;
	-- #}
-- #}
