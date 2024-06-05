The Vtl Data Generator strives to automate as much of the process of creating fake data as it is possible to do but by
definition it's unlikely to be perfect out of the box.

The very first thing that you can do before generating any data at all is to customise the Data Generator config.php
file which you can find in the vtl_faker assets folder.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/customise1.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/customise1.png">
</picture>
<figcaption>Location of the Vtl Data Generator config file</figcaption> 
</figure>
</div>

The file contains the following by default.

```php
define('FAKER_LOCALE', 'en_GB');
define('FAKER_SEED', '13579');
define('FAKER_DATE_FORMAT', 'Y-m-d');
define('FAKER_DATETIME_FORMAT', 'Y-m-d H:i:s');
```

Setting your locale will immediately improve the quality of data generated to make it more appropriate to your region.

The Date and DateTime formats can also be set and these will affect the way that the data is stored in the database. By
default a generally accepted norm for these values has been applied.

Lastly you can alter the seed value if you wish. The purpose behind seeding the Faker Generator is to ensure that
similar records are created on each run. Being able to predict that a record with a primary key of 'x' will contain
columns with specific values can be extremely beneficial when it comes to testing.

The VTL Data Generator has implemented seeding by default but it can be very easily disabled by commenting out the
relevant line in the createFakes method in the vtl_faker.php controller.

```php
 public function createFakes(): void
    {
        // Initialize Faker instance
        $faker = null;
        $faker = $this->$faker;
        // Seed the faker.  This will ensure that the same data gets recreated
        // which can be useful for testing purposes. 
        // Comment out the line below if you don't want to use a seeded faker.
        $faker->seed(FAKER_SEED);
```

#### The basic data Creation process

As soon as you click the Generate Fakes the Data Generator determines the number of rows that it needs to generate and
for each of those rows it loops through the fields of the table that were selected for data generation.
The generator notes the field name and type. Initially it will try and find a match in
the ```generateValueFromFieldName()``` method and failing that it will revert to the ```generateValueFromType()```
method.

Approximately 70 different potential field names have been catered for but clearly it cannot be an exhaustive list so in
the event that a match isn't found it will resort to using the field's type. All mySql / Mariadb types have been catered
for so by then a match should have been made.

If the only thing of importance is to create a given number of records then the data that gets created will almost be
suitable for most purposes, however there will be occasions when it becomes important to have much more realistic data.

## Creating more realistic Data

FakerPhp creates data with the help of formatters.

At the bottom of this page there is a comprehensive list of the formatters that are available with examples (this
information is a facsimile copy of the FakerPhp docs) as well as the custom formatters provided by the VTL Data
Generator itself. There are quick links to them below.




<div>
 <h3>Standard Formatters</h3>

        <li><a href="#barcode">Barcode</a></li>
        <li><a href="#biased">Biased</a></li>
        <li><a href="#colour">Colour</a></li>
        <li><a href="#dateandtime">Date and Time</a></li>
        <li><a href="#file">File</a></li>
        <li><a href="#htmllorem">Html-Lorem</a></li>
        <li><a href="#image">Image</a></li>
        <li><a href="#internet">Internet</a></li>
        <li><a href="#miscellaneous">Miscellaneous</a></li>
        <li><a href="#numbersandstrings">Numbers and Strings</a></li>
        <li><a href="#payment">Payment</a></li>
        <li><a href="#textandparagraphs">Text and Paragraphs</a></li>
        <li><a href="#useragent">User-Agent</a></li>
        <li><a href="#uuid">Uuid</a></li>
        <li><a href="#version">Version</a></li>

<h3>Custom Formatters Provided By The Data Generator</h3>

        <li><a href="#blog">Blog</a></li>
        <li><a href="#commerce">Commerce</a></li>

</div>

It is also possible to add to the custom locale providers that come with FakerPhp. There are a whole set of customised
locale provider files in the Faker Vendor directory that is supplied with the Vtl Data Directory and you can see an
example of one customisation file that we added to get more realistic English text.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/customise2.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/customise2.png">
</picture>
<figcaption>Custom Locale Provider</figcaption> 
</figure>
</div>

In addition to simply adding customisations of existing providers to specific locales it is also possible to create your
custom provider from scratch. To illustrate the point the following section goes over the creation of a custom commerce
provider that is included in the Vtl Data Generator. It should provide sufficient detail for you to be able to create
your own custom provider(s).

### Creating a custom commerce provider

One common requirement in fake data is to have reasonably realist data to represent elements of the commercial world.
Out of the box FakerPhp does not really have anything suitable so the goal of this exercise is to show how a suitable
provider was created and hopefully it should give you the confidence to try it out for yourself.

Begin by creating a new file called called Commerce,php in the Provider directory that is located in the
vtl_faker/assets/vendor/fakerphp/faker/src/Faker folder and ensure that its basic layout is as follows.

```php
<?php

namespace Faker\Provider;


class Commerce extends Base
{

    
}
```

It would be nice to have something realistic for things like Product Names, Departments and Categories to enable the
creation of realistic data for things like e commerce applications.

It is necessary to actually add the various items that will eventually be used by FakerPhp so these will be some of the
items that we will eventually see.

```php
<?php
namespace Faker\Provider;
class Commerce extends Base
{
    protected static $department = [
        "Sales", "Marketing", "Human Resources", "Finance", "Operations", "Customer Service",
        "Research and Development", "Information Technology", "Product Management", "Supply Chain Management",
        "Quality Assurance", "Legal", "Public Relations", "Business Development", "Accounting",
        "Administration", "Purchasing", "Training and Development", "Corporate Communications",
        "Strategic Planning", "Facilities Management", "Risk Management", "Internal Audit", "Project Management",
        "Creative Services", "E-commerce", "Vendor Management", "Logistics", "Regulatory Compliance",
        "Sustainability", "Market Research", "Brand Management", "Procurement", "Community Relations",
        "Employee Relations", "Data Analytics", "Event Planning", "Health and Safety", "Investor Relations",
        "Merchandising", "Social Media", "Talent Acquisition", "Training and Development",
        "Warehouse Management", "Strategic Partnerships", "Corporate Development"
    ];

    protected static $categories = [
        "Electronics", "Apparel", "Home & Kitchen", "Beauty & Personal Care", "Books", "Toys & Games",
        "Health & Wellness", "Sports & Outdoors", "Automotive", "Grocery & Gourmet Food", "Pet Supplies",
        "Office Products", "Tools & Home Improvement", "Baby Products", "Jewelry", "Electrical & Lighting",
        "Furniture", "Arts & Crafts", "Industrial & Scientific", "Musical Instruments", "Software",
        "Mobile Accessories", "Outdoor Living", "Luggage & Travel Gear", "Camera & Photo", "Party Supplies",
        "Food & Beverage", "Garden & Outdoor", "Electrical Equipment", "Medical Supplies", "Home Improvement",
        "Computer Accessories", "School & Office Supplies", "Building Materials", "Kitchen & Dining",
        "Electrical Components", "Automotive Parts", "Cleaning Supplies", "Craft Supplies", "Patio & Garden",
        "Personal Care", "Travel Accessories", "Home Decor", "Storage & Organization", "Home Appliances",
        "Electrical Appliances", "Construction Materials", "Building Supplies", "Art Supplies"
    ];

    protected static $productName = [
        'adjective' => ['Small', 'Rustic', 'Ergonomic', 'Modern', 'Vintage', 'Sleek', 'Compact', 'Stylish', 'Luxurious',
            'Efficient', 'Durable', 'Versatile', 'Innovative', 'Premium', 'Customizable', 'Handcrafted',
            'Chic', 'Minimalist', 'Practical', 'Elegant', 'Contemporary', 'Sophisticated', 'Robust', 'Trendy',
            'Futuristic', 'Affordable', 'Unique', 'Functional', 'Fashionable', 'Timeless', 'High-quality', 'Exclusive',
            'Glamorous', 'Eclectic', 'Dynamic', 'Creative', 'Adaptable', 'Refined', 'Sustainable', 'Artisan',
            'Compact', 'Vibrant', 'Cosy', 'Gorgeous', 'Whimsical', 'Charming', 'Fresh', 'Cozy', 'Soothing'
        ],
        'material' => ['Wood', 'Paper', 'Metal', 'Glass', 'Leather', 'Plastic', 'Fabric', 'Ceramic', 'Stone', 'Bamboo',
            'Acrylic', 'Canvas', 'Rubber', 'Stainless Steel', 'Aluminum', 'Carbon Fiber', 'Wicker', 'Velvet', 'Suede',
            'Granite', 'Marble', 'Brass', 'Bronze', 'Copper', 'Porcelain', 'Silicone', 'Linen', 'Velour', 'Mahogany',
            'Pine', 'Oak', 'Maple', 'Teak', 'Birch', 'Cherry Wood', 'Walnut', 'Cotton', 'Silk', 'Wool', 'Polyester',
            'Fur', 'Fiberglass', 'Vinyl', 'Laminate', 'MDF (Medium-Density Fiberboard)', 'HDPE (High-Density Polyethylene)', 'PVC (Polyvinyl Chloride)'
        ],
        'product' => ['Chair', 'Car', 'Pants', 'Plate', 'Table', 'Phone', 'Watch', 'Shoes', 'Bag', 'Lamp', 'Camera', 'Book',
            'Speaker', 'Knife', 'Sofa', 'Hat', 'Bike', 'Glasses', 'Headphones', 'Guitar', 'Ring', 'Wallet', 'Scarf',
            'Monitor', 'Printer', 'Painting', 'Earrings', 'Backpack', 'Desk', 'Keyboard', 'Vase', 'Mug', 'Mirror',
            'Jacket', 'Sunglasses', 'Drone', 'Fan', 'Rug', 'Clock', 'Sculpture', 'Blender', 'Chair', 'Bench', 'Couch', 'Chandelier',
            'Necklace', 'Oven', 'Watch', 'Pot', 'Easel', 'Bottle', 'Calendar'
        ]

    ];


}
```

