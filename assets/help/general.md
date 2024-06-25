The Vtl Data Generator is a module designed to help you with everyday database administrative tsaks that should make your development of applications with the Trongate Framework a bit easier.

> <br>
> <b>It is designed to be used by Administrators only and only when the ENV configuration setting (found in the
> config.php file in the config folder of Trongate itself) is set to 'dev'. Any other setting OR a non administrative user
> will result in a fallback to the main welcome page.</b>


<br>

The module has four key areas of operation and a set of comprehensive help files to assist you with understanding how it all works and can be tweaked to your own liking.

<div>
<ul>
     <li><a href="#datageneration">Data Generation and Visualisation</a></li>
        <ul>
            <li><a href="#createrecords">Create Records</a></li>
            <li><a href="#deleterecords">Delete Records</a></li>
             <li><a href="#browse">Browse Data</a></li>
        </ul>
    <li><a href="#indexops">Index Operations</a></li>
        <ul>
            <li><a href="#createindex">Create Index</a></li>
            <li><a href="#deleteindex">Delete Index</a></li>
             <li><a href="#browseindex">Browse Indexes</a></li>
        </ul>
    <li><a href="#keyops">Foreign and Primary Key Operations</a></li>
        <ul>
            <li><a href="#createkey">Create Foreign Key</a></li>
            <li><a href="#dropkey">Drop Foreign Key</a></li>
             <li><a href="#browseforeign">Browse Foreign Keys</a></li>
            <li><a href="#browseprimary">Browse Primary Keys</a></li>
        </ul>
     <li><a href="#databaseops">Database Operations</a></li>
        <ul>
            <li><a href="#createtable">Create Table</a></li>
            <li><a href="#droptable">Drop Table</a></li>
             <li><a href="#edittable">Edit Table</a></li>
            <li><a href="#export">Export Script</a></li>
        </ul>
     
</ul>
</div>

This latest iteration has involved a complete refactoring, and in some cases rewriting, of the component parts to both make it more efficient and to open the door to some additional functionality that I had wanted to add.  In the process the module has gained a smaller code base which should make integrating a couple of other things I'd like to add possible and keep it withing the maximum allowable size for Trongate modules.

Every function is accessible from the home page which acts as a navigation hub. 

<br>
<div id="datageneration"></div>
### Data Generation and Visualisation


<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlRecordAddDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlRecordAdd.svg">
</picture>
<figcaption>Create Records</figcaption> 
</figure>
</div>

<br>
<div id="createrecords"></div>

- Select the table for which you wish to create fake data from the dropdown.
- From the list of fields in that table that now appears select those for which you need data.
- Enter the number of rows that you would like generated
- Click the 'Generate Fake Data' button.

The Data Generator now batch processes generation which means that you can now generate very large datasets.  

Support is also provided for generating images for those modules that utilise the Trongate Single picture uploader.  The Data Generator looks for a field called 'picture' of type varchar(255) and if it finds one it will add one of 11 pre supplied images to that field. If a table was found to have such a field, and very specifically if the number of rows generated was less than 250, once generation has taken place the Generator will offer to transfer images and create the necessary folders that the single picture uploader would normally create.

<br>

><b>It is important to realise that cannot create absolutely realistic data without some degree of customisation on your part.  There is a separate help file that goes into great detail about how you can customise the faker to produce some very realistic data.  </b>

<br>

An example of this is the way that fake data is created for Trongate Pages.  

Clearly if you go in for extensive customisation then it makes sense for you to keep a customised version of the Data Generator and use it rather that continually downloading an un-customised version from the module market.

<br>

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlRecordRemoveDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlRecordRemove.svg">
</picture>
<figcaption>Delete Records</figcaption> 
</figure>
</div>

<br>
<div id="deleterecords"></div>

- Select the table (or tables) for which you wish to delete data.
- If you wish to reset the auto increment on the table then ensure you check the relevant box.
- Click the Delete Data button.

> Only those tables that actually contain data will be displayed for selection.

