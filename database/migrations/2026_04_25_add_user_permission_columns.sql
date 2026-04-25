ALTER TABLE users
    ADD COLUMN permission_view TINYINT(1) NOT NULL DEFAULT 1 AFTER password,
    ADD COLUMN permission_edit TINYINT(1) NOT NULL DEFAULT 1 AFTER permission_view,
    ADD COLUMN permission_delete TINYINT(1) NOT NULL DEFAULT 0 AFTER permission_edit,
    ADD COLUMN permission_manage_settings TINYINT(1) NOT NULL DEFAULT 0 AFTER permission_delete;
