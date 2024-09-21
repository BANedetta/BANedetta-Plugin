-- #! sqlite

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans (
			banned TEXT,
			postId INTEGER,
			nickname TEXT,
			by TEXT,
			reason TEXT,
			confirmed INTEGER NOT NULL CHECK (confirmed IN (0, 1))
		);
	-- #}
-- #}

-- #{ bans
	-- #{ add
		-- # :banned string
		-- # :nickname string
		-- # :by string
		-- # :reason string
		-- # :confirmed bool
		INSERT INTO bans(banned, by, nickname, reason, confirmed)
		VALUES (:banned, :by, :nickname, :reason, :confirmed);
	-- #}

	-- #{ confirm
		-- # :banned string
		-- # :confirmed bool
		UPDATE bans
		SET confirmed = :confirmed
		WHERE banned = :banned;
	-- #}

	-- #{ getData
		-- # :banned string
		SELECT * FROM bans
		WHERE banned = :banned
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
		-- # :banned string
		-- # :postId int
		UPDATE bans
		SET postId = :postId
		WHERE banned = :banned;
	-- #}

	-- #{ remove
		-- # :banned string
		DELETE FROM bans
		WHERE banned = :banned;
	-- #}
-- #}