<br>

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlBrowseDataDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlBrowseData.svg">
</picture>
<figcaption>Browse Data</figcaption> 
</figure>
</div>

<br>
<div id="browse"></div>

- Select the table from which you wish to browse the data.
- Data is displayed in a paginated table.

Underneath the table displaying the data are three buttons that allow you to download the data contained in the table in either HTML, CSV or Json.

<br>
<div id="indexops"></div>
### Index Operations


<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlIndexAddDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlIndexAdd.svg">
</picture>
<figcaption>Create Index</figcaption> 
</figure>
</div>

<br>
<div id="createindex"></div>

- Select the table for which you wish to create an index from the dropdown.
- All the table's columns (with the exception of the primary key) will then be displayed.
- Select the column upon which you wish to create an index.
- Select the index type you wish to create (Standard or Unique).
- Click the 'Create Index' button.

> If you create a unique index you need to be aware that data you have already created for that column may not comply.  Unique indexes require a dgree of customisation in the way that fake data is created.  See the customisation help topic.

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlIndexRemoveDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlIndexRemove.svg">
</picture>
<figcaption>Drop Index</figcaption> 
</figure>
</div>

<br>
<div id="deleteindex"></div>

- Select the index or indexes that you wish to drop.

> <b> Note that Primary indexes are also displayed, which you can opt to drop (although you will be asked if you want to first). Dropping of Primary indexes is not recommended but there are occasions when it is a viable thing to do. Proceed at your own risk. </b>

- Click the 'Drop Index' button.


<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlIndexBrowseDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlIndexBrowse.svg">
</picture>
<figcaption>Browse Indexes</figcaption> 
</figure>
</div>

<br>
<div id="browseindex"></div>

You can opt to see all of the indexes that are present in the database by choosing this option.  They will be displayed in a table 9which itself will be paginated if necessary).  The option to download the information to HTML, CSV or Json is present if required.

<br>
<div id="keyops"></div>
### Foreign and Primary Key Operations


<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlForeignKeysAddDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlForeignKeysAdd.svg">
</picture>
<figcaption>Create Foreign Key</figcaption> 
</figure>
</div>

<br>
<div id="createkey"></div>

Creating Foreign Keys requires the linking of fields in two tables.  Imagine that there are two tables in the database, orders and orderDetails.  Each orderDetail is linked to a specific order.  This is generally described as an order => orderDetails relationship. An order has one or more related orderDetail items.  In this example orderDetails is the Foreign Key Side of the relationship and orders is the Related To side of the relationship.  Before you beging to create foreign keys you need to be sure that you know which table will be associated with which side.

- On the Foreign Ket side select a table, and then from the list of columns that appears select the column that will be part of the relationship.
- On the Related To side select the table that the foreign side will be related to and then from the list of columns that is shown select the column that will form the other half of the relationship.
- Once you have selected a single column from each table click the 'Create Foreign Key' button.


<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlForeignKeysRemoveDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlForeignKeysRemove.svg">
</picture>
<figcaption>Drop Foreign Key</figcaption> 
</figure>
</div>

<br>
<div id="dropkey"></div>

- From the provided table listing all the foreign keys in the database select those that you wish to drop.
- Click the 'Drop Foreign Key' button.

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlForeignKeysViewDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlForeignKeysView.svg">
</picture>
<figcaption>Browse Foreign Keys</figcaption> 
</figure>
</div>

<br>
<div id="browseforeign"></div>

From this table you can view all of the foreign keys that are present in the database.  There is the option to export the information to HTML, CSV or Json.

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlPrimaryKeysDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlPrimaryKeys.svg">
</picture>
<figcaption>Browse Primary Keys</figcaption> 
</figure>
</div>

<br>
<div id="browseprimary"></div>

From this table you can see all of the database table's primary keys, and the value of the latest primary key for each table.  This information can be dowloaded to HTML, CSV or Json.

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlTableAddDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlTableAdd.svg">
</picture>
<figcaption>Create Table</figcaption> 
</figure>
</div>

