
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
<section class="container">
    <div>
        <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        <p><?= $instruction1 ?> </p>
        <p><?= $instruction2 ?> </p>
    </div>
    <div class="dropdown-container">
        <div class="dropdown-wrapper">
            <h4>Foreign Key side</h4>
            <?php
            $tableChoiceAttr1['id'] = 'tableChoiceDropdown1';
            $tableChoiceAttr1['onchange'] = 'selectedTableOne()'; // Add parentheses
            echo form_dropdown('tableChoice1', $tables, '', $tableChoiceAttr1);
            ?>
            <div id="columnInfoTableContainer1" class="column-container">
                <div id="datatable1"></div>
            </div>
        </div>
        <div class="dropdown-wrapper">
            <h4>Related To side</h4>
            <?php
            $tableChoiceAttr2['id'] = 'tableChoiceDropdown2';
            $tableChoiceAttr2['onchange'] = 'selectedTableTwo()'; // Add parentheses
            echo form_dropdown('tableChoice2', $tables, '', $tableChoiceAttr2);
            ?>
            <div id="columnInfoTableContainer2" class="column-container">
                <div id="datatable2"></div>
            </div>
        </div>
    </div>
    <div id="createButton" style="display: none;">
        <button onclick="createForeignKey()">Create Foreign Key</button>
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
    async function selectedTableOne() {

        var dropdown = document.getElementById('tableChoiceDropdown1');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;
        var columnInfo = <?php echo json_encode($columnInfo); ?>;
        var selectedTableColumns = columnInfo.find(table => table.table === selectedTable).columns;
        var tableData1 = [];
        selectedTableColumns.forEach(column => {
                tableData1.push({
                    field: column.Field,
                });

        });

        let table1 = new Tabulator("#datatable1", {
            data: tableData1,
            layout: "fitColumns",
            selectable: true,
            columns: [
                {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle", headerHozAlign: "center", headerSort: false, width: 60, cellClick: function(e, cell) {
                        cell.getRow().toggleSelect();
                    }}, // Add checkbox column
                {title: "Field", field: "field", width: 200, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left" },
               ],
        });
        table1.on("rowSelected", function(row){
            row.getElement().style.backgroundColor = "var(--primary)";
            row.getElement().style.color = "white";
        });

        table1.on("rowDeselected", function(row){
            row.getElement().style.backgroundColor = '';
            row.getElement().style.color = '';
        });
        table1.on("rowSelectionChanged", function(data, rows, selected, deselected){
            showHideCreateButton();
        });
    }

    async function selectedTableTwo() {
        var dropdown = document.getElementById('tableChoiceDropdown2'); // Corrected ID
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;
        var columnInfo = <?php echo json_encode($columnInfo); ?>;
        var selectedTableColumns = columnInfo.find(table => table.table === selectedTable).columns;
        var tableData2 = [];
        selectedTableColumns.forEach(column => {
            tableData2.push({
                field: column.Field,
            });
        });

        let table2 = new Tabulator("#datatable2", {
            data: tableData2,
            layout: "fitColumns",
            selectable: true,
            columns: [
                {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle", headerHozAlign: "center", headerSort: false, width: 60, cellClick: function(e, cell) {
                        cell.getRow().toggleSelect();
                    }}, // Add checkbox column
                {title: "Field", field: "field", width: 200, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left" },
            ],
        });
        table2.on("rowSelected", function(row){
            row.getElement().style.backgroundColor = "var(--primary)";
            row.getElement().style.color = "white";
        });

        table2.on("rowDeselected", function(row){
            row.getElement().style.backgroundColor = '';
            row.getElement().style.color = '';
        });
        table2.on("rowSelectionChanged", function(data, rows, selected, deselected){
            showHideCreateButton();
        });
    }

    async function  showHideCreateButton(){

        var table1 = Tabulator.findTable("#datatable1")[0];
        var table2 = Tabulator.findTable("#datatable2")[0];
        if(table1.getSelectedRows().length > 0 && table2.getSelectedRows().length > 0) {
            document.getElementById('createButton').style.display = 'block';
        } else {
            document.getElementById('createButton').style.display = 'none';
        }
    }

    async function createForeignKey(){
        var dropdown1 = document.getElementById('tableChoiceDropdown1');
        var selectedTable1 = dropdown1.options[dropdown1.selectedIndex].text;
        var dropdown2 = document.getElementById('tableChoiceDropdown2');
        var selectedTable2 = dropdown2.options[dropdown2.selectedIndex].text;
        var table1 = Tabulator.findTable("#datatable1")[0];
        var table2 = Tabulator.findTable("#datatable2")[0];
        // Get the data from the selected rows
        var selectedColumn1 = table1.getSelectedData()[0].field;
        var selectedColumn2 = table2.getSelectedData()[0].field;
        var targetUrl = '<?= BASE_URL ?>vtlgen/createforeignkeySetForeignKey';
        var postData = {
            table1: selectedTable1,
            table2: selectedTable2,
            selectedField1: selectedColumn1,
            selectedField2: selectedColumn2
        };
        var xhr = new XMLHttpRequest();
        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('Content-type', 'application/json');
        var jsonData = JSON.stringify(postData);
        console.log('post data: ' + jsonData);
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    // Parse the JSON response
                    var response = JSON.parse(xhr.responseText);
                    let message = response.message;

                    // Handle the response with the custom modal
                    openVtlModal('Foreign Key Created Successfully', true, message);

                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    openVtlModal('Error Parsing Json', false, 'An error occurred while processing the response.');
                }
            } else {
                openVtlModal('Error', true, 'Error creating Foreign key: ' + xhr.responseText);
            }
        };
        xhr.send(jsonData);


    }

</script>
<style>
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

    #datatable1, #datatable2 {
        margin-top: 20px;
    }

    #datatable1 input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static; /* Change to relative positioning */
        width: auto;
        height: auto;
    }
    #datatable2 input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static; /* Change to relative positioning */
        width: auto;
        height: auto;
    }
    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 1200px; /* Adjust max-width as needed */
        margin: 0 auto; /* Center the container horizontally */
    }

    .button {
        margin-bottom: 10px; /* Add some bottom margin to the button */
    }

    .dropdown-container {
        display: flex;
        justify-content: center; /* Center the dropdowns horizontally */
        margin-top: 20px; /* Adjust spacing between button/instructions and dropdowns */
    }

    .dropdown-wrapper {
        margin-right: 20px; /* Adjust margin between dropdowns if needed */
    }

    /* Adjust the width of the tables */
    .column-container table {
        width: 100%; /* Adjust width as needed */
    }
</style>




