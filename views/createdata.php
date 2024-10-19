<?php echo json($data);?>
<?php $picDirectoryExists = false; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="color-scheme" content="dark light">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator_midnight.css">
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/tabulator.js"></script>
    <title>Vtl_Data_Generator</title>
</head>
<body>
<h2 class="text-center"><?= $headline ?></h2>
<section>
    <div class="container">
        <div class="flex">
            <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        </div>
        <p><?= $instruction1 ?> </p>
        <p><?= $instruction2 ?> </p>
        <?php
        $tableChoiceAttr['id'] = 'tableChoiceDropdown';
        $tableChoiceAttr['onchange'] = 'selectedTable()';
        echo form_dropdown('tableChoice', $tables, '', $tableChoiceAttr);
        ?>
        <div class="container" id="datatable"></div>
        <div id="task" style="display: none"><?= $task ?></div>
        <div id="columnInfoTableContainer">
            <div class="flex-container">
                <div id="numRowsContainer" style="display: none;">
                    <label for="numRows">Enter the number of rows of data you wish to generate:</label>
                    <input type="number" id="numRows" name="numRows" min="1" value="1">
                </div>
                <button id="submitBtn" onclick='generateData()' style="display: none; margin-bottom: 15px;">Generate Fake Data</button>
                <button id="movePicsBtn" onclick="movePictures()" style="display: none; margin-bottom: 15px;">Transfer Images</button>

            </div>
        </div>
        <div class="progress-bar" id="progress-bar" style="display: none" >
            <div class="progress" id="progress" ></div>
        </div>
<!--        index related stuff-->
        <div class="flex-container">
            <div id="indexTypeDropdown" style="display: none;">
                <label for="indexType">Select Index Type:</label>
                <select id="indexType" name="indexType">
                    <option value="Standard" selected>Standard</option>
                    <option value="Unique">Unique</option>
                </select>
            </div>
            <button id="generateIndexButton" onclick="generateIndex()" style="display: none;">Create Index</button>
        </div>



    </div>
</section>
<section>
    <div>
        <input type="hidden" id="picDirectoryExists" value="<?php echo $picDirectoryExists ? 'true' : 'false'; ?>">
    </div>
</section>
<!-- vtl modal, overlay and script ref. Place just above lower body tag-->
<div id="vtlOverlay" class="vtlOverlay"></div>
<dialog id="vtlModal" class="vtlModal">
    <div id="vtlModalHeader" class="vtlModalHeader">
        <h2 class="vtlModalTitle" id="vtlModalTitle">Default Title</h2>
    </div>
    <div class="vtlModalContentWrapper">
        <p id="vtlResponse">Default content</p>
    </div>
    <div class="vtlModalFooter">
        <button class="vtlCloseButton" id="vtlCloseModal">Close</button>
    </div>
</dialog>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
</body>
</html>

