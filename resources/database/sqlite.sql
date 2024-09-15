-- #! sqlite
-- #{ example
-- #    { insert
-- # 	  :foo string
-- # 	  :bar int
INSERT INTO example(
	foo_column
	bar_column
) VALUES (
	:foo,
	:bar
);
-- #    }
-- #    { select
-- # 	  :foo string
-- # 	  :bar int
SELECT * FROM example
WHERE foo_column = :foo
LIMIT :bar;
-- #    }
-- #}