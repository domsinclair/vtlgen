let picDirectoryExists = window.picDirectoryExists;
const baseUrl = window.baseUrl;
const task = window.task;
const columnInfo = window.columnInfo;
const relatedTables = window.relatedTables;


// Function to check if the picture directory exists for the selected table
async function setPictureDirectoryExistsForSelectedTableModule(selectedTable) {
    const postData = {
        selectedTable: selectedTable
    };

    try {
        const xhr = new XMLHttpRequest();
        const targetUrl = baseUrl + 'vtlgen/createdataGetPictureFolderExists';
        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        const jsonData = JSON.stringify(postData);
        xhr.send(jsonData);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = xhr.responseText;
                const responseObject = JSON.parse(response);
                const picDirectoryExists = responseObject.picDirectoryExists;

                const picDirectoryExistsInput = document.getElementById('picDirectoryExists');
                picDirectoryExistsInput.value = picDirectoryExists ? 'true' : 'false';
            } else {
                console.error('Error:', xhr.status);
            }
        };
    } catch (error) {
        console.error('Error:', error);
    }
}

// Event handler for table dropdown change
async function selectedTable() {
    const dropdown = document.getElementById('tableChoiceDropdown');

    if (!dropdown || dropdown.selectedIndex === -1) {
        console.error('Dropdown element not found or no selection made.');
        return;
    }

    const selectedTable = dropdown.options[dropdown.selectedIndex].text;

    if (!columnInfo) {
        console.error('Could not find columnInfo data.');
        return;
    }    const selectedTableColumns = columnInfo.find(table => table.table === selectedTable).columns;
    const tableData = [];

    selectedTableColumns.forEach(column => {
        if (!(column.Key === 'PRI' && column.Extra.includes('auto_increment'))) {
            tableData.push({
                title: column.Field,
                field: column.Field,
                type: column.Type,
                null: column.Null,
                key: column.Key,
                default: column.Default,
                extra: column.Extra,
            });
        }
    });

    const table = new Tabulator("#datatable", {
        layout: "fitColumns",
        selectableRows: true,
        columns: [
            {title: "Select", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", vertAlign: "middle", headerHozAlign: "center", headerSort: false, width: 60,
                cellClick: function (e, cell) { cell.getRow().toggleSelect(); }},
            {title: "Field", field: "field", width: 200, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
            {title: "Type", field: "type", width: 100, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
            {title: "Null", field: "null", width: 100, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
            {title: "Key", field: "key", width: 80, hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
            {title: "Default", field: "default", hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"},
            {title: "Extra", field: "extra", hozAlign: "left", vertAlign: "middle", headerHozAlign: "left"}
        ],
        data: tableData,
    });

    table.on("rowSelected", function(row){
        row.getElement().style.backgroundColor = "var(--primary)";
        row.getElement().style.color = "white";
    });

    table.on("rowDeselected", function(row){
        row.getElement().style.backgroundColor = '';
        row.getElement().style.color = '';
    });

    table.on("rowSelectionChanged", function(data, rows, selected, deselected){
        const numRowsContainer = document.getElementById('numRowsContainer');
        const submitBtn = document.getElementById('submitBtn');
        const indexTypeDropdown = document.getElementById('indexTypeDropdown');
        const generateIndexButton = document.getElementById('generateIndexButton');

        if(table.getSelectedRows().length > 0) {
            switch(task) {
                case 'data':
                    numRowsContainer.style.display = 'block';
                    submitBtn.style.display = 'block';
                    indexTypeDropdown.style.display = 'none';
                    generateIndexButton.style.display = 'none';
                    break;
                case 'index':
                    numRowsContainer.style.display = 'none';
                    submitBtn.style.display = 'none';
                    indexTypeDropdown.style.display = 'block';
                    generateIndexButton.style.display = 'block';
                    break;
                default:
                    numRowsContainer.style.display = 'none';
                    submitBtn.style.display = 'none';
                    indexTypeDropdown.style.display = 'none';
                    generateIndexButton.style.display = 'none';
            }

        } else {
            numRowsContainer.style.display = 'none';
            submitBtn.style.display = 'none';
            indexTypeDropdown.style.display = 'none';
            generateIndexButton.style.display = 'none';
        }
    });

    if (task === 'data') {
        await setPictureDirectoryExistsForSelectedTableModule(selectedTable);
    }
}

async function generateData() {
    // Get the selected table name from the dropdown
    const dropdown = document.getElementById('tableChoiceDropdown');
    const selectedTable = dropdown.options[dropdown.selectedIndex].text;
    const hasForeignKeyReferences = isSelectedTableReferencingAnother(selectedTable);
    let referencedInfo = [];
    if (hasForeignKeyReferences) {
        referencedInfo = getReferencedTableInfo(selectedTable);
    }

    // Get the selected rows from the Tabulator datatable
    const table = Tabulator.findTable("#datatable")[0];
    const selectedRows = table.getSelectedData();

    // Filter the selected rows to include only the field name and data type
    const filteredRows = selectedRows.map(row => {
        return {
            field: row.field,
            type: row.type
        };
    });

    // Get the value from the numRows input field
    const numRows = document.getElementById('numRows').value;

    // Prepare the data to send
    const postData = {
        selectedTable: selectedTable,
        selectedRows: filteredRows,
        numRows: numRows
    };

    if (hasForeignKeyReferences) {
        postData.referencedInfo = referencedInfo;
    }

    // Specify the PHP file or endpoint to handle the data
    const targetUrl = hasForeignKeyReferences ?
        `${window.baseUrl}vtlgen/createdataWithForeignKeys` :
        `${window.baseUrl}vtlgen/createdataCreateFakeData`;

    // Send the POST request
    try {
        // Create a new XMLHttpRequest
        const xhr = new XMLHttpRequest();

        // Open a POST request to the specified URL
        xhr.open('POST', targetUrl, true);

        // Set the content type to JSON
        xhr.setRequestHeader('Content-type', 'application/json');

        // Define a callback function to handle the response
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    // Parse the JSON response
                    const response = JSON.parse(xhr.responseText);

                    // Handle the response
                    openVtlModal('Fake Data Generated',true,response.message);

                    // Additional logic based on response
                   const picDirectoryExists = document.getElementById('picDirectoryExists').value ;

                    console.log('picDirectoryExists:', picDirectoryExists);

                    if (picDirectoryExists && numRows <= 100) {
                        document.getElementById('submitBtn').style.display = 'none';
                        document.getElementById('movePicsBtn').style.display = 'block';
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    openVtlModal('Error Parsing Json',false,response.message);
                }
            } else {
                const errorResponse = xhr.responseText;
                openVtlModal('Error Generating Fake Data',false,errorResponse.message);
            }
        };

        // Convert the data object to a JSON string
        const jsonData = JSON.stringify(postData);
        // Send the request with the JSON data
        xhr.send(jsonData);
    } catch (error) {
        console.error('Error:', error);
    }
}

function isSelectedTableReferencingAnother(selectedTable) {
    return relatedTables.some(rel => rel.TABLE_NAME === selectedTable);
}

function getReferencedTableInfo(selectedTable) {
    return relatedTables
        .filter(rel => rel.TABLE_NAME === selectedTable)
        .map(rel => ({
            referencedTable: rel.REFERENCED_TABLE_NAME,
            referencedColumn: rel.REFERENCED_COLUMN_NAME,
            columnName: rel.COLUMN_NAME
        }));
}

async function generateIndex() {
    // Get the selected table name from the dropdown
    const dropdown = document.getElementById('tableChoiceDropdown');
    const selectedTable = dropdown.options[dropdown.selectedIndex].text;

    // Get the selected rows from the Tabulator datatable
    const table = Tabulator.findTable("#datatable")[0];
    const selectedRows = table.getSelectedData();

    // Filter the selected rows to include only the field name
    const filteredRows = selectedRows.map(row => {
        return {
            field: row.field
        };
    });

    // Get the value from the index type dropdown
    const indexDropdown = document.getElementById('indexType');
    const indexType = indexDropdown.options[indexDropdown.selectedIndex].value;

    // Prepare the data to send
    const postData = {
        selectedTable: selectedTable,
        selectedRows: filteredRows,
        indexType: indexType
    };

    // Send the POST request
    try {
        // Create a new XMLHttpRequest
        const xhr = new XMLHttpRequest();

        // Specify the PHP file or endpoint to handle the data
        const targetUrl = `${window.baseUrl}vtlgen/createdataCreateIndex`;


        // Open a POST request to the specified URL
        xhr.open('POST', targetUrl, true);

        // Set the content type to JSON
        xhr.setRequestHeader('Content-type', 'application/json');

        // Define a callback function to handle the response
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    // Parse the JSON response
                    const response = JSON.parse(xhr.responseText);
                    let message = response.message;

                    // Format the response message
                    if (response.createdIndexes.length > 0) {
                        message += '<br><br>Created Indexes:<br>' + response.createdIndexes.join('<br>');
                    }
                    if (response.failedIndexes.length > 0) {
                        message += '<br><br>Failed Indexes:<br>' + response.failedIndexes.join('<br>');
                    }

                    // Handle the response with the custom modal
                    openVtlModal('Index Generated', true, message);
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    openVtlModal('Error Parsing JSON', false, 'An error occurred while processing the response.');
                }
            } else {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    openVtlModal('Error Generating Index', false, errorResponse.message);
                } catch (e) {
                    console.error('Error parsing error response JSON:', e);
                    openVtlModal('Error Generating Index', false, xhr.responseText);
                }
            }
        };

        // Convert the data object to a JSON string
        const jsonData = JSON.stringify(postData);
        // Send the request with the JSON data
        xhr.send(jsonData);
    } catch (error) {
        console.error('Error:', error);
    }
}