<br>
<div id="createtable"></div>

At first sight the Vtl Data Generator Create Table form may seem complicated but once you become familiar with it it is pretty simple to use and provides a lot of useful functionality, especially if you are someone who decides to customise the Generator and reuse it across projects.

Start by entering the name of the table that you want to create. By convention table names are usually plural.

Then add the details for each column.

#### Field Name 

This is the name you want for the column / field that you are adding.

#### Data Type

This is the data Type for the column.  This is a list box of data types (shown below).

```js
                            "autoincrement": "Autoincrement",
                            "varchar": "Varchar",
                            "varchar(10)": "Varchar(10)",
                            "varchar(15)": "Varchar(15)",
                            "varchar(25)": "Varchar(25)",
                            "varchar(32)": "Varchar(32)",
                            "varchar(50)": "Varchar(50)",
                            "varchar(75)": "Varchar(75)",
                            "varchar(100)": "Varchar(100)",
                            "varchar(255)": "Varchar(255)",
                            "text": "Text",
                            "int": "Int",
                            "int(11)": "Int(11)",
                            "tinyint": "Tinyint",
                            "bigint": "Bigint",
                            "decimal": "Decimal",
                            "float": "Float",
                            "double": "Double",
                            "boolean": "Boolean",
                            "date": "Date",
                            "datetime": "Datetime",
                            "time": "Time",
                            "timestamp": "Timestamp",
                            "char": "Char",
                            "binary": "Binary",
                            "varbinary": "Varbinary",
                            "blob": "Blob",
                            "uuid": "Uuid"
```

This covers the majority of common data types used in MySql and or MariaDb.

> <b> Note the autoincrement datatype.  Technically no such data type exists but this has been added specifically to make it easy for you to set up auto incrementing columns.  At the point at which the relevant SQL statement is created it will be substituted for an integer data type.</b>

#### Nullable

You can decide whether a column should allow null values or not, by default null values will be allowed.

#### Default Value

You may opt for a column to add a defaulyt value.  By default the following data types will set default values (which you can override).

```js
 switch (column.default) {
                        case 'CURRENT_TIMESTAMP':
                            columnDef += ' DEFAULT CURRENT_TIMESTAMP';
                            break;
                        case 'CURRENT_DATE':
                            columnDef += ' DEFAULT CURRENT_DATE';
                            break;
                        case 'CURRENT_TIME':
                            columnDef += ' DEFAULT CURRENT_TIME';
                            break;
                        case 'UTC_TIMESTAMP':
                            columnDef += ' DEFAULT UTC_TIMESTAMP';
                            break;
                        case 'UNIX_TIMESTAMP':
                            columnDef += ' DEFAULT UNIX_TIMESTAMP';
                            break;
                        case 'UUID()':
                            columnDef += ' DEFAULT UUID()';
                            break;
```

#### Primary Key

Select this if the column is to be the primary key, or form part of the primary key.

#### Unique

Select this if the column should only contain unique values.

#### Delete Row

The final column in the data entry table allows you to delete rows.

<br>

Navigating through the data entry table can be done entirely from the keyboard, predominantly with the tab key but also the up and down arrows and the space bar.  If you are familiar with data entry tables them you should have no issues navigating around it.

As each row in the data entry table gets completed so the sql statement is updated (you can always click the 'Generate Sql Statement' for an immediate look at how it is).

Once you are ready to actually generate the table then click the 'Generate Table' button.

There are two other buttons that provide you with some additional functionality.  You can opt to save the Sql statement that you have created which will easily allow you to create a library of tables, and you can opt to create a table from and existing sql query.  This effectively allows you to create a reusable library of ready made table creation statements.

<div class = "text-center">
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/vtlTableRemoveDark.svg" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/vtlTableRemove.svg">
</picture>
<figcaption>Drop Tables</figcaption> 
</figure>
</div>

<br>
<div id="droptable"></div>

- Select the table, or tables that you want to drop from the database and then click the 'Drop Table' button.  Tables that form part of existing table relationships will not be dropped.