<?php

require __DIR__ . '/src/bootstrap.php';

exec('rm -f ' . CONFIG_DB_PATH);
exec('sqlite3 ' . CONFIG_DB_PATH . ' < ' . __DIR__ . '/schema/schema.sql');
