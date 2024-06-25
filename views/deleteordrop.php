<?php //echo json($data); ?>
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
    </div>
</section>
<section>
    <div class="container" id="datatable"></div>
</section>
<section>
<div id="task" style="display: none"><?= $task ?></div>
<div class="container" id="deleteCheckbox" style="display: none">
    <label><input type="checkbox" id="resetAutoIncrementCheckbox" name="reset-auto-increment" > Reset Primary Auto
        Increment</label>
    <div>
        <button id="submitBtn" onclick='deleteData()' style=" margin-bottom: 15px;">Delete Data</button>
    </div>
</div>
</section>
<section>
<div class="container" id="dropTableDiv" style="display: none" >
    <button  onclick='dropTables()' style="margin-bottom: 15px;">Drop Tables</button>
</div>
</section>
<section>
    <div class="container" id="exportTableDiv" style="display: none" >
        <button  onclick='exportTables()' style="margin-bottom: 15px;">Export Tables</button>
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
        var task = document.getElementById('task');
        if (task.innerText === 'drop') {
            location.reload();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Sample data from PHP
        let tableData = <?php echo json_encode($data['tables']); ?>;
        let formattedData = tableData.map(table => ({ table: table }));

        // Create Tabulator
        let table = new Tabulator("#datatable", {
            data: formattedData,
            layout: "fitColumns",
            selectable: true,
            columns: [
                {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle",headerHozAlign: "center", headerSort: false,width:60, cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    }
                },
                { title: "Table Name", field: "table", sorter: "string" }
            ],
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
            var task = document.getElementById('task');
            var deleteCheckbox = document.getElementById('deleteCheckbox');
            var dropTableDiv = document.getElementById('dropTableDiv');

            if(table.getSelectedRows().length > 0) {
                switch(task.innerText) {
                    case 'delete':
                        deleteCheckbox.style.display = 'block';
                        break;
                    case 'drop':
                        dropTableDiv.style.display = 'block';
                        break;
                    case 'export':
                        exportTableDiv.style.display = 'block';
                        break;
                    default:
                        deleteCheckbox.style.display = 'none';
                        dropTableDiv.style.display = 'none';
                }

            } else {
                switch(task.innerText) {
                    case 'delete':
                        deleteCheckbox.style.display = 'none';
                        break;
                    case 'drop':
                        dropTableDiv.style.display = 'none';
                        break;
                    case 'export':
                        exportTableDiv.style.display = 'none';
                        break;
                    default:
                        deleteCheckbox.style.display = 'none';
                        dropTableDiv.style.display = 'none';
                        exportTableDiv.style.display = 'none';

                }
            }
        });

    });

    async function deleteData() {
        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // Filter the selected rows to include only the table name
        var filteredRows = selectedRows.map(row => {
            return {
                table: row.table // Ensure the correct field name is used
            };
        });

        // Get the value of the checkbox
        var resetAutoIncrement = document.getElementById('resetAutoIncrementCheckbox').checked;

        // Prepare the data to send
        var postData = {
            selectedTables: filteredRows,
            resetAutoIncrement: resetAutoIncrement
        };

        // Send the POST request
        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/deleteordropDeleteTableData';

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
                        if (response.deletedTables !== "") {
                            message += '<br>Dropped Tables:<br>' + response.deletedTables;
                        }
                        if (response.failedTables !== "") {
                            message += '<br>Failed To Drop Tables:<br>' + response.failedTables;
                        }

                        // Handle the response with the custom modal
                        openVtlModal('Data Deleted', true, message);


                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                    }
                } else {
                    var errorResponse = xhr.responseText;
                   openVtlModal('Error', false, errorResponse.message);
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


    async function dropTables() {
        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // Filter the selected rows to include only the table name
        var filteredRows = selectedRows.map(row => {
            return {
                table: row.table // Ensure the correct field name is used
            };
        });
        // Prepare the data to send
        var postData = {
            selectedTables: filteredRows
        };

        // Send the POST request
        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/deleteordropDropTables';

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
                        if (response.deletedTables !== "") {
                            message += '<br>Dropped Tables:<br>' + response.deletedTables;
                        }
                        if (response.failedTables !== "") {
                            message += '<br>Failed To Drop Tables:<br>' + response.failedTables;
                        }

                        // Handle the response with the custom modal
                        openVtlModal('Tables Dropped', true, message);
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                    }
                } else {
                    var errorResponse = xhr.responseText;
                   openVtlModal('Error', false, errorResponse.message);
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

    async function exportTables() {
        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();
        var tablesToExport = selectedRows.map(row => row.table);

        // Prepare the data to send
        var postData = {
            tablesToExport: tablesToExport
        };

        // Send the POST request
        try {
            // Create a new XMLHttpRequest
            var xhr = new XMLHttpRequest();

            // Specify the PHP file or endpoint to handle the data
            var targetUrl = '<?= BASE_URL ?>vtlgen/deleteordropExportTables';

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

                        // Handle the response with the custom modal
                        openVtlModal('Export Successful', true, message);
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                    }
                } else {
                    var errorResponse = xhr.responseText;
                    openVtlModal('Error', false, errorResponse);
                }
            };

            // Convert the data object to a JSON string
            var jsonData = JSON.stringify(postData);
            // Send the request with the JSON data
            xhr.send(jsonData);
        } catch (error) {
            console.error('Error:', error);
            openVtlModal('Error', false, 'An error occurred: ' + error);
        }
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

    input[type="checkbox"] {
        margin: 6px;
        align-self: inherit;
    }

</style>