-- Drop the procedure if it exists
DROP PROCEDURE IF EXISTS `process_leave_approval` //

-- Create the procedure
CREATE PROCEDURE `process_leave_approval` (
    IN p_leave_id INT,
    IN p_approver_id VARCHAR(20),
    IN p_action VARCHAR(20),
    IN p_comments TEXT
)
BEGIN
    DECLARE v_approver_role VARCHAR(50);
    DECLARE v_next_approver VARCHAR(50);
    DECLARE v_current_level INT;
    DECLARE v_max_level INT;
    DECLARE v_department_id INT;

    -- Get approver details
    SELECT new_role_id INTO v_approver_role 
    FROM employees 
    WHERE eid = p_approver_id;

    -- Get current workflow status
    SELECT current_level, max_level, department_id 
    INTO v_current_level, v_max_level, v_department_id 
    FROM leave_workflow 
    WHERE leave_id = p_leave_id;

    -- Start transaction
    START TRANSACTION;

    IF p_action = 'APPROVED' THEN
        IF v_current_level >= v_max_level THEN
            -- Final approval
            UPDATE leaves 
            SET status = 'APPROVED' 
            WHERE id = p_leave_id;

            UPDATE leave_workflow 
            SET status = 'APPROVED',
                current_approver_role = NULL,
                next_approver_role = NULL 
            WHERE leave_id = p_leave_id;
        ELSE
            -- Get next approver
            SELECT parent_role_id INTO v_next_approver 
            FROM role_hierarchy 
            WHERE department_id = v_department_id 
            AND workflow_level = v_current_level + 1;

            -- Update to next level
            UPDATE leave_workflow 
            SET current_level = v_current_level + 1,
                current_approver_role = v_next_approver,
                status = 'IN_PROGRESS' 
            WHERE leave_id = p_leave_id;
        END IF;
    ELSE
        -- Rejected
        UPDATE leaves 
        SET status = 'REJECTED' 
        WHERE id = p_leave_id;

        UPDATE leave_workflow 
        SET status = 'REJECTED' 
        WHERE leave_id = p_leave_id;
    END IF;

    -- Record action in history
    INSERT INTO leave_approval_history (
        leave_id,
        action_by_id,
        action_by_role,
        action,
        comments
    ) VALUES (
        p_leave_id,
        p_approver_id,
        v_approver_role,
        p_action,
        p_comments
    );

    COMMIT;
END //

DELIMITER ; 