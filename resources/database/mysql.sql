-- #! mysql

-- #{ table
	-- #{ init
		CREATE TABLE IF NOT EXISTS bans (
			id INTEGER, -- ID постов с ВК
			banned TEXT,
			nickname TEXT,
			by TEXT,
			reason TEXT,
			confirmed BOOLEAN NOT NULL
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

	-- #{ setId
		-- # :banned string
		-- # :id int
		UPDATE bans
		SET id = :id
		WHERE banned = :banned;
	-- #}

	-- #{ get
		-- # :banned string
		SELECT * FROM bans WHERE banned = :banned LIMIT 1;
	-- #}

	-- #{ remove
		-- # :banned string
		DELETE FROM bans WHERE banned = :banned;
	-- #}
-- #}
