<?php
require_once __DIR__ . '/../assets/vendor/autoload.php';
require_once __DIR__ . '/../assets/vtlgenConfig.php';
class Vtlgen extends Trongate
{


    //region variables
    private string $host = HOST;

    private string $dbname = DATABASE;

    private string $user = USER;

    private string $pass = PASSWORD;

    private $port = '';

    private $dbh;
    private $stmt;

    private $error;

    private mixed $applicationModules;
    //endregion

    //region Constructor

    /**
     * Constructor for the Vtlgen class.
     *
     * This constructor initializes the database connection by creating a PDO instance
     * with the provided database credentials. If the database name is not provided,
     * the constructor returns without establishing a connection.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Now we need to be able to interact with the database
        if (DATABASE == '') {
            return;
        }

        $this->port = (defined('PORT') ? PORT : '3306');
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);

        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            die();
        }

        // Initialize the Faker instance
        $faker = null;
        $this->$faker = \Faker\Factory::create(FAKER_LOCALE);

        //Get a list of all modules in the application and whether they have an api.
        $this->applicationModules = $this->list_all_modules();
    }
    //endregion

    //region Index

    public function index(): void
    {
        $this->module('trongate_administrators');
        $token = $this->trongate_administrators->_make_sure_allowed();


        if (ENV != 'dev') {
            redirect(BASE_URL);
            die();
        } else {
            if ($token == '') {
                redirect(BASE_URL);
                die();
            }
        }
        unset($_SESSION['selectedDataTable']);

        // Define the list item HTML with a newline character at the end
        $listItemHTML = "\n<li>" . anchor('vtlgen', '<img src="vtlgen_module/help/images/vtlgen.svg"> Vtl Data Generator') . '</li>' . "\n";

// Path to the dynamic_nav.php file
        $filePath = APPPATH . 'templates/views/partials/admin/dynamic_nav.php';

// Read the content of dynamic_nav.php
        $fileContent = file_get_contents($filePath);

// Check if the list item already exists in the file
        if (strpos($fileContent, $listItemHTML) === false) {
            // Find the position to insert the new list item after the opening <ul> tag
            $pos = strpos($fileContent, '<ul>');
            if ($pos !== false) {
                // Move the position to after the opening <ul> tag
                $pos += strlen('<ul>') + 1; // +1 to include the newline after <ul>

                // Insert the list item after the opening <ul> tag
                $newContent = substr_replace($fileContent, "\n" . $listItemHTML, $pos, 0);

                // Write the modified content back to the file
                file_put_contents($filePath, $newContent);
            }
        }
        $this->module('trongate_administrators');
        $token = $this->trongate_administrators->_make_sure_allowed();


        if (ENV != 'dev') {
            redirect(BASE_URL);
            die();
        } else {
            if ($token == '') {
                redirect(BASE_URL);
                die();
            }
        }
        unset($_SESSION['selectedDataTable']);

        // Define the list item HTML
        $listItemHTML = '<li>' . anchor('vtlgen', '<img src="vtlgen_module/help/images/vtlgen.svg"> Vtl Data Generator') . '</li>';

        // Path to the dynamic_nav.php file
        $filePath = APPPATH . 'templates/views/partials/admin/dynamic_nav.php';

        // Read the content of dynamic_nav.php
        $fileContent = file_get_contents($filePath);

        // Check if the list item already exists in the file
        if (strpos($fileContent, $listItemHTML) === false) {
            // If not, find the position to insert the new list item
            $pos = strpos($fileContent, '</ul>');
            if ($pos !== false) {
                // Insert the list item before the closing </ul> tag
                $newContent = substr_replace($fileContent, $listItemHTML, $pos, 0);

                // Write the modified content back to the file
                file_put_contents($filePath, $newContent);
            }
        }

        $data['tables'] = $this->setupTablesForDropdown();
        $data['headline'] = 'Vtl Data Generator: Home Page';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'vtlgen';
        $this->template('admin', $data);
    }
    //endregion

    //region Modules Information

    /**
     * This function was created by Simon Field aka Dafa.
     * I am indebted to him for it.
     *
     * Retrieves information about all modules in the application.
     *
     * This function scans the modules directory and gathers information about each module,
     * including whether it has associated database tables and whether it has an API defined.
     * It returns an array containing information about each module and its submodules.
     *
     * @return array An array containing information about all modules in the application.
     */
    private function list_all_modules(): array
    {

        // Define the path to the modules directory
        $modules_dir = APPPATH . 'modules';

        // Query the database to retrieve a list of all tables
        $sql = "SHOW TABLES";
        $stmt = $this->dbh->query($sql);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Extract table names from the query result
        $table_names = [];
        foreach ($tables as $table) {
            $table_names[] = $table[array_key_first($table)];
        }

        // Initialize an array to store module information
        $module_info = [];

        // Iterate through each directory in the modules directory
        foreach (new DirectoryIterator($modules_dir) as $module_dir) {
            if ($module_dir->isDir() && !$module_dir->isDot() && $module_dir->getFilename() !== 'modules') {
                // Get the name of the module
                $module_name = $module_dir->getFilename();

                // Check if the module has associated database tables
                if (in_array($module_name, $table_names)) {
                    $parent_has_table = true;
                    unset($table_names[array_search($module_name, $table_names)]);
                } else {
                    $parent_has_table = false;
                }

                // Define the path to the controllers directory within the module
                $controllers_dir = $module_dir->getPathname() . '/controllers';

                // Check if the controllers directory exists
                if (is_dir($controllers_dir)) {
                    // Define the path to the assets directory within the module
                    $assets_dir = $module_dir->getPathname() . '/assets';

                    // Check if the assets directory exists
                    if (is_dir($assets_dir)) {
                        // Check if the module has an API defined by looking for an api.json file in the assets directory
                        $api_json_exists = file_exists($assets_dir . '/api.json');
                    } else {
                        $api_json_exists = false;
                    }

                    // Construct a check to see if there is a module_pics and module_pics_thumbnails folder
                    // in the assets folder.  That would indicate that there's a single picture uploader in the module.
                    $picsDir = $assets_dir . '/' . $module_name . '_pics';
                    $pic_directory_exists = is_dir($picsDir);
                    $pic_directory = $picsDir;
                    // Initialize an array to store information about submodules
                    $submodules = [];

                    // Iterate through each directory within the module directory
                    foreach (new DirectoryIterator($module_dir->getPathname()) as $submodule_dir) {
                        if ($submodule_dir->isDir() && !$submodule_dir->isDot() && $submodule_dir->getFilename() !== 'controllers') {
                            // Get the name of the submodule
                            $submodule_name = $submodule_dir->getFilename();

                            // Check if the submodule has associated database tables
                            if (in_array($submodule_name, $table_names)) {
                                $child_has_table = true;
                                unset($table_names[array_search($submodule_name, $table_names)]);
                            } else {
                                $child_has_table = false;
                            }

                            // Define the path to the controllers directory within the submodule
                            $submodule_controllers_dir = $submodule_dir->getPathname() . '/controllers';

                            // Check if the controllers directory exists within the submodule
                            $controllers_exist = is_dir($submodule_controllers_dir);

                            // Define the path to the assets directory within the submodule
                            $submodule_assets_dir = $submodule_dir->getPathname() . '/assets';

                            // Check if the assets directory exists within the submodule and if an api.json file exists
                            $submodule_api_json_exists = is_dir($submodule_assets_dir) && file_exists($submodule_assets_dir . '/api.json');

                            // check if there's a pics directory
                            $submodule_pic_directory_exists = is_dir($submodule_assets_dir . '/' . $module_name . '_pics');

                            // If controllers exist within the submodule, add submodule information to the submodules array
                            if ($controllers_exist) {
                                $submodules[] = [
                                    'module_name' => $submodule_name,
                                    'is_child_module_of' => $module_name,
                                    'has_table' => $child_has_table,
                                    'api_json_exists' => $submodule_api_json_exists,
                                    'pic_directory_exists' => $submodule_pic_directory_exists
                                ];
                            }
                        }
                    }

                    // If submodules exist, add module information to the module_info array including submodule details
                    if (!empty($submodules)) {
                        $module_info[] = [
                            'module_name' => $module_name,
                            'has_table' => $parent_has_table,
                            'api_json_exists' => $api_json_exists,
                            'pic_directory_exists' => $pic_directory_exists,
                            'pic_directory' => $pic_directory,
                            'submodules' => $submodules
                        ];
                    } else {
                        // If no submodules exist, add module information to the module_info array
                        $module_info[] = [
                            'module_name' => $module_name,
                            'has_table' => $parent_has_table,
                            'api_json_exists' => $api_json_exists,
                            'pic_directory_exists' => $pic_directory_exists,
                            'pic_directory' => $pic_directory
                        ];
                    }
                }
            }
        }

        // If there are any table names remaining in the table_names array, add them as orphaned_tables in module_info
        if (!empty($table_names)) {
            $module_info[] = [
                'orphaned_tables' => $table_names
            ];
        }

        // Return the module_info array containing information about all modules in the application
        return $module_info;
    }
    //endregion

