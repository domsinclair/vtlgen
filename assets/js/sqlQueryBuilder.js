

document.addEventListener('DOMContentLoaded', function () {



    if (window.relatedTableData && window.noDataMessage) {


        new Tabulator('#datatable', {
            data: window.relatedTableData,
            layout: "fitColumns",
            pagination: true,
            paginationSize: 20,
            movableColumns: true,
            paginationCounter: "pages",
            autoColumns: false,
            placeholder: window.noDataMessage,
            columns: [
                { title: "Table Name", field: "TABLE_NAME" },
                { title: "Column Name", field: "COLUMN_NAME" },
                { title: "Referenced Table", field: "REFERENCED_TABLE_NAME" },
                { title: "Referenced Column", field: "REFERENCED_COLUMN_NAME" }
            ]
        });
    } else {
        console.warn('No data provided for Tabulator initialization');
    }

    const initialDropdown = document.getElementById('tableDropdown1');
    if (initialDropdown) {


        if (!initialDropdown.classList.contains('event-registered')) {
            initialDropdown.addEventListener('change', function () {
                selectTable('tableDropdown1', 1);
            });
            initialDropdown.classList.add('event-registered');
        }
    } else {
        console.error('Initial dropdown (tableDropdown1) not found');
    }
});

// State tracking variables
/**
 * Stores the selected tables within a given context.
 *
 * This variable is an object where each key represents an identifier or name
 * for a selected table and the corresponding value holds specific details,
 * configurations, or data related to that table. It can be used to manage
 * which tables are currently active, being edited, or require certain
 * operations within an application.
 *
 * The structure of this object allows for dynamic management and lookup
 * of selected tables based on their identifiers.
 */
let selectedTables = {};

/**
 * Selects a table from a dropdown and updates relevant display fields and related tables accordingly.
 *
 * @param {string} dropdownId - The ID of the dropdown element containing table names.
 * @param {number} level - The level indicating the hierarchy or depth of the selection.
 * @return {Promise<void>}
 */
async function selectTable(dropdownId, level) {


    const tableDropdown = document.getElementById(dropdownId);
    if (!tableDropdown) {
        console.error(`Dropdown element with ID ${dropdownId} not found.`);
        return;
    }

    const selectedTableName = tableDropdown.options[tableDropdown.selectedIndex].text;
    if (!selectedTableName) {
        console.error('No table selected.');
        return;
    }

    if (selectedTables[level] === selectedTableName) {
        return;
    }

    selectedTables[level] = selectedTableName;

    const columnInfo = window.columnInfo;
    if (!columnInfo || !Array.isArray(columnInfo)) {
        console.error('Column info data structure is invalid.');
        return;
    }

    const selectedTableColumns = columnInfo.find(table => table.table === selectedTableName);
    if (!selectedTableColumns || !selectedTableColumns.columns) {
        console.error('No columns found for the selected table');
        return;
    }



    displayFields(selectedTableColumns.columns, level);
    const relatedTables = getRelatedTables(selectedTableName);

    if (relatedTables.length > 0) {
        displayRelatedTables(relatedTables, level + 1);
    } else {
        console.warn(`No related tables found for ${selectedTableName}`);
    }


}



/**
 * Displays columns of data in a formatted grid using the Tabulator library.
 *
 * @param {Array} columns - An array of column objects containing field information.
 * @param {number} level - An identifier to distinguish between different data grids.
 * @return {void}
 */
