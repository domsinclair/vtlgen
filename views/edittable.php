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
        <div class="flex">
        <button id="createSql" class="button" onclick="generateSql()" style="display: none;">Generate Sql</button>
        <button id="saveButton" class="button" onclick="handleSave()" style="display: none;">Save Changes</button>
        </div>
        <pre>
            <code id="sqlCode" class="language-sql">
            </code>
        </pre>
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
<script src="<?= BASE_URL ?>vtlgen_module/js/prism.js"></script>
</body>
</html>

<script type="text/javascript">
    var tabulator;
    var tableName;
    var tableData;
    var initialTableData = [];
    var updatedData =[];
    var rowStates = [];
    var sql = '';
    var successfulOperation = false;

    vtlModal.addEventListener('vtlModalClosed', () => {
        if (successfulOperation) {
            location.reload();
        }
    });

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
                    uniqueId: generateUniqueId(),
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

    function generateUniqueId() {
        return 'id-' + Math.random().toString(36).substr(2, 16);
    }

    function initializeTabulator(tableData) {
        tabulator = new Tabulator("#datatable", {
            layout: "fitColumns",
            tabEndNewRow: { nullable: true, unique: false },
            columns: [
                { title: "Unique ID", field: "uniqueId", visible: false },
                {
                    title: "Field Name", field: "colname", editor: "input"
                },
                {
                    title: "Data Type", field: "type", editor: "list", editorParams: {
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
                    }
                },
                {
                    title: "Nullable", field: "nullable", editor: "tickCross", hozAlign: "center", vertAlign: "middle", formatter: "tickCross", width: 100
                },
                {
                    title: "Default Value", field: "default", editor: "input"
                },
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

        tabulator.on("rowAdded", function (row) {
           // handleRowAdded(row);
        });
        tabulator.on("cellEdited", function (cell) {
            handleCellEdited(cell);
        });


    }

    function handleCellEdited(cell) {
        // Add logic to handle cell edited
        const btn = document.getElementById('createSql');
        btn.style.display = 'block';
    }

    function handleRowDeletion(row) {
        // Add logic to handle row deletion
    }

    function handleRowUpdate(row) {
        // Add logic to handle row update
        const btn = document.getElementById('createSql');
        btn.style.display = 'block';
    }

    function handleTableBuilt() {
        // Add logic to handle table built
        tabulator.setData(tableData);
        initialTableData = tabulator.getData(); // Store the initial table data
    }

    function handleRowAdded(row) {
        // Add logic to handle row added
        const uniqueId = generateUniqueId();
        tabulator.addRow({uniqueId: uniqueId});
    }

    function generateSql(){
        updatedData = tabulator.getData();
       sql = generateAlterTableSql(initialTableData, updatedData, tableName);
        document.getElementById('sqlCode').innerText = sql;
        Prism.highlightAll();
        const btn = document.getElementById('saveButton');
        btn.style.display = 'Block';
    }

    function handleSave() {
        // Add logic to handle saving changes
        var xhr = new XMLHttpRequest();
        var targetUrl = '<?= BASE_URL ?>vtlgen/edittableAlterDataTable';
        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({sql: sql}));
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Parse the JSON response
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    successfulOperation = true;
                    openVtlModal('Table Altered',true,response.message);
                } else {
                    openVtlModal('Error Altering Table',false,response.message);
                }
            } else {
                openVtlModal('Error Altering Table',false,response.message);
            }
        };
    }
    function addNewRowToTabulator(){
        const uniqueId = generateUniqueId();
        tabulator.addRow({uniqueId: uniqueId,nullable: true, unique: false});
    }

    function generateAlterTableSql(initialTableData, updatedData, tableName) {
        let dropColumns = [];
        let addColumns = [];
        let modifyColumns = [];
        let renameColumns = [];
        let dropConstraints = [];
        let addConstraints = [];
        let dropIndexes = [];
        let addIndexes = [];

        let initialColumnsMap = {};
        let updatedColumnsMap = {};

        // Create a map of initial columns
        initialTableData.forEach(col => {
            initialColumnsMap[col.uniqueId] = col;
        });

        // Create a map of updated columns
        updatedData.forEach(col => {
            updatedColumnsMap[col.uniqueId] = col;
        });

        // Identify dropped columns
        initialTableData.forEach(col => {
            if (!updatedColumnsMap[col.uniqueId]) {
                dropColumns.push(col.colname);
            }
        });

        // Identify added columns
        updatedData.forEach(col => {
            if (!col.uniqueId) {
                addColumns.push(col);
            }
        });

        // Identify modified and renamed columns
        updatedData.forEach(col => {
            let initialCol = initialColumnsMap[col.uniqueId];
            if (initialCol) {
                if (initialCol.colname !== col.colname) {
                    // Column renamed
                    renameColumns.push({
                        oldName: initialCol.colname,
                        newName: col.colname,
                        type: initialCol.type // Assuming type remains the same for rename
                    });
                }
                if (initialCol.type !== col.type || initialCol.nullable !== col.nullable || initialCol.default !== col.default) {
                    // Column modified
                    modifyColumns.push(col);
                }
                // Check for constraints and indexes
                if (initialCol.constraints !== col.constraints) {
                    dropConstraints.push({
                        colname: initialCol.colname,
                        constraints: initialCol.constraints
                    });
                    addConstraints.push({
                        colname: col.colname,
                        constraints: col.constraints
                    });
                }
                if (initialCol.indexes !== col.indexes) {
                    dropIndexes.push({
                        colname: initialCol.colname,
                        indexes: initialCol.indexes
                    });
                    addIndexes.push({
                        colname: col.colname,
                        indexes: col.indexes
                    });
                }
            }
        });

        // Construct the SQL statement
        let sqlParts = [];

        if (dropIndexes.length > 0) {
            dropIndexes.forEach(idx => {
                idx.indexes.forEach(index => {
                    sqlParts.push(`DROP INDEX \`${index}\``);
                });
            });
        }

        if (dropConstraints.length > 0) {
            dropConstraints.forEach(con => {
                con.constraints.forEach(constraint => {
                    sqlParts.push(`DROP CONSTRAINT \`${constraint}\``);
                });
            });
        }

        if (dropColumns.length > 0) {
            dropColumns.forEach(colname => {
                sqlParts.push(`DROP COLUMN \`${colname}\``);
            });
        }

        if (addColumns.length > 0) {
            addColumns.forEach(col => {
                let columnDef = `ADD COLUMN \`${col.colname}\` `;
                if (col.type === 'autoincrement') {
                    columnDef += 'INT(11) AUTO_INCREMENT';
                } else {
                    columnDef += col.type;
                }
                if (col.nullable === false) columnDef += ' NOT NULL';
                if (col.default) {
                    switch (col.default) {
                        case 'CURRENT_TIMESTAMP':
                            columnDef += ' DEFAULT CURRENT_TIMESTAMP';
                            break;
                        case 'CURRENT_DATE':
                            columnDef += ' DEFAULT CURRENT_DATE';
                            break;
                        case 'CURRENT_TIME':
                            columnDef += ' DEFAULT CURRENT_TIME';
                            break;
                        case 'UTC_TIMESTAMP':
                            columnDef += ' DEFAULT UTC_TIMESTAMP';
                            break;
                        case 'UNIX_TIMESTAMP':
                            columnDef += ' DEFAULT UNIX_TIMESTAMP';
                            break;
                        case 'UUID()':
                            columnDef += ' DEFAULT UUID()';
                            break;
                        default:
                            columnDef += ` DEFAULT '${col.default}'`;
                    }
                }
                if (col.unique) {
                    columnDef += ' UNIQUE';
                }
                columnDef = columnDef.trim();
                sqlParts.push(columnDef);
            });
        }

        if (modifyColumns.length > 0) {
            modifyColumns.forEach(col => {
                let columnDef = `CHANGE COLUMN \`${col.colname}\` \`${col.colname}\` `;
                if (col.type === 'autoincrement') {
                    columnDef += 'INT(11) AUTO_INCREMENT';
                } else {
                    columnDef += col.type;
                }
                if (col.nullable === false) columnDef += ' NOT NULL';
                if (col.default) {
                    switch (col.default) {
                        case 'CURRENT_TIMESTAMP':
                            columnDef += ' DEFAULT CURRENT_TIMESTAMP';
                            break;
                        case 'CURRENT_DATE':
                            columnDef += ' DEFAULT CURRENT_DATE';
                            break;
                        case 'CURRENT_TIME':
                            columnDef += ' DEFAULT CURRENT_TIME';
                            break;
                        case 'UTC_TIMESTAMP':
                            columnDef += ' DEFAULT UTC_TIMESTAMP';
                            break;
                        case 'UNIX_TIMESTAMP':
                            columnDef += ' DEFAULT UNIX_TIMESTAMP';
                            break;
                        case 'UUID()':
                            columnDef += ' DEFAULT UUID()';
                            break;
                        default:
                            columnDef += ` DEFAULT '${col.default}'`;
                    }
                }
                columnDef = columnDef.trim();
                sqlParts.push(columnDef);
            });
        }

        if (renameColumns.length > 0) {
            renameColumns.forEach(col => {
                let columnDef = `CHANGE COLUMN \`${col.oldName}\` \`${col.newName}\` `;
                if (col.type === 'autoincrement') {
                    columnDef += 'INT(11) AUTO_INCREMENT';
                } else {
                    columnDef += col.type;
                }
                columnDef = columnDef.trim();
                sqlParts.push(columnDef);
            });
        }

        if (addConstraints.length > 0) {
            addConstraints.forEach(con => {
                con.constraints.forEach(constraint => {
                    sqlParts.push(`ADD CONSTRAINT \`${constraint}\``);
                });
            });
        }

        if (addIndexes.length > 0) {
            addIndexes.forEach(idx => {
                idx.indexes.forEach(index => {
                    sqlParts.push(`ADD INDEX \`${index}\``);
                });
            });
        }

        return `ALTER TABLE \`${tableName}\` ${sqlParts.join(', ')};`;
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
    .hidden {
        display: none;
    }
    #columnsHeader {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px; /* Adjust the gap as needed */
        margin-top: 20px;
    }
