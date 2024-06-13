<?php // echo json($data);?>
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
    </div>
</section>
</body>
</html>
<script type="text/javascript">
    var tabulator;

    async function selectedTable() {
        var dropdown = document.getElementById('tableChoiceDropdown');
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;
        var columnInfo = <?php echo json_encode($columnInfo); ?>;
        var selectedTableColumns = columnInfo.find(table => table.table === selectedTable).columns;

        var tableData = selectedTableColumns.map(col => ({
            colname: col.Field,
            type: col.Extra.toLowerCase().includes('auto_increment') ? 'auto_increment' : col.Type,
            nullable: col.Null === 'YES' ? true : false,
            default: col.Default || '',
            primary: col.Key === 'PRI' ? true : false,
            unique: col.Key === 'UNI' ? true : false,
            autoIncrement: col.Extra.toLowerCase().includes('auto_increment')
        }));

        // Initialize Tabulator with column data
        if (tabulator) {
            tabulator.setData(tableData);
        } else {
            tabulator = new Tabulator("#datatable", {
                layout: "fitColumns",
                tabEndNewRow: { nullable: true, unique: false },
                columns: [
                    { title: "Field Name", field: "colname", editor: "input" },
                    {
                        title: "Data Type", field: "type", editor: "select", editorParams: function (cell) {
                            var values = {
                                "varchar": "Varchar",
                                "int": "Int",
                                "text": "Text",
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
                                "uuid": "Uuid",
                                "auto_increment": "Auto Increment"  // Add auto_increment option
                            };
                            return {
                                values: values,
                                showListOnEmpty: true
                            };
                        },
                        cellEdited: function (cell) {
                            var value = cell.getValue();
                            switch (value) {
                                case "auto_increment":
                                    cell.getRow().update({ nullable: false, default: null });
                                    break;
                                case "timestamp":
                                    cell.getRow().update({ nullable: false });
                                    cell.getRow().update({ default: 'CURRENT_TIMESTAMP' });
                                    break;
                                case "uuid":
                                    cell.getRow().update({ nullable: false });
                                    cell.getRow().update({ default: 'UUID()' });
                                    break;
                                // Add more cases here if needed
                                default:
                                    // Default case if needed
                                    break;
                            }
                        }
                    },
                    {
                        title: "Nullable",
                        field: "nullable",
                        editor: "tickCross",
                        hozAlign: "center",
                        vertAlign: "middle",
                        formatter: "tickCross",
                        width: 100
                    },
                    { title: "Default Value", field: "default", editor: "input" },
                    {
                        title: "Primary Key",
                        field: "primary",
                        vertAlign: "middle",
                        hozAlign: "center",
                        editor: "tickCross",
                        formatter: "tickCross",
                        width: 120
                    },
                    {
                        title: "Unique",
                        field: "unique",
                        vertAlign: "middle",
                        hozAlign: "center",
                        editor: "tickCross",
                        formatter: "tickCross",
                        width: 100
                    },
                    {
                        title: '',
                        formatter: function (cell, formatterParams, onRendered) {
                            var span = document.createElement("span");
                            span.className = "tabulator-button tabulator-button-cross custom-button-cross"; // Apply custom class
                            span.innerHTML = "&times;";
                            return span;
                        },
                        width: 40,
                        hozAlign: "center",
                        vertAlign: "middle",
                        cellClick: function (e, cell) {
                            cell.getRow().delete();
                        }
                    }
                ],
                data: tableData
            });
        }
    }




    document.getElementById("add-column-btn").addEventListener("click", function () {
        tabulator.addRow({ colname: "", type: "", nullable: true, default: "", primary: false, unique: false });
    });

    document.getElementById("save-btn").addEventListener("click", function () {
        var dropdown = document.getElementById('tableChoiceDropdown');
        var tableName = dropdown.options[dropdown.selectedIndex].text;
        var columnData = tabulator.getData();

        fetch("<?= BASE_URL ?>vtlgen/update_table", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ table: tableName, columns: columnData })
        })
            .then(response => response.json())
            .then(data => {
                alert("Table updated successfully!");
            })
            .catch(error => {
                console.error("Error updating table:", error);
            });
    });
</script>