function displayFields(columns, level) {
    const datatableId = `datatable${level}`;
    const gridCell = document.getElementById(`fieldGridContainer${level}`);
    gridCell.innerHTML = ''; // Clear previous content

    const dataTable = document.createElement("div");
    dataTable.id = datatableId;
    dataTable.classList.add('fieldGridContainer'); // Add CSS class for overflow handling
    gridCell.appendChild(dataTable);

    const fieldData = columns.map(column => ({ field: column.Field }));

    const table = new Tabulator(`#${datatableId}`, {
        data: fieldData,
        layout: "fitColumns", // Adjust layout to fit columns within container
        movableColumns: true, // Allow column reordering
        columns: [
            {
                title: "Select",
                formatter: "rowSelection",
                titleFormatter: "rowSelection",
                hozAlign: "center"
            },
            {
                title: "Field",
                field: "field"
            }
        ],
        rowFormatter: function (row) {
            row.getElement().style.height = 'auto'; // Adjust row height
        }
    });

    // Add event handlers for row selection and deselection
    table.on("rowSelected", function(row) {
        row.getElement().style.backgroundColor = "var(--primary)";
        row.getElement().style.color = "white";
    });

    table.on("rowDeselected", function(row) {
        row.getElement().style.backgroundColor = '';
        row.getElement().style.color = '';
    });

    gridCell.classList.remove('hidden');

}


/**
 * Displays dropdowns for related tables based on the specified level.
 *
 * @param {Array<string>} tables - An array of table names to be displayed in the dropdown.
 * @param {number} level - The current level of dropdown (used to differentiate multiple dropdowns).
 * @return {void} This function does not return a value.
 */
function displayRelatedTables(tables, level) {
    const maxDropdowns = 4; // Define the maximum number of dropdowns


    // Check if level exceeds the maximum allowed dropdowns
    if (level > maxDropdowns) {
        console.warn(`Level ${level} exceeds the maximum allowed dropdowns of ${maxDropdowns}.`);
        return;
    }

    const containerId = `tableDropdownContainer${level}`;
    const container = document.getElementById(containerId);

    // Check if the container exists
    if (!container) {
        console.error(`Container element with ID ${containerId} not found.`);
        return;
    }

    container.classList.remove('hidden');


    const dropdownId = `tableDropdown${level}`;
    const tableDropdown = document.getElementById(dropdownId);

    // Check if the dropdown exists
    if (!tableDropdown) {
        console.error(`Dropdown element with ID ${dropdownId} not found.`);
        return;
    }

    tableDropdown.innerHTML = ''; // Clear existing options


    const placeholderOption = document.createElement('option');
    placeholderOption.value = "";
    placeholderOption.text = "Select a related table...";
    placeholderOption.disabled = true;
    placeholderOption.selected = true;
    tableDropdown.appendChild(placeholderOption);

    tables.forEach(table => {
        const option = document.createElement('option');
        option.value = table;
        option.text = table;
        tableDropdown.appendChild(option);

    });



    if (!tableDropdown.classList.contains('event-registered')) {
        tableDropdown.addEventListener('change', function () {
            selectTable(dropdownId, level);
        });
        tableDropdown.classList.add('event-registered');
    }
}



/**
 * Retrieves a list of tables related to the specified table.
 *
 * This method searches for tables that either reference the specified table
 * or are referenced by the specified table, and returns a list of these tables.
 *
 * @param {string} selectedTableName - The name of the table for which related tables are to be found.
 * @return {string[]} An array of table names that are related to the specified table.
 */
function getRelatedTables(selectedTableName) {
    const relatedTables = new Set();

    // Find tables that reference the selected table
    window.relatedTableData.forEach(table => {
        if (table.REFERENCED_TABLE_NAME === selectedTableName) {
            relatedTables.add(table.TABLE_NAME);
        }
    });

    // Find tables that are referenced by the selected table
    window.relatedTableData.forEach(table => {
        if (table.TABLE_NAME === selectedTableName) {
            relatedTables.add(table.REFERENCED_TABLE_NAME);
        }
    });

    // Convert Set to Array
    return Array.from(relatedTables);
}





/**
 * Generate and display an SQL query based on user-selected tables, fields, and join types.
 *
 * The function performs the following steps:
 * 1. Retrieves selected tables from dropdowns.
 * 2. Gathers selected fields from each table.
 * 3. Collects join type information for each table pair.
 * 4. Generates the SQL query based on the gathered data.
 * 5. Formats and displays the final SQL query in a designated element.
 *
 * @return {void}
 */
