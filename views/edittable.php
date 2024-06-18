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
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/prism.css">
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
        $tableChoiceAttr['onchange'] = 'handleTableSelection()'; // Changed function name
        echo form_dropdown('tableChoice', $tables, '', $tableChoiceAttr);
        ?>
        <h3 id="columnsHeader" style="display: none;">Columns</h3>
        <div class="container" id="datatable"></div>
    </div>
</section>
<section>
    <div class="container">
        <pre >
            <code id="sqlCode" class="language-sql">

             </code>
        </pre>
    </div>
</section>
<script src="<?= BASE_URL ?>vtlgen_module/js/prism.js"></script>
</body>
</html>
<script type="text/javascript">
    var tabulator;
    var tableName;
    var droppedColumns = [];
    var modifiedColumns = [];
    var tableData;

    document.addEventListener("DOMContentLoaded", function () {
        const dropdown = document.getElementById('tableChoiceDropdown');
        dropdown.addEventListener('change', handleTableSelection);
    });

    async function handleTableSelection() {
        const dropdown = document.getElementById('tableChoiceDropdown');
        tableName = dropdown.options[dropdown.selectedIndex].text;
        const colHeader = document.getElementById('columnsHeader');

        if (tableName === 'Select table...') {
            colHeader.style.display = 'none';
            if (tabulator) {
                tabulator.destroy();
                tabulator = null;
            }
        } else {
            colHeader.style.display = 'block';
            const columnInfo = <?php echo json_encode($columnInfo); ?>;
            const selectedTableInfo = columnInfo.find(t => t.table === tableName);

            if (selectedTableInfo) {
                const selectedTableColumns = selectedTableInfo.columns;
                tableData = selectedTableColumns.map(col => ({
                    colname: col.Field,
                    type: col.Extra.toLowerCase().includes('auto_increment') ? 'autoincrement' : col.Type,
                    nullable: col.Null === 'YES',
                    default: col.Default || '',
                    primary: col.Key === 'PRI',
                    unique: col.Key === 'UNI',
                    autoIncrement: col.Extra.toLowerCase().includes('auto_increment')
                }));

                if (tabulator) {

                } else {
                    initializeTabulator(tableData);
                }
            }
        }
    }

    function initializeTabulator(tableData) {
        tabulator = new Tabulator("#datatable", {
            layout: "fitColumns",
            tabEndNewRow: { nullable: true, unique: false },
            columns: [
                { title: "Field Name", field: "colname", editor: "input" },
                {
                    title: "Data Type", field: "type", editor: "list", editorParams: function (cell) {
                        return {
                            values: {
                                "autoincrement": "Autoincrement",
                                "varchar": "Varchar",
                                "varchar(10)": "Varchar(10)",
                                "varchar(15)": "Varchar(15)",
                                "varchar(25)": "Varchar(25)",
                                "varchar(32)": "Varchar(32)",
                                "varchar(50)": "Varchar(50)",
                                "varchar(75)": "Varchar(75)",
                                "varchar(100)": "Varchar(100)",
                                "varchar(255)": "Varchar(255)",
                                "text": "Text",
                                "int": "Int",
                                "int(11)": "Int(11)",
                                "tinyint": "Tinyint",
                                "bigint": "Bigint",
                                "decimal": "Decimal",
                                "float": "Float",
                                "double": "Double",
                                "boolean": "Boolean",
                                "date": "Date",
                                "datetime": "Datetime",
                                "time": "Time",
                                "timestamp": "Timestamp",
                                "char": "Char",
                                "binary": "Binary",
                                "varbinary": "Varbinary",
                                "blob": "Blob",
                                "uuid": "Uuid"
                            },
                            showListOnEmpty: true
                        };
                    },
                    cellEdited: function (cell) {
                        handleCellEdit(cell);
                    }
                },
                {
                    title: "Nullable", field: "nullable", editor: "tickCross", hozAlign: "center", vertAlign: "middle", formatter: "tickCross", width: 100
                },
                { title: "Default Value", field: "default", editor: "input" },
                {
                    title: "Primary Key", field: "primary", vertAlign: "middle", hozAlign: "center", editor: "tickCross", formatter: "tickCross", width: 120
                },
                {
                    title: "Unique", field: "unique", vertAlign: "middle", hozAlign: "center", editor: "tickCross", formatter: "tickCross", width: 100
                },
                {
                    title: '', formatter: function (cell) {
                        const span = document.createElement("span");
                        span.className = "tabulator-button tabulator-button-cross custom-button-cross";
                        span.innerHTML = "&times;";
                        return span;
                    },
                    width: 40, hozAlign: "center", vertAlign: "middle", cellClick: function (e, cell) {
                        cell.getRow().delete();
                    }
                }
            ],
            data: tableData
        });

        tabulator.on("rowDeleted", function (row) {
            handleRowDeletion(row);
        });

        tabulator.on("rowUpdated", function (row) {
            handleRowUpdate(row);
        });
        tabulator.on("tableBuilt", function () {
            handleTableBuilt();
        });
    }

    function handleCellEdit(cell) {
        const row = cell.getRow();
        const data = row.getData();
        const oldValue = cell.getOldValue();
        const newValue = cell.getValue();

        if (cell.getField() === "colname" && oldValue !== newValue) {
            // Column name changed
            modifiedColumns.push({
                type: 'rename',
                oldName: oldValue,
                newName: newValue
            });
        } else if (cell.getField() === "type" && oldValue !== newValue) {
            // Column type changed
            modifiedColumns.push({
                type: 'modify',
                colname: data.colname,
                oldType: oldValue,
                newType: newValue
            });
        }

        // Additional logic based on cell edits if needed
        switch (newValue) {
            case "autoincrement":
                cell.getRow().update({ nullable: false, default: null });
                break;
            case "timestamp":
                cell.getRow().update({ nullable: false, default: 'CURRENT_TIMESTAMP' });
                break;
            case "uuid":
                cell.getRow().update({ nullable: false, default: 'UUID()' });
                break;
            default:
                // Default case if needed
                break;
        }
    }

    function handleRowDeletion(row) {
        const data = row.getData();
        droppedColumns.push(data.colname);
        // You can also add more sophisticated logic to handle dropped columns
        // and generate SQL statements for dropping columns.
    }

    function handleRowUpdate(row) {
        // Handle row update logic here
        // You can use modifiedColumns array to track modified columns
        console.log("Row updated:", row.getData());
    }

    function handleTableBuilt() {
        // Add your logic to handle table built
        tabulator.setData(tableData);
    }

    async function generateSql() {
        // Generate SQL code using modifiedColumns and droppedColumns
        console.log("Modified columns:", modifiedColumns);
        console.log("Dropped columns:", droppedColumns);
        // You can use modifiedColumns and droppedColumns to generate SQL statements
        // for modifying and dropping columns, respectively.
        // We will want to start with an Alter Table statement and the follow that with
        // dropped columns, modified columns and finally added columns.
    }