With that done code now needs to be added to retrieve those values.

add the following functions to the provider.

```php
  public function category(): string
    {
        return static::randomElement(static::$category);
    }

    public function department(): string
    {
        return static::randomElement(static::$department);
    }

    public function productName(): string
    {
        return static::randomElement(static::$productName['adjective'])
            . ' ' . static::randomElement(static::$productName['material'])
            . ' ' . static::randomElement(static::$productName['product']);
    }
```

These functions should be relatively self explanatory, they simply return an element picked at random from the lists
that we provided. The Product name actively concatenates one of each of the three value groups so it should be possible
to create several thousand unique product names.

The last task that needs to be done is to actively register the provider with any new faker instance that we create.

In the Data Generator this is done just after we setupup the faker for use.

```php
public function createFakes(): void
    {
        // Initialize Faker instance
        $faker = null;
        $faker = $this->$faker;
        // register any custom provider(s) with the faker
        $faker->addProvider(new Faker\Provider\Commerce($faker));
```

### Tweaking and using the custom provider

With the provider created and a simple test mProducts module added it should now be possible to test what we have done
and see what's what.

The illustration below shows the result form having created one row of test data in the products table.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/customise3Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/customise3.png">
</picture>
<figcaption>First test run with new provider</figcaption> 
</figure>
</div>


You will recall from earlier that the Data Generator initially tries to find a match for the field name and failing that
it resorts to the type. Currently the Data Generator has an enrty for 'productname' and it is calling our cutom provider
to get a name and we can see that it has worked. However no such entry exists for category and because of that it has
fallen back to using the type. This can be quite quickly remedied by adding category to generate a good looking
category.

In the vtl_faker controller navigate to the generateValueFromFieldName() method and add the following somewhere within
the switch statement.

```php
case 'category':
                $value = $faker -> category();
                $statement = '"' . $value . '"';
                break;
```

With that done a second row can now be created in the products table.

<div>
<figure>
<picture>
    <source srcset="vtlgen_module/help/images/customise4Dark.png" media="(prefers-color-scheme: dark)">
    <img src="vtlgen_module/help/images/customise4.png">
</picture>
<figcaption>Second test run with new provider</figcaption> 
</figure>
</div>

Now you can see that some meaningful data is returned for the category. In order to tweak the SKU the provider itself
would need additional code adding to it and the case statement would need additional field names adding to it. As you
can see though this is not a particularly onerous task.

> <b> It should be noted that anything that you do like like this will not persist if you download a fresh copy of the module.  However if you were to fork the github repository add your provider and ammendments and submit a pull request them your changes will awlays be readily available not just for you but everyone who makes use of the Data Generator.</b>

The changes above have all been included in the Vtl Data Generator so you should find that this all just works out of
the box.

### Handling tables with special requirements (eg Trongate Pages)

There will be occasions when it becomes necessary to generate highly customised data to meet certain pre requisites. The
Trongate Pages table was a good case in point as the page body is actually an html block.

This required a rethink about how the Vtl Data Generator does things but in the process has led to a far more robust
solution.

When you initially select a table for data generation your choice is handled by the createFakes() method.

```php
 public function createFakes(): void
    {
        // Initialize Faker instance
        $faker = null;
        $faker = $this->$faker;

        // register any custom provider(s) with the faker
        $faker->addProvider(new Faker\Provider\Commerce($faker));
        $faker->addProvider(new Faker\Provider\Blog($faker));

        // Seed the faker.  This will ensure that the same data gets recreated
        // which can be useful for testing purposes.
        // Comment out the line below if you don't want to use a seeded faker.
        $faker->seed(FAKER_SEED);


        // Retrieve raw POST data from the request body
        $rawPostData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $postData = json_decode($rawPostData, true);

        // Ensure JSON decoding was successful
        if ($postData === null) {
            throw new Exception("Invalid JSON data");
        }

        // Extract relevant data from the decoded JSON
        $selectedTable = $postData['selectedTable'];
        $selectedRows = $postData['selectedRows'];
        $numRows = $postData['numRows'];


        // Now is the time to hive off highly customised data creation for particular tables
        // like Trongate pages

        switch ($selectedTable) {
            case 'trongate_pages':
                $this->transferImagesToTrongatePages();
                $this->generateDataForTrongatePages($faker, $selectedRows, $numRows);
                break;
            default :
                $this->processGeneralTablesThatAreNotSpecialCases($faker, $selectedTable, $selectedRows, $numRows);
                break;
        }


    }
```

The key part of this revision is that you can now effectively create a custom generation for any table you have that
might require special handling. You can also trigger certain preparatory tasks as well (note how a method to add images
to the Trongate pages images folder is being called first.)

You can then handle your custom generation any way you want. The method for Trongate Pages is shown below.