function movePictures() {
    // Get the selected table name from the dropdown
    const dropdown = document.getElementById('tableChoiceDropdown');
    const selectedTable = dropdown.options[dropdown.selectedIndex].text;
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.display = 'block';

    // Prepare the data to send
    const postData = {
        selectedTable: selectedTable
    };

    try {
        // Create a new XMLHttpRequest
        const xhr = new XMLHttpRequest();

        // Specify the PHP file or endpoint to handle the data
        const targetUrl = `${window.baseUrl}vtlgen/createdataSetImageFoldersAndTransferImages`;

        // Open a POST request to the specified URL
        xhr.open('POST', targetUrl, true);

        // Set the content type to JSON
        xhr.setRequestHeader('Content-type', 'application/json');

        // Convert the data object to a JSON string
        const jsonData = JSON.stringify(postData);

        // Send the request with the JSON data
        xhr.send(jsonData);

        // Define a callback function to handle the response
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Handle the response here
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    // Handle error responses here
                    openVtlModal('Error Moving Images',false,response.message);
                } else {
                    // Start processing records
                    processRecords(response.totalRows);
                }
            } else {
                // Handle error responses here
                openVtlModal('Error Moving Images',false,response.message);

            }
        };
    } catch (error) {
        openVtlModal('Error Moving Images',false,error);
    }
}

