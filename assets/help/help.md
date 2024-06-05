Vtl Gen is a module designed to assist you with everyday database administrative tasks that should make your development
work easier.

<br>
> <b>It is designed to be used by Administrators only and only when the ENV configuration setting (found in the
> config.php file in the config folder of Trongate itself) is set to 'dev'. Any other setting OR a non administrative user
> will result in a fallback to the main welcome page.</b>


<br>

It will allow you to create and delete data, create and delete indexes and generate an export script when it comes time
to move your project into production.
If this is the first time that you've used the Vtl Data Generator then please take some time and read through the help
here to acquaint yourself with what the module does and how it does it.

Once you are familiar with the way the module works you can just jump to whichever function you wish to employ from the
navigation buttons at the top of the page.

#### Light andDark Mode Support

Vtl Data Generator has its own css stylesheet that makes use of the css
property ```@media (prefers-color-scheme: dark)``` which means that it should automatically respect your own system
preference for light or dark theme.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/introDark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/intro.png">
</picture>
<figcaption>Vtl Data Generator supports Light and Dark Mode</figcaption> 
</figure>
</div>

#### Issues

If you find an issue with the VTL Data Generator then please report
it [here](https://github.com/domsinclair/vtlgen/issues). Items reported on github will automatically generate an email
to me and as such you're likely to get a faster response. General questions can of course be raised on the Trongate Help
Bar.

Please bear in mind that it's not always possible to respond immediately.

#### Disclaimer

The free version of this is supplied as is with no implied warranty. The code is open source and you are free to do with
it as you wish. Leaving this module in a production site could seriously compromise data integrity. You have been
warned!

### Creating Data

Vtl Data Generator makes use of the FakerPhp library. To generate data follow these steps

- Start at the Vtl Data Generator home page and click the create button under the Data section in the navigation.
- Select a table for which you wish create data from the drop down.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata1.png">
</picture>
<figcaption>Use the Select Table dropdown to choose a table</figcaption> 
</figure>
</div>

<br>

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata2Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata2.png">
</picture>
<figcaption>Pick the table you want to create data for</figcaption> 
</figure>
</div>

- Once you have selected your table details about all of its columns will be shown.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata3Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata3.png">
</picture>
<figcaption>All of the table's available column details</figcaption> 
</figure>
</div>

- Select one or all of the fields for which you want data and then enter the number of rows you want to generate.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata4Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata4.png">
</picture>
<figcaption>Select one or all columns for data generation</figcaption> 
</figure>
</div>

<br>

> Note the last field in the illustration is picture of type varchar(255). The orders module had a single image uploader
> added to it via the Trongate Desktop app and that field was added automatically.

At the point that the single image uploader is added to the app only the required field is added to the database no
storage location is created by default. Vtl Data Generator understands the rules for the storage and creates it for you
automatically when it realises that you have selected a table that has a picture field in it.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata6.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata6.png">
</picture>
<figcaption>No default storage is create by desktop app for image uploader</figcaption> 
</figure>
</div>

<br>

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata7.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata7.png">
</picture>
<figcaption>Default storage is automatically created for you</figcaption> 
</figure>
</div>

<br>

- With your chosen fields selected and number of rows required set click the Generate Fake Data button.
- Once generation is complete a dialog will inform you of success.
- If the presence of a picture field was detected you'll now see a Transfer Images button.

> Vtl Data Generator ships with its own set of dummy images  (in standard and thumbnail sizes). When you invoke the
> Transfer Images function it will transfer those dummies to the chosen module's image storage creating all the required
> folders as it does so. This process could be potentially time consuming so a progress bar will be displayed.

- Click the Transfer Images button.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata8Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata8.png">
</picture>
<figcaption>Transferring Images</figcaption> 
</figure>
</div>

<br>
This will create all the requires storage folders.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata9.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata9.png">
</picture>
<figcaption>Newly Created folders for image storage</figcaption> 
</figure>
</div>

<br>

That completes the process. Now we can examine the data that has been created.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata10.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata10.png">
</picture>
<figcaption>Generated data</figcaption> 
</figure>
</div>

<br>

When we examine an individual record we can see that it also has an image.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createdata11.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createdata11.png">
</picture>
<figcaption>Individual record illustrating successful image transfer</figcaption> 
</figure>
</div>

<br>

#### How is the data generated

vtlgen generates data from either the field name or the data type.

By definition this is an imprecise science in as much as it necessitates having solutions for common field names and all
the likely data types. You can add your own additions to the relevant functions (both found in the Vtl_faker.php
controller). However, a more practical solution would probably be to submit requests for additional option to the
module's GitHub repo. By doing that if they warrant inclusion then they'll be available to all via the Trongate Module
repository.

#### Can I customise the way data is generated

The short answer is yes.

For a more detailed answer see the separate help topic on the subject.

<br/>

#### Inserting the Data

vtlgen checks to see if the table that you have selected to have data generated for has an api associated with it. If
it does then it uses that to insert records. In the event that an api is not present for that table then it will
fall back to traditional Sql statements tio insert data.

In the event that you opt to insert a large number of records into a table (generally 2000 or more) then a sql
transaction will be employed rather than the api batch insert.

Once the operation has been completed a modal popup will inform you of the results.

#### The Inserted Data is not very Realistic

The first thing that you should do in this instance is to check that you have correctly set the FAKER_LOCALE to your own
locale. That will greatly improve things like address and name generation.

If the generator is able to use field names as the key to generation your end results will be better, falling back to
type is not ideal, but at least it does provide some data to work with.

It is possible to create very realistic data but to do so involves a great deal of customisation.

#### Images

The Vtl Data Generator ships with a number of images (all of one of my dogs as it happens) that will be used by the
generator to add image names to your data tables.
There are some important caveats to this. The first is that , currently at least, only the following field names will
end up with a valid image name in the database.

- Picture
- Picture Url
- Product Image
- Product Image Url
- Image
- Image Url

> <b> Remember that when working with field names the generator will convert everything to lowercase and strip out all spaces and underscores.</b>

#### Current Limitations

The biggest issue faced at present is with some, but not all, of the basic tables that form part of a basic Trongate
setup. Specifically issues revolve around Trongate security that I have yet to properly work out because you may well
need to refer to those tables from your own tables.

My current thoughts are that maybe a predefined number of users and associate levels should be created but I'm still
undecided on that point.

### Showing Data from the Application Tables

This facility is provided so that you can quickly look at existing data in all of the tables that reside in your
application's database.

- Click on the 'Show' button in the data section of the main navigation

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/showdata1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/showdata1.png">
</picture>
<figcaption>Select a table to show its data</figcaption> 
</figure>
</div>

- As soon as you have selected a table its data will be displayed in a seperate view.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/showdata2Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/showdata2.png">
</picture>
<figcaption>Table data displayed</figcaption> 
</figure>
</div>

If there is no data in the table to be displayed an appropriate message to that effect will be shown.

### Deleting Data

Deleting data is a pretty straightforward operation.

Simply select those tables for which you wish to delete data and then click the 'Clear Data' button. You will then see a
modal detailing what has happened.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/deletedata1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/deletedata1.png">
</picture>
<figcaption>Select the table or tables from which to delete data</figcaption> 
</figure>
</div>



> By default, the following tables will have all but the first row of data deleted so as to retain a default Trongate
> installation;
>  - trongate_administrators
>  - trongate_users
>  - trongate_user_levels

You can also opt to delete data from a table and reset the auto increment. To trigger that option you should check the '
Reset Primary Auto Increment' at the bottom of the list of tables. This invokes a Truncate command dropping and
resetting the table. It is a much quicker way to delete large tables. Because of the fact that you may need to retain
certain Trongate security information this option will not be applied to the following tables;

- trongate_administrators
- trongate_users
- trongate_user_levels

Images that have been added to folders that follow the naming conventions employed by the desktop app when adding a
single image uploader will also be deleted along with the folders that contained them. The main repository folders will
not be deleted.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/deletedata2.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/deletedata2.png">
</picture>
<figcaption>Image storage returned to pristine state</figcaption> 
</figure>
</div>

### Creating Indexes

Indexes on database columns can be extremely beneficial on those columns that are frequently searched. They do however
exact a price, insertion takes longer because of the need to keep the index up to date, and they can consume resources.
However, the benefits that they bring to searches make them worthwhile..

#### To create an Index

- Select the table for which you wish to create an index.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createindex1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createindex1.png">
</picture>
<figcaption>Select the table on which to create an index</figcaption> 
</figure>
</div>

- Select the column(s) you wish to index.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/createindex2Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/createindex2.png">
</picture>
<figcaption>Select the table on which to create an index</figcaption> 
</figure>
</div>

- Click 'Create Index'.

Indexes will be named automatically for you and will follow this pattern;

idx_table name_column name

### Deleting Indexes

You can delete indexes that have been created by this module. to do so head to the delete indexes view and select the
table you're interested in. If there are any indexes on it created by this module then they will be shown in a table,
and
you will have the option to select and then delete them. If only the Primary key index is there you will be informed as
such but will not be given the option to delete it.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/deleteindex1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/deleteindex1.png">
</picture>
<figcaption>Select a table to see custom indexes </figcaption> 
</figure>
</div>


<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/deleteindex2Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/deleteindex2.png">
</picture>
<figcaption>Select the index or indexes to delete</figcaption> 
</figure>
</div>


<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/deleteindex3Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/deleteindex3.png">
</picture>
<figcaption>This table has no custom indexes</figcaption> 
</figure>
</div>

### Exporting a Database Script

Vtl Data Generator also includes the following library mysqldump.php which allows you to run a mysqldump from php.

To export the database select those tables that you wish to export by checking the relevant boxes on the lefthand side
of the table. If you wish to export the data from the tables then check the relevant box io the right hand side.

The export process is highly configurable so to avoid too much only the most common settings have been applied. However
if you look at the export code in the module and consult the readme for the mysqldump.php file you will be able to see
what you can configure.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/exportdata1Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/exportdata1.png">
</picture>
<figcaption>Select the tables for which you require an export script</figcaption> 
</figure>
</div>


> Backups will be stored in the following location
> vtlgen/vtl_faker/assets/backups   
> If that folder does not exist it will be created for you.


<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/exportdata2.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/exportdata2.png">
</picture>
<figcaption>The newly created directory and backup script</figcaption> 
</figure>
</div>