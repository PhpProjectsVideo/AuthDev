<?php

require __DIR__ . '/../vendor/autoload.php';


/**
 * Check to make sure our test db exists, if it does not, create it and execute our sql against it.
 */
if (!file_exists(__DIR__ . '/../data/test-auth.sqlite'))
{
    exec('sqlite3 ' . __DIR__ . '/../data/' . TEST_DATABASE_FILE . ' < ' . __DIR__ . '/../schema/schema.sql');
}

