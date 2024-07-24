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

            <div class="grid-item" id="showButton">
                <button class="svg-button" aria-label="Browse Data" onclick="toggleDropdown()">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlBrowseDataDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlBrowseData.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Browse Data</div>
                </button>
            </div>

            <div class="grid-item">
                <button class="svg-button" aria-label="Delete Data" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateSql'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlCreateSqlDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlCreateSql.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Create Sql</div>
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
            <div class="grid-item index-heading" colspan="4">Index and Documentation Operations</div>

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
                    <button class="svg-button" aria-label="Browse Indexes" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowIndexes'">
                        <picture>
                            <source srcset="vtlgen_module/help/images/vtlIndexBrowseDark.svg" media="(prefers-color-scheme: dark)">
                            <img class="svg-icon" src="vtlgen_module/help/images/vtlIndexBrowse.svg" alt="Create Data Icon">
                        </picture>
                        <div class="popup popupRight">Indexes</div>
                    </button>
                </div>
            </div>

            <div class="grid-item">
                <button class="svg-button" aria-label="Generate Documentation" onclick="generateDocumentation()">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlDocumentDatabaseDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlDocumentDatabase.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Document Db</div>
                </button>
            </div>


            <!-- Group Title for Foreign Key Operations -->
            <div class="grid-item fk-heading" colspan="4">Foreign and PrimaryKey Operations</div>


            <div class="grid-item">
                <button class="svg-button" aria-label="Create FK" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateForeignKey'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlForeignKeysAddDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlForeignKeysAdd.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Create FK</div>
                </button>
            </div>
            <div class="grid-item">
                <div class="grid-item">
                    <button class="svg-button" aria-label="Delete FK's" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDeleteForeignKeys'">
                        <picture>
                            <source srcset="vtlgen_module/help/images/vtlForeignKeysRemoveDark.svg" media="(prefers-color-scheme: dark)">
                            <img class="svg-icon" src="vtlgen_module/help/images/vtlForeignKeysRemove.svg" alt="Create Data Icon">
                        </picture>
                        <div class="popup popupLeft">Delete FK's</div>
                    </button>
                </div>
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
                <button class="svg-button" aria-label="Browse Primary" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenFetchLatestPkValues'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlPrimaryBrowseDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlPrimaryBrowse.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Browse PK's</div>
                </button>
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
                <button class="svg-button" aria-label="Drop Table" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDropTables'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlTableRemoveDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlTableRemove.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Drop Table</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Edit Table" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenEditDataTable'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlTableEditDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlTableEdit.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupRight">Edit Table</div>
                </button>
            </div>

            <div class="grid-item">
                <button class="svg-button" aria-label="Export Script" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenExportDatabase'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlDatabaseExportDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlDatabaseExport.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Export </div>
                </button>
            </div>

            <!-- Add two more buttons if needed, or leave these empty for now -->
            <div class="grid-item"></div>
            <div class="grid-item"></div>

            <!-- Group Title for Module Operations -->

            <div class="grid-item module-heading" colspan="4">Module and Project Operations</div>

            <div class="grid-item">
                <button class="svg-button" aria-label="Create Module" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenCreateModules'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlCreateModuleDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlCreateModule.svg" alt="Create Module Icon">
                    </picture>
                    <div class="popup popupRight">Create Module</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Delete Module" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenDeleteModules'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlDeleteModuleDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlDeleteModule.svg" alt="Delete Module Icon">
                    </picture>
                    <div class="popup popupLeft">Delete Module</div>
                </button>
            </div>
            <div class="grid-item">
                <button class="svg-button" aria-label="Zip Module" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenZipModuleProject'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlZipDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlZip.svg" alt="Create Module Icon">
                    </picture>
                    <div class="popup popupRight">Zip Mod / Proj</div>
                </button>
            </div>


        <div class="grid-item">
            <button class="svg-button" aria-label="Unzip Module" onclick="document.getElementById('zipFileInput').click()">
                <picture>
                    <source srcset="vtlgen_module/help/images/vtlUnzipDark.svg" media="(prefers-color-scheme: dark)">
                    <img class="svg-icon" src="vtlgen_module/help/images/vtlUnzip.svg" alt="Create Module Icon">
                </picture>
                <div class="popup popupLeft">Unzip Mod</div>
            </button>
            <input type="file" id="zipFileInput" style="display: none;" accept=".zip" onchange="uploadAndUnzipFile(event)">
        </div>
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
                    <div class="popup popupLeft">Customise</div>
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
            <div class="grid-item">
                <button class="svg-button" aria-label="Tabulator" onclick="window.location.href='<?= BASE_URL ?>vtlgen/vtlgenShowTabulatorHelp'">
                    <picture>
                        <source srcset="vtlgen_module/help/images/vtlHelpDark.svg" media="(prefers-color-scheme: dark)">
                        <img class="svg-icon" src="vtlgen_module/help/images/vtlHelp.svg" alt="Create Data Icon">
                    </picture>
                    <div class="popup popupLeft">Tabulator</div>
                </button>
            </div>
        </div>

    </div>

</section>

