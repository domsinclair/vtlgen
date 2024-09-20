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
    <div class="container">
        <label><input type="checkbox" id="addMultiFileUploaderCheckbox" name="multiFileUploader" > Add Multi File Uploader</label>
    </div>
</section>
<section>
    <div class="container" id="createModuleDiv" style="display: none" >
        <button  onclick='createModules()' style="margin-bottom: 15px;">Create Module</button>
    </div>
</section>
<section>
    <div class="container" id="progressContainer" style="display: none">
        <div class="progress-bar" id="progress-bar" style="display: none" >
            <div class="progress" id="progress" ></div>
        </div>
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
<div id="vtlQuestionOverlay" class="vtlOverlay"></div>
<dialog id="vtlQuestionModal" class="vtlModal">
    <div id="vtlQuestionModalHeader" class="vtlModalHeader">
        <picture id="vtlQuestionIconPicture" class="vtlModalIcon">
            <source id="vtlQuestionIconDark" media="(prefers-color-scheme: dark)">
            <img id="vtlQuestionIconLight" src="" alt="Icon">
        </picture>
        <h2 class="vtlModalTitle" id="vtlQuestionModalTitle">Question Title</h2>
    </div>
    <div class="vtlModalContentWrapper">
        <p id="vtlQuestionContent">Question content</p>
    </div>
    <div class="vtlModalFooter">
        <button class="vtlAcceptButton" id="vtlAcceptQuestion">Accept</button>
        <button class="vtlCancelButton" id="vtlCancelQuestion">Cancel</button>
    </div>
</dialog>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlQuestionModal.js"></script>
</body>
</html>
<script>
    var noDataMessage = "<?= $noDataMessage ?>";
    let isSimpleModuleCreated = false; // Track whether a simple module was created
    vtlModal.addEventListener('vtlModalClosed', () => {
        if (isSimpleModuleCreated) {
            // If a simple module was created, redirect to home page
            window.location.href = '<?= BASE_URL ?>vtlgen';
        } else {
            // Otherwise, reload the page
            location.reload();
        }
    });
    document.addEventListener('DOMContentLoaded', function() {

        // Add event listeners for modal buttons
        document.getElementById('vtlAcceptQuestion').addEventListener('click', acceptQuestion);
        document.getElementById('vtlCancelQuestion').addEventListener('click', cancelQuestion);

        // Step 1: Show the question modal when the page loads
        openVtlQuestionModal('Create Simple Module', 'Would you like to create a simple module? If so please enter the name:', 'vtlInfo', 'info');

        // Add an input field to the modal dynamically
        const inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.id = 'simpleModuleName';
        inputField.placeholder = 'Enter module name...';
        inputField.style.marginTop = '10px';
        document.getElementById('vtlQuestionContent').appendChild(inputField);

        // Step 2: Function to handle creating a simple module
        function acceptQuestion() {
            const moduleName = document.getElementById('simpleModuleName').value;

            if (moduleName) {
                // Send the simple module name to the backend
                fetch('<?= BASE_URL ?>vtlgen/createSimpleModule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name: moduleName })
                }).then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            // Set the flag to true if a simple module is created
                            isSimpleModuleCreated = true;
                            // Use the vtlModal for success notification
                            openVtlModal('Simple Module Created', true, 'Simple module "' + moduleName + '" created successfully.');
                        } else {
                            // Use the vtlModal for error notification
                            openVtlModal('Error', false, 'Failed to create simple module. ' + (result.message || 'Unknown error.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        openVtlModal('Error', false, 'An error occurred while trying to create the simple module.');
                    });
            } else {
                openVtlModal('Error', false, 'Please enter a module name.');
            }

           // closeQuestionModal();
        }

        // Step 3: Handle the case where the user cancels the modal
        function cancelQuestion() {
            // Close the modal and proceed with the default behavior
            //closeQuestionModal();
            runDefaultTableBasedModuleCreation();  // This will invoke the existing functionality
        }



        // Step 4: Define the function that runs the default table-based module creation (original functionality)
        function runDefaultTableBasedModuleCreation() {
            // Sample data from PHP
            let tableData = <?php echo json_encode($data['tables']); ?>;
            let formattedData = tableData.map(table => ({ table: table }));

            // Create Tabulator
            let table = new Tabulator("#datatable", {
                height: "300px",
                data: formattedData,
                layout: "fitColumns",
                selectable: true,
                columns: [
                    {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle",headerHozAlign: "center", headerSort: false,width:60, cellClick: function (e, cell) {
                            cell.getRow().toggleSelect();
                        }
                    },
                    { title: "Tables without Modules", field: "table", sorter: "string" }
                ],
                placeholder: noDataMessage
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
                var createMods = document.getElementById('createModuleDiv');
                if (rows.length > 0) { // If one or more rows are selected
                    createMods.style.display = 'block';
                } else {
                    createMods.style.display = 'none';
                }

            });
        }
    });



    async function createModules() {
        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // get the value from the multi file uploader checkbox
        var addMultiFileUploader = document.getElementById("addMultiFileUploaderCheckbox").checked;

        // Filter the selected rows to include only the table names
        var tableNames = selectedRows.map(row => row.table);

        // Initialize progress bar
        var progressBarContainer = document.getElementById('progressContainer');
        var progressBar = document.getElementById('progress-bar');
        var progress = document.getElementById('progress');

        progressBarContainer.style.display = 'block';
        progressBar.style.display = 'block';
        progress.style.width = '0%';

        // Iterate over each selected table and call the backend
        for (let i = 0; i < tableNames.length; i++) {
            let tableName = tableNames[i];

            try {
                let response = await fetch('<?= BASE_URL ?>vtlgen/createModules', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ table: tableName , addMultiFileUploader: addMultiFileUploader})
                });

                if (response.ok) {
                    let result = await response.json();

                    // Update progress bar
                    let progressPercentage = ((i + 1) / tableNames.length) * 100;
                    progress.style.width = progressPercentage + '%';
                    progress.textContent = progressPercentage + '%';

                    // Handle success or error for each table
                    if (result.status === 'success') {
                        console.log('Module created for table:', tableName);
                    } else {
                        console.error('Failed to create module for table:', tableName, '-', result.message);
                    }
                } else {
                    console.error('Failed to create module for table:', tableName);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Optionally, you can display a completion message or refresh the page
        openVtlModal('Modules Created', true, 'All modules created successfully.');
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