    //region vtlgen page redirect functions

    /**
     * Generates the data needed for the Vtl Data Generator: Create Data page.
     *
     * Does not take any parameters.
     * Does not throw any exceptions.
     */
    public function vtlgenCreateData(): void
    {
        $data['tables'] = $this->setupTablesForDropdown();
        $data['columnInfo'] = $this->getAllTablesAndTheirColumnData();
        $data['headline'] = 'Vtl Data Generator: Create Data';
        $data['instruction1'] = 'Select the table in the database from the drop down below for which you wish to create some fake data.';
        $data['instruction2'] = 'Select those columns into which you want to add data, or just check the checkbox in the header if you want to select all the rows.';
        $data['task'] = 'createData';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createdata';
        $this->template('admin', $data);
    }

    public function vtlgenCreateIndex(): void{
        $data['tables'] = $this->setupTablesForDropdown();
        $data['columnInfo'] = $this->getAllTablesAndTheirColumnData();
        $data['headline'] = 'Vtl Data Generator: Create Index';
        $data['instruction1'] = 'Select the table in the database from the drop down below for which you wish to create an index.';
        $data['instruction2'] = 'Select the column on which you wish to create the index, and when asked select the index type';
        $data['task'] = 'createIndex';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createdata';
        $this->template('admin', $data);
    }

    /**
     * Opens the delete view for the VTL data generator.
     *
     * Does not take any parameters.
     * Does not throw any exceptions.
     * Does not return any value.
     */
    public function vtlgenDeleteData(): void{
        $this->openDeleteOrDropView('delete');
    }

    /**
     * Shows the foreign keys in the database for the Vtl Data Generator.
     * Very specifically it will show those created by the Data Generator
     */
    public function vtlgenShowForeignKeys(): void{
        $rows = $this->getForeignKeysFromDatabase();
        $headline = 'Vtl Data Generator: Foreign Keys in Database';
        $noDataMessage = 'There are currently no foreign keys in the database: ' . DATABASE;
        $this->showRowData($rows, $headline, $noDataMessage);
    }

    /**
     * Opens the delete or drop view for the VTL data generator.
     *
     * This function opens the delete or drop view for the VTL data generator. It takes no parameters and does not
     * throw any exceptions.
     *
     * @return void
     */
    public function vtlgenDropTables(): void{
        $this->openDeleteOrDropView('drop');
    }

    public function vtlgenCreateDataTable(): void{
        $data['headline'] = 'Vtl Data Generator: Create Table';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createtable';
        $this->template('admin', $data);
    }


    /**
     * Displays the general help documentation.
     *
     * This function reads the markdown file located at '../assets/help/help.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'generalHelp' view.
     *
     * @return void
     */
    public function vtlgenShowGeneralHelp(): void
    {
        $filepath = __DIR__ . '/../assets/help/help.md';
        $headline = 'Vtl Data Generator: General Help';
        $this->openHelpView($filepath, $headline);
    }

    /**
     * Displays the customized Faker help documentation.
     *
     * This function reads the markdown file located at '../assets/help/customise.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'customisefaker' view.
     *
     * @return void
     */
    public function vtlgenShowCustomiseFakerHelp(): void
    {
        $filepath = __DIR__ . '/../assets/help/customise.md';
        $headline = 'Vtl Data Generator: Customise Faker';
        $this->openHelpView($filepath, $headline);
    }

    /**
     * Fetches and displays the latest primary key values for tables.
     *
     * @return void
     */

    public function vtlgenFetchLatestPkValues(): void{
        $rows = $this->showLatestPkValues();
        $headline = 'Vtl Data Generator: Latest Primary Key Values for Tables';
        $noDataMessage = 'There are currently no tables in the database: ' . DATABASE . ' with any rows of data';
        $this->showRowData($rows, $headline, $noDataMessage);
    }

    /**
     * Displays data from the selected table.
     *
     * This function checks if the 'selectedTable' parameter is set in the GET request.
     * If it is, it sets the selected data table in the session and retrieves data from the database
     * using the 'pdoGet' method. It then displays the data using the 'showRowData' method.
     *
     * If the 'selectedTable' parameter is not set, it retrieves the selected data table from the session.
     * It then calls the 'trongate_security' module's '_make_sure_allowed' method to ensure the user is allowed.
     * Finally, it retrieves data from the database using the 'pdoGet' method and displays the data using the
     * 'showRowData' method.
     *
     * @return void
     */
    public function vtlgenShowData(): void{
        if (isset($_GET['selectedTable'])) {
            $selectedDataTable = $_GET['selectedTable'];
            $_SESSION['selectedDataTable'] = $selectedDataTable;
        } else {
            $selectedDataTable = $_SESSION['selectedDataTable'];
        }

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $rows = $this->pdoGet(target_tbl: $selectedDataTable);
        $headline = 'Vtl Data Generator: Show Data<br>From '.$selectedDataTable;
        $noDataMessage = 'There is no data to display from the table ' . $selectedDataTable;
        $this->showRowData($rows, $headline, $noDataMessage);
    }
    //endregion

    //region private functions