</style>
<!--<script type="text/javascript">-->
<!--    var tabulator;-->
<!--    var tableName;-->
<!--    var originalColumnNames = [];-->
<!--    var newColumns = [];-->
<!--    var droppedColumns = [];-->
<!--    var modifiedColumns = [];-->
<!--    var tableData;-->
<!--    var rowStates = [];-->
<!---->
<!--    document.addEventListener("DOMContentLoaded", function () {-->
<!--        const dropdown = document.getElementById('tableChoiceDropdown');-->
<!--        dropdown.addEventListener('change', handleTableSelection);-->
<!--    });-->
<!---->
<!--    async function handleTableSelection() {-->
<!--        const dropdown = document.getElementById('tableChoiceDropdown');-->
<!--        tableName = dropdown.options[dropdown.selectedIndex].text;-->
<!--        const colHeader = document.getElementById('columnsHeader');-->
<!---->
<!--        if (tableName === 'Select table...') {-->
<!--            colHeader.style.display = 'none';-->
<!--            if (tabulator) {-->
<!--                tabulator.destroy();-->
<!--                tabulator = null;-->
<!--            }-->
<!--        } else {-->
<!--            colHeader.style.display = 'block';-->

<!--//            const selectedTableInfo = columnInfo.find(t => t.table === tableName);-->
<!--//-->
<!--//            if (selectedTableInfo) {-->
<!--//                const selectedTableColumns = selectedTableInfo.columns;-->
<!--//                tableData = selectedTableColumns.map(col => ({-->
<!--//                    colname: col.Field,-->
<!--//                    type: col.Extra.toLowerCase().includes('auto_increment') ? 'autoincrement' : col.Type,-->
<!--//                    nullable: col.Null === 'YES',-->
<!--//                    default: col.Default || '',-->
<!--//                    primary: col.Key === 'PRI',-->
<!--//                    unique: col.Key === 'UNI',-->
<!--//                    autoIncrement: col.Extra.toLowerCase().includes('auto_increment')-->
<!--//                }));-->
<!--//-->
<!--//                if (tabulator) {-->
<!--//-->
<!--//                } else {-->
<!--//                    initializeTabulator(tableData);-->
<!--//                }-->
<!--//            }-->
<!--//        }-->
<!--//    }-->
<!--//-->
<!--//    function generateUniqueId() {-->
<!--//        return 'id-' + Math.random().toString(36).substr(2, 16);-->
<!--//    }-->
<!--//-->
<!--//    function initializeTabulator(tableData) {-->
<!--//        rowStates = tableData.map(row => ({-->
<!--//            uniqueId: row.uniqueId,-->
<!--//            isNew: false // Initially, all rows are existing rows-->
<!--//        }));-->
<!--//        tabulator = new Tabulator("#datatable", {-->
<!--//            layout: "fitColumns",-->
<!--//            tabEndNewRow: { nullable: true, unique: false },-->
<!--//            columns: [-->
<!--//                {title: "Unique ID", field: "uniqueId", visible: false}, // Hidden unique identifier column-->
<!--//                {-->
<!--//                    title: "Field Name", field: "colname", editor: "input", cellEdited: function (cell) {-->
<!--//                        handleCellEdit(cell);-->
<!--//                    }-->
<!--//                },-->
<!--//                {-->
<!--//                    title: "Data Type", field: "type", editor: "list", editorParams: function (cell) {-->
<!--//                        return {-->
<!--//                            values: {-->
<!--//                                "autoincrement": "Autoincrement",-->
<!--//                                "varchar": "Varchar",-->
<!--//                                "varchar(10)": "Varchar(10)",-->
<!--//                                "varchar(15)": "Varchar(15)",-->
<!--//                                "varchar(25)": "Varchar(25)",-->
<!--//                                "varchar(32)": "Varchar(32)",-->
<!--//                                "varchar(50)": "Varchar(50)",-->
<!--//                                "varchar(75)": "Varchar(75)",-->
<!--//                                "varchar(100)": "Varchar(100)",-->
<!--//                                "varchar(255)": "Varchar(255)",-->
<!--//                                "text": "Text",-->
<!--//                                "int": "Int",-->
<!--//                                "int(11)": "Int(11)",-->
<!--//                                "tinyint": "Tinyint",-->
<!--//                                "bigint": "Bigint",-->
<!--//                                "decimal": "Decimal",-->
<!--//                                "float": "Float",-->
<!--//                                "double": "Double",-->
<!--//                                "boolean": "Boolean",-->
<!--//                                "date": "Date",-->
<!--//                                "datetime": "Datetime",-->
<!--//                                "time": "Time",-->
<!--//                                "timestamp": "Timestamp",-->
<!--//                                "char": "Char",-->
<!--//                                "binary": "Binary",-->
<!--//                                "varbinary": "Varbinary",-->
<!--//                                "blob": "Blob",-->
<!--//                                "uuid": "Uuid"-->
<!--//                            },-->
<!--//                            showListOnEmpty: true-->
<!--//                        };-->
<!--//                    },-->
<!--//                    cellEdited: function (cell) {-->
<!--//                        handleCellEdit(cell);-->
<!--//                    }-->
<!--//                },-->
<!--//                {-->
<!--//                    title: "Nullable", field: "nullable", editor: "tickCross", hozAlign: "center", vertAlign: "middle", formatter: "tickCross", width: 100, cellEdited: function (cell) {-->
<!--//                        handleCellEdit(cell);-->
<!--//                    }-->
<!--//                },-->
<!--//                { title: "Default Value", field: "default", editor: "input", cellEdited: function (cell) {-->
<!--//                        handleCellEdit(cell);-->
<!--//                    } },-->
<!--//                {-->
<!--//                    title: "Primary Key", field: "primary", vertAlign: "middle", hozAlign: "center", editor: "tickCross", formatter: "tickCross", width: 120-->
<!--//                },-->
<!--//                {-->
<!--//                    title: "Unique", field: "unique", vertAlign: "middle", hozAlign: "center", editor: "tickCross", formatter: "tickCross", width: 100,cellEdited: function (cell) {-->
<!--//                        handleCellEdit(cell);-->
<!--//                    }-->
<!--//                },-->
<!--//                {-->
<!--//                    title: '', formatter: function (cell) {-->
<!--//                        const span = document.createElement("span");-->
<!--//                        span.className = "tabulator-button tabulator-button-cross custom-button-cross";-->
<!--//                        span.innerHTML = "&times;";-->
<!--//                        return span;-->
<!--//                    },-->
<!--//                    width: 40, hozAlign: "center", vertAlign: "middle", cellClick: function (e, cell) {-->
<!--//                        cell.getRow().delete();-->
<!--//                    }-->
<!--//                }-->
<!--//            ],-->
<!--//            data: tableData-->
<!--//        });-->
<!--//-->
<!--//        tabulator.on("rowDeleted", function (row) {-->
<!--//            handleRowDeletion(row);-->
<!--//        });-->
<!--//-->
<!--//        tabulator.on("rowUpdated", function (row) {-->
<!--//            handleRowUpdate(row);-->
<!--//        });-->
<!--//        tabulator.on("tableBuilt", function () {-->
<!--//            handleTableBuilt();-->
<!--//        });-->
<!--//        tabulator.on("rowAdded", function (row) {-->
<!--//            handleRowAdded();-->
<!--//        })-->
<!--//    }-->
<!--//-->
<!--//    function handleCellEdit(cell) {-->
<!--//        const row = cell.getRow();-->
<!--//        const data = row.getData();-->
<!--//        const uniqueId = data.uniqueId;-->
<!--//        const columnName = data.colname;-->
<!--//        const oldValue = cell.getOldValue();-->
<!--//        const newValue = cell.getValue();-->
<!--//        const editedField = cell.getField();-->
<!--//-->
<!--//        const rowState = rowStates.find(state => state.uniqueId === uniqueId);-->
<!--//-->
<!--//        if (!rowState) {-->
<!--//            console.error("Row state not found for uniqueId:", uniqueId);-->
<!--//            return;-->
<!--//        }-->
<!--//-->
<!--//        if (rowState.isNew) {-->
<!--//            // Handle new column-->
<!--//            let existingNewColumn = newColumns.find(col => col.uniqueId === uniqueId);-->
<!--//            if (!existingNewColumn) {-->
<!--//                existingNewColumn = {-->
<!--//                    uniqueId: uniqueId,-->
<!--//                    name: columnName-->
<!--//                };-->
<!--//                newColumns.push(existingNewColumn);-->
<!--//            }-->
<!--//            existingNewColumn[editedField] = newValue;-->
<!--//        } else {-->
<!--//            // Handle existing column-->
<!--//            if (editedField === 'colname' && oldValue !== newValue) {-->
<!--//                modifiedColumns.push({-->
<!--//                    type: 'rename',-->
<!--//                    oldName: oldValue,-->
<!--//                    newName: newValue-->
<!--//                });-->
<!--//            } else {-->
<!--//                let existingModification = modifiedColumns.find(col => col.colname === columnName);-->
<!--//                if (!existingModification) {-->
<!--//                    existingModification = {-->
<!--//                        type: 'modify',-->
<!--//                        colname: columnName-->
<!--//                    };-->
<!--//                    modifiedColumns.push(existingModification);-->
<!--//                }-->
<!--//                existingModification[editedField] = newValue;-->
<!--//            }-->
<!--//        }-->
<!--//-->
<!--//        console.log('newColumns', newColumns);-->
<!--//        console.log('modifiedColumns', modifiedColumns);-->
<!--//-->
<!--//        // Additional logic based on cell edits if needed-->
<!--//        switch (newValue) {-->
<!--//            case "autoincrement":-->
<!--//                cell.getRow().update({ nullable: false, default: null });-->
<!--//                break;-->
<!--//            case "timestamp":-->
<!--//                cell.getRow().update({ nullable: false, default: 'CURRENT_TIMESTAMP' });-->
<!--//                break;-->
<!--//            case "uuid":-->
<!--//                cell.getRow().update({ nullable: false, default: 'UUID()' });-->
<!--//                break;-->
<!--//            default:-->
<!--//                // Default case if needed-->
<!--//                break;-->
<!--//        }-->
<!--//        generateSql();-->
<!--//    }-->
<!--//-->
<!--//    function handleRowDeletion(row) {-->
<!--//        const data = row.getData();-->
<!--//        droppedColumns.push(data.colname);-->
<!--//        // You can also add more sophisticated logic to handle dropped columns-->
<!--//        // and generate SQL statements for dropping columns.-->
<!--//    }-->
<!--//-->
<!--//    function handleRowUpdate(row) {-->
<!--//        // Handle row update logic here-->
<!--//        // You can use modifiedColumns array to track modified columns-->
<!--//        console.log("Row updated:", row.getData());-->
<!--//    }-->
<!--//-->
<!--//    function handleTableBuilt() {-->
<!--//        // Add your logic to handle table built-->
<!--//        tabulator.setData(tableData);-->
<!--//-->
<!--//        // You can also use modifiedColumns array to track modified columns-->
<!--//        console.log("Table built:", modifiedColumns);-->
<!--//    }-->
<!--//-->
<!--//    function handleRowAdded(row){-->
<!--//        const uniqueId = generateUniqueId();-->
<!--//        tabulator.addRow({uniqueId: uniqueId});-->
<!--//-->
<!--//-->
<!--//        // Store the new row state-->
<!--//        rowStates.push({-->
<!--//            uniqueId: uniqueId,-->
<!--//            isNew: true // Mark this row as new-->
<!--//        });-->
<!--//-->
<!--//        // // Only store the column name in newColumns-->
<!--//        // const data = row.getData();-->
<!--//        // newColumns.push({-->
<!--//        //     uniqueId: uniqueId,-->
<!--//        //     name: data.colname-->
<!--//        // });-->
<!--//-->
<!--//        //console.log('Row added:', data);-->
<!--//    }-->
<!--//-->
<!--//    async function generateSql() {-->
<!--//        var sql = `ALTER TABLE ${tableName} `;-->
<!--//-->
<!--//        if (droppedColumns.length > 0) {-->
<!--//            sql += droppedColumns.map(col => `DROP COLUMN ${col}`).join(', ');-->
<!--//        }-->
<!--//-->
<!--//        if (newColumns.length > 0) {-->
<!--//            if (sql.slice(-1) !== ' ') sql += ', ';-->
<!--//            sql += newColumns.map(newCol => {-->
<!--//                const row = tableData.find(row => row.uniqueId === newCol.uniqueId);-->
<!--//                if (!row) {-->
<!--//                    console.error(`Row data not found for uniqueId: ${newCol.uniqueId}`);-->
<!--//                    return '';-->
<!--//                }-->
<!--//-->
<!--//                let columnDef = `ADD COLUMN ${newCol.name} `;-->
<!--//                switch (row.type) {-->
<!--//                    case 'autoincrement':-->
<!--//                        columnDef += 'int(11)';-->
<!--//                        break;-->
<!--//                    default:-->
<!--//                        columnDef += row.type;-->
<!--//                        break;-->
<!--//                }-->
<!--//                if (row.nullable === false) {-->
<!--//                    columnDef += ' NOT NULL';-->
<!--//                }-->
<!--//                if (row.default) {-->
<!--//                    switch (row.default) {-->
<!--//                        case 'CURRENT_TIMESTAMP':-->
<!--//                            columnDef += ' DEFAULT CURRENT_TIMESTAMP';-->
<!--//                            break;-->
<!--//                        case 'CURRENT_DATE':-->
<!--//                            columnDef += ' DEFAULT CURRENT_DATE';-->
<!--//                            break;-->
<!--//                        case 'CURRENT_TIME':-->
<!--//                            columnDef += ' DEFAULT CURRENT_TIME';-->
<!--//                            break;-->
<!--//                        case 'UTC_TIMESTAMP':-->
<!--//                            columnDef += ' DEFAULT UTC_TIMESTAMP';-->
<!--//                            break;-->
<!--//                        case 'UNIX_TIMESTAMP':-->
<!--//                            columnDef += ' DEFAULT UNIX_TIMESTAMP';-->
<!--//                            break;-->
<!--//                        case 'UUID()':-->
<!--//                            columnDef += ' DEFAULT UUID()';-->
<!--//                            break;-->
<!--//                        default:-->
<!--//                            columnDef += ` DEFAULT '${row.default}'`;-->
<!--//                    }-->
<!--//                }-->
<!--//                if (row.type === 'autoincrement') {-->
<!--//                    columnDef += ' AUTO_INCREMENT';-->
<!--//                }-->
<!--//                if (row.unique) {-->
<!--//                    columnDef += ' UNIQUE';-->
<!--//                }-->
<!--//                return columnDef;-->
<!--//            }).filter(Boolean).join(', ');-->
<!--//        }-->
<!--//-->
<!--//        if (modifiedColumns.length > 0) {-->
<!--//            if (sql.slice(-1) !== ' ') sql += ', ';-->
<!--//            sql += modifiedColumns.map(col => {-->
<!--//                if (col.type === 'rename') {-->
<!--//                    return `RENAME COLUMN ${col.oldName} TO ${col.newName}`;-->
<!--//                } else if (col.type === 'modify') {-->
<!--//                    let modifyDef = `MODIFY COLUMN ${col.colname} ${col.newType || col.type}`;-->
<!--//                    if (col.nullable !== undefined) {-->
<!--//                        modifyDef += col.nullable ? ' NULL' : ' NOT NULL';-->
<!--//                    }-->
<!--//                    if (col.default !== undefined) {-->
<!--//                        switch (col.default) {-->
<!--//                            case 'CURRENT_TIMESTAMP':-->
<!--//                                modifyDef += ' DEFAULT CURRENT_TIMESTAMP';-->
<!--//                                break;-->
<!--//                            case 'CURRENT_DATE':-->
<!--//                                modifyDef += ' DEFAULT CURRENT_DATE';-->
<!--//                                break;-->
<!--//                            case 'CURRENT_TIME':-->
<!--//                                modifyDef += ' DEFAULT CURRENT_TIME';-->
<!--//                                break;-->
<!--//                            case 'UTC_TIMESTAMP':-->
<!--//                                modifyDef += ' DEFAULT UTC_TIMESTAMP';-->
<!--//                                break;-->
<!--//                            case 'UNIX_TIMESTAMP':-->
<!--//                                modifyDef += ' DEFAULT UNIX_TIMESTAMP';-->
<!--//                                break;-->
<!--//                            case 'UUID()':-->
<!--//                                modifyDef += ' DEFAULT UUID()';-->
<!--//                                break;-->
<!--//                            default:-->
<!--//                                modifyDef += ` DEFAULT '${col.default}'`;-->
<!--//                        }-->
<!--//                    }-->
<!--//                    if (col.unique !== undefined) {-->
<!--//                        modifyDef += col.unique ? ' UNIQUE' : '';-->
<!--//                    }-->
<!--//                    return modifyDef;-->
<!--//                }-->
<!--//            }).join(', ');-->
<!--//        }-->
<!--//-->
<!--//        sql += ';';-->
<!--//-->
<!--//        document.getElementById('sqlCode').innerHTML = sql;-->
<!--//        Prism.highlightAll();-->
<!--//        console.log('sql:', sql);-->
<!--//    }-->
<!--//</script>-->