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
