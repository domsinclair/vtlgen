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
    <div id="task" style="display: none"><?= $task ?></div>
    <section>
        <div class="container" id="zipCheckbox" style="display: none;" >
            <label><input type="checkbox" id="zipProjectCheckbox" name="zipProject" >Zip Project</label>
            <div>
                <button id="zipProjectSubmitBtn" onclick='zipProject()' style=" margin-bottom: 15px; display: none">Zip Project</button>
            </div>
        </div>
    </section>
    <section>
        <div class="container" id="zipModule" style="display: none;">
            <button id="zipModuleSubmitBtn" onclick='zipModule()' >Zip Module</button>
        </div>
    </section>
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
    const task = "<?= $task ?>";
    const projectContainer = document.getElementById('zipCheckbox');
    const zipProjectCheckbox = document.getElementById('zipProjectCheckbox');
    const zipProjectSubmitBtn = document.getElementById('zipProjectSubmitBtn');
    const zipModule = document.getElementById('zipModule');
    let table;
    document.addEventListener('DOMContentLoaded', function() {
        // Sample data from PHP
        let tableData = <?php echo json_encode($data['modules']); ?>;

        // Filter out the orphaned_tables entry
        tableData = tableData.filter(item => !item.orphaned_tables);

        // Map the module data to the correct format for Tabulator
        let formattedData = tableData.map(module => ({ table: module.module_name }));

        // Create Tabulator
         table = new Tabulator("#datatable", {
            data: formattedData,
            layout: "fitColumns",
            selectable: true,
            columns: [
                {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle",headerHozAlign: "center", headerSort: false,width:60, cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    }
                },
                { title: "Modules", field: "table", sorter: "string" }
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
            switch (task) {
                case 'delete':
                   if (selected.length > 0) {
                        zipModule.style.display = 'block';
                    } else {
                        zipModule.style.display = 'none';
                    }
                    break;
                case 'zip':
                    if (selected.length > 0) {
                        zipProjectCheckbox.checked = false;
                        zipModule.style.display = 'block';
                    } else {
                        zipModule.style.display = 'none';
                    }
                    break;
            }




        });
        if (task === 'zip') {
            projectContainer.style.display = 'block';
        }
    });

    zipProjectCheckbox.addEventListener('change', function() {
        if (this.checked) {
            table.deselectRow();
            zipProjectSubmitBtn.style.display = 'block';
        } else {
            zipProjectSubmitBtn.style.display = 'none';
        }
    })

    function zipProject() {
        const targetUrl = "<?= BASE_URL ?>vtlgen/deleteorzipZipProject";

        try {
            // Create a new XMLHttpRequest
            const xhr = new XMLHttpRequest();

            // Open a GET request to the specified URL
            xhr.open('GET', targetUrl, true);

            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // If you expect a response, you can process it here
                    console.log('Response from server:', xhr.responseText);
                    // You can also display the response using a modal or alert
                    openVtlModal('Zip Project', true, xhr.responseText);
                } else {
                    // Handle errors
                    console.error('Request failed. Status:', xhr.status, 'Response:', xhr.responseText);
                    openVtlModal('Error', false, 'Request failed with status ' + xhr.status);
                }
            };

            // Send the request
            xhr.send();
        } catch (error) {
            console.error('Error:', error);
            openVtlModal('Error', false, 'An error occurred while sending the request.');
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
