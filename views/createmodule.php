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
    <div class="container" id="createModuleDiv" style="display: none" >
        <button  onclick='createModules()' style="margin-bottom: 15px;">Create Module</button>
    </div>
</section>
<section>
    <div class="container" id="progress" style="display: none" >
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
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
</body>
</html>
<script>
    vtlModal.addEventListener('vtlModalClosed', () => {
            location.reload();
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
                { title: "Tables without Modules", field: "table", sorter: "string" }
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
            var createMods = document.getElementById('createModuleDiv');


            if (createMods.style.display === 'block') {
                createMods.style.display = 'none';
            } else {
                createMods.style.display = 'block';
            }
        });

    });

    function createModules() {
        // Get the selected rows from the Tabulator datatable
        var table = Tabulator.findTable("#datatable")[0];
        var selectedRows = table.getSelectedData();

        // Filter the selected rows to include only the table name
        var filteredRows = selectedRows.map(row => {
            return {
                table: row.table // Ensure the correct field name is used
            };
        });

        // At this stage we might need to invoke a function to iterate over a loop so that we can have a progress bar.

        // Prepare the data to send
        var postData = {
            selectedTables: filteredRows
        };


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