    public function getForeignKeysFromDatabase(): mixed
    {
        // Run the query to collect the information
        $sql = 'SELECT 
            CONCAT(table_name, \'.\', column_name) AS "foreign key", 
            CONCAT(referenced_table_name, \'.\', referenced_column_name) AS "references", 
            constraint_name AS "constraint name" 
        FROM 
            information_schema.key_column_usage 
        WHERE 
            referenced_table_name IS NOT NULL 
        AND 
            table_schema = :database';



        // Ensure the user is allowed to perform the action
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        try {
            // Prepare and execute the query
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([':database' => DATABASE]);

            // Fetch the results
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $rows;
        } catch (PDOException $e) {
            // Handle any errors
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Get the latest primary key values for all tables that have data.
     *
     * @return array An array containing information about tables with data, including table name, primary key field,
     *               and latest primary key value.
     */
    private function showLatestPkValues(): array
    {
        // Get all tables
        $allTables = $this->setupTablesForDatabaseAdmin();
        $tablesWithData = [];

        foreach ($allTables as $table) {
            $primaryKeyField = $this->getPrimaryKeyField($table);
            if ($primaryKeyField && $this->tableHasRows($table)) {
                $latestPkValue = $this->getLatestPkValue($table, $primaryKeyField);
                $tablesWithData[] = [
                    'tableName' => $table,
                    'primaryKeyField' => $primaryKeyField,
                    'latestPkValue' => $latestPkValue
                ];
            }
        }

        return $tablesWithData;

    }

    /**
     * Retrieves the primary key field for the specified table.
     *
     * @param string $tableName The name of the table to retrieve the primary key field for.
     * @throws Exception Error message if there is an issue preparing or executing the query.
     * @return mixed The primary key field of the specified table, or null if not found.
     */
    private function getPrimaryKeyField($tableName)
    {
        $query = "SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'";

        $stmt = $this->dbh->prepare($query);
        if (!$stmt) {
            $errorInfo = $this->dbh->errorInfo();
            throw new Exception("Error preparing query '$query': " . $errorInfo[2]);
        }

        $success = $stmt->execute();
        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error executing query '$query': " . $errorInfo[2]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $row['Column_name'];
    }

    /**
     * Retrieves whether a table has rows or not.
     *
     * @param string $tableName The name of the table to check for rows.
     * @throws Exception Error message if there is an issue with the query execution.
     * @return bool Whether the table has rows (true) or not (false).
     */
    private function tableHasRows($tableName)
    {
        $query = "SELECT COUNT(*) as count FROM `$tableName`";

        try {
            $stmt = $this->dbh->query($query);
            if (!$stmt) {
                $errorInfo = $this->dbh->errorInfo();
                throw new Exception("Error executing query '$query': " . $errorInfo[2]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['count'] > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieves the latest primary key value from the specified table.
     *
     * @param string $tableName The name of the table to retrieve the primary key value from.
     * @param string $primaryKeyField The field representing the primary key.
     * @throws Exception Error message if there is an issue with the query execution.
     * @return mixed The latest primary key value, or null if no value is found.
     */
    private function getLatestPkValue($tableName, $primaryKeyField)
    {
        $query = "SELECT `$primaryKeyField` FROM `$tableName` ORDER BY `$primaryKeyField` DESC LIMIT 1";

        try {
            $stmt = $this->dbh->query($query);
            if (!$stmt) {
                $errorInfo = $this->dbh->errorInfo();
                throw new Exception("Error executing query '$query': " . $errorInfo[2]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row[$primaryKeyField] : null;
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * Sets up the tables for the database admin.
     *
     * This private function retrieves all the tables using the `getAllTables()` method,
     * merges them into a single array, and returns the resulting array.
     *
     * @return array The array of tables for the database admin.
     */
    private function setupTablesForDatabaseAdmin(): array
    {
        $tables = $this->getAllTables();
        $tables = array_merge($tables);
        return $tables;
    }

    /**
     * Sets up the tables for the dropdown.
     *
     * This function retrieves all the tables using the `getAllTables()` method,
     * adds a 'Select table...' option to the beginning of the array, and returns
     * the resulting array.
     *
     * @return array The array of tables for the dropdown.
     */
    private function setupTablesForDropdown(): array
    {
        $tables = $this->getAllTables();
        $starterArray = ['Select table...'];
        $tables = array_merge($starterArray, $tables);
        return $tables;
    }

    /**
     * Retrieves all tables and their corresponding column data from the database.
     *
     * This function queries the database to retrieve the names of all tables and then
     * retrieves the column information for each table. The resulting data is stored
     * in an array, with each element containing the table name and its corresponding
     * column data.
     *
     * @return array An array of tables and their column data. Each element of the array
     *               is an associative array with the keys 'table' and 'columns', where
     *               'table' is the name of the table and 'columns' is an array of column
     *               information for that table.
     */
    private function getAllTablesAndTheirColumnData(): array
    {
        $tablesAndColumns = [];
        $tables = $this->getAllTables();
        foreach ($tables as $table) {
            $sql = "SHOW COLUMNS IN $table";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tableInfo = [
                'table' => $table,
                'columns' => $columns
            ];

            $tablesAndColumns[] = $tableInfo;
        }
        return $tablesAndColumns;


    }

    /**
     * Get All Tables in the Database
     *
     * This function retrieves the names of all tables in the database using standard PDO.
     *
     * @return array An array containing the names of all tables in the database.
     */
    private function getAllTables(): array
    {
        try {
            // Prepare the SQL statement
            $stmt = $this->dbh->prepare('SHOW TABLES');
            $stmt->execute();

            // Fetch all the results
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $tables;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return [];
        }
    }

    /**
     * Displays the row data in the admin template with the given headline and no data message.
     *
     * @param array $rows The array of rows to be displayed.
     * @param string $headline The title for the help view.
     * @param string $noDataMessage The message to be displayed when there is no data.
     * @return void
     */
    private function showRowData(array $rows, string $headline, string $noDataMessage): void
    {

        $data['rows'] = $rows;
        $data['headline'] = $headline;
        $data['noDataMessage'] = $noDataMessage;
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'showdata';
        $this->template('admin', $data);
    }


    /**
     * Opens the help view by parsing a markdown file and rendering it in the admin template.
     *
     * @param string $filepath The path to the markdown file to be parsed.
     * @param string $headline The title for the help view.
     * @return void
     * @throws None
     */
    private function openHelpView(string $filepath, string $headline): void
    {
        $parsedown = new Parsedown();
        $file = fopen($filepath, 'r');
        $markdown = $parsedown->text(fread($file, filesize($filepath)));
        fclose($file);
        $data['headline'] = $headline;
        $data['markdown'] = $markdown;
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'help';
        $this->template('admin', $data);
    }

    /**
     * Opens the delete or drop view for the Vtl Data Generator.
     *
     * @param string $task The task to perform ('delete' or 'drop').
     * @return void
     */
    private function openDeleteOrDropView(string $task): void {
        $data['tables'] = $this->setupTablesForDatabaseAdmin();
        $data['task'] = $task;
        $data['headline'] = 'Vtl Data Generator: ' . ($task === 'delete' ? 'Delete Data' : 'Drop Tables');
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'deleteordrop';
        $this->template('admin', $data);
    }

    /**
     * Retrieves data from the database using PDO.
     *
     * @param string|null $order_by The column to order the results by. Defaults to null.
     * @param string|null $target_tbl The target table to query. Must not be null.
     * @param int|null $limit The maximum number of rows to return. Defaults to null.
     * @param int $offset The number of rows to skip before returning results. Defaults to 0.
     * @throws InvalidArgumentException If $target_tbl is null.
     * @return array An array of objects representing the fetched rows.
     */
    private function pdoGet(?string $order_by = null, ?string $target_tbl = null, ?int $limit = null, int $offset = 0): array
    {
        if (is_null($target_tbl)) {
            throw new InvalidArgumentException('Target table cannot be null');
        }

        // Create the base SQL query
        $sql = "SELECT * FROM $target_tbl";

        // Add ORDER BY clause
        if (!is_null($order_by)) {
            $sql .= " ORDER BY $order_by";
        } else {
            // If no order_by is provided, order by the primary key
            $primary_key = $this->getPrimaryKey($target_tbl);
            if ($primary_key) {
                $sql .= " ORDER BY $primary_key";
            }
        }

        // Add LIMIT and OFFSET if provided
        if (!is_null($limit)) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        // Prepare the SQL statement
        $stmt = $this->dbh->prepare($sql);

        // Bind parameters if limit is provided
        if (!is_null($limit)) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }


        // Execute the query
        $stmt->execute();

        // Fetch all rows
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);


        return $rows;
    }

    /**
     * Retrieves the primary key of a given table.
     *
     * @param string $table The name of the table.
     * @return string|null The name of the primary key column, or null if no primary key is found.
     */
    private function getPrimaryKey(string $table): ?string
    {
        $sql = 'SHOW COLUMNS FROM ' . $table;
        $stmt = $this->dbh->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $column) {
            if ($column['Key'] === 'PRI') {
                return $column['Field'];
            }
        }

        return null;
    }

    /**
     * Deletes all subdirectories and their contents recursively.
     *
     * @param string $dir The directory to delete subdirectories from.
     * @return void
     */
    private function deleteSubDirectories($dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $subDirectories = array_diff(scandir($dir), array('.', '..'));

        foreach ($subDirectories as $subDir) {
            $path = $dir . '/' . $subDir;
            if (is_dir($path)) {
                // Recursively delete subdirectories and their contents
                $this->deleteSubDirectories($path);
                // Remove the empty subdirectory
                rmdir($path);
            } else {
                // Delete files directly within the directory
                unlink($path);
            }
        }

    }


    //endregion

    //region createdata view functions

    /**
     * Creates fake data for the selected table.
     *
     * @throws Exception if the JSON data is invalid.
     * @return void
     */
    public function createdataCreateFakeData(): void{
        // Initialize Faker instance
        $faker = null;
        $faker = $this->$faker;

        // register any custom provider(s) with the faker
        $faker->addProvider(new Faker\Provider\Commerce($faker));
        $faker->addProvider(new Faker\Provider\Blog($faker));

        // Seed the faker.  This will ensure that the same data gets recreated
        // which can be useful for testing purposes.
        // Comment out the line below if you don't want to use a seeded faker.

        //$faker->seed(FAKER_SEED);

        $rawPostData = file_get_contents('php://input');
        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);
        // Ensure JSON decoding was successful
        if ($postData === null) {
            throw new Exception("Invalid JSON data");
        }
        // Extract relevant data from the decoded JSON
        $selectedTable = $postData['selectedTable'];
        $selectedRows = $postData['selectedRows'];
        $numRows = $postData['numRows'];

        // Now is the time to hive off highly customised data creation for particular tables
        // like Trongate pages

        switch ($selectedTable) {
            case 'trongate_pages':
                $this->transferImagesToTrongatePages();
                $this->generateDataForTrongatePages($faker, $selectedTable, $selectedRows, $numRows);
                break;
            default :
                $this->processGeneralTablesThatAreNotSpecialCases($faker, $selectedTable, $selectedRows, $numRows);
        }
    }



    /**
     * Retrieves the picture folder existence based on the selected table from the request body.
     *
     * @throws Exception if the JSON data is invalid
     * @return void
     */
    public function createdataGetPictureFolderExists(): void{
        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Ensure JSON decoding was successful
        if ($postData === null) {
            throw new Exception("Invalid JSON data");
        }
        $selectedTable = $postData['selectedTable'];


        $picDirectoryExists = false;
        // Find out if a picture directory exists
        if ($this->findPicDirectoryExists($selectedTable) ) {
            $picDirectoryExists = true;
        } else {
            // it is just possible that someone is creating fake data before doing anything else
            // so just to be certain let's also check for the existence of a picture field
            // in the table.

            if ($this->checkForExistenceOfPictureFieldAndCreatePicsDirectories($selectedTable) ){
                $picDirectoryExists = true;
            }
        }

        // Output the result as JSON
        header('Content-Type: application/json');
        echo json_encode(array('picDirectoryExists' => $picDirectoryExists));
    }



    /**
     * Creates image folders and transfers images for a given data set.
     *
     * This function retrieves raw POST data, validates it, and checks for the
     * 'selectedTable' parameter. It then constructs an SQL query to select the
     * 'id' and 'picture' columns from the selected table. It ensures that the
     * user is allowed to perform the action, executes the SQL query using PDO,
     * and fetches the results. If no records are found, an exception is thrown.
     * The function returns the total number of records to process as a JSON
     * response. If any exceptions occur, an error message is returned as a JSON
     * response.
     *
     * @throws Exception Invalid JSON data or missing 'selectedTable' parameter.
     * @throws Exception No records found for the selected table.
     * @return void
     */
    public function createdataSetImageFoldersAndTransferImages(): void{
        try {
            // Retrieve raw POST data
            $rawPostData = file_get_contents('php://input');
            $postData = json_decode($rawPostData, true);

            // Validate POST data and check for 'selectedTable' parameter
            if ($postData === null || !isset($postData['selectedTable'])) {
                throw new Exception("Invalid JSON data or missing 'selectedTable' parameter.");
            }

            $selectedTable = $postData['selectedTable'];

            //TODO: Check that the query below will work with primary keys that are not named id

            // Construct SQL query to select id and picture from the selected table
            $sql = 'SELECT id, picture FROM ' . $selectedTable;

            // Ensure the user is allowed to perform this action
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed();

            // Execute the SQL query using PDO
            $stmt = $this->dbh->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
            // Check if any records were retrieved
            if (empty($rows)) {
                throw new Exception("No records found for the selected table.");
            }

            // Return the total number of records to process
            $totalRows = count($rows);
            echo json_encode(['totalRows' => $totalRows]);

        } catch (Exception $e) {
            // Handle exceptions and return error message as JSON
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Creates a copy of an image for a specific record in the database.
     *
     * This function retrieves the raw POST data, checks if the data is valid,
     * extracts the record ID and selected table from the POST data, retrieves
     * the column info for the table, finds the primary key field, constructs
     * an SQL query to select the picture from the selected table, ensures the
     * user is allowed to perform the action, executes the SQL query using PDO,
     * checks if there is at least one element in the array, copies image files
     * to the specified directories, and sends a success response.
     *
     * @throws Exception if there is an error in the process.
     * @return void
     */
    public function createdataCopyImageForRecords(): void{
        try {
            // Get the raw POST data
            $rawPostData = file_get_contents('php://input');
            $postData = json_decode($rawPostData, true);

            // Check if the POST data is valid
            if ($postData === null || !isset($postData['recordId'], $postData['selectedTable'])) {
                // Invalid POST data, send an error response
                http_response_code(400); // Bad request
                echo json_encode(['message' => 'Invalid request data']);
                return;
            }

            // Extract the record ID and selected table from the POST data
            $id = $postData['recordId'];
            $selectedTable = $postData['selectedTable'];

            // Output the data as JSON


            // Retrieve the column info for the table and find the primary key field
            $sql = 'SHOW COLUMNS IN ' . $selectedTable;
            $stmt = $this->dbh->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $field = '';
            foreach ($columns as $column) {
                if ($column['Key'] == 'PRI') {
                    $field = $column['Field'];
                    break;
                }
            }

            if (empty($field)) {
                // No primary key field found, send an error response
                http_response_code(400); // Bad request
                echo json_encode(['message' => 'Primary key not found in the selected table']);
                return;
            }

            // Construct SQL query to select the picture from the selected table
            $sql = 'SELECT picture FROM ' . $selectedTable . ' WHERE ' . $field . ' = :id';

            // Ensure the user is allowed to perform this action
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed();

            // Execute the SQL query using PDO
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([':id' => $id]);
            $pictureData = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Check if there is at least one element in the array
            if (!empty($pictureData) && isset($pictureData[0]->picture)) {
                $picture = $pictureData[0]->picture;
                echo $picture;
            } else {
                // Handle the case where there is no "picture" property in the response
                echo "No picture found";
                return;
            }

            // Define base directories
            $basedir = APPPATH . 'modules/vtlgen/assets/images/';
            //$basedir = __DIR__ . '/../assets/images';
            $picDirectoryPath = $this->getPicDirectory($selectedTable);

            // Copy image files
            $this->copyImageFile($basedir, $picDirectoryPath, $id, $picture);
            $this->copyImageFile($basedir . 'thumbnails/', $picDirectoryPath . '_thumbnails/', $id, $picture);

            // Send a success response
            http_response_code(200); // OK
//            echo json_encode(['message' => 'Image copied successfully for record ' . $id]);

        } catch (Exception $e) {
            // Handle exceptions and return error message as JSON
            http_response_code(500); // Internal server error
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    //endregion

    //region createtable view functions

    /**
     * Saves the SQL data table creation script to a file.
     *
     * This function retrieves the raw POST data from the request body, decodes it into an associative array,
     * extracts the table name and SQL script from the decoded JSON data. It then creates a folder if it doesn't exist
     * and saves the SQL script to a file with the table name and .sql extension. If the file cannot be saved, an
     * exception is thrown. The function returns a JSON response indicating the status and message of the operation.
     *
     * @throws Exception if the SQL script cannot be saved to the file
     * @return void
     */
    public function createtableSaveSqlDataTableCreationScript(): void{
        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $tableName = $postData['tableName'];
        $sql = $postData['sql'];

        // Initialize response array
        $response = ['status' => '', 'message' => ''];
        try {
            $folderPath = SQL_SCRIPTS_LOCATION;
            //$folderPath = __DIR__ . '/../assets/sqltablescripts';
            if (is_dir($folderPath)) {
                // we have a folder
            } else {
                if (mkdir($folderPath, 0777, true)) {
                    // Creates the directory recursively if it doesn't exist
                } else {
                    echo "Failed to create folder!";
                }
            }
            // Define the filename with the table name and .sql extension
            $fileName = $folderPath . '/CreateTable_' . $tableName . '.sql';

            // Save the SQL script to the file
            if (file_put_contents($fileName, $sql) === false) {
                throw new Exception("Failed to save SQL script to file!");
            }

            // Set the response status and message
            $response['status'] = 'success';
            $response['message'] = 'SQL script saved successfully.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }


    /**
     * Creates a new data table by executing the SQL statement provided in the request body.
     *
     * @throws Exception if the SQL statement execution fails
     * @return void
     */
    public function createtableCreateNewDataTable(): void{
        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $sql = $postData['sql'];

        // Initialize response array
        $response = ['status' => '', 'message' => ''];

        // Execute the SQL statement and handle any exceptions
        try {
            $this->dbh->exec($sql);
            $response['status'] = 'success';
            $response['message'] = 'Operation completed successfully.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'Operation failed: ' . $e->getMessage();
        }

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    //endregion

    //region DeleteOrDrop view functions

    /**
     * Deletes table data based on the selected tables and resetAutoIncrement flag.
     *
     * @throws PDOException when a database operation fails
     * @throws Exception when the operation fails
     * @return void
     */
    public function deleteordropDeleteTableData() {
        // At the top of your PHP script
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);

        // Start the output buffering to prevent any accidental output
        ob_start();

        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        $selectedTables = $postData['selectedTables'];
        $resetAutoIncrement = $postData['resetAutoIncrement'];

        if ($selectedTables != null && $selectedTables != "") {
            $responseText = '';
            $deletedTables = [];
            $failedTables = [];

            try {
                if ($resetAutoIncrement) {
                    foreach ($selectedTables as $selectedTable) {
                        $sql = 'nothing';
                        $table = $selectedTable['table'];
                        switch ($table) {

                            case 'trongate_users':
                            case 'trongate_user_levels':
                            case 'trongate_administrators':
                                break;
                            default:
                                $sql = 'TRUNCATE TABLE ' . $table;
                                break;
                        }

                        if ($sql != 'nothing') {
                            try {
                                $stmt = $this->dbh->prepare($sql);
                                $stmt->execute();
                                $deletedTables[] = $table;

                                if ($this->findPicDirectoryExists($table)) {
                                    $picDirectory = $this->getPicDirectory($table);
                                    $this->deleteSubDirectories($picDirectory);
                                    $thumbsDir = $picDirectory . '_thumbnails';
                                    $this->deleteSubDirectories($thumbsDir);
                                }

                                if ($table === 'trongate_pages') {
                                    $sourcedir = APPPATH . 'modules/trongate_pages/assets/images/uploads';
                                    for ($i = 1; $i <= 11; $i++) {
                                        $filename = 'img' . $i . '.jpg';
                                        $filepath = $sourcedir . '/' . $filename;
                                        if (file_exists($filepath)) {
                                            unlink($filepath);
                                        }
                                    }
                                }
                            } catch (PDOException $e) {
                                $failedTables[] = $table;
                            }
                        }
                    }
                } else {
                    foreach ($selectedTables as $selectedTable) {
                        $table = $selectedTable['table'];
                        $sql = 'DELETE FROM ' . $table;

                        switch ($table) {
                            case 'trongate_users':
                            case 'trongate_user_levels':
                            case 'trongate_administrators':
                                $sql .= ' WHERE id > 1';
                                break;
                            default:
                                break;
                        }

                        try {
                            $stmt = $this->dbh->prepare($sql);
                            $stmt->execute();
                            $deletedTables[] = $table;

                            if ($this->findPicDirectoryExists($table)) {
                                $picDirectory = $this->getPicDirectory($table);
                                $this->deleteSubDirectories($picDirectory);
                                $thumbsDir = $picDirectory . '_thumbnails';
                                $this->deleteSubDirectories($thumbsDir);
                            }

                            if ($table === 'trongate_pages') {
                                $sourcedir = APPPATH . 'modules/trongate_pages/assets/images/uploads';
                                for ($i = 1; $i <= 11; $i++) {
                                    $filename = 'img' . $i . '.jpg';
                                    $filepath = $sourcedir . '/' . $filename;
                                    if (file_exists($filepath)) {
                                        unlink($filepath);
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            $failedTables[] = $table;
                        }
                    }
                }

                ob_end_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'Operation completed successfully.',
                    'deletedTables' => implode("\n", $deletedTables),
                    'failedTables' => implode("\n", $failedTables)
                ]);
            } catch (Exception $e) {
                ob_end_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Operation failed: ' . $e->getMessage(),
                    'deletedTables' => implode("\n", $deletedTables),
                    'failedTables' => implode("\n", $failedTables)
                ]);
            }
        } else {
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'message' => 'No Tables were selected'
            ]);
        }
    }

    /**
     * Drops table data based on the selected tables.
     *
     * @return void
     */
    public function deleteordropDropTables() {
        // Initialize response data
        $response = [
            'success' => true,
            'message' => '',
            'deletedTables' => [],
            'failedTables' => []
        ];

        // Get JSON data from request
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $selectedTables = $postData['selectedTables'];

        // Check if tables were selected
        if (!empty($selectedTables)) {
            try {
                // Loop through selected tables
                foreach ($selectedTables as $selectedTable) {
                    // Prepare SQL query to drop table
                    $table = $selectedTable['table'];
                    $sql = 'DROP TABLE IF EXISTS ' . $table;

                    // Execute SQL query
                    try {
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->execute();

                        // Add table to list of deleted tables
                        $response['deletedTables'][] = $table;
                    } catch (PDOException $ex) {
                        // Add table to list of failed tables
                        $response['failedTables'][] = $table;
                    }
                }

                // Set success message
                $response['message'] = 'Operation completed successfully.';
            } catch (Exception $e) {
                // Set error message
                $response['success'] = false;
                $response['message'] = 'Operation failed: ' . $e->getMessage();
            }
        } else {
            // Set error message for no tables selected
            $response['success'] = false;
            $response['message'] = 'No tables were selected';
        }

        // Output response as JSON
        echo json_encode($response);
    }

    //endregion

    //region Faker functions

    /**
     * Process general tables that are not special cases.
     *
     * @param Faker\Generator $faker         The Faker generator object.
     * @param string          $selectedTable The selected table.
     * @param array|null      $selectedRows  The selected rows.
     * @param int|null        $numRows       The number of rows.
     * @return void
     */
    private function processGeneralTablesThatAreNotSpecialCases(\Faker\Generator $faker, string $selectedTable, ?array $selectedRows, ?int $numRows): void
    {
        $picDirectoryExists = $this->findPicDirectoryExists($selectedTable);
        if ($picDirectoryExists) {
            $picDirectory = $this->getPicDirectory($selectedTable);
        }

        // Determine the method for generating and inserting fake data
        if ($selectedRows !== null) {
            // Check if $numRows is an integer
            if (is_numeric($numRows) && intval($numRows) == $numRows) {
                $this->generateAndInsertRowsInBatches($faker, $selectedRows, $selectedTable, $numRows);
            }
        } else {
           echo 'No Rows were selected';
        }
    }

    private function generateAndInsertRowsInBatches(\Faker\Generator $faker, array $selectedRows, string $selectedTable, ?int $numRows = null, int $batchSize = 1000): void
    {
        // At the top of your PHP script
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);

        // Start the output buffering to prevent any accidental output
        ob_start();

        try {
            // Check if $numRows is an integer
            if (!is_numeric($numRows) || intval($numRows) != $numRows) {
                $numRows = 1;
            }
            // Determine the number of rows to generate
            $numRows = $numRows ?? 1;

            // Begin transaction
            $this->dbh->beginTransaction();

            $totalRowsInserted = 0;

            // Insert rows in batches
            for ($offset = 0; $offset < $numRows; $offset += $batchSize) {
                // Calculate actual batch size
                $currentBatchSize = min($batchSize, $numRows - $offset);

                // Generate values for this batch of rows
                $batchValues = $this->generateBatchValues($faker, $selectedRows, $currentBatchSize);

                // Construct SQL statement
                $sql = $this->buildBatchInsertQuery($selectedTable, $selectedRows, $batchValues);

                // Prepare and execute SQL statement
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute();

                // Count rows inserted in this batch
                $rowsInserted = $stmt->rowCount();
                $totalRowsInserted += $rowsInserted;
            }

            // Commit transaction after all batches
            $this->dbh->commit();

            // Clear the output buffer and disable buffering
            ob_end_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Successfully inserted ' . $totalRowsInserted . ' rows into ' . $selectedTable . '.'
            ]);
        } catch (PDOException $e) {
            // Roll back transaction on error
            $this->dbh->rollBack();

            // Clear the output buffer and disable buffering
            ob_end_clean();

            echo json_encode([
                'success' => false,
                'message' => 'Failed to insert rows: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            // Handle any other exceptions
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generates a batch of values using Faker and the provided selected rows.
     *
     * @param \Faker\Generator $faker The Faker generator instance.
     * @param array $selectedRows The array of selected rows.
     * @param int $batchSize The number of values to generate in the batch.
     * @return string The generated batch of values as a string.
     */
    private function generateBatchValues(\Faker\Generator $faker, array $selectedRows, int $batchSize): string
    {
        $values = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $rowValues = [];

            foreach ($selectedRows as $selectedRow) {
                $field = $this->processFieldName($selectedRow['field']);
                $dbType = $selectedRow['type'];
                list($type, $length) = $this->parseDatabaseType($dbType);

                // Generate fake value using the field and type information
                $fieldFakerStatement = $this->generateValueFromFieldName($faker, $field, $length);
                $customFieldValue = $this->checkForCustomFieldNameGeneration($field, $faker);

                // Use custom value if provided, otherwise use the default generation
                if ($customFieldValue !== 'nothing') {
                    $fieldFakerStatement = $customFieldValue;
                }

                if ($fieldFakerStatement === "nothing") {
                    $fieldFakerStatement = $this->generateValueFromType($faker, $type, $length);
                }

                // Ensure the generated value is properly quoted for SQL insertion
                $rowValues[] = $this->dbh->quote($fieldFakerStatement);
            }

            $values[] = '(' . implode(',', $rowValues) . ')';
        }

        return implode(',', $values);
    }


    /**
     * Builds a batch insert query for the given selected table and selected rows.
     *
     * @param string $selectedTable The name of the table to insert into.
     * @param array $selectedRows An array of rows, each containing a 'field' key.
     * @param string $batchValues The values to insert, formatted as a string.
     * @return string The built insert query.
     */
    private function buildBatchInsertQuery(string $selectedTable, array $selectedRows, string $batchValues): string
    {
        $columns = array_map(function($row) {
            return $row['field'];
        }, $selectedRows);

        $columnList = implode(',', $columns);

        return sprintf('INSERT INTO %s (%s) VALUES %s;', $selectedTable, $columnList, $batchValues);
    }


    /**
     * Processes the input string to prepare it as a field name.
     *
     * This function takes an input string and performs the following operations:
     * - Trims leading and trailing whitespace.
     * - Removes spaces, underscores, and dashes from the string.
     * - Converts the string to lowercase.
     *
     * @param string $inputString The input string to be processed.
     * @return string Returns the processed field name string.
     */
    private function processFieldName($inputString): string
    {
        // Trim leading and trailing whitespace
        $trimmedString = trim($inputString);

        // Remove spaces, underscores, and dashes from the string
        $filteredString = preg_replace('/[\s_\-]+/', '', $trimmedString);

        // Convert the string to lowercase
        return strtolower($filteredString);
    }

    /**
     * Parses the database type definition and returns the type and length.
     *
     * @param string $dbType The database type definition.
     * @return array Returns an array containing the type and length.
     */
    private function parseDatabaseType($dbType): array
    {
        // Split the type definition by "(" and ")"
        $parts = explode('(', $dbType);

        // Extract the type
        $type = $parts[0];

        // Check if the split was successful
        if (count($parts) < 2) {
            // If not, return type with a default length value
            return array($type, -1);
        }

        // Extract the length
        $length = rtrim($parts[1], ')');
        return array($type, $length);
    }

    /**
     * Generates a value based on the provided field name and length.
     *
     * @param \Faker\Generator $faker The Faker generator instance.
     * @param string $fieldName The name of the field.
     * @param int $length The length of the field.
     * @return mixed Returns the generated value.
     */
    private function generateValueFromFieldName(\Faker\Generator $faker, string $fieldName, int $length): mixed
    {
        $statement = null;
        $value = null;
        switch ($fieldName) {
            case 'firstname':
                $value = $faker->firstName();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'lastname':
                $value = $faker->lastName();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'customername':
            case 'name':
                $value = $faker->name();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'username':
                $value = $faker->userName();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'customeremail':
            case 'emailaddress':
            case 'email':
                $value = $faker->email();
            //$statement = '"' . $value . '"';
            $statement = $value;
                break;

            case 'password':
                $value = $faker->password();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'age':
                $value = $faker->numberBetween($min = 18, $max = 99);
                $statement = $value;
                break;

            case 'customeraddress':
            case 'companyaddress':
            case 'address':
                $value = $faker->address();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'city':
            case 'town':
                $value = $faker->city();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;


            case 'addressline1':
            case 'addressline2':
            case 'addressline3':
            case 'streetaddress':
                $value = $faker->streetAddress();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'state';
                $value = $faker->state();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'county':
                $value = $faker->county();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'country':
                $value = $faker->country();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'zipcode':
            case 'postcode':
                $value = $faker->postcode();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'phone':
                $value = $faker->phoneNumber();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'company':
                $value = $faker->company();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'job':
                $value = $faker->jobTitle();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'title':
                $value = $faker->title();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'deliverydate':
            case 'orderdate':
            case 'lastupdateddate':
            case 'datemodified':
            case 'dateadded':
            case 'date':
            case 'dateofbirth':
            case 'dob':
                $value = $faker->date($format = 'Y-m-d', $max = 'now');
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'gender':
                $value = $faker->randomElement(['Male', 'Female']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'website':
                $value = $faker->url();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'comment':
            case 'productdescription':
            case 'description':
                $value = $faker->text();
                if ($length == -1) {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                } else {
                    if (!is_int($length)) {
                        $length = intval($length);
                    }
                    //$statement = '"' . substr($value, 0, $length) . '"';
                    $statement = substr($value, 0, $length);
                }
                break;

            case 'lastupdated':
            case 'datecreated':
                $value = $faker->unixTime(new dateTime('-3 days'));
                $statement = $value;
                break;

            case 'active':
            case 'isactive':
                $value = $faker->boolean();
                $statement = $value;
                break;

            case 'productname':
                $value = $faker->productName();
                if ($length == -1) {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                } else {
                    if (!is_int($length)) {
                        $length = intval($length);
                    }
                    //$statement = '"' . substr($value, 0, $length) . '"';
                    $statement = substr($value, 0, $length);
                }
                break;

            case 'category':
                $value = $faker->category();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'sku':
            case 'productsku':
                $value = $faker->sku();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'pagetitle':
                $value = $faker->words(5);
                if (is_array($value)) {
                    //$statement = '"' . implode(' ', $value) . '"';
                    $statement =implode($value);
                } else {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                }
                break;
            case 'metakeywords':
                $value = $faker->words;
                if (is_array($value)) {
                    //$statement = '"' . implode(', ', $value) . '"';
                    $statement =implode($value);
                } else {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                }
                break;
            case 'metadescription':
                $value = $faker->sentence(7);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;
            case 'pagebody':
                $value = $faker->realText(200, 2);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;
            case 'picture':
            case 'pictureurl':
            case 'productimage':
            case 'productimageurl':
            case 'image':
            case 'imageurl':
                $value = $faker->randomElement(['img1.jpg', 'img2.jpg', 'img3.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg', 'img7.jpg', 'img8.jpg', 'img9.jpg', 'img10.jpg', 'img11.jpg']);
            //$statement = '"' . $value . '"';
            $statement = $value;
                break;
            case 'totalamount':
            case 'total':
            case 'ordernumber':
            case 'quantity':
            case 'price':
            case 'productprice':
                $value = $faker->numberBetween($min = 0, $max = 1000000);
                $statement = $value;
                break;

            case 'orderstatus':
                $value = $faker->randomElement(['Processed', 'Out for Delivery', 'Fulfilled']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'deliverystatus':
                $value = $faker->randomElement(['Delivered', 'Returned']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'paymentmethod':
                $value = $faker->randomElement(['Cash', 'Credit Card', 'PayPal']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'paymentstatus':
                $value = $faker->randomElement(['Paid', 'Unpaid']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'paymenttype':
                $value = $faker->randomElement(['Credit Card', 'Cash', 'PayPal']);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'transactionid':
                $value = $faker->uuid();
                $statement = $value;
                break;

            case 'discount':
            case 'discountpercentage':
                $value = $faker->numberBetween($min = 0, $max = 100);
                $statement = $value;
                break;

            case 'taxamount':
                $value = $faker->randomFloat(2, 0, 50);
                $statement = $value;
                break;

            case 'task':
            case 'tasktitle':
                $value = $faker->complexTask(); //This could be substituted with task()
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;


            default:
                $statement = 'nothing';
        }
        //allow for the fact that a known field name may still fail to get data
        if ($statement === null) {
            $statement = 'nothing';
        }
        return $statement;
    }

    /**
     * Checks if the given field name requires custom field name generation.
     *
     * @param string $field The field name to check.
     * @param \Faker\Generator $faker The Faker generator instance.
     * @return mixed The generated statement or 'nothing' if no custom generation is required.
     */
    private function checkForCustomFieldNameGeneration(string $field, \Faker\Generator $faker): mixed
    {
        $statement = null;
        $value = null;
        switch ($field) {
            // Add your custom field name generation here:
            //This would be in the form of a case statement

//            case 'productid';
//                $value = $faker->$faker->numberBetween($min = 0, $max = 250);
//                $statement = $value;
//                break;


            //  NB   if dealing with string values $statement should be set like this
            //  $statement = '"' . $value . '"';

            // DO NOT DELETE THIS PART OR THE SWITCH STATEMENT OR YOU WILL BREAK THE GENERATOR
            default:
                $statement = 'nothing';
        }
        //allow for the fact that a known field name may still fail to get data
        if ($statement === null) {
            $statement = 'nothing';
        }
        return $statement;

    }

    /**
     * Generates a value based on the given type and length using the Faker library.
     *
     * @param Faker\Generator $faker The Faker library instance.
     * @param string $type The data type.
     * @param int|string $length The length of the value. If -1, the entire value is used.
     * @return string|int|float|bool The generated value.
     */
    private function generateValueFromType($faker, $type, $length)
    {
        $statement = null;
        $value = null;
        switch ($type) {

            case 'int':
            case 'bigint':
                $value = $faker->randomNumber();
                $statement = $value;
                break;

            case 'smallint':
                $value = $faker->numberBetween(1, 32767);
                $statement = $value;
                break;


            case 'varchar':
            case 'blob':
            case 'text':

                $value = $faker->text();
                if ($length == -1) {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                } else {
                    if (!is_int($length)) {
                        $length = intval($length);
                    }
                    //$statement = '"' . substr($value, 0, $length) . '"';
                    $statement = substr($value, 0, $length);
                }
                break;

            case 'char':
            case 'binary':
            case 'varbinary':
                $value = $faker->word();
                if ($length == -1) {
                    //$statement = '"' . $value . '"';
                    $statement = $value;
                } else {
                    if (!is_int($length)) {
                        $length = intval($length);
                    }
                    //$statement = '"' . substr($value, 0, $length) . '"';
                    $statement = substr($value, 0, $length);
                }
                break;

            case 'float':
            case 'double':
                $value = $faker->randomFloat();
                $statement = $value;
                break;

            case 'decimal':
                $value = $faker->randomFloat(NULL, 0, 999999.99);
                $statement = $value;
                break;

            case 'date':
                $value = $faker->date(FAKER_DATE_FORMAT, $max = 'now');
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'timestamp':
            case 'datetime':
                $value = $faker->dateTime()->format(FAKER_DATETIME_FORMAT);
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'time':
                $value = $faker->time();
                //$statement = '"' . $value . '"';
                $statement = $value;
                break;

            case 'tinyint':
                $value = $faker->boolean();
                $statement = $value;
                break;

            case 'bit':
                $value = $faker->randomElement(['0', '1']);
                $statement = $value;
                break;

            case 'enum':
                $value = $faker->randomElement(['value1', 'value2', 'value3']);
                $statement = $value;
                break;

            case 'set':
                $value = $faker->randomElements(['value1', 'value2', 'value3'], 2);
                $statement = $value;
                break;

            default:
                $statement = '';
        }
        return $statement;
    }


    //endregion

    //region Faker Picture Manipulation

    /**
     * Finds if the picture directory exists for the specified table.
     *
     * @param string $selectedTable The name of the table to search for.
     * @return bool Returns true if the picture directory exists for the specified module, false if it is an orphaned
     *              table, and false if the module name is not found or no API JSON exists for the table.
     */
    public function findPicDirectoryExists($selectedTable): bool
    {
        // Iterate over application modules to find the specified table
        foreach ($this->applicationModules as $module) {
            if (isset($module['module_name']) && $module['module_name'] === $selectedTable) {
                // Return true if picture directory exists for the specified module
                return $module['pic_directory_exists'];

            } elseif (isset($module['orphaned_tables']) && $module['orphaned_tables'] === $selectedTable) {
                return false;
            }
        }
        // Return false if the module name is not found or no API JSON exists for the table
        return false;

    }


    /**
     * Retrieves the picture directory for the specified table.
     *
     * @param string $selectedTable The name of the table to retrieve the picture directory for.
     * @return mixed The picture directory for the specified table, or an empty string if the table is not found.
     */
    public function getPicDirectory($selectedTable): mixed
    {
        // Iterate over application modules to find the specified table
        foreach ($this->applicationModules as $module) {
            if (isset($module['module_name']) && $module['module_name'] === $selectedTable) {
                // Return true if API JSON exists for the specified table
                return $module['pic_directory'];
            } elseif (isset($module['orphaned_tables']) && $module['orphaned_tables'] === $selectedTable) {
                return '';
            }
        }
        // Return false if the module name is not found or no API JSON exists for the table
        return '';
    }

    /**
     * Checks if the specified table has a 'picture' field of type 'varchar(255)'.
     * If it does, creates directories for pictures and thumbnails.
     *
     * @param string $selectedTable The name of the table to check.
     * @return bool Returns true if the 'picture' field exists and directories were created successfully, false
     *              otherwise.
     */
    private function checkForExistenceOfPictureFieldAndCreatePicsDirectories($selectedTable): bool{
        $result = 0;
        $pictureFieldExists = false;
        $sql = 'SHOW COLUMNS FROM ' . $selectedTable;
        try {
            $stmt = $this->dbh->query($sql);
            $colInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($colInfo as $column) {
                if ($column['Field'] === 'picture' && $column['Type'] === 'varchar(255)') {
                    $pictureFieldExists = true;
                    break;
                }
            }

            if ($pictureFieldExists) {
                // Define paths for pictures and thumbnails
                $picsPath = APPPATH . 'modules/' . $selectedTable . '/assets/' . $selectedTable . '_pics';
                $thumbsPath = APPPATH . 'modules/' . $selectedTable . '/assets/' . $selectedTable . '_pics_thumbnails';

                // Create directories if they do not exist
                mkdir($picsPath, 0777, true);
                mkdir($thumbsPath, 0777, true);

                // Check if directories were created successfully
                if (is_dir($picsPath) && is_dir($thumbsPath)) {
                    $result = 1;
                }
            }
        } catch (PDOException $e) {
            // Handle any exceptions
            var_dump('I have errored: ', $e->getMessage());
            echo($e->getMessage());
        }
        return (bool) $result;
    }

    /**
     * Copies an image file from the source directory to the target directory.
     *
     * @param string $sourceDir The directory containing the source image file.
     * @param string $targetDir The directory where the image file will be copied to.
     * @param int $id The ID used to create a subdirectory in the target directory (if not a thumbnail).
     * @param string $fileName The name of the image file to be copied.
     * @param bool $isThumbnail (optional) Whether the image is a thumbnail or not. Defaults to false.
     * @throws Exception If the source image file does not exist or if the copy operation fails.
     * @return void
     */
    private function copyImageFile(string $sourceDir, string $targetDir, int $id, string $fileName, bool $isThumbnail = false): void
    {
        $sourceFile = $sourceDir . ($isThumbnail ? 'thumbnails/' : '') . $fileName;

        $targetSubDir = rtrim($targetDir, '/') . ($isThumbnail ? '_thumbnails/' : '/') . ($isThumbnail ? '' : $id . '/');
        $targetFile = $targetSubDir . $fileName;

        if (!file_exists($sourceFile)) {
            throw new Exception("Source " . ($isThumbnail ? "thumbnail " : "") . "image file '$sourceFile' does not exist.");
        }

        if (!file_exists($targetSubDir)) {
            mkdir($targetSubDir, 0777, true);
        }

        if (!copy($sourceFile, $targetFile)) {
            throw new Exception("Failed to copy " . ($isThumbnail ? "thumbnail " : "") . "image file '$sourceFile' to '$targetFile'.");
        }
    }

    //endregion

    //region Fake Trongate Pages



    /**
     * Transfer Images to Trongate Pages
     *
     * This function checks if certain images reside in a specified directory and transfers them to another directory.
     * If the image does not exist in the target directory, it copies images from a source directory to the target
     * directory.
     *
     * @return void
     */
    private function transferImagesToTrongatePages(): void
    {
        //check if img1.png resides in the images/uploades directory
        $basedir = APPPATH . 'modules/vtlgen/assets/images/';
        $sourcedir = APPPATH . 'modules/trongate_pages/assets/images/uploads';
        if (!file_exists($sourcedir . '/img1.jpg')) {
            // Copy files from $basedir to $sourcedir
            $files = scandir($basedir);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {

                    $sourceFile = $basedir . $file;

                    $destinationFile = $sourcedir . '/' . $file;
                    // Check if the path is a regular file before copying
                    if (is_file($sourceFile)) {
                        copy($sourceFile, $destinationFile);
                    }
                }
            }
        }
    }

    /**
     * Generate Data for Trongate Pages
     *
     * This function generates data for Trongate Pages based on provided criteria and inserts them into the specified
     * table.
     *
     * @param object $faker         The Faker object for generating fake data.
     * @param string $selectedTable The name of the table where the data will be inserted.
     * @param array  $selectedRows  The array containing information about selected fields.
     * @param int    $numRows       The number of rows to generate.
     * @return void
     */
    private function generateDataForTrongatePages($faker, $selectedTable, $selectedRows, $numRows)
    {
        // Count the current tally of trongate pages
        $countSql = 'SELECT count(*) as count FROM ' . $selectedTable;
        $stmt = $this->dbh->query($countSql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the result is not empty and has the 'count' key
        $pagesCount = !empty($result) && isset($result['count']) ? (int)$result['count'] : 0;

        // Set number of rows to generate
        $numRows = is_int($numRows) ? $numRows : intval($numRows);

        $columns = '(' . implode(',', array_column($selectedRows, 'field')) . ')';
        $values = '';

        // Generate the values needed
        $pageTitle = '';
        for ($i = 0; $i < $numRows; $i++) {
            $rowValues = '';

            foreach ($selectedRows as $selectedRow) {
                $field = $this->processFieldName($selectedRow['field']);
                $value = null;

                switch ($field) {
                    case 'urlstring':
                        $existingUrlsStmt = $this->dbh->query('SELECT url_string FROM ' . $selectedTable);
                        $existingUrls = $existingUrlsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                        do {
                            $proposedUrl = 'article' . ($pagesCount + $i + 1);
                            $unique = !in_array($proposedUrl, $existingUrls);
                            if (!$unique) $i++;
                        } while (!$unique);
                        $value = '"' . $proposedUrl . '"';
                        break;
                    case 'pagetitle':
                        $pageTitle = $faker->articleTitle();
                        $value = '"' . $pageTitle . '"';
                        break;
                    case 'metakeywords':
                        $metaKeywords = $faker->metaKeywords(rand(1, 6));
                        $value = '"' . implode(', ', $metaKeywords) . '"';
                        break;
                    case 'metadescription':
                        $value = '"' . $faker->metaDescription() . '"';
                        break;
                    case 'pagebody':
                        $numParas = rand(1, 4);
                        $numSentences = rand(1, 3);
                        $pagebody = '<h1>' . $pageTitle . '</h1>';
                        for ($j = 0; $j < $numParas; $j++) {
                            $text = '';
                            for ($k = 0; $k < $numSentences; $k++) {
                                $text .= $faker->sentence() . ' ';
                            }
                            $text = '<div class=""text-div""><p>' . $text . '</p></div>';
                            $img = $faker->randomElement(['img1.jpg', 'img2.jpg', 'img3.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg', 'img7.jpg', 'img8.jpg', 'img9.jpg', 'img10.jpg', 'img11.jpg']);
                            $imgText = '<img src="' . BASE_URL . 'trongate_pages_module/images/uploads/' . $img . '" />';
                            $pagebody .= $text . $imgText;
                        }
                        $pagebody = '"' . str_replace('"', '""', $pagebody) . '"';
                        $value = $pagebody;
                        break;
                    case 'datecreated':
                        $value = $faker->unixTime(new DateTime('-3 days'));
                        break;
                    case 'lastupdated':
                        $value = $faker->unixTime(new DateTime('-1 days'));
                        break;
                    case 'published':
                        $value = $faker->numberBetween(0, 1);
                        break;
                    case 'createdby':
                        $value = 1;
                        break;
                }
                $rowValues .= ($rowValues !== '' ? ',' : '') . $value;
            }
            $values .= '(' . $rowValues . ')' . ($i < $numRows - 1 ? ', ' : '');
        }

        $sql = 'INSERT INTO ' . $selectedTable . ' ' . $columns . ' VALUES ' . $values;

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
            echo json_encode([
                'success' => true,
                'message' => 'The following number of rows were inserted into trongate_pages: ' . $numRows . '.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }



    }

    //endregion

    public function modaltest(): void{
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'modaltest';
        $this->template('admin', $data);
    }




}
