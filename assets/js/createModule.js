document.addEventListener('DOMContentLoaded', function() {
    const config = JSON.parse(document.getElementById('data-script').textContent);
    const noDataMessage = config.noDataMessage;
    const tableData = config.tableData;
    const relatedTables = config.relatedTables;
    const baseUrl = window.baseUrl;
    console.log ('Base url' , baseUrl);

    console.log('RT', relatedTables);

    let isSimpleModuleCreated = false;

    function attachModalEventListeners() {
        const vtlAcceptButton = document.getElementById('vtlAcceptQuestion');
        const vtlCancelButton = document.getElementById('vtlCancelQuestion');

        if (vtlAcceptButton && vtlCancelButton) {
            vtlAcceptButton.addEventListener('click', acceptQuestion);
            vtlCancelButton.addEventListener('click', cancelQuestion);
            console.log('Modal event listeners successfully attached.');
        } else {
            console.error('Failed to find modal buttons.');
        }
    }

    window.addEventListener('load', () => {
        // Display the question modal when the page loads
        openVtlQuestionModal(
            'Create Simple Module',
            'Would you like to create a simple module? If so please enter the name:',
            'vtlInfo',
            'info'
        );

        const inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.id = 'simpleModuleName';
        inputField.placeholder = 'Enter module name...';
        inputField.style.marginTop = '10px';
        document.getElementById('vtlQuestionContent').appendChild(inputField);

        attachModalEventListeners();
    });

    // Function to handle creating a simple module
    function acceptQuestion() {
        const moduleName = document.getElementById('simpleModuleName').value;
        document.getElementById('simpleModuleName').remove();

        if (moduleName) {
            fetch(baseUrl + 'vtlgen/createSimpleModule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: moduleName })
            })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        isSimpleModuleCreated = true;
                        openVtlModal('Simple Module Created', true, 'Simple module "' + moduleName + '" created successfully.');
                    } else {
                        openVtlModal('Error', false, 'Failed to create simple module. ' + (result.message || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    openVtlModal('Error', false, 'An error occurred while trying to create the simple module.');
                });
        } else {
            openVtlModal('Error', false, 'Please enter a module name.');
        }
    }

    function cancelQuestion() {
        console.log('The cancel button was clicked');
        runDefaultTableBasedModuleCreation();
    }

    function runDefaultTableBasedModuleCreation() {
        let formattedData = tableData.map(table => ({ table: table }));

        new Tabulator("#datatable", {
            height: "300px",
            data: formattedData,
            layout: "fitColumns",
            selectable: true,
            columns: [
                {
                    title: "Select",
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    hozAlign: "center",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    headerSort: false,
                    width: 60,
                    cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "Tables without Modules",
                    field: "table",
                    sorter: "string"
                }
            ],
            placeholder: noDataMessage
        }).on("rowSelectionChanged", function (data, rows) {
            const createMods = document.getElementById('createModuleDiv');
            createMods.style.display = rows.length > 0 ? 'block' : 'none';
        });
    }

    // Modal closed event listener
    window.addEventListener('vtlModalClosed', () => {
        if (isSimpleModuleCreated) {
            window.location.href = baseUrl + 'vtlgen';
        } else {
            location.reload();
        }
    });
});

// async function createModules() {
//     // Ensure BASE_URL is defined correctly
//     //const BASE_URL = 'http://localhost:8080/newdatagen/';
//
//     var table = Tabulator.findTable("#datatable")[0];
//     var selectedRows = table.getSelectedData();
//     var tableNames = selectedRows.map(row => row.table);
//
//     for (let i = 0; i < tableNames.length; i++) {
//         let tableName = tableNames[i];
//
//         // Debugging: Print the constructed URL to ensure it is correct
//         let url = baseUrl + 'vtlgen/createModules';
//         console.log('Request URL:', url);
//
//         try {
//             let response = await fetch(url, {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify({ table: tableName })
//             });
//
//             if (response.ok) {
//                 let result = await response.json();
//                 console.log('Related tables for', tableName, ':', result.relatedTables);
//             } else {
//                 console.error('Failed to fetch related tables for table:', tableName);
//             }
//         } catch (error) {
//             console.error('Error:', error);
//         }
//     }
// }


async function createModules() {
    // Get the selected rows from the Tabulator datatable
    var table = Tabulator.findTable("#datatable")[0];
    var selectedRows = table.getSelectedData();

    // get the value from the multi file uploader checkbox
    var addMultiFileUploader = document.getElementById("addMultiFileUploaderCheckbox").checked;

    // Filter the selected rows to include only the table names
    var tableNames = selectedRows.map(row => row.table);

    // Initialize progress bar
    var progressBarContainer = document.getElementById('progressContainer');
    var progressBar = document.getElementById('progress-bar');
    var progress = document.getElementById('progress');

    progressBarContainer.style.display = 'block';
    progressBar.style.display = 'block';
    progress.style.width = '0%';

    // Iterate over each selected table and call the backend
    for (let i = 0; i < tableNames.length; i++) {
        let tableName = tableNames[i];

        try {
            let response = await fetch(baseUrl + 'vtlgen/createModules', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ table: tableName , addMultiFileUploader: addMultiFileUploader})
            });

            if (response.ok) {
                let result = await response.json();

                // Update progress bar
                let progressPercentage = ((i + 1) / tableNames.length) * 100;
                progress.style.width = progressPercentage + '%';
                progress.textContent = progressPercentage + '%';

                // Handle success or error for each table
                if (result.status === 'success') {
                    console.log('Module created for table:', tableName);
                } else {
                    console.error('Failed to create module for table:', tableName, '-', result.message);
                }
            } else {
                console.error('Failed to create module for table:', tableName);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Optionally, you can display a completion message or refresh the page
    openVtlModal('Modules Created', true, 'All modules created successfully.');
}