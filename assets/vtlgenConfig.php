<?php
define('FAKER_LOCALE', 'en_GB');
define('FAKER_SEED', '13579');
define('FAKER_DATE_FORMAT', 'Y-m-d');
define('FAKER_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('FAKER_TIME_FORMAT', 'H:i:s');
define('FAKER_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');
define ('SQL_SCRIPTS_LOCATION', __DIR__ . '/../assets/sqltablescripts');
define ('BACKUP_SCRIPTS_LOCATION', __DIR__ . '/../assets/backups');
define ('DOCUMENTATION_LOCATION', __DIR__ . '/../assets/documentation');
define ('DOCUMENTATION_LEVEL', 'concise'); //set to verbose for a more detailed result.
define ('VERSION', 'Version: 3.6'); // used to control auto updates