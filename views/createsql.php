<!--This view makes use of the code-input plugin, an absolutely fabulous creation by Oliver Greer-->

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
    <title>Vtl_Data_Generator</title>
</head>
<body>
<h2 class="text-center"><?= $headline ?></h2>
<section>
    <div class="container">
        <div class="flex" style="margin-bottom: 15px">
            <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        </div>
        <p><?= $instruction1 ?> </p>
        <p><?= $instruction2 ?> </p>
    </div>
</section>
<section>
    <div class="container">
        <div id="datatable"></div>
    </div>
</section>
<section>
   <div class="container">
   <code-input id="code" template="syntax-highlighted" language ="sql"></code-input>
   </div>
</section>
<div class="container">
    <div class="flex">
        <button id="runSql" onclick="runSql()">Run SQL</button>
        <button id="save-sql" onclick="saveSql()">Save SQL</button>
        <button id="clearSql" onclick="clearSql()">Clear SQL</button>
        <!--            <button id="create-module">Create Module</button>-->
    </div>
</div>
<section>
    <div class="container">
        <div id="datatable2"></div>
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
<script defer src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
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

                { title: "Table Name", field: "table", sorter: "string" }
            ],
        });
    });

    function runSql() {
        var codeInput = document.getElementById('code').value;
        var targetUrl = '<?= BASE_URL ?>vtlgen/createsqlGetDataFromSuppliedSql';

        // Create a new XMLHttpRequest object
        var xhr = new XMLHttpRequest();

        // Configure it: POST-request for the URL
        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Send the request over the network
        xhr.send(JSON.stringify({ sql: codeInput }));

        // This will be called after the response is received
        xhr.onload = function() {
            if (xhr.status != 200) { // analyze HTTP response status
                console.error(`Error ${xhr.status}: ${xhr.statusText}`); // e.g. 404: Not Found
            } else {
                var responseData = JSON.parse(xhr.responseText); // parse the response data

                // Create Tabulator only if we received valid data
                if (responseData && responseData.data) {
                    // Destroy the existing Tabulator instance if it exists
                    if (window.table2) {
                        window.table2.destroy();
                    }
                    // Create a new Tabulator instance and store it in window.table2
                    window.table2 = new Tabulator("#datatable2", {
                        data: responseData.data,
                        layout: "fitColumns",
                        pagination: true,
                        paginationSize: 20,
                        paginationCounter: "pages",
                        columns: Object.keys(responseData.data[0]).map(function(key) {
                            return { title: key, field: key, sorter: "string" };
                        }),
                    });
                }
            }
        };

        xhr.onerror = function() {
            console.error("Request failed");
        };

    }


     function clearSql() {
         // Clear the code-input element
         document.getElementById('code').value = '';

         // Destroy the existing Tabulator instance if it exists
         if (window.table2) {
             window.table2.destroy(); // Destroy the instance
             window.table2 = null; // Clear the reference
         }

         // Clear the content of the datatable2 element
         var datatable2Element = document.getElementById('datatable2');
         datatable2Element.innerHTML = '';
    }

    function saveSql() {
        // Prompt the user to enter a name for the SQL query
        var queryName = prompt("Enter a name for your SQL query:");

        // If the user provided a name, proceed to save the query
        if (queryName) {
            var codeInput = document.getElementById('code').value;
            var targetUrl = '<?= BASE_URL ?>vtlgen/createsqlSaveEndUserCreatedQuery';

            // Create a new XMLHttpRequest object
            var xhr = new XMLHttpRequest();

            // Configure it: POST-request for the URL
            xhr.open('POST', targetUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/json');

            // Send the request over the network
            xhr.send(JSON.stringify({name: queryName, sql: codeInput}));
            xhr.onload = function () {
                if (xhr.status === 200 && xhr.responseText) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        openVtlModal('Sql Script Saved',true,response.message);
                    } else {
                        openVtlModal('Error Saving Sql',false,response.message);
                    }
                } else {
                    openVtlModal('Error Saving Sql',false,response.message);
                }
            };

        }else
        {
            openVtlModal('Error Saving Sql',false,'Please enter a name for your query');
        }
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

    #datatable{
        margin-top: 20px;
    }


    code-input {
        width: calc(100% - 40px); /* 100% - 2*margin */
        margin: 20px;
        --padding: 20px;
    }

    button{
        margin: 20px;
    }
    .flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
