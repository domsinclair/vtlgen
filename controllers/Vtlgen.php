<?php
require_once __DIR__ . '/../assets/vendor/autoload.php';
require_once __DIR__ . '/../assets/vtlgenConfig.php';
include_once(__DIR__ . '/../assets/vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php');
use Ifsnop\Mysqldump as IMysqldump;
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

    private array $updateInfo;
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

        // Assuming APPPATH is defined somewhere in the framework
        $sourceDir = rtrim(APPPATH, '/\\'); // Remove trailing slashes
        $parentDir = dirname($sourceDir); // Extract the parent directory path
        $appname = basename($sourceDir); // Extract the last segment (application name)

        // Define the constants for use with zip functions
        if (!defined('SOURCE_DIR')) {
            define('SOURCE_DIR', $sourceDir);
        }

        if (!defined('PARENT_DIR')) {
            define('PARENT_DIR', $parentDir);
        }

        if (!defined('APP_NAME')) {
            define('APP_NAME', $appname);
        }

        // check for updates
        $this->initialiseUpdateCache();
        $this->updateInfo = $this->CheckGithubForUpdates();

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
        $listItemHTML = "\n<li><?= anchor('vtlgen', '<img src=\"vtlgen_module/help/images/vtlgen.svg\" alt=\"Vtl Data Generator\"> Vtl Data Generator') ?></li>\n";

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


        $data['tables'] = $this->setupTablesForDropdown();
        $data['headline'] = 'Vtl Data Generator: Home Page';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'vtlgen';
        $data['update_available'] = $this->updateInfo['update_available'];
        $data['new_version'] = $this->updateInfo['new_version'] ?? null;
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
     * Unzips a module or project in the Vtl Data Generator.
     *
     * @return void
     */
    public function vtlgenUnzipModule(): void {
        header('Content-Type: application/json');

        try {
            // Define the target directory
            $targetDir = APPPATH . 'modules/';

            // Ensure the directory ends with a slash
            if (substr($targetDir, -1) !== DIRECTORY_SEPARATOR) {
                $targetDir .= DIRECTORY_SEPARATOR;
            }

            // Initialize an array to store debugging messages
            $debugMessages = [];

            // Check if the request contains a file
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zipFile'])) {
                $file = $_FILES['zipFile'];
                $debugMessages[] = "File upload detected: " . print_r($file, true);

                // Ensure the uploaded file is a ZIP file
                $fileType = mime_content_type($file['tmp_name']);
                if ($fileType !== 'application/zip' && $fileType !== 'application/x-zip-compressed') {
                    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a ZIP file.', 'debug' => $debugMessages]);
                    exit;
                }

                // Define the target file path
                $targetFilePath = $targetDir . basename($file['name']);
                $debugMessages[] = "Target file path: " . $targetFilePath;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    $debugMessages[] = "File moved to target directory.";

                    // Create a directory named after the ZIP file
                    $zipDirName = pathinfo($targetFilePath, PATHINFO_FILENAME);
                    $extractToDir = $targetDir . $zipDirName;
                    if (!is_dir($extractToDir)) {
                        mkdir($extractToDir, 0755, true);
                    }

                    // Unzip the file
                    $zip = new ZipArchive;
                    if ($zip->open($targetFilePath) === TRUE) {
                        $zip->extractTo($extractToDir);
                        $zip->close();
                        // Optionally delete the ZIP file after extraction
                        // @unlink($targetFilePath);

                        $debugMessages[] = "Unzipped to directory: " . $extractToDir;
                        echo json_encode(['success' => true, 'debug' => $debugMessages]);
                    } else {
                        $debugMessages[] = "Failed to unzip the file.";
                        echo json_encode(['success' => false, 'message' => 'Failed to unzip the file.', 'debug' => $debugMessages]);
                    }
                } else {
                    $debugMessages[] = "Failed to move the uploaded file.";
                    echo json_encode(['success' => false, 'message' => 'Failed to move the uploaded file.', 'debug' => $debugMessages]);
                }
            } else {
                $debugMessages[] = "No file uploaded.";
                echo json_encode(['success' => false, 'message' => 'No file uploaded.', 'debug' => $debugMessages]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }





    /**
     * Generates the view for zipping a module or project in the Vtl Data Generator.
     *
     * @return void
     */
    public function vtlgenZipModuleProject(): void {
        $headline = 'Vtl Data Generator: Zip Module or Project';
        $instruction1 = 'Select the module(s) that you want to zip.';
        $instruction2 = 'Alternatively zip the entire application by checking the Zip Project checkbox.';
        $task = 'zip';
        $this ->showDeleteOrZipView($headline, $instruction1, $instruction2, $task);

    }

    /**
     * Deletes a module by displaying a view for the user to select the module(s) to delete.
     *
     * @return void
     */
   public function vtlgenDeleteModules(): void {
       $headline = 'Vtl Data Generator: Delete Module';
       $instruction1 = 'Select the module(s) that you want to delete.';
       $instruction2 = 'This Process is Irreversible!';
       $task = 'delete';
      $this ->showDeleteOrZipView($headline, $instruction1, $instruction2, $task);
   }

    /**
     * Generates the view for creating modules in the Vtl Data Generator.
     *
     * This function retrieves the tables with orphaned modules from the
     * application modules and assigns them to the 'tables' key in the $data
     * array. It also retrieves the columns for the second table in the
     * 'tables' array and assigns them to the 'columns' key in the $data
     * array.
     *
     * The $data array is then populated with additional information such as
     * the headline, instructions, view module, and view file. Finally, the
     * 'admin' template is rendered with the $data array.
     *
     * @return void
     */
    public function vtlgenCreateModules(): void {
        $tables = [];
        foreach ($this->applicationModules as $module) {
            if (isset($module['orphaned_tables'])) {
                $tables = array_values($module['orphaned_tables']);
                break;
            }
        }
        $data['tables'] = $tables;
        $data['columns'] = $this->getColumnDataForGivenTable($tables[2]);
        $data['headline'] = 'Vtl Data Generator: Create Module';
        $data['instruction1'] = 'select those tables for which you wish to create modules.';
        $data['instruction2'] = '';
        $data['noDataMessage'] = 'There are currently no tables that do not have modules associated with them.';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createmodule';
        $this->template('admin', $data);
    }

    /**
     * Executes the update operation for the VTL Generator module.
     *
     * @throws No specific exceptions are thrown by this method.
     * @return void
     */
    public function vtlgenPerformUpdate() {
        $result = $this->updateModule();
        echo json_encode(['message' => $result]);
    }


    /**
     * Checks the prerequisites for updating the VTL Generator module.
     *
     * @return array The result array containing canUpdate, message, execEnabled, and gitInstalled keys.
     */
    public function vtlgenCheckUpdatePrerequisites()
    {
        $result = [
            'canUpdate' => true,
            'message' => '',
            'execEnabled' => function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))),
            'gitInstalled' => false
        ];

        if (!$result['execEnabled']) {
            $result['canUpdate'] = false;
            $result['message'] = 'The PHP exec() function is not available. Please contact your server administrator.';
            return $this->output_json($result);
        }

        // Check if Git is installed
        $gitPath = $this->getGitPath();
        if ($gitPath) {
            exec("\"$gitPath\" --version", $output, $returnVar);
            $result['gitInstalled'] = ($returnVar === 0);
        }

        if (!$result['gitInstalled']) {
            $result['canUpdate'] = false;
            $result['message'] = 'Git is not installed or not accessible. Please install Git or contact your server administrator.';
        } else {
            $result['message'] = 'All prerequisites are met.';
        }

        return $this->output_json($result);
    }

    /**
     * Get the path to the Git executable based on the operating system.
     *
     * @return string|null The path to the Git executable if found, null otherwise.
     */
    private function getGitPath()
    {
        // For Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $possiblePaths = [
                'C:\Program Files\Git\bin\git.exe',
                'C:\Program Files (x86)\Git\bin\git.exe',
            ];
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        } else {
            // For Unix-like systems
            $output = null;
            $returnVar = null;
            exec('which git', $output, $returnVar);
            if ($returnVar === 0 && !empty($output[0])) {
                return $output[0];
            }
        }
        return null;
    }

    /**
     * Outputs the given data as JSON response.
     *
     * @param mixed $data The data to be encoded into JSON.
     */
    private function output_json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }





    /**
     * Displays the Vtl Data Generator: Create Data page.
     *
     * This method sets up the data required to render the 'Create Data' view.
     *
     * @return void
     */
    public function vtlgenCreateData(): void
    {
        $this->generateVtlViewData(
            'Vtl Data Generator: Create Data',
            'Select the table in the database from the drop down below for which you wish to create some fake data.',
            'Select those columns into which you want to add data, or just check the checkbox in the header if you want to select all the rows.',
            'data',
            'createdata'
        );
    }

    /**
     * Displays the Vtl Data Generator: Create Index page.
     *
     * This method sets up the data required to render the 'Create Index' view.
     *
     * @return void
     */
    public function vtlgenCreateIndex(): void
    {
        $this->generateVtlViewData(
            'Vtl Data Generator: Create Index',
            'Select the table in the database from the drop down below for which you wish to create an index.',
            'Select the column on which you wish to create the index, and when asked select the index type',
            'index',
            'createdata'
        );
    }

    /**
     * Opens the delete view for the VTL data generator.
     *
     * Does not take any parameters.
     * Does not throw any exceptions.
     * Does not return any value.
     */
    public function vtlgenDeleteData(): void
    {
        $this->openDeleteOrDropView('delete');
    }

    public function vtlgenExportDatabase(): void
    {
        $this->openDeleteOrDropView('export');
    }

    /**
     * Generates the view for creating foreign keys in the Vtl Data Generator.
     *
     * @return void
     */
    public function vtlgenCreateForeignKey(): void
    {
        $data['tables'] = $this->setupTablesForDropdown();
        $data['columnInfo'] = $this->getAllTablesAndTheirColumnData();
        $data['headline'] = 'Vtl Data Generator: Create Foreign Keys';
        $data['instruction1'] = 'Select tables from the dropdowns below to view their columns.';
        $data['instruction2'] = 'Then select the columns on which you wish to create foreign keys.';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createforeignkey';
        $this->template('admin', $data);
    }

    /**
     * Generates the SQL creation view for the Vtl Data Generator.
     *
     * @return void
     */
    public function vtlgenCreateSql(): void
    {
        $data['tables'] = $this->getAllTablesWithRows();
        $data['headline'] = 'Vtl Data Generator: Create SQL';
        $data['instruction1'] = 'Tables shown below have data rows that can be queried.';
        $data['instruction2'] = 'Create your query then click Run Sql to see the results.';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createsql';
        $this->template('admin', $data);
    }


    /**
     * Generates documentation for the database in the Vtl Data Generator.
     *
     */
    public function vtlgenDocumentDatabase(): void
    {
        $this->generateDocumentation();
    }

    /**
     * Shows the foreign keys in the database for the Vtl Data Generator.
     * Very specifically it will show those created by the Data Generator
     */
    public function vtlgenShowForeignKeys(): void
    {
        $rows = $this->getForeignKeysFromDatabase();
        $headline = 'Vtl Data Generator: Foreign Keys in Database';
        $noDataMessage = 'There are currently no foreign keys in the database: ' . DATABASE;
        $this->showRowData($rows, $headline, $noDataMessage);
    }

    public function vtlgenDeleteForeignKeys(): void
    {
        $data['rows'] = $this->getForeignKeysFromDatabase();
        $data['headline'] = 'Vtl Data Generator: Drop Foreign Key';
        $data['instruction1'] = 'Select those foreign keys you wish to drop';
        $data['instruction2'] = '';
        $data['noDataMessage'] = 'There are currently no foreign keys in the database: ' . DATABASE;
        $data['task'] = 'key';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'deleteindexorforeignkey';
        $this->template('admin', $data);
    }

    /**
     * Opens the delete or drop view for the VTL data generator.
     *
     * This function opens the delete or drop view for the VTL data generator. It takes no parameters and does not
     * throw any exceptions.
     *
     * @return void
     */
    public function vtlgenDropTables(): void
    {
        $this->openDeleteOrDropView('drop');
    }

    /**
     * Displays the view for creating a new data table in the VTL data generator.
     *
     * @return void
     * @throws No exceptions are thrown by this function.
     */
    public function vtlgenCreateDataTable(): void
    {
        $data['headline'] = 'Vtl Data Generator: Create Table';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'createtable';
        $this->template('admin', $data);
    }

    /**
     * Displays the view for editing a data table in the VTL data generator.
     *
     * @return void
     * @throws No exceptions are thrown by this function.
     */
    public function vtlgenEditDataTable(): void
    {
        $data['tables'] = $this->setupTablesForDropdown();
        $data['columnInfo'] = $this->getAllTablesAndTheirColumnData();
        $data['headline'] = 'Vtl Data Generator: Edit Table';
        $data['instruction1'] = 'Select table to edit from the dropdown below.';
        $data['instruction2'] = 'Once the edit is complete, generate the Sql and then save it.';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'edittable';
        $this->template('admin', $data);
    }


    /**
     * Displays the general help documentation.
     *
     * This function reads the markdown file located at '../assets/help/general.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'help' view.
     *
     * @return void
     */
    public function vtlgenShowGeneralHelp(): void
    {
        $filepath = __DIR__ . '/../assets/help/general.md';
        $headline = 'Vtl Data Generator: General Help';
        $this->openHelpView($filepath, $headline);
    }

    /**
     * Displays the customized Faker help documentation.
     *
     * This function reads the markdown file located at '../assets/help/customise.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'help' view.
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
     * Displays the points of interest help documentation.
     *
     * This function reads the markdown file located at '../assets/help/pointsofinterest.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'help' view.
     *
     * @return void
     */
    public function vtlgenShowPointsOfInterestHelp(): void
    {
        $filepath = __DIR__ . '/../assets/help/pointsofinterest.md';
        $headline = 'Vtl Data Generator: Points of Interest';
        $this->openHelpView($filepath, $headline);
    }


    /**
     * Displays the tabulator help documentation.
     *
     * This function reads the markdown file located at '../assets/help/tabulator.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'help' view.
     *
     * @return void
     */
    public function vtlgenShowTabulatorHelp(): void
    {
        $filepath = __DIR__ . '/../assets/help/creatingmodules.md';
        $headline = 'Vtl Data Generator: Creating Modules';
        $this->openHelpView($filepath, $headline);
    }

    /**
     * Fetches and displays the latest primary key values for tables.
     *
     * @return void
     */

    public function vtlgenFetchLatestPkValues(): void
    {
        $rows = $this->showLatestPkValues();
        $headline = 'Vtl Data Generator: Latest Primary Key Values for Tables';
        $noDataMessage = 'There are currently no tables in the database: ' . DATABASE . ' with any rows of data';
        $this->showRowData($rows, $headline, $noDataMessage);
    }

    /**
     * Displays the Vtl Data Generator: Drop Index view.
     *
     * @return void
     */
    public function vtlgenDeleteIndex(): void
    {
        $data['rows'] = $this->getAllTableIndexes();
        $data['headline'] = 'Vtl Data Generator: Drop Index';
        $data['instruction1'] = 'Select those indexes you wish to drop';
        $data['instruction2'] = '';
        $data['noDataMessage'] = 'There are currently no indexes in the database: ' . DATABASE;
        $data['task'] = 'index';
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'deleteindexorforeignkey';
        $this->template('admin', $data);
    }

    /**
     * Displays the indexes from the database.
     *
     * @return void
     */
    public function vtlgenShowIndexes(): void
    {
        $rows = $this->getAllTableIndexes();
        $headline = 'Vtl Data Generator: Browse Indexes in Database';
        $noDataMessage = 'There are currently no indexes in the database: ' . DATABASE;
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
    public function vtlgenShowData(): void
    {
        if (isset($_GET['selectedTable'])) {
            $selectedDataTable = $_GET['selectedTable'];
            $_SESSION['selectedDataTable'] = $selectedDataTable;
        } else {
            $selectedDataTable = $_SESSION['selectedDataTable'];
        }

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $rows = $this->pdoGet(target_tbl: $selectedDataTable);
        $headline = 'Vtl Data Generator: Show Data<br>From ' . $selectedDataTable;
        $noDataMessage = 'There is no data to display from the table ' . $selectedDataTable;
        $this->showRowData($rows, $headline, $noDataMessage);
    }
    //endregion

    //region private functions

    /**
     * Define paths, clone the new version from a GitHub repository, compare versions, inform the user, and update the version check cache.
     *
     * @return string Information about the update process or any errors encountered.
     */
    public function updateModule() {
        // Define paths
        $modulesPath = APPPATH . 'modules/';
        $currentVtlgenPath = $modulesPath . 'vtlgen/';
        $newVtlgenPath = $modulesPath . 'vtlgen_new_' . time() . '/';
        $githubRepo = 'https://github.com/domsinclair/vtlgen.git';

        // Step 1: Clone the new version
        $output = null;
        $returnVar = null;
        exec("git clone $githubRepo $newVtlgenPath 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            // Handle error
            $error = implode("\n", $output);
            return "Failed to clone repository: $error";
        }

        // Step 2: Compare versions
        $currentVersion = $this->getVersion($currentVtlgenPath);
        $newVersion = $this->getVersion($newVtlgenPath);

        if (version_compare($newVersion, $currentVersion, '<=')) {
            $this->removeDirectory($newVtlgenPath);
            return "Current version ($currentVersion) is up to date. No update needed.";
        }

        // Step 3: Inform user about the update
        $message = "A new version ($newVersion) has been downloaded. " .
            "Please review the changes in the 'vtlgen_new_" . time() . "' directory " .
            "and manually merge your customizations.";

        // Step 4: Update the version check cache
        $this->updateVersionCache($newVersion);

        return $message;
    }

    /**
     * Retrieves the version number from the specified configuration file.
     *
     * @param string $path The path to the configuration file.
     * @return string|null The version number if found, null otherwise.
     */
    private function getVersion($path) {
        $configFile = $path . 'assets/vtlgenConfig.php';
        if (!file_exists($configFile)) {
            return null;
        }
        $content = file_get_contents($configFile);
        preg_match("/define\s*\(\s*'VERSION'\s*,\s*'Version:\s*([\d.]+)'\s*\)/", $content, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }


    /**
     * Updates the version cache with the new version information.
     *
     * @param datatype $newVersion The new version to update in the cache.
     * @throws Some_Exception_Class Description of exception
     * @return Some_Return_Value
     */
    private function updateVersionCache($newVersion) {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';
        $cache_data = json_decode(file_get_contents($cache_file), true);
        $cache_data['update_available'] = false;
        $cache_data['last_check'] = time();
        $cache_data['new_version'] = $newVersion;
        file_put_contents($cache_file, json_encode($cache_data));
    }

    /**
     * Initializes the update cache file with default data if it doesn't exist.
     *
     * This function creates the cache file with initial data structure if it doesn't already exist.
     *
     * @throws Exception If unable to write to the cache file.
     */
    private function initialiseUpdateCache() {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';

        if (!file_exists($cache_file)) {
            $initial_data = [
                'update_available' => false,
                'last_check' => time(),
                'error' => null,
                'new_version' => null
            ];

            file_put_contents($cache_file, json_encode($initial_data));

            // Ensure the file has the correct permissions
            chmod($cache_file, 0644);
        }
    }

    /**
     * A recursive function to remove a directory and its contents.
     *
     * @param string $dir The path to the directory to be removed.
     * @throws Exception If an error occurs during directory removal.
     * @return bool True if the directory was successfully removed, false otherwise.
     */
    private function removeDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }


    /**
     * Check for updates on Github and return the update status.
     *
     * @return array The update status containing 'update_available', 'last_check', 'error', and 'new_version'.
     */
    private function CheckGithubForUpdates() {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';
        $cache_time = 21600; // 6 hours

        if (file_exists($cache_file)) {
            $cache_data = json_decode(file_get_contents($cache_file), true);
            if (time() - $cache_data['last_check'] < $cache_time) {
                return $cache_data;
            }
        }

        $github_url = 'https://raw.githubusercontent.com/domsinclair/vtlgen/master/assets/vtlgenConfig.php';
        $github_content = @file_get_contents($github_url);

        if ($github_content === false) {
            $result = [
                'update_available' => false,
                'last_check' => time(),
                'error' => 'Unable to check for updates',
                'new_version' => null
            ];
        } else {
            preg_match("/define\s*\(\s*'VERSION'\s*,\s*'Version:\s*([\d.]+)'\s*\)/", $github_content, $matches);
            $github_version = isset($matches[1]) ? $matches[1] : null;

            $current_version = str_replace('Version: ', '', VERSION);

            $result = [
                'update_available' => version_compare($github_version, $current_version, '>'),
                'last_check' => time(),
                'error' => null,
                'new_version' => $github_version
            ];
        }

        file_put_contents($cache_file, json_encode($result));
        return $result;
    }

    /**
     * Generates the data required to render the VTL view.
     *
     * This method sets up the common data used by both the 'Create Data' and 'Create Index' views.
     *
     * @param string $headline     The headline for the view.
     * @param string $instruction1 The first instruction to be displayed.
     * @param string $instruction2 The second instruction to be displayed.
     * @param string $task         The task identifier.
     * @param string $viewFile     The name of the view file to be used.
     *
     * @return void
     */
    private function generateVtlViewData(
        string $headline,
        string $instruction1,
        string $instruction2,
        string $task,
        string $viewFile
    ): void
    {
        $data['Application Modules'] = $this->applicationModules;
        $data['tables'] = $this->setupTablesForDropdown();
        $data['columnInfo'] = $this->getAllTablesAndTheirColumnData();
        $data['headline'] = $headline;
        $data['instruction1'] = $instruction1;
        $data['instruction2'] = $instruction2;
        $data['task'] = $task;
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = $viewFile;
        $this->template('admin', $data);
    }

    private function getForeignKeysFromDatabase(): mixed
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
     * @return mixed The primary key field of the specified table, or null if not found.
     * @throws Exception Error message if there is an issue preparing or executing the query.
     */
    private function getPrimaryKeyField($tableName): mixed
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
     * @return bool Whether the table has rows (true) or not (false).
     * @throws Exception Error message if there is an issue with the query execution.
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
     * @param string $tableName       The name of the table to retrieve the primary key value from.
     * @param string $primaryKeyField The field representing the primary key.
     * @return mixed The latest primary key value, or null if no value is found.
     * @throws Exception Error message if there is an issue with the query execution.
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
     * Retrieves all table indexes from the database.
     *
     * @return array An array of associative arrays representing the table indexes,
     *               with the following keys:
     *               - index_schema: The schema of the index.
     *               - index_name: The name of the index.
     *               - index_columns: The columns of the index, concatenated.
     *               - index_type: The type of the index.
     *               - is_unique: Whether the index is unique or not.
     *               - table_name: The name of the table.
     *               The array is sorted by table name and index name.
     *               Returns an empty array if there is an error executing the query.
     */
    private function getAllTableIndexes(): array
    {
        $database = DATABASE;
        // Define the SQL query
        $sql = "SELECT 
            index_schema, 
            index_name, 
            GROUP_CONCAT(column_name ORDER BY seq_in_index) AS index_columns, 
            index_type, 
            CASE non_unique 
                WHEN 1 THEN 'Not Unique' 
                ELSE 'Unique' 
            END AS is_unique, 
            table_name 
        FROM 
            information_schema.statistics 
        WHERE 
            table_schema = :database
        GROUP BY 
            index_schema, 
            index_name, 
            index_type, 
            non_unique, 
            table_name 
        ORDER BY 
            table_name ASC, 
            index_name";

        try {
            // Prepare the query and bind parameters
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':database', $database, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the results
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Log the error message if logging is set up
            error_log("Error executing query '$sql': " . $e->getMessage());

            // Return an empty array in case of an error
            return [];
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
     * Retrieves column data for a given table.
     *
     * @param string $tableName The name of the table to retrieve column data for.
     * @return array The column data for the specified table.
     */
    private function getColumnDataForGivenTable(string $tableName): array{
        //var_dump('Fetching column Information for ' . $tableName);
        $sql = "SHOW COLUMNS FROM $tableName";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return $result ?: [];
    }


    /**
     * Retrieves all tables with rows from the database.
     *
     * @return array List of tables with rows
     */
    private function getAllTablesWithRows(): array
    {
        $database = DATABASE;
        $tables = [];
        $sql = "SELECT table_name AS 'table'
            FROM information_schema.tables
            WHERE table_schema = :database
              AND table_type = 'BASE TABLE'
              AND table_rows > 0
            ORDER BY table_name ASC;";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':database', $database, PDO::PARAM_STR);
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // Handle the exception (optional)
            // echo "Error: " . $e->getMessage();
        }

        return $tables;
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
     * @param array  $rows          The array of rows to be displayed.
     * @param string $headline      The title for the help view.
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
    private function openDeleteOrDropView(string $task): void
    {

        $data['task'] = $task;

        switch ($task) {
            case 'delete':
                $data['tables'] = $this->getAllTablesWithRows();
                $this->handleDeleteTask($data);
                break;
            case 'drop':
                $data['tables'] = $this->setupTablesForDatabaseAdmin();
                $this->handleDropTask($data);
                break;
            case 'export':
                $data['tables'] = $this->setupTablesForDatabaseAdmin();
                $this->handleExportTask($data);
                break;
            default:
                throw new InvalidArgumentException('Invalid task provided');
        }
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'deleteordrop';
        $this->template('admin', $data);
    }

    /**
     * Handles the delete task for the Vtl Data Generator by setting the headline in the provided data array.
     *
     * @param array &$data The data array to update with the headline.
     * @return void
     */
    private function handleDeleteTask(array &$data): void
    {
        $data['headline'] = 'Vtl Data Generator: Delete Data';
        $data['instruction1'] = 'Select table(s) for which you want to delete data .';
        $data['instruction2'] = '';
    }

    /**
     * Handles the delete task for the Vtl Data Generator by setting the headline in the provided data array.
     *
     * @param array &$data The data array to update with the headline.
     * @return void
     */
    private function handleDropTask(array &$data): void
    {
        $data['headline'] = 'Vtl Data Generator: Drop Tables';
        $data['instruction1'] = 'Select table(s) that you want to drop.';
        $data['instruction2'] = '';
    }

    /**
     * Handles the delete task for the Vtl Data Generator by setting the headline in the provided data array.
     *
     * @param array &$data The data array to update with the headline.
     * @return void
     */
    private function handleExportTask(array &$data): void
    {
        $data['headline'] = 'Vtl Data Generator: Export Table Scripts';
        $data['instruction1'] = 'Select table(s) that you wish to export .';
        $data['instruction2'] = '';
    }


    /**
     * Retrieves data from the database using PDO.
     *
     * @param string|null $order_by   The column to order the results by. Defaults to null.
     * @param string|null $target_tbl The target table to query. Must not be null.
     * @param int|null    $limit      The maximum number of rows to return. Defaults to null.
     * @param int         $offset     The number of rows to skip before returning results. Defaults to 0.
     * @return array An array of objects representing the fetched rows.
     * @throws InvalidArgumentException If $target_tbl is null.
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


    /**
     * Generates a markdown table based on the input data.
     *
     * @param mixed $data The data to be converted into a markdown table.
     * @return string The generated markdown table.
     */
    private function generateMarkdownTable($data): string
    {
        if (empty($data)) {
            return '';
        }

        $headers = array_keys($data[0]);
        $separator = '|' . str_repeat('---|', count($headers)) . PHP_EOL;

        $table = '';

        // Generate headers
        $table .= '|' . implode('|', $headers) . '|' . PHP_EOL;
        $table .= $separator;

        // Generate rows
        foreach ($data as $row) {
            $table .= '|' . implode('|', $row) . '|' . PHP_EOL;
        }

        return $table . PHP_EOL;
    }


    /**
     * Generates documentation based on database queries and templates.
     */
    private function generateDocumentation(): void{
        $database = DATABASE;
        $folderPath = DOCUMENTATION_LOCATION;

        // Ensure the documentation folder exists
        if (!is_dir($folderPath)) {
            if (!mkdir($folderPath, 0777, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create folder!']);
                return;
            }
        }

        // Queries for each section
        $queries = [
            'Tables' => "SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_ROWS, CREATE_TIME, UPDATE_TIME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database;",
            'Columns' => "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database ORDER BY TABLE_NAME, ORDINAL_POSITION;",
            'Indexes' => "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, COLLATION, CARDINALITY, SUB_PART, PACKED, NULLABLE, INDEX_TYPE, COMMENT, INDEX_COMMENT FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :database ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;",
            'Constraints' => "SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = :database ORDER BY TABLE_NAME, CONSTRAINT_NAME;",
            'Foreign Keys' => "SELECT kcu.TABLE_NAME, kcu.COLUMN_NAME, kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.UPDATE_RULE, rc.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE kcu JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA WHERE kcu.TABLE_SCHEMA = :database AND kcu.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME;",
            'Triggers' => "SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT, ACTION_TIMING, CREATED FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = :database;",
            'Views' => "SELECT TABLE_NAME, VIEW_DEFINITION, CHECK_OPTION, IS_UPDATABLE FROM information_schema.VIEWS WHERE TABLE_SCHEMA = :database;",
            'Routines' => "SELECT ROUTINE_NAME, ROUTINE_TYPE, DATA_TYPE, CREATED, LAST_ALTERED, DEFINER, SECURITY_TYPE, ROUTINE_DEFINITION FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = :database;"
        ];

        // Load the template
        $templatePath = APPPATH . 'modules/vtlgen/assets/templates/' . FAKER_LOCALE . '/' . DOCUMENTATION_LEVEL . '.md';

        if (!file_exists($templatePath)) {
            echo json_encode(['status' => 'error', 'message' => "Template not found: $templatePath"]);
            return;
        }

        $template = file_get_contents($templatePath);

        // Array to hold sections
        $sections = '';

        // Generate content for each section
        foreach ($queries as $section => $query) {
            $result = $this->getDocumentationQueryResult($query);

            if ($result === false || empty($result)) {
                continue;
            }

            // Generate Markdown table for current section
            $markdownTable = $this->generateMarkdownTable($result);

            // Replace section placeholder in template with generated table
            $sections .= "## $section\n\n";
            $sections .= $markdownTable;
        }

        // Replace placeholders in the template with generated content
        $markdown = str_replace('{{#sections}}', $sections, $template);
        $markdown = str_replace('{{database}}', $database, $markdown);

        // Save the markdown file
        $docFilePath = $folderPath . DIRECTORY_SEPARATOR . DATABASE . '_' . FAKER_LOCALE . '_' . DOCUMENTATION_LEVEL . '_Documentation.md';
        if (file_put_contents($docFilePath, $markdown)) {
            echo json_encode(['status' => 'success', 'message' => "Markdown documentation generated successfully! File saved to: $docFilePath"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save Markdown file!']);
        }
    }


    /**
     * Retrieves the documentation query result from the database.
     *
     * @param string $sql The SQL query to execute.
     * @return array|false An array of associative arrays containing the fetched rows, or false on failure.
     */

    private function getDocumentationQueryResult($sql): false|array
    {
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

    //endregion

    //region createdata view functions





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
            $primaryKey = $this->getPrimaryKey($selectedTable);
            // Construct SQL query to select id and picture from the selected table
            $sql = 'SELECT ' . $primaryKey . ', picture FROM ' . $selectedTable;

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

    /**
     * Creates indexes for selected rows in a specified table based on the provided index type.
     *
     * @throws Exception If an error occurs during index creation.
     */
    public function createdataCreateIndex() : void {
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        if ($postData === null || !isset($postData['selectedTable'])) {
            // Invalid POST data, send an error response
            http_response_code(400); // Bad request
            echo json_encode(['message' => 'Invalid request data']);
            return;
        }

        // Extract relevant data from the decoded JSON
        $selectedTable = $postData['selectedTable'];
        $selectedRows = $postData['selectedRows'];
        $indexType = $postData['indexType'];

        $response = [
            'createdIndexes' => [],
            'failedIndexes' => [],
            'message' => ''
        ];

        if ($selectedRows != null) {
            try {
                foreach ($selectedRows as $selectedRow) {
                    $indexName = 'idx_' . $selectedTable . '_' . $selectedRow['field'];
                    try {
                        // Check if index already exists
                        $existingIndexQuery = "SHOW INDEX FROM $selectedTable WHERE Key_name = '$indexName';";
                        $existingIndexResult = $this->model->query($existingIndexQuery);
                        if ($existingIndexResult && $existingIndexResult->num_rows > 0) {
                            $response['message'] .= "Index $indexName already exists.\n";
                            continue; // Skip creating index if it already exists
                        }

                        $sql = '';
                        switch ($indexType) {
                            case 'Standard':
                                $sql = 'CREATE INDEX ' . $indexName . ' ON ' . $selectedTable . ' (' . $selectedRow['field'] . ');';
                                break;
                            case 'Unique':
                                $sql = 'CREATE UNIQUE INDEX ' . $indexName . ' ON ' . $selectedTable . ' (' . $selectedRow['field'] . ');';
                                break;
                            default:
                                break;
                        }

                        $this->dbh->query($sql);
                        $response['createdIndexes'][] = $indexName;
                    } catch (Exception $ex) {
                        $response['failedIndexes'][] = $indexName;
                        $response['message'] .= 'Error: ' . $ex->getMessage() . "\n";
                    }
                }
            } catch (Exception $e) {
                $response['message'] .= 'Operation failed: ' . $e->getMessage() . "\n";
            }

            // Now response contains the report for the whole operation
            $response['message'] .= "Created Indexes:\n" . implode("\n", $response['createdIndexes']);
            $response['message'] .= "\nFailed Indexes:\n" . implode("\n", $response['failedIndexes']);
        } else {
            $response['message'] = 'No Rows were selected';
        }

        echo json_encode($response);
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

    //region Generate Module

    // The following code leans heavily on work done by Simon Field and Jake Castelli

    /**
     * Creates a new module folder structure based on the provided module name.
     *
     * @throws Exception if an error occurs during the module creation process.
     * @return void
     */
    public function createModules(): void {
        $posted_data = json_decode(file_get_contents('php://input'), true);
        $table_name = $posted_data['table'];

        // Initialize response array
        $response = ['status' => '', 'message' => ''];

        // Validate table name
        if (!$table_name) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid table name';
            echo json_encode($response);
            return;
        }

        // Get module details
        $moduleName = ucfirst($table_name);

        $columnInfo = $this->getColumnDataForGivenTable($table_name);

        $primaryKey = $this->getPrimaryKey($table_name);

        // Generate a singular module name
        $singularModuleName = $this->singulariseAndCapitalise($moduleName);

        // Generate validation rules
        $validationRules = $this->createValidationRules($columnInfo);

        // Define the path to the modules directory
        $modulesDir = APPPATH . 'modules';
        $modulePath = $modulesDir . DIRECTORY_SEPARATOR . strtolower($moduleName) ;

        if (is_dir($modulePath)) {
            // Module already exists
            $response['status'] = 'error';
            $response['message'] = 'Module already exists!';
        } else {
            try {
                // Ensure we have a lower case moduleName
                $stlModuleName = strtolower($moduleName);
                // Generate module infrastructure
                if ($this->generateModuleInfrastructure($modulePath, $moduleName, $columnInfo, $primaryKey, $stlModuleName, $validationRules,$singularModuleName)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Module created successfully.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Operation failed due to an unknown error.';
                }
            } catch (Exception $e) {
                $response['status'] = 'error';
                $response['message'] = 'Operation failed: ' . $e->getMessage();
            }
        }

        echo json_encode($response);
    }

    /**
     * Create validation rules based on column information.
     *
     * @param array $columnInfo The information about the columns to generate rules for.
     * @return array The generated validation rules for each field.
     */
    private function createValidationRules($columnInfo) {
        $validationRules = [];

        foreach ($columnInfo as $column) {
            $field = $column['Field'];
            $type = $column['Type'];
            $null = $column['Null'];
            $rules = [];

            // Ignore primary key fields
            if (isset($column['Key']) && $column['Key'] === 'PRI') {
                continue;
            }

            // Add 'required' rule if the field cannot be null
            if ($null === 'NO') {
                $rules[] = 'required';
            }

            // Add 'valid_email' rule if the field name contains 'email'
            if (stripos($field, 'email') !== false) {
                $rules[] = 'valid_email';
            }

            // Add password rules if the field name contains 'password'
            if (stripos($field, 'password') !== false) {
                $rules[] = 'min_length[8]'; // Example: Minimum length of 8 characters
            }

            // Add numeric rules if the type is int or tinyint
            if (preg_match('/^int|tinyint/i', $type)) {
                $rules[] = 'numeric';
            }

            // Add max_length rule if the type is varchar
            if (preg_match('/^varchar\((\d+)\)/i', $type, $matches)) {
                $rules[] = 'max_length[' . (int) $matches[1] . ']';
            }

            // Add the rules to the validation array if any rules exist for the field
            if (!empty($rules)) {
                $validationRules[$field] = implode('|', $rules);
            }
        }

        return $validationRules;
    }


    private function singulariseAndCapitalise($str): string{

        // Check if the word ends with "ies"
        if (substr($str, -3) === 'ies') {
            // Replace "ies" with "y"
            $singular = substr($str, 0, -3) . 'y';
        } elseif (substr($str, -1) === 's') {
            // Remove the trailing 's' for regular plurals
            $singular = rtrim($str, 's');
        } else {
            // If it doesn't end with "s" or "ies", return as is (or handle as needed)
            $singular = $str;
        }

        // Capitalize the first letter
        $capitalised = ucfirst($singular);

        return $capitalised;
    }



    /**
     * Generate the infrastructure for a new module, including directories for controllers, views, and assets.
     *
     * @param string $modulePath The path where the module will be created.
     * @param string $moduleName The name of the module.
     * @throws Exception If an error occurs during the generation process.
     * @return bool Returns true if the infrastructure generation is successful.
     */
    private function generateModuleInfrastructure($modulePath, $moduleName, $columnInfo, $primaryKey,$stlModuleName, $validationRules, $singularModuleName ): bool {
        try {
            $this->createDirectory($modulePath);
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'controllers');
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'views');
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'assets');
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js');
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css');
            $this->createDirectory($modulePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images');

            // Process columns info
            $processedColumns = $this->processColumnInfo($columnInfo, $primaryKey);



            // Generate module files
            $this->generateModuleController($modulePath . DIRECTORY_SEPARATOR . 'controllers', $moduleName, $processedColumns, $primaryKey, $stlModuleName, $validationRules, $singularModuleName);
            $this->generateModuleView($modulePath . DIRECTORY_SEPARATOR . 'views', $moduleName, $columnInfo, $primaryKey,$singularModuleName);
            $this->generateModuleApi($modulePath . DIRECTORY_SEPARATOR . 'assets', $moduleName);
            // Additional assets generation if needed
            $this->generatePictureDirectoriesIfRequired($modulePath . DIRECTORY_SEPARATOR . 'assets', $moduleName, $processedColumns);
            $this->generateCustomJs($modulePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js');
            $this->generateCustomCss($modulePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css');

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }





    /**
     * Processes the column information and returns an array containing the modified columns and table headers.
     *
     * @param array $columnInfo The array of column information.
     * @param string $primaryKey The primary key of the table.
     * @return array An array containing the modified columns and table headers.
     *               The array has the following structure:
     *               [
     *                   'columns' => array, // The modified columns array.
     *                   'tableHeaders' => string, // The table headers as a comma-separated string.
     *               ]
     */
    private function processColumnInfo($columnInfo, $primaryKey) {
        $columns = array_filter($columnInfo, function($column) use ($primaryKey) {
            return $column['Field'] === $primaryKey || $column['Key'] !== 'PRI';
        });

        array_unshift($columns, [
            "Field" => $primaryKey,
            "Type" => "int(11)",
            "Null" => "NO",
            "Key" => "PRI",
            "Default" => null,
            "Extra" => "auto_increment"
        ]);

        $tableHeaders = array_map(function($column) {
            return $column['Field'];
        }, $columns);

        return [
            'columns' => $columns,
            'tableHeaders' => implode(', ', $tableHeaders)
        ];
    }





    /**
     * Creates a directory at the specified path.
     *
     * @param string $path The path where the directory should be created.
     * @throws Exception Failed to create directory: $path
     */
    private function createDirectory($path): void {
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new Exception("Failed to create directory: $path");
        }
    }


    /**
     * Generates a module controller file based on the provided module name.
     *
     * @param string $moduleControllersPath The path where the module controller file will be generated.
     * @param string $moduleName The name of the module.
     * @throws Exception Failed to read controller template or write controller file.
     */
    private function generateModuleController($controllerPath, $moduleName, $processedColumns, $primaryKey, $stlModuleName, $validationRules, $singularModuleName) {

        $template = file_get_contents(APPPATH . 'modules' . DIRECTORY_SEPARATOR . 'vtlgen'.DIRECTORY_SEPARATOR . 'assets'  . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'controller.php');

        $replacements = [
            '{{ModuleName}}' => ucfirst($moduleName),
            '{{moduleName}}' => $moduleName,
            '{{tableHeaders}}' => $processedColumns['tableHeaders'],
            '{{columns}}' => json_encode($processedColumns['columns']),
            '{{primaryKey}}' => $primaryKey,
            '{{stlModuleName}}' => $stlModuleName,
            '{{validationRules}}' => json_encode($validationRules),
            '{{singularModuleName}}' => $singularModuleName
        ];

        $controllerContent = str_replace(array_keys($replacements), array_values($replacements), $template);

        file_put_contents($controllerPath . DIRECTORY_SEPARATOR . ucfirst($moduleName) . '.php', $controllerContent);

        // Update admin menu
        $this->updateAdminMenu($moduleName);
    }

    /**
     * Updates the admin menu by adding a new list item for the given module.
     *
     * @param string $moduleName The name of the module to add to the admin menu.
     * @throws None
     * @return void
     */
    private function updateAdminMenu($moduleName) {
        // Define the list item HTML with a newline character at the end
        $listItemHTML = "\n<li><?= anchor('" . strtolower($moduleName) . "/manage', 'Manage " . ucfirst($moduleName) . "') ?></li>\n";

        // Path to the dynamic_nav.php file
        $filePath = APPPATH . 'templates/views/partials/admin/dynamic_nav.php';

        // Read the content of dynamic_nav.php
        $fileContent = file_get_contents($filePath);

        // Normalize newlines and spaces for both the file content and the list item HTML
        $normalizedFileContent = preg_replace('/\s+/', ' ', $fileContent);
        $normalizedListItemHTML = preg_replace('/\s+/', ' ', $listItemHTML);

        // Check if the list item already exists in the file
        if (strpos($normalizedFileContent, $normalizedListItemHTML) === false) {
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
    }



    /**
     * A description of the entire PHP function.
     *
     * @param string $moduleViewsPath description
     * @param string $moduleName      description
     * @return void
     *@throws Some_Exception_Class description of exception
     */
    private function generateModuleView($moduleViewsPath, $moduleName, $columnInfo, $primaryKey, $singularModuleName): void {

        // Paths for display and manage view templates
        $manageViewPath = $moduleViewsPath . DIRECTORY_SEPARATOR . 'manage.php';
        $createViewPath = $moduleViewsPath . DIRECTORY_SEPARATOR . 'create.php';
        $showViewPath = $moduleViewsPath . DIRECTORY_SEPARATOR . 'show.php';


        $manageTemplatePath = APPPATH . 'modules' . DIRECTORY_SEPARATOR . 'vtlgen' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'manage.php';
        $createTemplatePath = APPPATH . 'modules' . DIRECTORY_SEPARATOR . 'vtlgen' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'create.php';
        $showTemplatePath = APPPATH . 'modules' . DIRECTORY_SEPARATOR . 'vtlgen' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'show.php';


        // Process manage view template
        $manageContent = file_get_contents($manageTemplatePath);
        if ($manageContent === false) {
            throw new Exception("Failed to read manage view template");
        }
        $tableHeaders = array_column($columnInfo, 'Field');
        $manageContent = str_replace('{{moduleName}}', strtolower($moduleName), $manageContent);
        $manageContent = str_replace('{{tableHeaders}}', json_encode($tableHeaders), $manageContent);
        $manageContent = str_replace('{{primaryKey}}', $primaryKey, $manageContent);
        $manageContent = str_replace('{{singularModuleName}}', $singularModuleName, $manageContent);

        // Prepare search field and operator options
        $searchFieldOptions = [];
        foreach ($columnInfo as $column) {
            $searchFieldOptions[$column['Field']] = ucfirst($column['Field']);
        }
        $manageContent = str_replace('{{searchFieldOptions}}', json_encode($searchFieldOptions), $manageContent);

        // Enhance search operators with additional options
        $searchOperatorOptions = [
            '=' => '=',
            'LIKE' => 'LIKE',
            '>' => '>',
            '<' => '<',
            '>=' => '>=',
            '<=' => '<=',
            '!=' => '!=',
            'IN' => 'IN'
        ];
        $manageContent = str_replace('{{searchOperatorOptions}}', json_encode($searchOperatorOptions), $manageContent);

        if (file_put_contents($manageViewPath, $manageContent) === false) {
            throw new Exception("Failed to write manage view file");
        }


        // Process create view template
        $createContent = file_get_contents($createTemplatePath);
        if ($createContent === false) {
            throw new Exception("Failed to read create view template");
        }
        $formFields = json_encode($columnInfo);
        $createContent = str_replace('{{moduleName}}', strtolower($moduleName), $createContent);
        $createContent = str_replace('{{formFields}}', $formFields, $createContent);
        $createContent = str_replace('{{singularModuleName}}', $singularModuleName, $createContent);
        if (file_put_contents($createViewPath, $createContent) === false) {
            throw new Exception("Failed to write create view file");
        }

        // Process show view template
        $showContent = file_get_contents($showTemplatePath);
        if ($showContent === false) {
            throw new Exception("Failed to read show view template");
        }
        $columnsData = '';
        foreach ($columnInfo as $column) {
            if ($column['Extra'] != 'auto_increment') {
                $columnsData .= '<div class="row"><div>' . $column['Field'] . '</div><div><?= out($' . $column['Field'] . ') ?></div></div>';
            }
        }
        $showContent = str_replace('{{moduleName}}', strtolower($moduleName), $showContent);
        $showContent = str_replace('{{columns}}', $columnsData, $showContent);
        $showContent = str_replace('{{singularModuleName}}', $singularModuleName, $showContent);
        if (file_put_contents($showViewPath, $showContent) === false) {
            throw new Exception("Failed to write show view file");
        }
    }


    /**
     * Generates the API file for a specific module using the provided paths.
     *
     * @param string $moduleAssetsPath The path to the assets folder of the module.
     * @param string $moduleName The name of the module.
     * @throws Exception Failed to read API template or failed to write API file.
     * @return void
     */
    private function generateModuleApi($moduleAssetsPath, $moduleName): void {
        $apiPath = $moduleAssetsPath . DIRECTORY_SEPARATOR . 'api.json';
        $templatePath = APPPATH . 'modules' . DIRECTORY_SEPARATOR . 'vtlgen' .DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'api.json';

        $apiContent = file_get_contents($templatePath);
        if ($apiContent === false) {
            throw new Exception("Failed to read API template");
        }

        // Replace placeholders with actual values
        $apiContent = str_replace('{{moduleName}}', strtolower($moduleName), $apiContent);

        // Write the processed content to the new API file
        if (file_put_contents($apiPath, $apiContent) === false) {
            throw new Exception("Failed to write API file");
        }
    }


    /**
     * Write custom JS content to a new custom.js file.
     *
     * @param string $moduleAssetsJsPath The path to the JavaScript assets folder of the module.
     * @return void
     */
    private function generateCustomJs($moduleAssetsJsPath): void {
        $jsPath = $moduleAssetsJsPath . DIRECTORY_SEPARATOR . 'custom.js';
        $jsContent = "// Add your custom JS here";

        // Write the JS content to the new custom.js file
        if (file_put_contents($jsPath, $jsContent) === false) {
            throw new Exception("Failed to write custom.js file");
        }
    }



    /**
     * Write custom CSS content to a new custom.css file.
     *
     * @param string $moduleAssetsCssPath The path to the CSS assets folder of the module.
     * @return void
     */
    private function generateCustomCss($moduleAssetsCssPath): void {
        $cssPath = $moduleAssetsCssPath . DIRECTORY_SEPARATOR . 'custom.css';
        $cssContent = "/* Add your custom CSS here */";

        // Write the CSS content to the new custom.css file
        if (file_put_contents($cssPath, $cssContent) === false) {
            throw new Exception("Failed to write custom.css file");
        }
    }

    /**
     * Generate picture directories if required.
     *
     * This function checks if the 'picture' substring exists in the table headers.
     * If it does, it creates two directories: one for the pictures and one for the thumbnails.
     *
     * @param string $moduleAssetsImagesPath The path to the images assets folder of the module.
     * @param string $moduleName The name of the module.
     * @param array $processedColumns The processed columns containing the table headers.
     * @throws Exception If the table headers are not an array or if the directories cannot be created.
     * @return void
     */
    private function generatePictureDirectoriesIfRequired($moduleAssetsPath, $moduleName, $processedColumns) {
        // Determine if we need to proceed in the first place


        // Ensure that $tableheaders is an array
        $tableheaders = $processedColumns['tableHeaders'];
        if (is_string($tableheaders)) {
            $tableheaders = explode(',', $tableheaders); // Convert string to array
        }

        $pictureFieldExists = false;
        foreach ($tableheaders as $tableheader) {
            if (strpos($tableheader, 'picture') !== false) { // Check if 'picture' is a substring
                $pictureFieldExists = true;
                break; // No need to continue checking once we found it
            }
        }

        if ($pictureFieldExists) {
            $this->createDirectory($moduleAssetsPath.'/'.strtolower($moduleName).'_pics');
            $this->createDirectory($moduleAssetsPath.'/'.strtolower($moduleName).'_pics_thumbnails');
        }
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

    public function deleteordropExportTables(): void {
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $tablesToExport = $postData['tablesToExport'];
        $dumpSettings = array(
            'include-tables' => $tablesToExport,
            'no-data' => true,
            'add-drop-database' => true,
            'no-create-db' => false,
            'add-drop-table' => true,
            'single-transaction' => true,
            'reset-auto-increment' => true
        );
        $pdoSettings = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        $response = array();
        try {
            //run a check to see if there is a backups directory in the assets folder
            $folderPath = BACKUP_SCRIPTS_LOCATION;
            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    $response['status'] = 'error';
                    $response['message'] = 'Failed to create folder!';
                    echo json_encode($response);
                    return;
                }
            }
            $dump = new IMysqldump\Mysqldump('mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->user, $this->pass, $dumpSettings, $pdoSettings);
            $dateSuffix = date('Ymd_His'); // Current date and time format: YYYYMMDD_HHmmss
            $backupFilename = $folderPath . '/backup_' . $dateSuffix . '.sql';
            $dump->start($backupFilename);
            $response['status'] = 'success';
            $response['message'] = 'Success, your database script ( backup' . $dateSuffix . '.sql )is in the folder modules/vtl_gen/vtl_faker/assets/backups';
        } catch (\Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'mysqldump-php error: ' . $e->getMessage();
        }
        echo json_encode($response);
    }




    //endregion

    //region Drop Index or Foreign Key View Functions

    /**
     * A function to delete indexes or foreign keys from the database.
     *
     * @throws PDOException When there is an issue with the database operation.
     */
    public function deleteindexorforeignkeyDeleteIndex(): void {
        $rawPostData = file_get_contents('php://input');
        $selectedRows = json_decode($rawPostData, true);

        $responseText = '';
        $indexesDeleted = [];
        $failedDeletions = [];

        try {
            foreach ($selectedRows as $selectedRow) {
                $table = $selectedRow['table_name'];
                $index = $selectedRow['index_name'];
                try {
                    $sql = 'ALTER TABLE ' . $table . ' DROP INDEX ' . $index;
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute();
                    $indexesDeleted[] = $index;
                } catch (PDOException $ex) {
                    $failedDeletions[] = $index;
                }
            }
        } catch (Exception $e) {
            $responseText = $e->getMessage();
        }

        // Constructing the response text with proper HTML formatting
        if (!empty($indexesDeleted)) {
            $responseText .= "Deleted Indexes:<br>";
            foreach ($indexesDeleted as $index) {
                $responseText .= "- $index<br>";
            }
        }

        if (!empty($failedDeletions)) {
            $responseText .= "Failed To Drop Indexes:<br>";
            foreach ($failedDeletions as $index) {
                $responseText .= "- $index<br>";
            }
        }

        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => $responseText
        ]);
    }


    public function deleteindexorforeignkeyDeleteKey(): void{
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        if (isset($postData['selectedRows']) && is_array($postData['selectedRows'])) {
            $responseText = '';
            $deletedKeys = [];
            $failedKeys = [];



            foreach ($postData['selectedRows'] as $row) {
                if (isset($row['foreign key'], $row['constraint name'])) {
                    $foreignKey = $row['foreign key'];
                    $constraintName = $row['constraint name'];

                    // Extract the table name from the foreign key
                    list($tableName, $columnName) = explode('.', $foreignKey);

                    // SQL to drop the foreign key constraint
                    $sql = "ALTER TABLE $tableName DROP FOREIGN KEY $constraintName;";

                    try {
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->execute();
                        $deletedKeys[] = $constraintName;
                    } catch (PDOException $ex) {
                        echo 'Error: ' . $ex->getMessage();
                        // Add the foreign key to the list of failed keys
                        $failedKeys[] = $constraintName;
                    }
                } else {
                    $failedKeys[] = json_encode($row); // Include row data for debugging
                }
            }

            // Prepare response text
            if (!empty($deletedKeys)) {
                $responseText .= 'Deleted foreign keys: ' . implode(', ', $deletedKeys) . '. ';
            }
            if (!empty($failedKeys)) {
                $responseText .= 'Failed to delete foreign keys: ' . implode(', ', $failedKeys) . '.';
            }

            // Return the response as JSON
            echo json_encode(['status' => 'success', 'message' => $responseText]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No rows selected']);
        }
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

    //region Create Foreign Key View Functions

    /**
     * Set a foreign key relationship between two tables based on the provided fields.
     *
     */
    public function createforeignkeySetForeignKey(): void{
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);
        $table1 = $postData['table1'];
        $table2 = $postData['table2'];
        $selectedField1 = $postData['selectedField1'];
        $selectedField2 = $postData['selectedField2'];
        // Define the query
        $query = "ALTER TABLE `$table1` ADD CONSTRAINT `FK_{$table1}_{$table2}` FOREIGN KEY (`$selectedField1`) REFERENCES `$table2` (`$selectedField2`)";
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute();
            echo json_encode([
                'success' => true,
                'message' => 'Foreign key created successfully'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    }
    //endregion

    //region edittable view functions

    /**
     * Execute an SQL statement received in the request body and handle transactional operations.
     *
     * @throws Exception Rollbacks the transaction if an error occurs during SQL execution.
     */
    public function edittableAlterDataTable(): void {
        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $sql = $postData['sql'];

        // Initialize response array
        $response = ['status' => '', 'message' => ''];

        try {
            // Check if dbh is properly initialized
            if (!$this->dbh) {
                throw new Exception('Database connection not established.');
            }

            // Execute the SQL statement (no transaction needed)
            $this->dbh->exec($sql);

            // Update response on success
            $response['status'] = 'success';
            $response['message'] = 'Operation completed successfully.';
        } catch (Exception $e) {
            // Log the error message for debugging purposes
            error_log('SQL Error: ' . $e->getMessage());

            // Update response on error
            $response['status'] = 'error';
            $response['message'] = 'Operation failed: ' . $e->getMessage();
        }

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    //endregion

    //region Create Sql Functions

    /**
     * Set content-type header to application/json
     * Get the raw POST data
     * Check if 'sql' is present in the POST data
     * Prepare and execute the SQL statement
     * Fetch all results as associative arrays
     * Send the response with the data
     * Send the error message in case of an exception
     */
    public function createsqlGetDataFromSuppliedSql(): void {
        // Set content-type header to application/json
        header('Content-Type: application/json');

        // Get the raw POST data
        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        // Check if 'sql' is present in the POST data
        if (!isset($postData['sql']) || empty($postData['sql'])) {
            echo json_encode([
                'success' => false,
                'message' => 'SQL query is missing or empty'
            ]);
            return;
        }

        $sql = $postData['sql'];

        try {
            // Prepare and execute the SQL statement
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();

            // Fetch all results as associative arrays
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Send the response with the data
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (PDOException $e) {
            // Send the error message in case of an exception
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Set content-type header to application/json
     * Get the raw POST data
     * Check if 'name' and 'sql' are present in the POST data
     * Save the SQL script to a file
     * Send the response
     * Send the error message in case of an exception
     */
    public function createsqlSaveEndUserCreatedQuery(): void {
        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Extract relevant data from the decoded JSON
        $queryName = $postData['name'];
        $sql = $postData['sql'];

        // Initialize response array
        $response = ['status' => '', 'message' => ''];

        try {
            $folderPath = SQL_SCRIPTS_LOCATION;
            //$folderPath = __DIR__ . '/../assets/sqltablescripts';
            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    throw new Exception("Failed to create folder!");
                }
            }

            // Define the filename with the query name and .sql extension
            $fileName = $folderPath . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $queryName) . '.sql';

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
    //endregion

    //region Customisable Functions
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

    //endregion

    //region Delete or Zip View Functions

    private function showDeleteOrZipView($headline, $instruction1, $instruction2, $task):void{

        $data = [];
        $data['modules'] = $this -> applicationModules;
        $data['headline'] = $headline;
        $data['instruction1'] = $instruction1;
        $data['instruction2'] = $instruction2;
        $data['task'] = $task;
        $data['view_module'] = 'vtlgen';
        $data['view_file'] = 'deleteorzip';
        $this -> template('admin', $data);
    }

    /**
     * Deletes or zips the project.
     *
     * This function generates a zip file of the project. It gets the current date
     * in YYYYMMDD format and appends it to the application name. The zip file is
     * created in the parent directory. If the zip file is created successfully,
     * it prints "Application zipped successfully." Otherwise, it prints "Failed
     * to zip the application."
     *
     * @throws None
     * @return void
     */
    public function deleteorzipZipProject(): void
    {
        // the constants for this are defined in the constructor at the top

        $date = date('Ymd'); // Get the current date in YYYYMMDD format
        $zipFile = PARENT_DIR . '/' . APP_NAME . '_' . $date . '.zip';
        if ( $this -> zipApplication(SOURCE_DIR, $zipFile)) {
            echo "Application zipped successfully.";
        } else {
            echo "Failed to zip the application.";
        }
    }

    /**
     * Recursively adds files and directories to the zip archive.
     *
     * @param string $source The source directory.
     * @param ZipArchive $zip The zip archive instance.
     * @param string $path The internal path within the zip archive.
     */
    function addFilesToZip($source, $zip, $path = '') {
        $source = realpath($source);
        if (is_dir($source)) {
            $iterator = new RecursiveDirectoryIterator($source);
            // Skip dot files while iterating
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $filePath = realpath($file);
                $relativePath = $path . '/' . substr($filePath, strlen($source) + 1);

                if (is_dir($filePath)) {
                    $zip->addEmptyDir($relativePath);
                } elseif (is_file($filePath)) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } elseif (is_file($source)) {
            $zip->addFile($source, $path . '/' . basename($source));
        }
    }

    /**
     * Zips the entire application directory.
     *
     * @param string $sourceDir The directory to zip.
     * @param string $zipFile The path to the output zip file.
     * @return bool Returns true on success or false on failure.
     */
    private function zipApplication($sourceDir, $zipFile) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $this -> addFilesToZip($sourceDir, $zip);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Zips the selected modules.
     *
     * @param void
     * @throws Exception An error occurred while zipping the modules.
     * @return void
     */
    public function deleteorzipZipModules(): void {
        header('Content-Type: application/json'); // Set the content type to JSON

        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        $response = array('status' => '', 'message' => '', 'errors' => array());

        if (!isset($postData['modulesToZip'])) {
            $response['status'] = 'error';
            $response['message'] = 'No modules specified for zipping.';
            echo json_encode($response);
            return;
        }

        $selectedModules = $postData['modulesToZip'];

        try {
            foreach ($selectedModules as $selectedModule) {
                $modulePath = APPPATH . 'modules'. '/' . $selectedModule;
                $zipFile = APPPATH . 'modules' . '/' . $selectedModule . '.zip';
                $this->zipApplication($modulePath, $zipFile);
            }
            $response['status'] = 'success';
            $response['message'] = 'Modules zipped successfully.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'An error occurred while zipping the modules.';
            $response['errors'][] = $e->getMessage();
        }

        echo json_encode($response);
    }

    /**
     * Deletes the selected modules.
     *
     * @param void
     * @throws Exception An error occurred while deleting the modules.
     * @return void
     */
    public function deleteorzipDeleteModules(): void{
        header('Content-Type: application/json'); // Set the content type to JSON

        $rawPostData = file_get_contents('php://input');
        $postData = json_decode($rawPostData, true);

        $response = array('status' => '', 'message' => '', 'errors' => array());

        if (!isset($postData['modulesToDelete'])) {
            $response['status'] = 'error';
            $response['message'] = 'No modules specified for deletion.';
            echo json_encode($response);
            return;
        }

        $selectedModules = $postData['modulesToDelete'];

        try {
            foreach ($selectedModules as $selectedModule) {
                $modulePath = APPPATH . 'modules' . '/' . $selectedModule;
               // Delete the module here
                if ($this->deleteDirectory($modulePath)) {
                    // delete the admin sidebar menu item
                    $this -> removeFromAdminMenu($selectedModule);
                    $response['message'] .= "Module $selectedModule deleted successfully. ";
                } else {
                    throw new Exception("Failed to delete module $selectedModule.");
                }
            }
            $response['status'] = 'success';
            $response['message'] = 'Modules Deleted successfully.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = 'An error occurred while deleting the modules.';
            $response['errors'][] = $e->getMessage();
        }

        echo json_encode($response);
    }

    /**
     * Remove a specific list item HTML from the dynamic_nav.php file if it exists.
     *
     * @param string $moduleName The name of the module for which the list item HTML should be removed.
     */
    private function removeFromAdminMenu($moduleName) {
        // Path to the dynamic_nav.php file
        $filePath = APPPATH . 'templates/views/partials/admin/dynamic_nav.php';

        // Read the content of dynamic_nav.php
        $fileContent = file_get_contents($filePath);

        // Define the list item HTML to be removed
        $listItemHTML = "\n<li><?= anchor('" . strtolower($moduleName) . "/manage', 'Manage " . ucfirst($moduleName) . "') ?></li>\n";

        // Normalize newlines and spaces for both the file content and the list item HTML
        $normalizedFileContent = preg_replace('/\s+/', ' ', $fileContent);
        $normalizedListItemHTML = preg_replace('/\s+/', ' ', $listItemHTML);

        // Check if the list item exists in the file
        if (strpos($normalizedFileContent, $normalizedListItemHTML) !== false) {
            // Replace the list item HTML with an empty string
            $newContent = str_replace($listItemHTML, '', $fileContent);

            // Write the modified content back to the file
            file_put_contents($filePath, $newContent);
        }
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @param string $dir The path to the directory to be deleted.
     * @return bool True if the directory was successfully deleted, false otherwise.
     */
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }


    //endregion
}