```php
  private function transferImagesToTrongatePages()
    {
        //check if img1.png resides in the images/uploades directory
        $basedir = APPPATH . 'modules/vtlgen/vtl_faker/assets/images/';
        $sourcedir = APPPATH . 'modules/trongate_pages/assets/images/uploads';
        if (!file_exists($sourcedir . '/img1.jpg')) {
            // Copy files from $basedir to $sourcedir
            $files = scandir($basedir);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {

                    $sourceFile = $basedir . $file;

                    $destinationFile = $sourcedir . '/' . $file;
                    // Check if the path is a regular file before copying
                    if (is_file($sourceFile)) {
                        copy($sourceFile, $destinationFile);
                    }
                }
            }
        }
    }

    private function generateDataForTrongatePages($faker, $selectedTable, $selectedRows, $numRows)
    {

        // we ought to count the current tally of trongate pages as we'll use that to help
        // generate short unique uri strings

        $countSql = 'Select count(*) from ' . $selectedTable;
        $result = $this->model->query($countSql, 'array');

        // Check if the result is not empty and has the 'count' key
        if (!empty($result) && isset($result[0]['count(*)'])) {
            $pagesCount = (int)$result[0]['count(*)'];
        } else {
            // Handle the case when no count is returned or there's an error
            $pagesCount = 0; // or any default value you want
        }


        // now we can set to work
        if (!is_int($numRows)) {
            $numRows = intval($numRows);
        }

        $columns = '(';
        $values = '';

        foreach ($selectedRows as $key => $selectedRow) {
            $originalFieldName = $selectedRow['field'];
            $columns .= $originalFieldName;
            if ($key < count($selectedRows) - 1) {
                $columns .= ',';
            } else {
                $columns .= ')';
            }
        }

        // That's the columns part of the eventual sql statement taken care of
        // now to generate the values needed.
        $pageTitle = '';
        for ($i = 0; $i < $numRows; $i++) {
            $rowValues = '';

            foreach ($selectedRows as $selectedRow) {
                if ($rowValues !== '') {
                    $rowValues .= ',';
                }
                $value = null;
                $field = $this->processFieldName($selectedRow['field']);

                switch ($field) {
                    case 'urlstring':
                        if ($pagesCount > 0) {
                            // Fetch existing URLs from the database
                            $existingUrls = $this->model->query('SELECT url_string FROM ' . $selectedTable, 'array');

                            do {
                                $proposedUrl = 'article' . ($pagesCount + $i + 1);
                                $unique = true;
                                foreach ($existingUrls as $row) {
                                    if ($row['url_string'] === $proposedUrl) {
                                        $unique = false;
                                        break;
                                    }
                                }
                                $i++;
                            } while (!$unique);

                            $value = '"' . $proposedUrl . '"';
                        } else {
                            $value = '"article' . $i . '"';
                        }
                        break;
                    case 'pagetitle':
                        $pageTitle = $faker->articleTitle(); // Assign to $pageTitle instead of $value
                        $value = '"' . $pageTitle . '"';
                        break;
                    case 'metakeywords':
                        $value = $faker->metaKeywords(rand(1, 6));
                        $value = implode(', ', $value);
                        $value = '"' . $value . '"';
                        break;
                    case 'metadescription':
                        $value = $faker->metaDescription();
                        $value = '"' . $value . '"';
                        break;
                    case 'pagebody':
                        $numParas = rand(1, 4);
                        $numSentences = rand(1, 3);
                        $pagebody = '<h1>' . $pageTitle . '</h1>';
                        for ($j = 0; $j < $numParas; $j++) {
                            $text = ''; // Reset $text for each paragraph
                            for ($k = 0; $k < $numSentences; $k++) {
                                $text .= $faker->sentence() . ' '; // Append sentences to $text
                            }
                            // Escape double quotes within HTML attributes by doubling them
                            $text = '<div class=""text-div"">' . $text . '</div>'; // Wrap text in a div
                            $img = $faker->randomElement(['img1.jpg', 'img2.jpg', 'img3.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg', 'img7.jpg', 'img8.jpg', 'img9.jpg', 'img10.jpg', 'img11.jpg']);
                            $imgText = '<img src="' . BASE_URL . 'trongate_pages_module/images/uploads/' . $img . '" />';
                            $pagebody .= $text . $imgText; // Append $text and $imgText to $pagebody
                        }
                        // Escape double quotes within SQL string by doubling them
                        $pagebody = '"' . str_replace('"', '""', $pagebody) . '"';
                        $value = $pagebody;
                        break;
                    case 'datecreated' :
                        $value = $faker->unixTime(new dateTime('-3 days'));
                        break;
                    case 'lastupdated' :
                        $value = $faker->unixTime(new dateTime('-1 days'));
                        break;
                    case 'published':
                        $value = $faker->numberBetween(0, 1);
                        break;
                    case 'createdby' :
                        $value = 1;
                        break;
                }
                $rowValues .= $value;
            }
            $values .= '(' . $rowValues . ')';

            if ($i < $numRows - 1) {
                $values .= ', ';
            }
        }

        $sql = 'INSERT INTO ' . $selectedTable . ' ' . $columns . ' VALUES ' . $values . ';';


        try {
            $data = [];
            $this->model->prepare_and_execute($sql, $data);
            echo('The following number rows were inserted into trongate_pages: ' . $numRows);
        } catch (Exception $e) {
            echo($e->getMessage());
        }

    }
```

Notice how it is making use of another custom provider that has been added to make creating realist data for items like
this much easier.

Both of the new providers have been fully documented at the bottom of this page.

### Handling relationships between tables

The overriding strength of relational databases are the relations between tables. FakerPHP though knows nothing of those
so when it comes to creating data that actually shows realistic links between tables it will be necessary to add some
custom code.

> <b>NB What is about to be discussed will work if the tables in question use auto-incremented integer primary keys.
> If guid primary keys are in use then a great deal more work will need to be done in order to get realistic related
> data.</b>

A typical example of the type of scenario that one might want to cater for would be a relationship that exists between
Orders and their detail lines and those detail lines with a products table.

Visually the relationship would look a little like this;

An Order would have one or many OrderDetail Lines and each OrderDetail would be directly related to a Single Product. In
this example the crucial table (from the perspective of creating the fake data) will be the OrderDetails. Each
OrderDetail will need to have a reference to the Orders table (probably through a field called OrderId) and it will also
have a link to the Products table (probably through a field called ProductId).

The way to approach this is as follows.

