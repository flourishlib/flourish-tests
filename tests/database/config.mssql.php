<?php
include_once './support/constants.php';

define('DB_TYPE',     'mssql');
define('DB',          DB_NAME);
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'win-db.flourishlib.com');
define('DB_PORT',     1122);

define('DB_SETUP_FILE',    './database/setup.mssql.sql');
define('DB_POPULATE_FILE', './database/populate.mssql.sql');
define('DB_WIPE_FILE',     './database/wipe.mssql.sql');
define('DB_TEARDOWN_FILE', './database/teardown.mssql.sql');
define('DB_SCHEMA_FILE',   './database/schema.mssql.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.mssql.sql');
define('DB_EXTENDED_POPULATE_FILE', './database/populate-extended.mssql.sql');
define('DB_EXTENDED_WIPE_FILE',     './database/wipe-extended.mssql.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.mssql.sql');

define('DB_ALTERNATE_SCHEMA_SETUP_FILE',    './database/setup-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_POPULATE_FILE', './database/populate-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_WIPE_FILE',     './database/wipe-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_TEARDOWN_FILE', './database/teardown-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_SCHEMA_FILE',   './database/schema-alternate_schema.mssql.json');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.mssql.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.mssql.sql');

if (!defined('SKIPPING')) {
	//$db_name = DB_NAME;
	//`sh reset_databases.sh -t mssql $db_name`;
}