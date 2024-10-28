<?php //echo json($data);?>
<?php $picDirectoryExists = false; ?>
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
        <div id="task" style="display: none"><?= $task ?></div>
        <div id="columnInfoTableContainer">
            <div class="flex-container">
                <div id="numRowsContainer" style="display: none;">
                    <label for="numRows">Enter the number of rows of data you wish to generate:</label>
                    <input type="number" id="numRows" name="numRows" min="1" value="1">
                </div>
                <button id="submitBtn" onclick='generateData()' style="display: none; margin-bottom: 15px;">Generate Fake Data</button>
                <button id="movePicsBtn" onclick="movePictures()" style="display: none; margin-bottom: 15px;">Transfer Images</button>

            </div>
        </div>
        <div class="progress-bar" id="progress-bar" style="display: none" >
            <div class="progress" id="progress" ></div>
        </div>
        <!--        index related stuff-->
        <div class="flex-container">
            <div id="indexTypeDropdown" style="display: none;">
                <label for="indexType">Select Index Type:</label>
                <select id="indexType" name="indexType">
                    <option value="Standard" selected>Standard</option>
                    <option value="Unique">Unique</option>
                </select>
            </div>
            <button id="generateIndexButton" onclick="generateIndex()" style="display: none;">Create Index</button>
        </div>



    </div>
</section>
<section>
    <div>
        <input type="hidden" id="picDirectoryExists" value="<?php echo $picDirectoryExists ? 'true' : 'false'; ?>">
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
<script type="text/javascript">
    window.picDirectoryExists = <?= json_encode($picDirectoryExists) ?>;
    window.baseUrl = "<?= BASE_URL ?>";
    window.task = "<?= $task ?>";
    // Properly formatted columnInfo
    window.columnInfo = <?= json_encode($columnInfo) ?>;
    window.relatedTables = <?= json_encode($relatedTables) ?>;
</script>
<script src="<?= BASE_URL ?>vtlgen_module/js/createDataAndIndex.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
</body>
</html>
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