- Decide on the number of Products that you intend to generate (we'll sayy 250 for this example).
- Decide on the number of Orders that you want to create (we'll say 150 for this example)
- Add custom generation for the Field names productid and orderid when generating the OrderDetails rows.

Within the vtl_faker controller file you will find the following function

```php
 /**
     * Check for custom field name generation based on provided Faker instance.
     * This function is intended for use when adding custom field name generators.
     *
     *
     * @param string           $field The Faker generator instance.
     * @param \Faker\Generator $faker The Faker instance.
     * @return mixed The generated field name statement.
     */
     */
    private function checkForCustomFieldNameGeneration(string $field, \Faker\Generator $faker): mixed
    {
        $statement = null;
        $value = null;
        switch ($field) {
            // Add your custom field name generation here:
            //This would be in the form of a case statement
            
            //case 'orderid';
                //$value = $faker -> $faker->numberBetween($min = 0, $max = 150);
                //$statement = $value;
                //break; 
                
            //  NB   if dealing with string values $statement should be set like this
            //  $statement = '"' . $value . '"';
            
            // DO NOT DELETE THIS PART OR THE SWITCH STATEMENT OR YOU WILL BREAK THE GENERATOR 
            default:
                $statement = 'nothing';
        }
        //allow for the fact that a known field name may still fail to get data
        if ($statement === null) {
            $statement = 'nothing';
        }
        return $statement;

    }
```

Its sole purpose is to allow you, the end user of the Data Generator to add your own custom generators for field names.

In the example that we are working with you might add something like this.

```php
   private function checkForCustomFieldNameGeneration(string $field, \Faker\Generator $faker): mixed
    {
        $statement = null;
        $value = null;
        switch ($field) {
            // Add your custom field name generation here:
            //This would be in the form of a case statement
            
            case 'productid';
                $value = $faker -> $faker->numberBetween($min = 0, $max = 250);
                $statement = $value;
                break;

            case 'orderid';
                $value = $faker -> $faker->numberBetween($min = 0, $max = 150);
                $statement = $value;
                break; 
                
            //  NB   if dealing with string values $statement should be set like this
            //  $statement = '"' . $value . '"';
            
            // DO NOT DELETE THIS PART OR THE SWITCH STATEMENT OR YOU WILL BREAK THE GENERATOR   
            default:
                $statement = 'nothing';
        }
        //allow for the fact that a known field name may still fail to get data
        if ($statement === null) {
            $statement = 'nothing';
        }
        return $statement;

    }
```

With that added productid and orderid will be set to have specific integer values that match Orders and Products.


> <b>Note that in order to make matching field names as easy as possible all field names are stripped of spaces,
> underscores or hyphens and then converted to lowercase. Therefore, for any customisation that you add, based on field
> names, make sure that you work with a lower case version. For example if the field name was Order_Id you would want to
> work with orderid.</b>


Finally you should consider Faker's modifiers

### Modifiers

Faker provides three special providers, `unique()`, `optional()`, and `valid()`, to be called before any provider.

```php
// unique() forces providers to return unique values
$values = [];
for ($i = 0; $i < 10; $i++) {
    // get a random digit, but always a new one, to avoid duplicates
    $values []= $faker->unique()->randomDigit();
}
print_r($values); // [4, 1, 8, 5, 0, 2, 6, 9, 7, 3]

// providers with a limited range will throw an exception when no new unique value can be generated
$values = [];
try {
    for ($i = 0; $i < 10; $i++) {
        $values []= $faker->unique()->randomDigitNotNull();
    }
} catch (\OverflowException $e) {
    echo "There are only 9 unique digits not null, Faker can't generate 10 of them!";
}

// you can reset the unique modifier for all providers by passing true as first argument
$faker->unique($reset = true)->randomDigitNotNull(); // will not throw OverflowException since unique() was reset
// tip: unique() keeps one array of values per provider

// optional() sometimes bypasses the provider to return a default value instead (which defaults to NULL)
$values = [];
for ($i = 0; $i < 10; $i++) {
    // get a random digit, but also null sometimes
    $values []= $faker->optional()->randomDigit();
}
print_r($values); // [1, 4, null, 9, 5, null, null, 4, 6, null]

// optional() accepts a weight argument to specify the probability of receiving the default value.
// 0 will always return the default value; 1.0 will always return the provider. Default weight is 0.5 (50% chance).
// Please note that the weight can be provided as float (0 / 1.0) or int (0 / 100)

// As float
$faker->optional($weight = 0.1)->randomDigit(); // 90% chance of NULL
$faker->optional($weight = 0.9)->randomDigit(); // 10% chance of NULL

// As int
$faker->optional($weight = 10)->randomDigit; // 90% chance of NULL
$faker->optional($weight = 100)->randomDigit; // 0% chance of NULL

// optional() accepts a default argument to specify the default value to return.
// Defaults to NULL.
$faker->optional($weight = 0.5, $default = false)->randomDigit(); // 50% chance of FALSE
$faker->optional($weight = 0.9, $default = 'abc')->word(); // 10% chance of "abc"

// valid() only accepts valid values according to the passed validator functions
$values = [];
$evenValidator = function($digit) {
	return $digit % 2 === 0;
};
for ($i = 0; $i < 10; $i++) {
	$values []= $faker->valid($evenValidator)->randomDigit();
}
print_r($values); // [0, 4, 8, 4, 2, 6, 0, 8, 8, 6]

// just like unique(), valid() throws an overflow exception when it can't generate a valid value
$values = [];
try {
    $faker->valid($evenValidator)->randomElement([1, 3, 5, 7, 9]);
} catch (\OverflowException $e) {
    echo "Can't pick an even number in that set!";
}
```

If you would like to use a modifier with a value not generated by Faker, use the `passthrough()` method. `passthrough()`
simply returns whatever value it was given.

```php
$faker->optional()->passthrough(mt_rand(5, 15));
```

<br/>


<div id="barcode">
<hr/>
<hr/>
</div>

## Barcode

### `ean13`

Generate a random [EAN-13](https://en.wikipedia.org/wiki/International_Article_Number) barcode.

```php
echo $faker->ean13();

// '5803352818140', '4142935337533'
```

### `ean8`

Generate a random [EAN-8](https://en.wikipedia.org/wiki/International_Article_Number) barcode.

```php
echo $faker->ean8();

// '30272446', '00484527'
```

### `isbn10`

[ISBN-10]: <https://en.wikipedia.org/wiki/International_Standard_Book_Number#ISBN-10_check_digit_calculation>

Generate a random [ISBN-10][ISBN-10] compliant `string`.

```php
echo $faker->isbn10();

// '4250151735', '8395066937'
```

### `isbn13`

[ISBN-13]: <https://en.wikipedia.org/wiki/International_Standard_Book_Number#ISBN-13_check_digit_calculation>

Generate a random [ISBN-13][ISBN-13] compliant `string`.

```php
echo $faker->isbn13();

// '9786881116078', '9785625528412'
```

<div id="biased">
<hr/>
</div>

## Biased

### `biasedNumberBetween`

Generate a random `integer`, with a bias using a given function.

```php
function biasedNumberBetween(
    int $min = 0, 
    int $max = 100, 
    string $function = 'sqrt'
): int;
```

Examples:

```php
echo $faker->biasedNumberBetween(0, 20);

// 14, 18, 12

echo $faker->biasedNumberBetween(0, 20, 'log');

// 9, 4, 12
```

<div id="colour">
<hr/>
</div>

## Colour

### `hexColor`

Generate a random hex color.

```php
$faker->hexColor();

// '#ccd578', '#fafa11', '#ea3781'
```

### `safeHexColor`

Generate a random hex color, containing only 16 values per R, G and B.

```php
$faker->safeHexColor();

// '#00eecc', '#00ff88', '#00aaee'
```

### `rgbColorAsArray`

Generate a random RGB color, as an `array`.

```php
$faker->rgbColorAsArray();

// [0 => 30, 1 => 22, 2 => 177], [0 => 150, 1 => 55, 2 => 34], [0 => 219, 1 => 253, 2 => 248]
```

### `rgbColor`

Generate a comma-separated RGB color `string`.

```php
$faker->rgbColor();

// '105,224,78', '97,249,98', '24,250,221'
```

### `rgbCssColor`

Generate a CSS-friendly RGB color `string`.

```php
$faker->rgbCssColor();

// 'rgb(9,110,101)', 'rgb(242,133,147)', 'rgb(124,64,0)'
```

### `rgbaCssColor`

Generate a CSS-friendly RGBA (alpha channel) color `string`.

```php
$faker->rgbaCssColor();

// 'rgba(179,65,209,1)', 'rgba(121,53,231,0.4)', 'rgba(161,239,152,0.9)'
```

### `safeColorName`

Generate a CSS-friendly color name.

```php
$faker->safeColorName();

// 'white', 'fuchsia', 'purple'
```

### `colorName`

Generate a CSS-friendly color name.

```php
$faker->colorName();

// 'SeaGreen', 'Crimson', 'DarkOliveGreen'
```

### `hslColor`

Generate a random HSL color `string`.

```php
$faker->hslColor();

// '87,10,25', '94,24,27', '207,68,84'
```

### `hslColorAsArray`

Generate a random HSL color, as an `array`.

```php
$faker->hslColorAsArray();

// [0 => 311, 1 => 84, 2 => 31], [0 => 283, 1 => 85, 2 => 49], [0 => 57, 1 => 48, 2 => 36]
```

<div id="dateandtime">
<hr/>
</div>

## Date and Time

Methods accepting a `$timezone` argument default to `date_default_timezone_get()`. You can pass a custom timezone string
to each method, or define a custom timezone for all time methods at once using `$faker::setDefaultTimezone($timezone)`.

### `unixTime`

Generate an [unix time](https://en.wikipedia.org/wiki/Unix_time) between zero, and the given value. By default, `now` is
used as input.

Optionally, a parameter can be supplied containing a `DateTime`, `int` or `string`.

```php
echo $faker->unixTime();

// 1605544623, 1025544612

echo $faker->unixTime(new DateTime('+3 weeks'));

// unix timestamp between 0 and the date 3 weeks from now.
```

### `dateTime`

Generate a `DateTime` between January 1, 1970, and the given max. By default, `now` is used as max.

Optionally, a parameter can be supplied containing a `DateTime`, `int` or `string`.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTime();

// DateTime: August 12, 1991 
```

### `dateTimeAD`

Generate a `DateTime` between January 1, 0001, and the given max. By default, `now` is used as max.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeAD();

// DateTime: September 19, 718
```

### `iso8601`

Generate an ISO8601 formatted `string` between January 1, 0001, and the given max. By default, `now` is used as max.

```php
echo $faker->iso8601();
```

### `date`

Generate a date `string`, with a given format and max value. By default, `'Y-m-d'` and `'now'` are used for the format
and maximum value respectively.

```php
echo $faker->date();

// '1999-06-09'

echo $faker->date('Y_m_d');

// '2011_02_19'
```

### `time`

Generate a time `string`, with a given format and max value. By default, `H:i:s'` and `now` are used for the format and
maximum value respectively.

```php
echo $faker->time();

// '12:02:50'

echo $faker->time('H_i_s');

// '20_49_12'
```

### `dateTimeBetween`

Generate a `DateTime` between two dates. By default, `-30 years` and `now` are used.

An optional third parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeBetween();

// a date between -30 years ago, and now

echo $faker->dateTimeBetween('-1 week', '+1 week');

// a date between -1 week ago, and 1 week from now
```

### `dateTimeInInterval`

Generate a `DateTime` between a date and an interval from that date. By default, the date is set to `-30 years`, and the
interval is set to `+5 days`.

An optional third parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeInInterval();

// a date between -30 years ago, and -30 years + 5 days

echo $faker->dateTimeInInterval('-1 week', '+3 days');

// a date between -1 week ago, and -1 week + 3 days
```

### `dateTimeThisCentury`

Generate a `DateTime` that is within the current century. An optional `$max` value can be supplied, by default this is
set to `now`.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeThisCentury();

// a date somewhere in this century

echo $faker->dateTimeThisCentury('+8 years');

// a date somewhere in this century, with an upper bound of +8 years
```

### `dateTimeThisDecade`

Generate a `DateTime` that is within the current decade. An optional `$max` value can be supplied, by default this is
set to `now`.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeThisDecade();

// a date somewhere in this decade

echo $faker->dateTimeThisDecade('+2 years');

// a date somewhere in this decade, with an upper bound of +2 years
```

### `dateTimeThisYear`

Generate a `DateTime` that is within the current year. An optional `$max` value can be supplied, by default this is set
to `now`.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeThisYear();

// a date somewhere in this year

echo $faker->dateTimeThisYear('+2 months');

// a date somewhere in this year, with an upper bound of +2 months
```

### `dateTimeThisMonth`

Generate a `DateTime` that is within the current month. An optional `$max` value can be supplied, by default this is set
to `now`.

An optional second parameter can be supplied, with the timezone.

```php
echo $faker->dateTimeThisMonth();

// a date somewhere in this month

echo $faker->dateTimeThisMonth('+12 days');

// a date somewhere in this month, with an upper bound of +12 days
```

### `amPm`

Generate a random `DateTime`, with a given upper bound, and return the am/pm `string` value. By default, the upper bound
is set to `now`.

```php
echo $faker->amPm();

// 'am'

echo $faker->amPm('+2 weeks');

// 'pm'
```

### `dayOfMonth`

Generate a random `DateTime`, with a given upper bound, and return the day of month `string` value. By default, the
upper bound is set to `now`.

```php
echo $faker->dayOfMonth();

// '24'

echo $faker->dayOfMonth('+2 weeks');

// '05'
```

### `dayOfWeek`

Generate a random `DateTime`, with a given upper bound, and return the day of week `string` value. By default, the
upper bound is set to `now`.

```php
echo $faker->dayOfWeek();

// 'Tuesday'

echo $faker->dayOfWeek('+2 weeks');

// 'Friday'
```

### `month`

Generate a random `DateTime`, with a given upper bound, and return the month's number `string` value. By default, the
upper bound is set to `now`.

```php
echo $faker->month();

// '9'

echo $faker->month('+10 weeks');

// '7'
```

### `monthName`

Generate a random `DateTime`, with a given upper bound, and return the month's name `string` value. By default, the
upper bound is set to `now`.

```php
echo $faker->monthName();

// 'September'

echo $faker->monthName('+10 weeks');

// 'April'
```

### `year`

Generate a random `DateTime`, with a given upper bound, and return the year's `string` value. By default, the
upper bound is set to `now`.

```php
echo $faker->year();

// '1998'

echo $faker->year('+10 years');

// '2022'
```

### `century`

Generate a random century name.

```php
echo $faker->century();

// 'IX', 'XVII', 'II'
```

### `timezone`

Generate a random timezone name.

```php
echo $faker->timezone();

// 'Europe/Amsterdam', 'America/Montreal'

echo $faker->timezone('US');

// 'America/New_York', 'America/Los_Angeles'
```

<div id="file">
<hr/>
</div>

## File

### `mimeType`

Generate a random MIME-type `string`.

```php
$faker->mimeType();

// 'application/vnd.ms-artgalry', 'application/mods+xml', 'video/x-sgi-movie'
```

### `fileExtension`

Generate a random file extension type `string`.

```php
$faker->fileExtension();

// 'deb', 'mp4s', 'uvg'
```

### `file`

Copy a random file from the source directory to the target directory and return the filename / relative path.

```php
$faker->file('docs', 'site', true);

// 'site/f6df6c74-2884-35c7-b802-6f96cf2ead01.md', 'site/423cfca4-709c-3942-8d66-34b08affd90b.md', 'site/c7a76943-e2cc-3c99-b75b-ac2df15cb3cf.md'

$faker->file('docs', 'site', false);

// 'c4cdee40-0eee-3172-9bca-bdafbb743c17.md', '88aef77e-040d-39a3-8f88-eca522f759ba.md', 'ecbee0e9-6fad-397b-88fb-d84704c7a71c.md'
```

<div id="htmllorem">
<hr/>
</div>

## HTML Lorem

### `randomHtml`

Generate a random HTML `string`, with a given maximum depth and width. By default, the depth and width are `4`.

Depth defines the maximum depth of the body.

Width defines the maximum of siblings each element can have.

```php
echo $faker->randomHtml();

// '<html><head><title>Laborum doloribus voluptatum vitae quia voluptatum ipsum veritatis.</title></head><body><form action="example.org" method="POST"><label for="username">sit</label><input type="text" id="username"><label for="password">amet</label><input type="password" id="password"></form><div class="et"><span>Numquam magnam.</span><p>Neque facere consequuntur autem quisquam.</p><ul><li>Veritatis sint.</li><li>Et ducimus.</li><li>Veniam accusamus cupiditate.</li><li>Eligendi eum et doloribus.</li><li>Voluptate ipsa dolores est.</li><li>Enim.</li><li>Dignissimos nostrum atque et excepturi.</li><li>Nisi veniam.</li><li>Voluptate nihil labore sapiente.</li><li>Ut.</li><li>Id suscipit.</li></ul><i>Qui tempora minima ad.</i></div></body></html>'

echo $faker->randomHtml(1, 1);

// '<html><head><title>Architecto ut eius nisi molestiae atque ab.</title></head><body><form action="example.net" method="POST"><label for="username">saepe</label><input type="text" id="username"><label for="password">est</label><input type="password" id="password"></form></body></html>'
```

<div id="image">
<hr/>
</div>

## Image

### `imageUrl`

Get a random image URL from [placeholder.com](https://placeholder.com).

To provide a less verbose explanation of this function, we'll use a function definition here:

```php
function imageUrl(
    int $width = 640,
    int $height = 480,
    ?string $category = null, /* used as text on the image */
    bool $randomize = true,
    ?string $word = null,
    bool $gray = false,
    string $format = 'png'
): string;
```

Below, a few examples of possible parameter combinations:

```php
echo $faker->imageUrl(640, 480, 'animals', true);

// 'https://via.placeholder.com/640x480.png/004466?text=animals+omnis'

echo $faker->imageUrl(360, 360, 'animals', true, 'cats');

// 'https://via.placeholder.com/360x360.png/00bbcc?text=animals+cats+vero'

echo $faker->imageUrl(360, 360, 'animals', true, 'dogs', true);

// https://via.placeholder.com/360x360.png/CCCCCC?text=animals+dogs+veniam

echo $faker->imageUrl(360, 360, 'animals', true, 'dogs', true, 'jpg');

// https://via.placeholder.com/360x360.jpg/CCCCCC?text=animals+dogs+veniam
```

### `image`

Get a random `image` from [placeholder.com](https://placeholder.com) and download it to a directory (`$dir`). The full
path of the image is returned as a `string`.

All the parameters are the same as `imageUrl`. Except an extra first parameter, this defines where the
image should be stored.

```php
function image(
    ?string $dir = null,
    int $width = 640,
    int $height = 480,
    ?string $category = null,
    bool $fullPath = true,
    bool $randomize = true,
    ?string $word = null,
    bool $gray = false,
    string $format = 'png'
)
```

Below, a few examples of possible parameter combinations:

```php
echo $faker->image(null, 640, 480);

// '/tmp/309fd63646f6d781848850277c14aef2.png'

echo $faker->image(null, 360, 360, 'animals', true);

// '/tmp/4d2666e5968e10350428e3ed64de9175.png'

echo $faker->image(null, 360, 360, 'animals', true, true, 'cats', true);

// '/tmp/9444227f06f0b024a14688ef3b31fe7a.png'

echo $faker->image(null, 360, 360, 'animals', true, true, 'cats', true, 'jpg');

// '/tmp/9444227f06f0b024a14688ef3b31fe7a.jpg'
```

<div id="internet">
<hr/>
</div>

## Internet

### `email`

Generate an email address.

```php
echo $faker->email();

// 'orval.treutel@blick.com', 'hickle.lavern@erdman.com'
```

### `safeEmail`

Generate a safe email address.

```php
echo $faker->safeEmail();

// 'spencer.ricardo@example.com', 'wolf.sabryna@example.org'
```

### `freeEmail`

Generate a free email address (free, as in, free sign-up).

```php
echo $faker->freeEmail();

// 'marcelino.hyatt@yahoo.com', 'abby81@gmail.com'
```

### `companyEmail`

Generate a company email.

```php
echo $faker->companyEmail();

// 'hschinner@reinger.net', 'paula.blick@hessel.com'
```

### `freeEmailDomain`

Generate a free email domain name.

```php
echo $faker->freeEmailDomain();

// 'gmail.com', 'hotmail.com'
```

### `safeEmailDomain`

Generate a safe email domain.

```php
echo $faker->safeEmailDomain();

// 'example.net', 'example.org'
```

### `userName`

Generate a username.

```php
echo $faker->userName();

// 'ipaucek', 'homenick.alexandre'
```

### `password`

Generate a password, with a given minimum and maximum length. By default, the values `6` and `20` are used for the
minimum and maximum respectively.

```php
echo $faker->password();

// 'dE1U[G$n4g%-Eie[]rn[', '-YCc1t|NSh)U&j6Z'

echo $faker->password(2, 6);

// 'GK,M|', '/ZG.'
```

### `domainName`

Generate a domain name.

```php
echo $faker->domainName();

// 'toy.com', 'schamberger.biz'
```

### `domainWord`

Generate a domain word.

```php
echo $faker->domainWord();

// 'feil', 'wintheiser'
```

### `tld`

Generate a tld (top-level domain).

```php
echo $faker->tld();

// 'com', 'org'
```

### `url`

Generate a URL.

```php
echo $faker->url();

// 'http://cormier.info/eligendi-rem-omnis-quia.html', 'http://pagac.com/'
```

### `slug`

Generate a slug, with a given amount of words. By default, the amount of words it set to 6.

Optionally, a second parameter can be supplied. When `false`, only slugs with the given amount of words will be
generated.

```php
echo $faker->slug();

// 'facere-ipsam-sit-aut-ut-dolorem', 'qui-soluta-sed-facilis-est-ratione-dolor-autem'

echo $faker->slug(2);

// 'et-et-et', 'temporibus-iure'

echo $faker->slug(3, false);

// 'ipsa-consectetur-est', 'quia-ad-nihil'
```

### `ipv4`

Generate an IPv4 address.

```php
echo $faker->ipv4();

// '90.119.172.201', '84.172.232.19'
```

### `localIpv4`

Generate an IPv4 address, inside a local subnet.

```php
echo $faker->localIpv4();

// '192.168.85.208', '192.168.217.138'
```

### `ipv6`

Generate an IPv6 address.

```php
echo $faker->ipv6();

// 'c3f3:40ed:6d6c:4e8e:746b:887a:4551:42e5', '1c3d:a2cf:80ad:f2b6:7794:4f3f:f9fb:59cf'
```

### `macAddress`

Generate a random MAC address.

```php
echo $faker->macAddress();

// '94:00:10:01:58:07', '0E:E1:48:29:2F:E2'
```

<div id="miscellaneous">
<hr/>
</div>

## Miscellaneous

### `boolean`

Generate a random `bool`.

```php
echo $faker->boolean();

// true, true, false
```

### `md5`

[MD5]: <https://en.wikipedia.org/wiki/MD5>

Generate a random [MD5][MD5] hash `string`.

```php
echo $faker->md5();

// 'b1f447c2ee6029c7d2d8b3112ecfb160', '6d5d81469dfb247a15c9030d5aae38f1'
```

### `sha1`

[SHA-1]: <https://en.wikipedia.org/wiki/SHA-1>

Generate a random [SHA-1][SHA-1] hash `string`.

```php
echo $faker->sha1();

// '20d1061c44ca4eef07e8d129c7000101b3e872af', '28cda1350140b3465ea8f65b933b1dad98ee5425'
```

### `sha256`

[SHA-2]: <https://en.wikipedia.org/wiki/SHA-2>

Generate a random [SHA-256][SHA-2] hash `string`.

```php
echo $faker->sha256();

// 'bfa80759a5c40a8dd6694a3752bac231ae49c136396427815b0e33bd10974919'
```

### `locale`

Generate a random locale `string`.

```php
echo $faker->locale();

// 'ln_CD', 'te_IN', 'sh_BA'
```

### `countryCode`

Generate a random [two-letter country code](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) `string`.

```php
echo $faker->countryCode();

// 'LK', 'UM', 'CZ'
```

### `countryISOAlpha3`

Generate a random [three-letter country code](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3) `string`.

```php
echo $faker->countryISOAlpha3();

// 'ZAF', 'UKR', 'MHL'
```

### `languageCode`

Generate a random [two-letter language code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) `string`.

```php
echo $faker->languageCode();

// 'av', 'sc', 'as'
```

### `currencyCode`

Generate a random [currency code](https://en.wikipedia.org/wiki/ISO_4217) `string`.

```php
echo $faker->currencyCode();

// 'AED', 'SAR', 'KZT'
```

### `emoji`

Generate a random emoji.

```php
echo $faker->emoji();

// '😦', '😎', '😢'
```

<div id="numbersandstrings">
<hr/>
</div>

## Numbers and Strings

### `randomDigit`

Generates a random integer from 0 until 9.

```php
echo $faker->randomDigit();

// an integer between 0 and 9
```

### `randomDigitNot`

Generates a random integer from 0 until 9, excluding a given number.

```php
echo $faker->randomDigitNot(2);

// 0, 1, 3, 4, 5, 6, 7, 8 or 9
```

### `randomDigitNotNull`

Generates a random integer from 1 until 9.

```php
echo $faker->randomDigitNotNull();

// an integer between 1 and 9
```

### `randomNumber`

Generates a random integer, containing between 0 and `$nbDigits` amount of digits. When the `$strict` parameter
is `true`, it will only return integers with $nbDigits amount of digits.

```php
echo $faker->randomNumber(5, false);

// 123, 43, 19238, 5, or 1203

echo $faker->randomNumber(5, true);

// 12643, 42931, or 32919
```

### `randomFloat`

Generates a random float. Optionally it's possible to specify the amount of decimals.

The second and third parameters optionally specify a lower and upper bound respectively.

```php
echo $faker->randomFloat();

// 12.9830, 2193.1232312, 102.12

echo $faker->randomFloat(2);

// 43.23, 1203.49, 3428.93

echo $faker->randomFloat(1, 20, 30);

// 21.7, 27.2, 28.1
```

### `numberBetween`

Generates a random integer between `$min` and `$max`. By default, an integer is generated between `0`
and `2,147,483,647` (32-bit integer).

```php
echo $faker->numberBetween();

// 120378987, 182, 102310983

echo $faker->numberBetween(0, 100);

// 32, 87, 91, 13, 75
```

### `randomLetter`

Generates a random character from the alphabet.

```php
echo $faker->randomLetter();

// 'h', 'i', 'q'
```

### `randomElements`

Returns `$count` amount of random element from the given array, traversable, or enum. By default, the `$count` parameter
is set to 1, when `null` a random number of elements is returned.

```php
echo $faker->randomElements(['a', 'b', 'c', 'd', 'e']);

// ['c']

echo $faker->randomElements(['a', 'b', 'c', 'd', 'e'], null);

// ['c', 'a', 'e']

echo $faker->randomElements(new \ArrayIterator(['a', 'b', 'c', 'd', 'e']));

// ['c']

enum Bar
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case D = 'd';
    case E = 'e';
}

echo $faker->randomElements(Bar::class);

// ['c']

echo $faker->randomElements(['a', 'b', 'c', 'd', 'e'], 3);

// ['a', 'd', 'e']
```

### `randomElement`

Returns a random element from the given array, traversable, or enum.

```php
echo $faker->randomElement(['a', 'b', 'c', 'd', 'e']);

// 'c'

echo $faker->randomElement(new \ArrayIterator(['a', 'b', 'c', 'd', 'e']));

// 'c'

enum Bar
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case D = 'd';
    case E = 'e';
}

echo $faker->randomElement(Bar::class);

// 'c'
```

### `randomKey`

Returns a random key from the given array.

```php
echo $faker->randomKey(['a' => 1, 'b' => 2, 'c' => 3]);

// 'b'
```

### `shuffle`

Returns a shuffled version of either an array or string.

```php
echo $faker->shuffle('hello-world');

// 'lrhoodl-ewl'

echo $faker->shuffle([1, 2, 3]);

// [3, 1, 2]
```

### `numerify`

Generate a string where all `#` characters are replaced by digits between 0 and 9. By default, `###` is used as input.

```php
echo $faker->numerify();

// '912', '271', '109', '674'

echo $faker->numerify('user-####');

// 'user-4928', 'user-3427', 'user-1280'
```

### `lexify`

Generate a string where all `?` characters are replaces with a random letter from the Latin alphabet. By default, `????`
is used as input.

```php
echo $faker->lexify();

// 'sakh', 'qwei', 'adsj'

echo $faker->lexify('id-????');

// 'id-xoqe', 'id-pqpq', 'id-zpeu'
```

### `bothify`

Generate a string where `?` characters are replaced with a random letter, and `#` characters are replaces with a random
digit between 0 and 9. By default, `## ??` is used as input.

```php
echo $faker->bothify();

// '46 hd', '19 ls', '75 pw'

echo $faker->bothify('?????-#####');

// 'lsadj-10298', 'poiem-98342', 'lcnsz-42938'
```

### `asciify`

Generate a string where `*` characters are replaced with a random character from the ASCII table. By default, `****` is
used as input.

```php
echo $faker->asciify();

// '%Y+!', '{<"B', 'kF^a'

echo $faker->asciify('user-****');

// 'user-ntwx', 'user-PK`A', 'user-n`,X'
```

### `regexify`

Generate a random string based on a regex. By default, an empty string is used as input.

```php
echo $faker->regexify();

// ''

echo $faker->regexify('[A-Z]{5}[0-4]{3}');

// 'DRSQX201', 'FUDPA404', 'CQVIU411'
```

<div id="payment">
<hr/>
</div>

## Payment

### `creditCardType`

Generate a credit card type.

```php
echo $faker->creditCardType();

// 'MasterCard', 'Visa'
```

### `creditCardNumber`

Generate a credit card number with a given type. By default, a random type is used. Supported types are 'Visa', '
MasterCard', 'American Express', and 'Discover'.

Optionally, a second and third parameter may be supplied. These define if the credit card number should be formatted,
and which separator to use.

```php
echo $faker->creditCardNumber();

// '4556817762319090', '5151791946409422'

echo $faker->creditCardNumber('Visa');

// '4539710900519030', '4929494068680706'

echo $faker->creditCardNumber('Visa', true);

// '4624-6303-5483-5433', '4916-3711-2654-8734'

echo $faker->creditCardNumber('Visa', true, '::');

// '4539::6626::9844::3867', '4916::6161::0683::7022'
```

### `creditCardExpirationDate`

Generate a credit card expiration date (`DateTime`). By default, only valid dates are generated. Potentially invalid
dates can be generated by using `false` as input.

```php
echo $faker->creditCardExpirationDate();

// DateTime: between now and +36 months

echo $faker->creditCardExpirationDate(false);

// DateTime: between -36 months and +36 months
```

### `creditCardExpirationDateString`

Generate a credit card expiration date (`string`). By default, only valid dates are generated. Potentially invalid dates
can be generated by using `false` as input.

The string is formatted using `m/y`. Optionally, a second parameter can be passed to override this format.

```php
echo $faker->creditCardExpirationDateString();

// '09/23', '06/21'

echo $faker->creditCardExpirationDateString(false);

// '01/18', '09/21'

echo $faker->creditCardExpirationDateString(true, 'm-Y');

// '12-2020', '07-2023'
```

### `creditCardDetails`

Generate an `array` with credit card details. By default, only valid expiration dates will be generated. Potentially
invalid expiration dates can be generated by using `false` as input.

```php
echo $faker->creditCardDetails();

// ['type' => 'Visa', 'number' => '4961616159985979', 'name' => 'Mr. Charley Greenfelder II', 'expirationDate' => '01/23']

echo $faker->creditCardDetails(false);

// ['type' => 'MasterCard', 'number' => '2720381993865020', 'name' => 'Dr. Ivy Gerhold Jr.', 'expirationDate' => '10/18']
```

### `iban`

Generate an `IBAN` string with a given country and bank code. By default, a random country and bank code will be used.

The country code format should be [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2).

```php
echo $faker->iban();

// 'LI2690204NV3C0BINN164', 'NL56ETEE3836179630'

echo $faker->iban('NL');

// 'NL95ZOGL3572193597', 'NL76LTTM8016514526'

echo $faker->iban('NL', 'INGB');

// 'NL11INGB2348102199', 'NL87INGB6409935479'
```

### `swiftBicNumber`

Generate a random [SWIFT/BIC](https://en.wikipedia.org/wiki/ISO_9362) number `string`.

```php
echo $faker->swiftBicNumber();

// 'OGFCTX2GRGN', 'QFKVLJB7'
```

<div id="textandparagraphs">
<hr/>
</div>

## Text and Paragraphs

### `word`

Generate a string containing random single word.

```php
echo $faker->word();

// 'molestiae', 'occaecati', 'distinctio'
```

### `words`

Generate an array containing a specified amount of random words.

Optionally, a second boolean parameter can be supplied. When `true`, a string will be returned instead of an array.

```php
echo $faker->words();

// ['praesentium', 'possimus', 'modi']

echo $faker->words(5);

// ['molestias', 'repellendus', 'qui', 'temporibus', 'ut']

echo $faker->words(3, true);

// 'placeat vero saepe'
```

### `sentence`

Generate a sentence containing a given amount of words. By default, `6` words is used.

Optionally, a second boolean parameter can be supplied. When `false`, only sentences with the given amount of words will
be generated. By default, `sentence` will deviate from the given amount by +/- 40%.

```php
echo $faker->sentence();

// 'Sit vitae voluptas sint non voluptates.'

echo $faker->sentence(3);

// 'Laboriosam non voluptas.'
```

### `sentences`

Generate an array containing a given amount of sentences. By default, `3` sentences are generated.

Optionally, a second boolean parameter can be supplied. When `true`, a string will be returned instead of an array.

```php
echo $faker->sentences();

// ['Optio quos qui illo error.', 'Laborum vero a officia id corporis.', 'Saepe provident esse hic eligendi.']

echo $faker->sentences(2);

// ['Consequatur animi cumque.', 'Quibusdam eveniet ut.']
```

### `paragraph`

Generate a paragraph of text, containing a given amount of sentences. By default, `3` sentences are generated.

Optionally, a second boolean parameter can be supplied. When `false`, only sentences with the given amount of words will
be generated. By default, sentences will deviate from the default word length of 6 by +/- 40%.

```php
echo $faker->paragraph();

// 'Similique molestias exercitationem officia aut. Itaque doloribus et rerum voluptate iure. Unde veniam magni dignissimos expedita eius.'

echo $faker->paragraph(2);

// 'Consequatur velit incidunt ipsam eius beatae. Est omnis autem illum iure.'

echo $faker->paragraph(2, false);

// 'Laborum unde mollitia distinctio nam nihil. Quo expedita et exercitationem voluptas impedit.'
```

### `paragraphs`

Generate an array containing a given amount of paragraphs. By default, `3` paragraphs are generated.

Optionally, a second boolean parameter can be supplied. When `true`, a string will be returned instead of an array.

```php
echo $faker->paragraphs();

// [
//     'Aperiam fugiat alias nobis sunt hic. Quasi dolore autem quo sapiente et distinctio. Dolor ipsum saepe quaerat possimus molestiae placeat iste.', 
//     'Et enim labore debitis consequatur id omnis. Dolorum qui id natus tenetur doloremque sed. Delectus et quis sit quod. Animi assumenda dolorum voluptate nobis aut.',
//     'Voluptas quidem corporis non sed veritatis laudantium eaque modi. Quidem est et est deserunt. Voluptatem magni assumenda voluptas et qui delectus.'
// ]

echo $faker->paragraphs(2);

// [
//     'Quasi nihil nisi enim omnis natus eum. Autem sed ea a maxime. Qui eaque doloribus sit et ab repellat. Aspernatur est rem ut.',
//     'Corrupti quibusdam qui et excepturi. Fugiat minima soluta quae sunt. Aperiam adipisci quas minus eius.'
// ]

echo $faker->paragraphs(2, true);

// Quia odit et quia ab. Eos officia dolor aut quia et sed. Quis sint amet aut. Eius enim sint praesentium error quo sed eligendi. Quo id sint et amet dolorem rem maiores.
//
// Fuga atque velit consectetur id fugit eum. Cupiditate aut itaque dolores praesentium. Eius sunt ut ut ipsam.
```

### `text`

Generate a random string of text. The first parameter represents the maximum number of characters the text should
contain (by default, `200`).

```php
echo $faker->text();

// Omnis accusantium non ut dolor modi. Quo vel omnis eum velit aspernatur pariatur. Blanditiis nisi accusantium a deleniti. Nam aut dolorum aut officiis consequatur.

echo $faker->text(100);

// Quaerat eveniet magni a optio. Officia facilis cupiditate fugiat earum ipsam nemo nulla.
```

<div id="useragent">
<hr/>
</div>

## User Agent

### `userAgent`

Generate a user agent.

```php
echo $faker->userAgent();

// 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5350 (KHTML, like Gecko) Chrome/37.0.806.0 Mobile Safari/5350'
```

### `chrome`

Generate a user agent that belongs to Google Chrome.

```php
echo $faker->chrome();

// 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/40.0.848.0 Mobile Safari/5352'
```

### `firefox`

Generate a user agent that belongs to Mozilla Firefox.

```php
echo $faker->firefox();

// 'Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20121220 Firefox/35.0'
```

### `safari`

Generate a user agent that belongs to Apple Safari.

```php
echo $faker->safari();

// 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_3 rv:5.0; sl-SI) AppleWebKit/532.33.2 (KHTML, like Gecko) Version/5.0 Safari/532.33.2'
```

### `opera`

Generate a user agent that belongs to Opera.

```php
echo $faker->opera();

// 'Opera/8.55 (Windows 95; en-US) Presto/2.9.286 Version/11.00'
```

### `internetExplorer`

Generate a user agent that belongs to Internet Explorer.

```php
echo $faker->internetExplorer();

// 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/5.1)'
```

### `msedge`

Generate a user agent that belongs to Microsoft Ege.

```php
echo $faker->msedge();

// 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36 Edg/99.0.1150.36'
```

<div id="uuid">
<hr/>
</div>

## UUID

### `uuid`

Generate a random UUID.

```php
echo $faker->uuid();

// 'bf91c434-dcf3-3a4c-b49a-12e0944ef1e2', '5b2c0654-de5e-3153-ac1f-751cac718e4e'
```

<div id="version">
<hr/>
</div>

## Version

### `semver`

Generate a random [semantic version v2.0.0](https://semver.org/spec/v2.0.0.html) string.

Optionally, the parameters `$preRelease` and `$build` can be set to `true` to randomly include pre-release and/or build
parts into the version.

Examples:

```php
echo $faker->semver();

// 0.0.1, 1.0.0, 9.99.99

echo $faker->semver(true, true);

// 0.0.1-beta, 1.0.0-rc.1, 1.5.9+276e88b, 5.6.2-alpha.2+20180419085616
```

<div id="blog">
<hr/>
<hr/>
</div>

## Blog

### Task

Generates a random task

Example:

```php
echo $faker -> task();

//  Optimize Marketing Strategy
```

### ComplexTask

Generates a complex task

Example:

```php
echo $faker -> complexTask();

//  Build Detailed Marketing Strategy Quickly
```

### ArticleTitle

Generates a random title for a news or blog article.

Example:

```php
echo $faker -> articleTitle();

// The Art of Creativity with Inspiration
```

### Comment

Generates a comment.

Example:

```php
echo $faker -> comment();

// Great article, very informative!
```

### Sentence

Generates a sentence.

Example:

```php
echo $faker -> sentence();

// As the sun dipped below the horizon, casting a warm glow across the landscape, they sat together on the porch swing, sipping lemonade and reminiscing about the adventures they had shared over the years.
```

### MetaKeyword

Generates a meta keyword.

Example:

```php
echo $faker -> metaKeyword();

// health
```

### MetaKeywords

Generates an array of meta keywords, taking an integer as the number of words to create.

Example:

```php
echo $faker -> metaKeywords(4);

// {'fitness', 'education', 'business', 'fashion'}
```

### MetaDescription

Generates a meta description.

Example:

```php
echo $faker -> metaDescription();

// Get access to diverse perspectives and viewpoints
```

<div id="commerce">
<hr/>
</div>

## Commerce

### SKU

Generates a SKU Code. These are 8 characters in length and a mixture of uppercase letters and numerals.

Example:

```php
echo $faker -> sku();

// Y7KH649F
```

### UPC

Generates a 12 digit UPC code.

Example:

```php
echo $faker -> upc();

// 254686920481
```

### EAN

Generates an EAN code of 13 digits.

Example:

```php
echo $faker -> ean();

// 2593071394710
```

### Category

Generates a category.

Example:

```php
echo $faker -> category();

// Sports & Outdoors
```

### Department

Generates a Department.

Example:

```php
echo $faker -> department();

// Facilities Management
```

### Product Name

Generates a product Name.

Example:

```php
echo $faker -> productName();

// Ergonomic Aluminum Chair
```

### Product Description

Generates a product description.

Example:

```php
echo $faker -> productDescription();

// Efficient Stainless Steel Water Bottle perfect for Hydration during Outdoor Activities
```

### PromoCoupon

Generates a promo coupon.

Example:

```php
echo $faker -> promoCoupon();

// AmazingDeal
```

### PromoCoupon with digits

Generates a promo coupon with 4 random digits appended to it.

Example:

```php
echo $faker -> promoCouponWithDigits();

// AmazingDeal2479
```

### PromoCoupon with discount

Generates a promo coupon onto which will be appended the discount figure you pass to the generator.

Example:

```php
echo $faker -> promoCouponWithDiscount(25);

// AmazingDeal25
```