function createSql() {

    let selectedTables = [];
    let joinTypes = [];

    // Get selected tables
    for (let i = 1; i <= 4; i++) {
        let table = getSelectValue(`tableDropdown${i}`);
        if (table) selectedTables.push(table);
    }


    let selectedTableCount = selectedTables.length;

    // Gather selected fields
    let selectedFields = [];

    if (selectedTableCount > 0) {
        selectedTables.forEach((tableName, index) => {
            selectedFields = selectedFields.concat(getSelectedFieldsFromTabulator(`#datatable${index + 1}`, tableName));
            if (index < selectedTableCount - 1) {
                // Collect join type information
                let joinType = getSelectValue(`joinTypeDropdown${index + 1}`);
                joinTypes.push(joinType);
            }
        });
    }



    // Generate SQL query
    let sql = generateSQLQuery(selectedFields, selectedTables, joinTypes);



    // Format the SQL before displaying it
    // Display formatted SQL query
    document.getElementById('code').value = formatSql(sql);
}

/**
 * Gets the value of a select element by its ID. Returns the text of the selected option
 * if the value is an integer, or the value itself if it's not. Ignores certain placeholder values.
 *
 * @param {string} id - The ID of the select element.
 * @return {string|null} The text of the selected option or the value itself, or null if the value is a placeholder.
 */
function getSelectValue(id) {
    let selectElement = document.getElementById(id);
    if (selectElement) {
        let value = selectElement.value;
        const placeholderValues = ["Select table...","Select a related table..."]; // Placeholder values to ignore

        // Check if the value is an integer
        if (isNaN(value)) {
            if (placeholderValues.includes(value)) {
                return null; // Ignore placeholder values if value is a string
            }
            return value; // Return the text value if it's not a placeholder
        } else {
            // For integer values, get the text of the selected option
            let selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption) {
                if (placeholderValues.includes(selectedOption.text)) {
                    return null; // Ignore placeholder values if option text is a placeholder
                }
                return selectedOption.text; // Return the text of the selected option
            }
        }
    }
    return null;
}

/**
 * Retrieves an array of selected field names from a Tabulator table.
 *
 * @param {string} selector - The CSS selector used to locate the Tabulator table.
 * @param {string} tableName - The name of the table to prefix the field names with.
 * @return {string[]} - An array of selected field names, prefixed with the table name.
 */
function getSelectedFieldsFromTabulator(selector, tableName) {
    let table = Tabulator.findTable(selector)[0];
    if (!table) return [];

    // Get selected rows
    let selectedRows = table.getSelectedData();

    // Extract qualified field names
    return selectedRows.map(row => `${tableName}.${row.field}`);
}

/**
 * Generates an SQL query based on the provided fields, tables, and join types.
 *
 * @param {string[]} selectedFields - An array of strings representing the fields to select.
 * @param {string[]} selectedTables - An array of strings representing the tables to select from.
 * @param {string[]} joinTypes - An array of strings representing the types of joins to use.
 * @return {string} The generated SQL query or an error message if no join condition found.
 */
function generateSQLQuery(selectedFields, selectedTables, joinTypes) {
    if (selectedTables.length === 0) return "No tables selected";

    let sql = 'SELECT ' + selectedFields.join(', ') + ' FROM ' + selectedTables[0];

    for (let i = 1; i < selectedTables.length; i++) {
        let joinCondition = getJoinCondition(selectedTables[i - 1], selectedTables[i]);
        if (joinCondition) {
            sql += ` ${joinTypes[i - 1]} ${selectedTables[i]} ON ${joinCondition}`;
        } else {
            return `No join condition found between ${selectedTables[i - 1]} and ${selectedTables[i]}`;
        }
    }

    return sql;
}

/**
 * Constructs a join condition string based on the relationship between two tables.
 *
 * @param {string} table1 - The name of the first table.
 * @param {string} table2 - The name of the second table.
 * @return {string|null} A join condition string if a relationship is found, otherwise null.
 */
