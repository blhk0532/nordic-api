-- Temporarily change boolean columns to integer to accept 0/1 values
ALTER TABLE sweden_personer 
    ALTER COLUMN is_hus DROP DEFAULT,
    ALTER COLUMN is_hus TYPE integer USING (CASE WHEN is_hus THEN 1 ELSE 0 END);

ALTER TABLE sweden_personer 
    ALTER COLUMN is_owner DROP DEFAULT,
    ALTER COLUMN is_owner TYPE integer USING (CASE WHEN is_owner THEN 1 ELSE 0 END);

ALTER TABLE sweden_personer 
    ALTER COLUMN is_active DROP DEFAULT,
    ALTER COLUMN is_active TYPE integer USING (CASE WHEN is_active THEN 1 ELSE 0 END);

ALTER TABLE sweden_personer 
    ALTER COLUMN is_queue DROP DEFAULT,
    ALTER COLUMN is_queue TYPE integer USING (CASE WHEN is_queue THEN 1 ELSE 0 END);

ALTER TABLE sweden_personer 
    ALTER COLUMN is_done DROP DEFAULT,
    ALTER COLUMN is_done TYPE integer USING (CASE WHEN is_done THEN 1 ELSE 0 END);