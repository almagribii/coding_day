
DELIMITER //

CREATE TRIGGER team_verified_log
AFTER UPDATE ON teams
FOR EACH ROW
BEGIN
    IF OLD.is_verified = 0 AND NEW.is_verified = 1 THEN
        INSERT INTO verification_logs (team_id, admin_id, verified_at)
        VALUES (NEW.id, NEW.verified_by_user_id, NOW());
    END IF;
END;

//
DELIMITER ;

DELIMITER //

CREATE PROCEDURE count_verified_teams (OUT total_teams INT)
BEGIN
    SELECT COUNT(id) INTO total_teams
    FROM teams
    WHERE is_verified = 1;
END //

DELIMITER ;