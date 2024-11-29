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
    <div class="container">
        <label><input type="checkbox" id="addMultiFileUploaderCheckbox" name="multiFileUploader" > Add Multi File Uploader</label>
    </div>
</section>
<section>
    <div class="container" id="createModuleDiv" style="display: none" >
        <button  onclick='createModules()' style="margin-bottom: 15px;">Create Module</button>
    </div>
</section>
<section>
    <div class="container" id="progressContainer" style="display: none">
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
<script id="data-script" type="application/json">
    {
        "noDataMessage": "<?= $noDataMessage ?>",
        "tableData": <?= json_encode($data['tables']) ?>
    }
</script>
<script type="text/javascript">
    window.baseUrl = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/vtlQuestionModal.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/createModule.js"></script>
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

    input[type="checkbox"] {
        margin: 6px;
        align-self: inherit;
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
