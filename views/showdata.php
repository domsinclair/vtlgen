<?php //echo json($data); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator_midnight.css">
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/tabulator.js"
    "></script>
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/luxon.min.js"
    "></script>
    <title>Vtl_Generator_ShowData</title>
    <style>
        #svgButtons {
            display: none;
        }
    </style>
</head>
<body>
<h2 class="container, text-center"><?= $headline ?></h2>
<section>
    <div class="container">
        <div class="flex" style="margin-bottom: 15px">
            <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        </div>
        <div id="datatable"></div
    </div>

</section>
<section class="container" id="svgButtons">
<div class="icon-container">
    <button class="svg-button" aria-label="Download HTML" onclick="downloadHtml()">
        <picture>
            <source srcset="vtlgen_module/help/images/vtlExportHtmlDark.svg" media="(prefers-color-scheme: dark)">
            <img class="svg-icon" src="vtlgen_module/help/images/vtlExportHtml.svg" alt="Customisation Icon">
        </picture>
        <div class="popup popupRight">Download HTML</div>
    </button>
    <button class="svg-button" aria-label="Download CSV" onclick="downloadCSV()">
        <picture>
            <source srcset="vtlgen_module/help/images/vtlExportCsvDark.svg" media="(prefers-color-scheme: dark)">
            <img class="svg-icon" src="vtlgen_module/help/images/vtlExportCsv.svg" alt="Download CSV Icon">
        </picture>
        <div class="popup popupLeft">Download CSV</div>
    </button>
    <button class="svg-button" aria-label="Download JSON" onclick="downloadJSON()">
        <picture>
            <source srcset="vtlgen_module/help/images/vtlExportJsonDark.svg" media="(prefers-color-scheme: dark)">
            <img class="svg-icon" src="vtlgen_module/help/images/vtlExportJson.svg" alt="Download JSON Icon">
        </picture>
        <div class="popup popupLeft">Download JSON</div>
    </button>

</div>
</section>
<script src="<?= BASE_URL ?>vtlgen_module/js/jspdf.plugin.autotable.min.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/jspdf.umd.min.js"></script>
<script src="<?= BASE_URL ?>vtlgen_module/js/xlxs.full.min.js"></script>
</body>
</html>
<script>

    var tableData = <?php echo json_encode($rows); ?>;
    var noDataMessage = "<?= $noDataMessage ?>";

    document.addEventListener('DOMContentLoaded', function () {
        // Date formatter function
        function dateFormatter(cell, formatterParams, onRendered) {
            return cell.getValue(); // Return the value without any formatting
        }


        // Function to determine if a string is a valid ISO date or datetime
        function isISODateString(value) {
            var dateTime = luxon.DateTime.fromISO(value);
            return dateTime.isValid;
        }

        // Generate columns dynamically based on the first row of data
        var columns = Object.keys(tableData[0] || {}).map(field => {
            return {
                title: field.charAt(0).toUpperCase() + field.slice(1),
                field: field,
                sorter: isISODateString(tableData[0][field]) ? "date" : "string",
                formatter: isISODateString(tableData[0][field]) ? dateFormatter : undefined
            };
        });

        // Create Tabulator table
        var table = new Tabulator("#datatable", {
            data: tableData,
            columns: columns,
            layout: "fitColumns",
            responsiveLayout: "hide",
            pagination: true,
            paginationSize: 20,
            // paginationSizeSelector: [10, 20, 30, 40],
            movableColumns: true,
            paginationCounter: "pages",
            autoColumns: false, // We handle columns manually
            placeholder: noDataMessage

        });
        table.on("renderComplete", function(){
            document.getElementById('svgButtons').style.display = 'block';
        });

    });
    async function downloadHtml(){
        var table = Tabulator.findTable("#datatable")[0];
        table.download("html", "data.html", {style:true});
    }
    async function downloadCSV(){
        var table = Tabulator.findTable("#datatable")[0];
        table.download("csv", "data.csv");
    }
    async function downloadJSON(){
        var table = Tabulator.findTable("#datatable")[0];
        table.download("json", "data.json");
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
        button:hover {
            background-color: transparent;
            border:none;
        }
        .svg-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            position: relative;
        }

        .popup {
            display: none;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px;
            position: absolute;
            z-index: 2;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

    }

    @media (prefers-color-scheme: dark) {
        button:hover {
            background-color: transparent;
            border: none;
        }

        .svg-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            position: relative;
        }
        .popup {
            display: none;
            color: white;
            background-color: white;
            border: 1px solid #dadada;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            position: absolute;
            z-index: 2;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }
    }

    @media(hover: hover) and (pointer: fine) {
        .tabulator .tabulator-header .tabulator-col.tabulator-sortable.tabulator-col-sorter-element:hover {
            background-color: var(--primary-dark);

        }
    }
    .tabulator .tabulator-footer .tabulator-page{
        background-color: var(--primary-dark);
        color: #050000;
    }
    .tabulator-col.tabulator-sortable.tabulator-col-sorter-element{
        background-color: var(--primary-dark);
    }
    div.tabulator-footer-contents{
        background-color: var(--primary-dark);
    }
    #datatable{
        margin-top: 20px;
    }
    .svg-icon {
        width: 50px;
        height: 50px;
    }
    .icon-container {
        display: flex;
        justify-content: space-around;
        align-items: center;
        margin: 20px 0;
    }

    .icon-button {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .svg-button:focus .popup,
    .svg-button:hover .popup {
        display: block;
        background-color: transparent; /*rgba(255, 255, 255, 0.8); /* Transparent background on hover */
    }

    .popupLeft {
        top: 50%; /* Vertically centers the popup relative to the button */
        right: 100%; /* Positions the popup to the left of the button */
        transform: translateY(-50%); /* Centers the popup vertically */
        margin-right: 5px;
    }

    .popupRight {
        top: 50%; /* Vertically centers the popup relative to the button */
        left: 100%; /* Positions the popup to the right of the button */
        transform: translateY(-50%); /* Centers the popup vertically */
        margin-left: 5px;
    }


</style>