</script>


<style>
    @media (prefers-color-scheme: light) {
        div.tabulator-cell {
            color: white;
        }
        div.tabulator-col-title {
            color: white;
        }
        div.tabulator-col.tabulator-sortable.tabulator-col-sorter-element {
            color: white;
        }
    }

    @media (hover: hover) and (pointer: fine) {
        .tabulator .tabulator-header .tabulator-col.tabulator-sortable.tabulator-col-sorter-element:hover {
            background-color: var(--primary-dark);
        }
    }
    div.tabulator-col.tabulator-sortable.tabulator-col-sorter-element {
        background-color: var(--primary-dark);
    }

    #datatable {
        margin-top: 20px;
    }

    #datatable input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static;
        width: auto;
        height: auto;
    }
    .flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .icon-button {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .icon-button:hover {
        background: transparent;
    }

    .icon-button svg {
        width: 32px;
        height: 32px;
    }

    #table-name-input {
        width: 40%;
        padding: 8px;
        box-sizing: border-box;
        font-size: 16px;
    }

    .custom-button-cross {
        color: #7b04d6;
        font-size: 24px;
        cursor: pointer;
    }

    .custom-button-cross:hover {
        color: #37035c;
    }

    #sqlCode {
        white-space: pre-wrap;
    }
</style>
