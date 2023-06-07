-- #!sqlite

-- #{ table
    -- #{ cooldowns
          CREATE TABLE IF NOT EXISTS GrapplingHookCooldowns(
              player TEXT NOT NULL,
              id TEXT NOT NULL,
              timestamp INTEGER NOT NULL,
              duration INTEGER NOT NULL
          );
    -- #}
-- #}