<div class="container">
    <div class="flex" style="display: flex; align-items: baseline; vertical-align: middle ">
        <?php if ($update_available): ?>
            <a href="#" onclick="initiateUpdate(); return false;" class="svg-button" aria-label="Update" style="display: inline-flex; align-items: center; margin-right: 10px; text-decoration: none;">
                <picture style="display: flex; align-items: center;">
                    <source srcset="vtlgen_module/help/images/vtlUpdateDark.svg" media="(prefers-color-scheme: dark)">
                    <img class="svg-icon" src="vtlgen_module/help/images/vtlUpdate.svg" alt="Update Icon" style="width: 24px; height: 24px; display: block;">
                </picture>
                <div class="popup popupLeft">Update to <?= $new_version ?></div>
            </a>
        <?php endif; ?>
        <p class="text-center" id="version" style="margin: 0; font-size: 16px; line-height: 24px;"><?=VERSION?></p>

        <a href="https://github.com/domsinclair/vtlgen" target="_blank" class="svg-button" aria-label="Github" style="display: inline-flex; align-items: center; margin-left: 10px;">
            <picture style="display: flex; align-items: center;">
                <source srcset="vtlgen_module/help/images/vtlGithubDark.svg" media="(prefers-color-scheme: dark)">
                <img class="svg-icon" src="vtlgen_module/help/images/vtlGithub.svg" alt="Github Icon" style="width: 24px; height: 24px; display: block;">
            </picture>
            <div class="popup popupRight">Github</div>
        </a>
    </div>
</div>






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
<script defer src="<?= BASE_URL ?>vtlgen_module/js/vtlModal.js"></script>
<script defer src="<?= BASE_URL ?>vtlgen_module/js/vtlQuestionModal.js"></script>
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

    function generateDocumentation() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= BASE_URL ?>vtlgen/vtlgenDocumentDatabase', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            openVtlModal('Documentation Created', true, response.message);
                        } else {
                            openVtlModal('Documentation Failed', false, response.message);
                        }
                    } catch (error) {
                        openVtlModal('Documentation Failed', false, 'Failed to parse response as JSON.');
                    }
                } else {
                    openVtlModal('Documentation Failed', false, 'Failed to generate documentation.');
                }
            }
        };

        xhr.send();
    }

    function initiateUpdate() {
        openVtlQuestionModal(
            "Update VTL Data Generator",
            `Do you want to update the VTL Data Generator to version <?= $new_version ?>?`,
            "vtlUpdate",
            "info"
        );

        document.getElementById('vtlQuestionModal').addEventListener('vtlQuestionAccepted', performUpdate, { once: true });
        document.getElementById('vtlQuestionModal').addEventListener('vtlQuestionCancelled', cancelUpdate, { once: true });
    }

    function performUpdate() {
        // Show loading indicator
        document.body.style.cursor = 'wait';
        document.querySelector('.svg-button[aria-label="Update"]').style.pointerEvents = 'none';

        // First, check prerequisites
        fetch('<?= BASE_URL ?>vtlgen/vtlgenCheckUpdatePrerequisites')
            .then(response => response.json())
            .then(data => {
                if (data.canUpdate) {
                    // If prerequisites are met, proceed with the update
                    return fetch('<?= BASE_URL ?>vtlgen/vtlgenPerformUpdate');
                } else {
                    // If prerequisites are not met, show modal with error message and exit the function
                    openVtlModal(
                        "Update Prerequisites Not Met",
                        false,
                        data.message
                    );
                    // Reset cursor and button state
                    document.body.style.cursor = 'default';
                    document.querySelector('.svg-button[aria-label="Update"]').style.pointerEvents = 'auto';
                    // Exit the function
                    return Promise.reject('Prerequisites not met');
                }
            })
            .then(response => response.json())
            .then(data => {
                openVtlModal(
                    "Update Result",
                    true,
                    data.message
                );
                // You might want to refresh the page or update the UI here
                // depending on the update process result
            })
            .catch(error => {
                console.error('Error:', error);
                if (error !== 'Prerequisites not met') {
                    openVtlModal(
                        "Error",
                        false,
                        "An unexpected error occurred while updating. Please try again later."
                    );
                }
            })
            .finally(() => {
                // Hide loading indicator
                document.body.style.cursor = 'default';
                document.querySelector('.svg-button[aria-label="Update"]').style.pointerEvents = 'auto';
            });
    }


    function cancelUpdate() {
        console.log("Update cancelled");
        // You can add any additional actions here if needed when the update is cancelled
    }
    function uploadAndUnzipFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('zipFile', file);

        console.log(formData.get('zipFile')); // Debugging step

        fetch('<?= BASE_URL ?>vtlgen/vtlgenUnzipModule', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json().catch(() => response.text()))
            .then(data => {
                if (typeof data === 'string') {
                    console.log('Non-JSON response:', data);
                    openVtlModal('Error Unzipping Module', false, 'Server returned an unexpected response');
                    return;
                }

                if (data.success) {
                    openVtlModal('Module Unzipped', true, data.message || 'Module unzipped successfully.');
                } else {
                    openVtlModal('Error Unzipping Module', false, data.message);
                }

                // Log debugging messages to the console
                if (data.debug) {
                    console.log('Debug Messages:', data.debug);
                }
            })
            .catch(error => {
                openVtlModal('Error Unzipping Module', false, error.message);
                console.log('Fetch error:', error); // Additional debugging for fetch errors
            });
    }




</script>
<style>

    @media (prefers-color-scheme: light) {
        .data-heading, .index-heading,.fk-heading, .dbase-heading, .module-heading, .help-heading {
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
        .data-heading, .index-heading,.fk-heading, .dbase-heading,.module-heading, .help-heading {
            grid-column: span 4; /* Span all 4 columns */
            color: #f0f0f0; /* Optional: Add a background color for the headings */
            text-align: center; /* Center the text in headings */
            font-weight: bold; /* Make the headings bold */
            /*margin-top: 10px;*/
        }
        button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            position: relative;
        }

        button:hover {
            background-color: transparent;
            border: none;
        }

        .svg-button {
            background: transparent;
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
    #version{
        font-size: 12px;
    }

    #vtlOverlay, #vtlModal {
        display: none;
    }

    #vtlOverlay.visible, #vtlModal.visible {
        display: block;
    }


</style>
