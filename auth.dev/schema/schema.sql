CREATE TABLE users
(
    id INTEGER PRIMARY KEY,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    email TEXT NOT NULL,
    name TEXT NOT NULL
);
CREATE UNIQUE INDEX users_email_uindex ON users (email);
CREATE UNIQUE INDEX users_username_uindex ON users (username);

CREATE TABLE groups
(
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
);
CREATE UNIQUE INDEX groups_name_uindex ON groups (name);

CREATE TABLE users_groups
(
    users_id INTEGER,
    groups_id INTEGER
);
CREATE UNIQUE INDEX users_groups_userid_groupid_uindex ON users_groups (users_id, groups_id);

CREATE TABLE permissions
(
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
);
CREATE UNIQUE INDEX permissions_name_uindex ON permissions (name);

CREATE TABLE groups_permissions
(
    groups_id INTEGER,
    permissions_id INTEGER
);
CREATE UNIQUE INDEX groups_permissions_groupid_permissionid_uindex ON groups_permissions (groups_id, permissions_id);

INSERT INTO users(id, username, password, email, name) VALUES (1, 'admin', '$2y$10$p.dgv9uMzcpanIeyTPb5B.Na9PMk0fia7s09PkzhrQLbY3UVCiM76', 'test@test.com', 'Test Account');
INSERT INTO groups(id, name) VALUES (1, 'Administrators');
INSERT INTO permissions(id, name) VALUES (1, 'Administrator');
INSERT INTO users_groups(users_id, groups_id) VALUES (1, 1);
INSERT INTO groups_permissions(groups_id, permissions_id) VALUES (1, 1);