async function processRecords(totalRows) {
    // Get the selected table name from the dropdown
    const dropdown = document.getElementById('tableChoiceDropdown');
    const selectedTable = dropdown.options[dropdown.selectedIndex].text;

    const progressElement = document.getElementById('progress');
    let progress = 0;

    // Define a function to handle each record asynchronously
    async function processRecord(recordId) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= BASE_URL ?>vtlgen/createdataCopyImageForRecords', true);
            xhr.setRequestHeader('Content-type', 'application/json');

            // Prepare the data to send
            const data = {
                recordId: recordId,
                selectedTable: selectedTable
            };

            // Convert the data object to a JSON string
            const jsonData = JSON.stringify(data);
            // Define a callback function to handle the response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Update progress bar
                    progress++;
                    const percent = Math.round((progress / totalRows) * 100);
                    progressElement.style.width = percent + '%';
                    progressElement.textContent = percent + '%';

                    // Resolve the Promise
                    resolve();
                } else {
                    // Reject the Promise on error
                    reject('Request from process images failed with status ' + xhr.responseText);
                }
            };

            xhr.onerror = function () {
                // Reject the Promise on connection error
                reject('Request failed');
            };

            // Send the request with the JSON data
            xhr.send(jsonData);
        });
    }

    // Loop through records and process them asynchronously
    for (let i = 1; i <= totalRows; i++) {
        try {
            await processRecord(i);
        } catch (error) {
            // Handle errors here if needed
            console.error(error);
            openVtlModal('Error Moving Images',false,error);
            return; // Stop processing further records on error
        }
    }

    // If all records processed, display success message
    openVtlModal('Success Moving Images',true,'Images copied successfully.');
}