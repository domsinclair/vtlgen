<!--This view makes use of the code-input plugin, an absolutely fabulous creation by Oliver Greer-->
<?php //echo json($data);?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="color-scheme" content="dark light">

    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator_midnight.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/prismEditor.css">

    <script src="<?= BASE_URL ?>vtlgen_module/js/prismEditor.js"></script>
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/code-input.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/code-input.css">
    <script src="<?= BASE_URL ?>vtlgen_module/js/auto-close-brackets.js"></script>
    <script src="<?= BASE_URL ?>vtlgen_module/js/indent.js"></script>
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/tabulator.js"></script>

    <script>

        codeInput.registerTemplate("syntax-highlighted", codeInput.templates.prism(Prism, [
            new codeInput.plugins.AutoCloseBrackets(),
            new codeInput.plugins.Indent(true, 2) // 2 spaces indentation
        ] ));
    </script>
    <title>SQL Query Builder</title>

</head>
<body data-base-url="<?= BASE_URL ?>">
<h2 class="text-center"><?= $headline ?></h2>
<section class="container">
    <div>
        <?= anchor('vtlgen', 'Back', ['class' => 'button']) ?>
    </div>
    <h3><?= DATABASE ?> Database Relationships</h3>

    <!-- Related Tables Selection -->
    <div id="relatedTableInfo">
        <div id="datatable" class="tabulator-container"></div>
    </div>
    <div>
        <p><?= $instruction1 ?></p>
        <p><?= $instruction2 ?></p>
    </div>

    <!-- Dropdowns and Field Data Grids -->
    <!-- New Grid Layout -->
    <div class="grid" id="queryGrid">
        <!-- First Row: Table Dropdowns -->
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="grid-item" id="tableDropdownContainer<?= $i ?>">
                <?php if ($i > 1): ?><div class="hidden"><?php endif; ?>
                    <label for="tableDropdown<?= $i ?>">Select Table <?= $i ?>:</label>
                    <?php
                        $tableDropdownAttr['id'] = 'tableDropdown' . $i;
                        $tableDropdownAttr['onchange'] = 'selectTable(this.id, ' . $i . ')';

                        if ($i === 1) {
                            // For the first dropdown, use the $tables array as options
                            echo form_dropdown('tableDropdown' . $i, $tables, '', $tableDropdownAttr);
                        } else {
                            // For subsequent dropdowns, leave them empty initially
                            echo form_dropdown('tableDropdown' . $i, [], '', $tableDropdownAttr);
                        }
                    ?>
                    <?php if ($i > 1): ?></div><?php endif; ?>
            </div>
        <?php endfor; ?>

        <!-- Second Row: Tabulator Grids for Fields -->
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="grid-item tabulator-container hidden" id="fieldGridContainer<?= $i ?>">
                <h4>Fields (Table <?= $i ?>)</h4>
                <div id="datatable<?= $i ?>"></div>
            </div>
        <?php endfor; ?>

        <!-- Third Row: Join Type Dropdowns -->
        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="grid-item" id="joinTypeContainer<?= $i ?>">
                <label for="joinTypeDropdown<?= $i ?>">Select Join Type <?= $i ?>-<?= $i + 1 ?>:</label>
                <?php
                    $joinTypes = [
                        'INNER JOIN' => 'INNER JOIN',
                        'LEFT JOIN' => 'LEFT JOIN',
                        'RIGHT JOIN' => 'RIGHT JOIN',
                        'FULL JOIN' => 'FULL JOIN',
                        'CROSS JOIN' => 'CROSS JOIN',
                        'SELF JOIN' => 'SELF JOIN'
                    ];
                    $joinTypeDropdownAttr['id'] = 'joinTypeDropdown' . $i;
                    echo form_dropdown('joinTypeDropdown' . $i, $joinTypes, '', $joinTypeDropdownAttr);
                ?>
            </div>
        <?php endfor; ?>
    </div>

    <!-- SQL Editor -->
    <div id="sqlEditor">
        <code-input id="code" template="syntax-highlighted" language="sql"></code-input>
    </div>
    <div>
        <button onclick="createSql()">Create SQL</button>
        <button onclick="runSql()">Run SQL</button>
        <button onclick="saveSql()">Save SQL</button>
    </div>
</section>
<section>
    <div class="container">
        <div id="datatable5"></div>
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
<script>
    // Pass related data table info back to the global window object for access in sqlQueryBuilder.js
    window.relatedTableData = <?php echo json_encode($relatedTables, JSON_HEX_TAG); ?>;
    window.noDataMessage = "<?= $noDataMessage ?>";
    // Pass column info to the global window object for access in sqlQueryBuilder.js
    window.columnInfo = <?php echo json_encode($columnInfo); ?>;
</script>
<script src="<?= BASE_URL ?>vtlgen_module/js/sqlQueryBuilder.js"></script>
</body>
</html>
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
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .container {
        width: 100%;
        margin: 0 auto;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: auto auto;
        grid-gap: 10px;
        margin-top: 20px;
    }

    .grid-item {
        padding: 10px;
        text-align: center;
    }

    .tabulator-container, .dropdown-wrapper {
        width: 100%;
    }

    #sqlEditor {
        width: 100%;
        margin-top: 20px;
    }

    code-input {
        width: calc(100% - 40px); /* 100% - 2*margin */
        margin: 20px;
        --padding: 20px;
    }

    button {
        margin-right: 10px;
        padding: 10px 20px;
    }

    .tabulator {
        width: 100%;
    }
    .tabulator-row {
        height: auto; /* Allow row height to auto-adjust */
    }

    /* Normalize Tabulator styles */
    .tabulator .tabulator-row {
        height: auto; /* Adjust for consistent height */
    }

    /* Additional CSS adjustments */
    .tabulator .tabulator-cell {
        padding: 5px; /* Adjust padding for consistency */
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
    #datatable3 input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static; /* Change to relative positioning */
        width: auto;
        height: auto;
    }
    #datatable4 input[type="checkbox"] {
        margin: 5px;
        top: 0;
        position: static; /* Change to relative positioning */
        width: auto;
        height: auto;
    }
    /* Ensure the container for field grids handles overflow */
    .fieldGridContainer {
        width: 100%;
        height: 300px; /* Adjust as necessary */
        overflow-x: auto;
        overflow-y: auto;
    }

    #fieldGridContainer1,
    #fieldGridContainer2,
    #fieldGridContainer3,
    #fieldGridContainer4{
        /* Avoid collapsing, ensure layout integrity */
        border: 1px solid #ddd;
        margin-top: 10px;
    }
</style>