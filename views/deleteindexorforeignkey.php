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
        <div>
            <button id="submitBtn" onclick='deleteSelectedIndexes()' style=" margin-bottom: 15px;">Drop Index</button>
        </div>
    </div>
    <div class="container" id="dropKey" style="display: none">
        <div>
            <button id="keySubmitBtn" onclick='deleteSelectedKeys()' style=" margin-bottom: 15px;">Drop Key</button>
        </div>
    </div>
</section>
<!--<section>-->
<!--    <div class="container" id="dropTableDiv" style="display: none" >-->
<!--        <button  onclick='dropIndexes()' style="margin-bottom: 15px;">Drop Index</button>-->
<!--    </div>-->
<!--</section>-->
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

    document.addEventListener('DOMContentLoaded', function() {


        // Ensure vtlModal is defined
        const vtlModal = document.getElementById('vtlModal');

        vtlModal.addEventListener('vtlModalClosed', () => {
            location.reload();
        });

        const data = <?= json_encode($data['rows']) ?>;
        const task = "<?= $task ?>";
        switch (task) {
            case 'index':
                createIndexTable(data);
                break;
            case 'key':
                createForeignKeyTable(data);
                break;
        }

        function createForeignKeyTable(data) {
            let table = new Tabulator("#datatable", {
                data: data,
                layout: "fitColumns",
                selectable: true,
                columns: [
                    {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle", headerHozAlign: "center", headerSort: false, width: 60, cellClick: function(e, cell) {
                            cell.getRow().toggleSelect();
                        }},
                    {title: "Foreign Key", field: "foreign key"},
                    {title: "References", field: "references"},
                    {title: "Constraint Name", field: "constraint name"}
                ],
            });
            table.on("rowSelected", function(row){
                row.getElement().style.backgroundColor = "var(--primary)";
                row.getElement().style.color = "white";
            });

            table.on("rowDeselected", function(row){
                row.getElement().style.backgroundColor = '';
                row.getElement().style.color = '';
            });
            table.on("rowSelectionChanged", function(data, rows){
                var deletekey = document.getElementById('dropKey');

                if(table.getSelectedRows().length > 0) {
                    deletekey.style.display = 'block';
                } else {
                    deletekey.style.display = 'none';
                }
            });
        }

        function createIndexTable(data) {
            let table = new Tabulator("#datatable", {
                data: data,
                layout: "fitColumns",
                selectable: true,
                columns: [
                    {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle", headerHozAlign: "center", headerSort: false, width: 60, cellClick: function(e, cell) {
                            cell.getRow().toggleSelect();
                        }},
                    {title: "Schema", field: "index_schema"},
                    {title: "Table Name", field: "table_name"},
                    {title: "Index Name", field: "index_name"},
                    {title: "Columns", field: "index_columns"},
                    {title: "Type", field: "index_type"},
                    {title: "Unique", field: "is_unique"}
                ],
            });

            table.on("rowSelected", function(row){
                if (row.getData().index_name === 'PRIMARY') {
                    row.getElement().style.backgroundColor = 'orangered';
                } else {
                    row.getElement().style.backgroundColor = "var(--primary)";
                }
                row.getElement().style.color = "white";
            });

            table.on("rowDeselected", function(row){
                row.getElement().style.backgroundColor = '';
                row.getElement().style.color = '';
            });
            table.on("rowSelectionChanged", function(data, rows){
                var deleteCheckbox = document.getElementById('deleteCheckbox');

                if(table.getSelectedRows().length > 0) {
                    deleteCheckbox.style.display = 'block';
                } else {
                    deleteCheckbox.style.display = 'none';
                }
            });
        }
    });

        async function deleteSelectedIndexes(){

            var table = Tabulator.findTable("#datatable")[0];
            var selectedRows = table.getSelectedData();
            if (selectedRows.length > 0) {
                // Check if any selected rows represent a primary key
                const primaryKeyRows = selectedRows.filter(row => row.index_name === 'PRIMARY');

                if (primaryKeyRows.length > 0) {
                    // Show the confirmation dialog in warning mode if a primary key is selected
                    openVtlQuestionModal(
                        'Caution',
                        'One or more of the selected indexes are primary keys. Are you sure you want to delete them?',
                        'vtlWarning', // Provide the base name of the warning icon
                        'warning'
                    );

                    // Listen for acceptance or cancellation
                    const handleAccept = async () => {
                        document.removeEventListener('vtlQuestionAccepted', handleAccept);
                        document.removeEventListener('vtlQuestionCancelled', handleCancel);
                        await performDeletionOfIndexes(selectedRows);
                    };

                    const handleCancel = () => {
                        document.removeEventListener('vtlQuestionAccepted', handleAccept);
                        document.removeEventListener('vtlQuestionCancelled', handleCancel);
                        // User canceled, so do nothing
                    };

                    document.addEventListener('vtlQuestionAccepted', handleAccept);
                    document.addEventListener('vtlQuestionCancelled', handleCancel);
                } else {
                    // Proceed with deletion if no primary key was selected
                    await performDeletionOfIndexes(selectedRows);
                }
            } else {
                alert("No indexes selected for deletion.");
            }

        }

        async function deleteSelectedKeys(){

            var table = Tabulator.findTable("#datatable")[0];
            var selectedRows = table.getSelectedData();
            if (selectedRows.length > 0) {
                try{
                    var postData = {
                        selectedRows: selectedRows
                    };
                    var xhr = new XMLHttpRequest();
                    var targetUrl = '<?= BASE_URL ?>vtlgen/deleteindexorforeignkeyDeleteKey';
                    // Open a POST request to the specified URL
                    xhr.open('POST', targetUrl, true);

                    // Set the content type to JSON
                    xhr.setRequestHeader('Content-type', 'application/json');

                    // Convert the data object to a JSON string
                    var jsonData = JSON.stringify(selectedRows);
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            try {
                                // Parse the JSON response
                                var response = JSON.parse(xhr.responseText);
                                let message = response.message;

                                // Handle the response with the custom modal
                                openVtlModal('Keys Dropped Successfully', true, message);

                            } catch (e) {
                                console.error('Error parsing JSON response:', e);
                                openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                            }
                        } else {
                            openVtlModal('Error', false, 'Error dropping foreign keys: ' + xhr.responseText);
                        }
                    };
                    // Send the request with the JSON data
                    xhr.send(jsonData);
                }catch (error) {
                    console.error('Error:', error);
                    openVtlModal('Error', false, 'An error occurred: ' + error);
                }

            }

        }

    async function performDeletionOfIndexes(selectedRows) {
       try {
           var xhr = new XMLHttpRequest();

           // Specify the PHP file or endpoint to handle the data
           var targetUrl = '<?= BASE_URL ?>vtlgen/deleteindexorforeignkeyDeleteIndex';
           // Open a POST request to the specified URL
           xhr.open('POST', targetUrl, true);

           // Set the content type to JSON
           xhr.setRequestHeader('Content-type', 'application/json');

           // Convert the data object to a JSON string
           var jsonData = JSON.stringify(selectedRows);
           xhr.onload = function () {
               if (xhr.status === 200) {
                   try {
                       // Parse the JSON response
                       var response = JSON.parse(xhr.responseText);
                       let message = response.message;

                       // Handle the response with the custom modal
                       openVtlModal('Indexes Dropped Successfully', true, message);

                   } catch (e) {
                       console.error('Error parsing JSON response:', e);
                       openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                   }
               } else {
                   openVtlModal('Error', false, 'Error dropping indexes: ' + xhr.responseText);
               }
           };

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