<?php //echo json($data) ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="color-scheme" content="dark light">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <title>Vtl_Data_Generator</title>
</head>
<body>
<h2 class="container, text-center"><?= $headline ?></h2>
<section>
    <div class="container">


        <div class="grid-container">
            <!-- Group Title for Data -->
            <div class="grid-item data-heading" colspan="4">Data Generation and Visualisation</div>

            <!-- Data Operation Buttons -->
            <div class="grid-item">
                <button class="svg-button" aria-label="Create Data" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateData'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlRecordAddDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlRecordAdd.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Create Data</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Delete Data" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDeleteData'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlRecordRemoveDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlRecordRemove.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Delete Data</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Last Primary" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenFetchLatestPkValues'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlPrimaryKeysDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlPrimaryKeys.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Last Primary</div>
                </button>
            </div>
            <div class="grid-item" id="showButton">
                <button class="svg-button" aria-label="Browse Data" onclick="toggleDropdown()">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlBrowseDataDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlBrowseData.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Browse Data</div>
                </button>
            </div>

            <!-- Table Dropdown Section -->
            <div class="grid-item table-dropdown">
                <section class="tableDropdown">
                    <div class="container">
                        <?php
                        $tableChoiceAttr['id'] = 'tableChoiceDropdown';
                        $tableChoiceAttr['style'] = 'display: none;'; // Initially hide the dropdown
                        $tableChoiceAttr['onchange'] = 'selectedTable()';
                        echo form_dropdown('tableChoice', $tables, '', $tableChoiceAttr);
                        ?>
                    </div>
                </section>
            </div>

            <!-- Group Title for Indexes -->
            <div class="grid-item index-heading" colspan="4">Index and Foreign Key Operations</div>

            <!-- Index Operation Buttons -->
            <div class="grid-item">
                <button class="svg-button" aria-label="Create Index" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateIndex'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlIndexAddDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlIndexAdd.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Create Index</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Delete Index" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDeleteIndex'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlIndexRemoveDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlIndexRemove.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Delete Index</div>
                </button>
            </div>
            <div class="grid-item">
                <div class="grid-item">
                    <button class="svg-button" aria-label="Browse FK's" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowForeignKeys'">
                        <picture>
                            <source srcset="vtlgen_module/help/images/vtlForeignKeysViewDark.svg" media="(prefers-color-scheme: dark)">
                            <img class="svg-icon" src="vtlgen_module/help/images/vtlForeignKeysView.svg" alt="Create Data Icon">
                        </picture>
                        <div class="popup popupRight">Browse FK's</div>
                    </button>
                </div>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Create FK" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateForeignKey'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlForeignKeysAddDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlForeignKeysAdd.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Create FK</div>
                </button>
            </div>
            <div class="grid-item">
                <div class="grid-item">
                    <button class="svg-button" aria-label="Delete FK's" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDeleteForeignKeys'">
                        <picture>
                            <source srcset="vtlgen_module/help/images/vtlForeignKeysRemoveDark.svg" media="(prefers-color-scheme: dark)">
                            <img class="svg-icon" src="vtlgen_module/help/images/vtlForeignKeysRemove.svg" alt="Create Data Icon">
                        </picture>
                        <div class="popup popupRight">Delete FK's</div>
                    </button>
                </div>
            </div>

            <!-- Group Title for Database Operations -->

            <div class="grid-item dbase-heading" colspan="4">Database Operations</div>

            <!-- Database Operation Buttons -->
            <div class="grid-item">
                <button class="svg-button" aria-label="Create Table" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateDataTable'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlTableAddDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlTableAdd.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Create Table</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Drop Table" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDropDataTable'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlTableRemoveDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlTableRemove.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Drop Table</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Export Script" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenExportDatabase'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlDatabaseExportDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlDatabaseExport.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Export Script</div>
                </button>
            </div>

            <!-- Add two more buttons if needed, or leave these empty for now -->
            <div class="grid-item"></div>
            <div class="grid-item"></div>

            <!-- Group Title for Help Operations -->

            <div class="grid-item help-heading" colspan="4">Help</div>

            <div class="grid-item">
                <button class="svg-button" aria-label="General" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowGeneralHelp'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlHelpDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlHelp.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">General</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Customisation" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowCustomiseFakerHelp'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlHelpDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlHelp.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Customisation</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="POI" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowPointsOfInterestHelp'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlHelpDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlHelp.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">POI</div>
                </button>
            </div>

        </div>

    </div>
</section>
</body>
</html>
<script>
    function toggleDropdown() {
        var dropdown = document.getElementById('tableChoiceDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }
    function selectedTable() {
        // Get the dropdown element
        var dropdown = document.getElementById('tableChoiceDropdown');

        // Get the selected value
        var selectedTable = dropdown.options[dropdown.selectedIndex].text;

        // Construct the URL with the selected table as a query parameter
        // Redirect to the URL
        window.location.href = '<?= BASE_URL ?>vtlgen/vtlgenShowData?selectedTable=' + encodeURIComponent(selectedTable);
    }
</script>
<style>

    @media (prefers-color-scheme: light) {
        .data-heading, .index-heading, .dbase-heading, .help-heading {
            grid-column: span 4; /* Span all 4 columns */
            color: #000000; /* Optional: Add a background color for the headings */
            text-align: center; /* Center the text in headings */
            font-weight: bold; /* Make the headings bold */
            /*margin-top: 10px;*/
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
        .svg-button:focus .popup,
        .svg-button:hover .popup {
            display: block;
            background-color:transparent; /*rgba(255, 255, 255, 0.8); /* Transparent background on hover */
        }
    }

    @media (prefers-color-scheme: dark) {
        .data-heading, .index-heading, .dbase-heading, .help-heading {
            grid-column: span 4; /* Span all 4 columns */
            color: #f0f0f0; /* Optional: Add a background color for the headings */
            text-align: center; /* Center the text in headings */
            font-weight: bold; /* Make the headings bold */
            /*margin-top: 10px;*/
        }

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

        .svg-button:focus .popup,
        .svg-button:hover .popup {
            display: block;
            background-color: transparent; /*rgba(255, 255, 255, 0.8); /* Transparent background on hover */
        }

    }
    .flex {
        display: flex;
        justify-content: center; /* Center items horizontally */
        align-items: center; /* Center items vertically */
        gap: 4px;
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px; /* Adjust the gap between grid items as needed */
        padding: 5px; /* Adjust the padding around the grid as needed */
    }

    .grid-item {
        padding: 5px; /* Adjust the padding inside grid items as needed */
        text-align: center; /* Center the text in grid items */
        /*margin-bottom: 5px;*/

    }
    .table-dropdown {
        grid-column: 2 / 4; /* Span from the 2nd to the 3rd column */
    }

    .svg-icon {
        width: 50px;
        height: 50px;
    }

    .button {
        border-radius: 10px;
        text-transform: capitalize;
        /*margin-bottom: 10px;*/
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
    .container{
        padding:0;
    }

</style>