<script>
    vtlModal.addEventListener('vtlModalClosed', () => {
            //location.reload();
    });
    async function setPictureDirectoryExistsForSelectedTableModule(selectedTable) {
        <?php $picDirectoryExists = false;?>
        var postData = {
            selectedTable: selectedTable
        };

        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/createdataGetPictureFolderExists';

            // Open a POST request to the specified URL
            xhr.open('POST', targetUrl, true);

            // Set the content type to JSON
            xhr.setRequestHeader('Content-type', 'application/json');

            // Convert the data object to a JSON string
            var jsonData = JSON.stringify(postData);

            // Send the request with the JSON data
            xhr.send(jsonData);

            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Handle the response here
                    var response = xhr.responseText;
                    // Parse the response as needed
                    var responseObject = JSON.parse(response);
                    var picDirectoryExists = responseObject.picDirectoryExists;
                    // Now you can use the picDirectoryExists variable


                    // Update the content of the hidden input field
                    var picDirectoryExistsInput = document.getElementById('picDirectoryExists');
                    picDirectoryExistsInput.value = picDirectoryExists ? 'true' : 'false';


                } else {
                    // Handle error responses here
                    console.error('Error:', xhr.status);
                }
            };
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function selectedTable() {
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;
        var columnInfo = <?php echo json_encode($columnInfo); ?>;
        var selectedTableColumns = columnInfo.find(table => table.table === selectedTable).columns;
        var tableData = [];

        selectedTableColumns.forEach(column => {
            if (!(column.Key === 'PRI' && column.Extra.includes('auto_increment'))) {
                tableData.push({
                    title: column.Field, // Assuming `Field` contains the column name
                    field: column.Field,
                    type: column.Type,
                    null: column.Null,
                    key: column.Key,
                    default: column.Default,
                    extra: column.Extra,
                });
            }
        });

        var table =new Tabulator("#datatable", {
            layout: "fitColumns", // fit columns to width of table
            selectableRows: true, // enable row selection
            columns: [
                {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle",headerHozAlign: "center", headerSort: false,width:60, cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    }
                },
                {title: "Field", field: "field", width: 200, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left" },
                {title: "Type", field: "type", width: 100, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
                {title: "Null", field: "null", width: 100, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
                {title: "Key", field: "key", width: 80, hozAlign: "leftr", vertAlign: "middle", headerHozAlign: "left"},
                {title: "Default", field: "default", hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
                {title: "Extra", field: "extra", hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"}
            ],

            data: tableData,


        });
        table.on("rowSelected", function(row){
            //row - row component for the selected row
            row.getElement().style.backgroundColor = "var(--primary)";
            row.getElement().style.color = "white";
        });
        table.on("rowDeselected", function(row){
            //row - row component for the deselected row
            row.getElement().style.backgroundColor = '';
            row.getElement().style.color = '';
        });
        table.on("rowSelectionChanged", function(data, rows, selected, deselected){
            var numRowsContainer = document.getElementById('numRowsContainer');
            var submitBtn = document.getElementById('submitBtn');
            var indexTypeDropdown = document.getElementById('indexTypeDropdown');
            var generateIndexButton = document.getElementById('generateIndexButton');


            if(table.getSelectedRows().length > 0) {
                switch(task.innerText) {
                    case 'data':
                        numRowsContainer.style.display = 'block';
                        submitBtn.style.display = 'block';
                        indexTypeDropdown.style.display = 'none';
                        generateIndexButton.style.display = 'none';
                        break;
                    case 'index':
                        numRowsContainer.style.display = 'none';
                        submitBtn.style.display = 'none';
                        indexTypeDropdown.style.display = 'block';
                        generateIndexButton.style.display = 'block';
                        break;
                    default:
                        numRowsContainer.style.display = 'none';
                        submitBtn.style.display = 'none';
                        indexTypeDropdown.style.display = 'none';
                        generateIndexButton.style.display = 'none';
                }

            } else {
                numRowsContainer.style.display = 'none';
                submitBtn.style.display = 'none';
                indexTypeDropdown.style.display = 'none';
                generateIndexButton.style.display = 'none';
            }
        });
        if (task.innerText === 'data') {
            await setPictureDirectoryExistsForSelectedTableModule(selectedTable);
        }

    }

    async function generateData() {
        // Get the selected table name from the dropdown
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;

        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // Filter the selected rows to include only the field name and data type
        var filteredRows = selectedRows.map(row => {
            return {
                field: row.field,
                type: row.type
            };
        });

        // Get the value from the numRows input field
        var numRows = document.getElementById('numRows').value;

        // Prepare the data to send
        var postData = {
            selectedTable: selectedTable,
            selectedRows: filteredRows,
            numRows: numRows
        };

        // Send the POST request
        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/createdataCreateFakeData';

            // Open a POST request to the specified URL
            xhr.open('POST', targetUrl, true);

            // Set the content type to JSON
            xhr.setRequestHeader('Content-type', 'application/json');

            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        // Parse the JSON response
                        var response = JSON.parse(xhr.responseText);

                        // Handle the response
                        openVtlModal('Fake Data Generated',true,response.message);

                        // Additional logic based on response
                        var picDirectoryExists = document.getElementById('picDirectoryExists').value ;

                        console.log('picDirectoryExists:', picDirectoryExists);

                        if (picDirectoryExists && numRows <= 100) {
                            document.getElementById('submitBtn').style.display = 'none';
                            document.getElementById('movePicsBtn').style.display = 'block';
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        openVtlModal('Error Parsing Json',false,response.message);
                    }
                } else {
                    var errorResponse = xhr.responseText;
                    openVtlModal('Error Generating Fake Data',false,errorResponse.message);
                }
            };

            // Convert the data object to a JSON string
            var jsonData = JSON.stringify(postData);
            // Send the request with the JSON data
            xhr.send(jsonData);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function generateIndex() {
        // Get the selected table name from the dropdown
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;

        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // Filter the selected rows to include only the field name
        var filteredRows = selectedRows.map(row => {
            return {
                field: row.field
            };
        });

        // Get the value from the index type dropdown
        var indexDropdown = document.getElementById('indexType');
        var indexType = indexDropdown.options[indexDropdown.selectedIndex].value;

        // Prepare the data to send
        var postData = {
            selectedTable: selectedTable,
            selectedRows: filteredRows,
            indexType: indexType
        };

        // Send the POST request
        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/createdataCreateIndex';

            // Open a POST request to the specified URL
            xhr.open('POST', targetUrl, true);

            // Set the content type to JSON
            xhr.setRequestHeader('Content-type', 'application/json');

            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        // Parse the JSON response
                        var response = JSON.parse(xhr.responseText);
                        let message = response.message;

                        // Format the response message
                        if (response.createdIndexes.length > 0) {
                            message += '<br><br>Created Indexes:<br>' + response.createdIndexes.join('<br>');
                        }
                        if (response.failedIndexes.length > 0) {
                            message += '<br><br>Failed Indexes:<br>' + response.failedIndexes.join('<br>');
                        }

                        // Handle the response with the custom modal
                        openVtlModal('Index Generated', true, message);
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        openVtlModal('Error Parsing JSON', false, 'An error occurred while processing the response.');
                    }
                } else {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        openVtlModal('Error Generating Index', false, errorResponse.message);
                    } catch (e) {
                        console.error('Error parsing error response JSON:', e);
                        openVtlModal('Error Generating Index', false, xhr.responseText);
                    }
                }
            };

            // Convert the data object to a JSON string
            var jsonData = JSON.stringify(postData);
            // Send the request with the JSON data
            xhr.send(jsonData);
        } catch (error) {
            console.error('Error:', error);
        }
    }


    function movePictures() {
        // Get the selected table name from the dropdown
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;
        var progressBar = document.getElementById('progress-bar');
        progressBar.style.display = 'block';

        // Prepare the data to send
        var postData = {
            selectedTable: selectedTable
        };

        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/createdataSetImageFoldersAndTransferImages';

            // Open a POST request to the specified URL
            xhr.open('POST', targetUrl, true);

            // Set the content type to JSON
            xhr.setRequestHeader('Content-type', 'application/json');

            // Convert the data object to a JSON string
            var jsonData = JSON.stringify(postData);

            // Send the request with the JSON data
            xhr.send(jsonData);

            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Handle the response here
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        // Handle error responses here
                        openVtlModal('Error Moving Images',false,response.message);
                    } else {
                        // Start processing records
                        processRecords(response.totalRows);
                    }
                } else {
                    // Handle error responses here
                    openVtlModal('Error Moving Images',false,response.message);

                }
            };
        } catch (error) {
           openVtlModal('Error Moving Images',false,error);
        }
    }

    async function processRecords(totalRows) {
        // Get the selected table name from the dropdown
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;

        var progressElement = document.getElementById('progress');
        var progress = 0;

        // Define a function to handle each record asynchronously
        async function processRecord(recordId) {
            return new Promise((resolve, reject) => {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?= BASE_URL ?>vtlgen/createdataCopyImageForRecords', true);
                xhr.setRequestHeader('Content-type', 'application/json');

                // Prepare the data to send
                var data = {
                    recordId: recordId,
                    selectedTable: selectedTable
                };

                // Convert the data object to a JSON string
                var jsonData = JSON.stringify(data);
                // Define a callback function to handle the response
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Update progress bar
                        progress++;
                        var percent = Math.round((progress / totalRows) * 100);
                        progressElement.style.width = percent + '%';
                        progressElement.textContent = percent + '%';

                        // Resolve the Promise
                        resolve();
                    } else {
                        // Reject the Promise on error
                        reject('Request from process images failed with status ' + xhr.responseText);
                    }
                };

                xhr.onerror = function () {
                    // Reject the Promise on connection error
                    reject('Request failed');
                };

                // Send the request with the JSON data
                xhr.send(jsonData);
            });
        }

        // Loop through records and process them asynchronously
        for (var i = 1; i <= totalRows; i++) {
            try {
                await processRecord(i);
            } catch (error) {
                // Handle errors here if needed
                console.error(error);
               openVtlModal('Error Moving Images',false,error);
                return; // Stop processing further records on error
            }
        }

        // If all records processed, display success message
       openVtlModal('Success Moving Images',true,'Images copied successfully.');
    }
</script>
<style>
    :root {
        --max-progress-width: 500px;
        --progress-height: 30px;
        --border-radius: 50px;
    }
    @media (prefers-color-scheme: light) {
        div.tabulator-cell{
            color: white;

        }
        div.tabulator-col-title{
            color: white;

        }
        div.tabulator-col.tabulator-sortable.tabulator-col-sorter-element{
            color: white;
        }

    }

    @media(hover: hover) and (pointer: fine) {
        .tabulator .tabulator-header .tabulator-col.tabulator-sortable.tabulator-col-sorter-element:hover {
            background-color: var(--primary-dark);

        }
    }
    div.tabulator-col.tabulator-sortable.tabulator-col-sorter-element{
        background-color: var(--primary-dark);
    }

    #datatable{
        margin-top: 20px;
    }

    #datatable input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static;
        width: auto;
        height: auto;
    }
    .flex-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }
    .progress-bar {
        max-width: var(--max-progress-width);
        height: var(--progress-height);
        border-radius: var(--border-radius);
        overflow: hidden;
        position: relative;
        display: block;
    }
    .progress {
        background-color: var(--primary);
        /*transition: width 0.1s ease-in-out;*/
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        position: absolute;
        width: 0;

    }
</style>
