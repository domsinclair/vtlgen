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
    </div>
</section>
<section>
    <div class="container" id="datatable"></div>
</section>
</body>
</html>
<script>
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

        // Delete Data Button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            let selectedRows = table.getSelectedRows();
            let tablesToDelete = selectedRows.map(row => row.getData().table);
            if (tablesToDelete.length > 0) {
                if (confirm(`Are you sure you want to delete data from the following tables?\n${tablesToDelete.join(", ")}`)) {
                    // Handle deletion logic here
                    console.log('Deleting data from tables:', tablesToDelete);
                    // Perform AJAX request or form submission to delete data
                }
            } else {
                alert('Please select at least one table to delete data.');
            }
        });

        // Drop Tables Button
        document.getElementById('dropBtn').addEventListener('click', function() {
            let selectedRows = table.getSelectedRows();
            let tablesToDrop = selectedRows.map(row => row.getData().table);
            if (tablesToDrop.length > 0) {
                if (confirm(`Are you sure you want to drop the following tables?\n${tablesToDrop.join(", ")}`)) {
                    // Handle drop logic here
                    console.log('Dropping tables:', tablesToDrop);
                    // Perform AJAX request or form submission to drop tables
                }
            } else {
                alert('Please select at least one table to drop.');
            }
        });
    });
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

</style>