function getJoinCondition(table1, table2) {
    for (let rel of window.relatedTableData) {
        if (rel.TABLE_NAME === table1 && rel.REFERENCED_TABLE_NAME === table2) {
            return `${table1}.${rel.COLUMN_NAME} = ${table2}.${rel.REFERENCED_COLUMN_NAME}`;
        }
        if (rel.REFERENCED_TABLE_NAME === table1 && rel.TABLE_NAME === table2) {
            return `${table1}.${rel.REFERENCED_COLUMN_NAME} = ${table2}.${rel.COLUMN_NAME}`;
        }
    }
    return null;
}

/**
 * Formats a given SQL query string to improve readability.
 *
 * @param {string} sql - The SQL query string that needs to be formatted.
 * @return {string} The formatted SQL query with appropriate line breaks and indentations.
 */
function formatSql(sql) {
    // Define the keywords to split the SQL statement
    const keywords = [
        "SELECT", "FROM", "WHERE", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN",
        "FULL JOIN", "JOIN", "ON", "ORDER BY", "GROUP BY", "HAVING", "LIMIT"
    ];

    // Escape keywords for use in a regular expression
    const regexKeywords = keywords.map(keyword => `\\b${keyword}\\b`).join("|");

    // Split the SQL by these keywords and preserver the keyword in the resulting array
    const formattedSqlArray = sql.split(new RegExp(`(${regexKeywords})`, 'ig'));

    // Rebuild the SQL array with proper indentation and line breaks
    let formattedSql = formattedSqlArray.map((part, index) => {
        // Add indentation based on the keyword
        if (keywords.includes(part.trim().toUpperCase())) {
            if (index > 0) return `\n${part.trim()}`; // Newline for the keyword
            return part.trim();  // No newline for the first keyword
        }
        // Indentation for fields and conditions
        return part.replace(/,/g, ',\n    ').trim();
    }).join(' ');

    return formattedSql;
}

/**
 * Executes an SQL query by sending a POST request to a specified URL and displays the results in a Tabulator table.
 *
 * The method retrieves the base URL from a data attribute in the document body and the SQL input from a specified input field.
 * It then constructs the target URL for the POST request, sends the request with the SQL code, and upon receiving a response,
 * parses the data and initializes a Tabulator table with the fetched data.
 *
 * @return {void}
 */
function runSql() {
    const baseUrl = document.body.getAttribute('data-base-url');
    const codeInput = document.getElementById('code').value;

    const targetUrl = baseUrl + 'vtlgen/createsqlGetDataFromSuppliedSql';

    // Create a new XMLHttpRequest object
    const xhr = new XMLHttpRequest();

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
            const responseData = JSON.parse(xhr.responseText); // parse the response data

            // Create Tabulator only if we received valid data
            if (responseData && responseData.data) {
                // Destroy the existing Tabulator instance if it exists
                if (window.table2) {
                    window.table2.destroy();
                }
                // Create a new Tabulator instance and store it in window.table2
                window.table2 = new Tabulator("#datatable5", {
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

/**
 * Prompts the user to enter a name for an SQL query and then saves the query
 * by sending it to the server. Displays a success or error message upon completion.
 *
 * @return {void}
 */
function saveSql() {
    const queryName = prompt("Enter a name for your SQL query:");

    if (queryName) {
        const codeInput = document.getElementById('code').value;
        const baseUrl = document.body.getAttribute('data-base-url');
        const targetUrl = baseUrl + 'vtlgen/createsqlSaveEndUserCreatedQuery';

        const xhr = new XMLHttpRequest();

        xhr.open('POST', targetUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.send(JSON.stringify({ name: queryName, sql: codeInput }));

        xhr.onload = function() {
            let responseMessage = "";
            if (xhr.status === 200 && xhr.responseText) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    responseMessage = response.message;
                    openVtlModal('SQL Script Saved', true, responseMessage); // Call the globally defined function
                } else {
                    responseMessage = response.message;
                    openVtlModal('Error Saving SQL', false, responseMessage); // Call the globally defined function
                }
            } else {
                responseMessage = xhr.responseText || "An unknown error occurred.";
                openVtlModal('Error Saving SQL', false, responseMessage); // Call the globally defined function
            }
        };
    } else {
        openVtlModal('Error Saving SQL', false, 'Please enter a name for your query'); // Call the globally defined function
    }

}


