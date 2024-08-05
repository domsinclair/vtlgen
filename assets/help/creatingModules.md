The Vtl Data Generator has a comprehensive Module Generator that is capable of creating a fully functional, crud enabled module that has a comprehensive search facility and validation.

This addition now gives the end user the necessary tools to create a functional line of business application from within a basic Trongate application.

## Adding a new module to you application

<br>

> <b>The Data Generator uses information that it can obtain from an existing table in order to generate a module.
> 
> It follows therefore that you will need to create a table before you can create a module</b>

<br>

Begin the process by creating a new table or using an existing SQL Create Table query if you have one.  The Data Generator has its own visual Table builder which should enable you to create the table structure that you want.

<br>

> If you want to make use of the Trongate picture uploader then you should create a field called 'picture' of type varchar.


<br>

Once your table has been created then the next stage is to create a module for it.  Use the Create Module facility to do this selecting the table you have just created as the table upon which the module will be based.

###  What happens next...

The Module creation process is almos instantaneous but a graet deal happens and knowing what happens will help you customise the finished article.  

The process begins by executing a Show Columns query on the table in question which returns a dataset containing comprehensive information about the columns in the selected table.

That information is then used to provide the information that the new module controller will need to pass on to the various views that it will create and the backend functions that will enable CRUD management.

The following example comes from a controller in a test Module that was created by the Data Generator.

<br>

```php
<?php
class Customers extends Trongate {

    private $default_limit = 20;

    private $per_page_options = array(10, 20, 50, 100);

    private $columns = [];

    private $validationRules = [];

   public function __construct() {
       parent::__construct();
       $this->columns = json_decode('[{"Field":"customerId","Type":"int(11)","Null":"NO","Key":"PRI","Default":null,"Extra":"auto_increment"},{"Field":"customerId","Type":"int(11)","Null":"NO","Key":"PRI","Default":null,"Extra":"auto_increment"},{"Field":"firstName","Type":"varchar(50)","Null":"YES","Key":"","Default":null,"Extra":""},{"Field":"lastName","Type":"varchar(50)","Null":"YES","Key":"","Default":null,"Extra":""},{"Field":"createdOn","Type":"date","Null":"NO","Key":"","Default":null,"Extra":""},{"Field":"active","Type":"tinyint(4)","Null":"NO","Key":"","Default":null,"Extra":""},{"Field":"picture","Type":"varchar(50)","Null":"YES","Key":"","Default":null,"Extra":""}]', true);
        $this->validationRules = json_decode('{"firstName":"max_length[50]","lastName":"max_length[50]","createdOn":"required","active":"required|numeric","picture":"max_length[50]"}', true);

   }
```

<br>

There are a some interesting points to note about this.

1) The module is setup to make use of Trongate's pagination.
2) The module needs to know about the columns in the table (hence the $columns variable).
3) It needs to know what validation rules will be required (hence the $validationRules variable)
4) Those variables need to be instantiated hence the need for a constructor.

This is a little different to what you may be used to but it's necessary because of the way that the module is automatically created without any intervention on your part.

The validation rules are created by the following function in the Data Generator's controller.

<br>

```php
 private function createValidationRules($columnInfo) {
        $validationRules = [];

        foreach ($columnInfo as $column) {
            $field = $column['Field'];
            $type = $column['Type'];
            $null = $column['Null'];
            $rules = [];

            // Ignore primary key fields
            if (isset($column['Key']) && $column['Key'] === 'PRI') {
                continue;
            }

            // Add 'required' rule if the field cannot be null
            if ($null === 'NO') {
                $rules[] = 'required';
            }

            // Add 'valid_email' rule if the field name contains 'email'
            if (stripos($field, 'email') !== false) {
                $rules[] = 'valid_email';
            }

            // Add password rules if the field name contains 'password'
            if (stripos($field, 'password') !== false) {
                $rules[] = 'min_length[8]'; // Example: Minimum length of 8 characters
            }

            // Add numeric rules if the type is int or tinyint
            if (preg_match('/^int|tinyint/i', $type)) {
                $rules[] = 'numeric';
            }

            // Add max_length rule if the type is varchar
            if (preg_match('/^varchar\((\d+)\)/i', $type, $matches)) {
                $rules[] = 'max_length[' . (int) $matches[1] . ']';
            }

            // Add the rules to the validation array if any rules exist for the field
            if (!empty($rules)) {
                $validationRules[$field] = implode('|', $rules);
            }
        }

        return $validationRules;
    }
```

<br>

As you can see it should do a reasonable job of creating a set of basic validation rules that match you current table structure.

In order to create a working search function for the 'manage' view it was necessary to actively allow the end user to select the field they want to search on, the search operator that they want applied and then obviously provide an input box to enter the searched for value.

Both of these features are comprehensive and work surprisingly well out of the box.

In addition to the controller and views the module will have an assets folder with blank JS and css files, and an images folder. If a picture field is detected it will also create the necessary folder structure to support the Trongate picture uploader.

<br>

## Adding Data

Once the module has been created you'll probably want to add some data.  Just head to the create data section, chose the table to add data to, select the fields you want data created for and the number of rows you want.

> If you have a picture field it is suggested that you limit the number of rows created to less than 150.  This will trigger the generator to offer to transfer image files to the module assets folder, ensuring that there are some images that you can use in the management and show views.

<br>

The speed of generation is quite remarable and you can generate a million rows of data in a very short time.


### Easy Table and Module Editing

There are bound to be occasions when it becomes apparent that your table isn't quite sufficient for your needs, requiring an additional field or two.

This is where the Data Generator really comes into its own.  Every function in the Generator is reversible.  This means that you can you can very easiliy do the following.

1) Delete the existing data from the table
2) Delete the module.
3) Edit the table to reflect the changes you meed to make.
4) Recreate the module (which will be done based on your new table structure).
5) Add new data to the module.

This is a fantastic way to experiment during the course of you application development and the whole procedure is phenomenally fast.

