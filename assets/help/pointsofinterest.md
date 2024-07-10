Vtl Data Generator makes use of a number of tools to improve its usability.

## Parsedown

This document and the others that comprise the help for the generator is written in markdown. Whilst it's not essential
to write such documents in markdown it has become a popular format as it's well known, especially amongst developers.
Consequently I wanted to see how easily it would be to integrate it into Trongate. As it transpires Integration was very
easy, and implemented via composer.

After adding it to the module (all 47.8 kb of it) all one then needs are some markdown files. to show them it's as
simple as;

```php
/**
     * Displays the customized Faker help documentation.
     *
     * This function reads the markdown file located at '../assets/help/customise.md',
     * parses its content to HTML using Parsedown, and then sets the parsed content
     * along with other necessary data to be used in the 'customisefaker' view.
     *
     * @return void
     */
    public function showCustomiseFakerHelp(): void
    {
        $filepathIntro = __DIR__ . '/../assets/help/customise.md';
        $parsedown = new Parsedown();
        $fileIntro = fopen($filepathIntro, 'r');
        $markdownIntro = $parsedown->text(fread($fileIntro, filesize($filepathIntro)));
        fclose($fileIntro);
        $data['headline'] = 'Vtl Data Generator: Customise Faker';
        $data['markdownCustomise'] = $markdownIntro;
        $data['view_module'] = 'vtl_gen';
        $data['view_file'] = 'customisefaker';
        $this->template('admin', $data);
    }
```

This ease of use made it an obvious choice for inclusion in the Data Generator.

You can find out more about parsedown [here](https://parsedown.org/).

## PrismJs

The main reason that I wanted to use markdown was because it makes adding code examples to documentation really simple,
but to display them we really need good syntax support. For that we need [PrismJs](https://prismjs.com/).

PrismJs is ridiculously easy to use, Just select the languages you want to have support for and any plugins you need (
along with a theme) from the download page and then download the JS and CSS files. As this is Trongate you can place the
downloaded files in Your assets directory.

After that just add links to the two files in your view's html.

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtl_gen_module/css/prism.css">
    <title>Vtl_Generator_Customise Fake Data</title>
</head>
<body>
<script src="<?= BASE_URL ?>vtl_gen_module/js/prism.js"></script>
</body>
</html>
```

Then when it comes to embedding example code into a page all you need add is the following;

```html

<pre><code class="language-css">p { color: red }</code></pre>
```

Markdown of course does all of the creation of ```html <pre><code></code></pre>``` tags for you so when it comes to
actually displaying the markdown with parsedown its as simple as this;

```html

<section>
    <div class="container">
        <div><?php echo $markdownCustomise; ?></div>
    </div>
</section>
```
<br>

## Code-Input

Code-Input is a web component (designed and created by Oliver Greer) that takes PrismJs and uses it to create a syntax highlighted code editor.  The Vtl Data Generator makes use of this in its Create Sql Queries view.  Generally it's much easier to follow code when it's syntactically highlighted and this was such a nice, simple example of a working syntax enabled code editor that it I felt it was worth using and brining to the attention of those of you using the Generator.

<br>

## FakerPhp

This whole little project grew out of the fact that I wanted a quick and simple way to add large quantities of fake data
into my database tables in order to be able to see how my applications would perform under stress. I learn't the hard
way many years ago that simple test things with a few recors simply doesn't replicate real life scenarios but who in
their right mind is going to add reams of data manually to an application.

Faker started life as a javascrpit library and has since been ported to other languages. The best example by far is
[Bogus](https://github.com/bchavez/Bogus). Nothing comes even remotely close to this .Net implementation. For Php we
have to make do with [FakerPhp](https://fakerphp.org/).

FakerPhp is not as sophisticated as Bogus but it still works. Install it via composer. It's a chunky bit of code but
then considering what it has top do it would be.

To make full use of Faker you need to read up the documentation to understand how it works. In reality I've done that
for you.

There are some important caveats to realise when using this with Trongate. Although loosely based on the
Model View Controller architecture Trongate does not make use of Models. There's nothing wrong with that per see but it
does make
generating Fake Data that much harder because everything ends up having to be done dynamically. By definition that means
that the VTL Data Generator can be a little hit and miss when it comes to generating absolutely realistic fake data
straight out of the box.

This (the Vtl Data Generator) is a development tool meant to be used during the development process so there is an
assumption that you, the end users) are reasonably aufay with the notion of actually 'developing' and are prepared to
get your hands dirty in order to produce the results that you want. There is a fairly comprehensive help page on
customising the Faker and I hope that the way Fake Data is generated has been laid out reasonably clearly.

I have provided examples of small tweaks that you can make to fully fledged examples of custom Faker Providers. Once
familiar with those you should have no trouble creating providers that will be capable of producing realistic fake data
that meets your needs.

## Tabulator JS

If you are working with large quantities of data (and by large I mean 100's of thousands of rows plus) then you really
need proper data grids to display it. Plain html tables simply don't cut it. Trongate does a pretty good job of handling
data and it's in-house pagination is implemented nicely but it simply can't match a properly implemented data grid. I
looked around for actively tried several purpose built data grids but hit a brick wall until I did some research into
how I would go about creating a visual Database table builder and stumbled across  [Tabulator](https://tabulator.info/).
This ticked the box for the majority of my requirements and it turned out to be remarkably easy to integrate into
Trongate.


## Light / Dark mode

The Generator has been designed to recognise your system preferences with regard to Light or Dark mode and should adjust accordingly.  This also extends to all of the illustrations and icons that it uses to make it as easy on the eye as it can be.  In truth this is still a work in progress so there may still be the odd glitch in its implementation.

## Custom Modals

The Vtl Data Generator has a couple of custom modals that generate their own events which I happened to need to accomplish a couple of specific tasks.  There is nothing overly complicated about them, and if you find yourself in need of something similar feel free to borrow the code that makes them work.

## Automatic Updater

The Vtl Data Generator has an automatic updater built in which will link to the github repo and determine if a newer version is available.

This was designed in such a way that it could be applied to any Trongate Module that might require updating.

For this to work there are some basic requirements.

- The Module has a configuration file that contains a version number.
- The Nodule has its own Github repository.
- The user of the Module has sufficient privileges to use PHP Exec.
- The machine on which the module that would be updated is running has Git installed.

The way this works is that when the module is initially instantiated it issues a call to the github repo to check the Configuration file there and match the version numbers.  The results of that call are then cached in a json file.

> The reason for the cache is related to the fact that Github does not take kindly to frequent calls to it so the updater will make one call every 24 hours, relying on the cache file for every other time that the Module is instantiated.

Within the constructor The Module initiates the Github Call

```php
 // check for updates
        $this->initialiseUpdateCache();
        $this->updateInfo = $this->CheckGithubForUpdates();
```
<br>

Creating an initial cache file to start with if one doesn't exist

<br>

```php
 private function initialiseUpdateCache() {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';

        if (!file_exists($cache_file)) {
            $initial_data = [
                'update_available' => false,
                'last_check' => time(),
                'error' => null,
                'new_version' => null
            ];

            file_put_contents($cache_file, json_encode($initial_data));

            // Ensure the file has the correct permissions
            chmod($cache_file, 0644);
        }
    }
```
 <br>

Then calling Github.

<br>

```php
 private function CheckGithubForUpdates() {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';
        $cache_time = 86400; // 24 hours

        if (file_exists($cache_file)) {
            $cache_data = json_decode(file_get_contents($cache_file), true);
            if (time() - $cache_data['last_check'] < $cache_time) {
                return $cache_data;
            }
        }

        $github_url = 'https://raw.githubusercontent.com/domsinclair/vtlgen/master/assets/vtlgenConfig.php';
        $github_content = @file_get_contents($github_url);

        if ($github_content === false) {
            $result = [
                'update_available' => false,
                'last_check' => time(),
                'error' => 'Unable to check for updates',
                'new_version' => null
            ];
        } else {
            preg_match("/define\s*\(\s*'VERSION'\s*,\s*'Version:\s*([\d.]+)'\s*\)/", $github_content, $matches);
            $github_version = isset($matches[1]) ? $matches[1] : null;

            $current_version = str_replace('Version: ', '', VERSION);

            $result = [
                'update_available' => version_compare($github_version, $current_version, '>'),
                'last_check' => time(),
                'error' => null,
                'new_version' => $github_version
            ];
        }

        file_put_contents($cache_file, json_encode($result));
        return $result;
    }
```
<br>

> NOTE:  The address to the github repository is very specific for making calls such as this and is not the same as the one you would normally see in your web browser address bar.

<br>

At this point the system has now determined whether or not an update is available.  The exact way that you choose to notify your end user of this is up to you.  In the case of the Data Generator this is done via the appearance of an Update icon to the left of the Version Number Text.

When the end user clicks the update button it initially instigates a check to see if the user has the correct priveleges to use Php Exec and that the machine on which the module resides has Git installed.

<br>

```php
  public function vtlgenCheckUpdatePrerequisites(): array
    {
        $result = [
            'canUpdate' => true,
            'message' => '',
            'execEnabled' => function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))),
            'gitInstalled' => false
        ];

        if (!$result['execEnabled']) {
            $result['canUpdate'] = false;
            $result['message'] = 'The PHP exec() function is not available. Please contact your server administrator.';
            return $result;
        }

        // Check if Git is installed
        exec('git --version', $output, $returnVar);
        $result['gitInstalled'] = ($returnVar === 0);

        if (!$result['gitInstalled']) {
            $result['canUpdate'] = false;
            $result['message'] = 'Git is not installed or not accessible. Please install Git or contact your server administrator.';
        }

        return $result;
    }
```

<br>

If the pre requisites check passes then the update is triggered.

<br>

```php
public function updateModule() {
        // Define paths
        $modulesPath = APPPATH . 'modules/';
        $currentVtlgenPath = $modulesPath . 'vtlgen/';
        $newVtlgenPath = $modulesPath . 'vtlgen_new_' . time() . '/';
        $githubRepo = 'https://github.com/domsinclair/vtlgen.git';

        // Step 1: Clone the new version
        $output = null;
        $returnVar = null;
        exec("git clone $githubRepo $newVtlgenPath 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            // Handle error
            $error = implode("\n", $output);
            return "Failed to clone repository: $error";
        }

        // Step 2: Compare versions
        $currentVersion = $this->getVersion($currentVtlgenPath);
        $newVersion = $this->getVersion($newVtlgenPath);

        if (version_compare($newVersion, $currentVersion, '<=')) {
            $this->removeDirectory($newVtlgenPath);
            return "Current version ($currentVersion) is up to date. No update needed.";
        }

        // Step 3: Inform user about the update
        $message = "A new version ($newVersion) has been downloaded. " .
            "Please review the changes in the 'vtlgen_new_" . time() . "' directory " .
            "and manually merge your customizations.";

        // Step 4: Update the version check cache
        $this->updateVersionCache($newVersion);

        return $message;
    }

     private function getVersion($path) {
        $configFile = $path . 'assets/vtlgenConfig.php';
        if (!file_exists($configFile)) {
            return null;
        }
        $content = file_get_contents($configFile);
        preg_match("/define\s*\(\s*'VERSION'\s*,\s*'Version:\s*([\d.]+)'\s*\)/", $content, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    private function updateVersionCache($newVersion) {
        $cache_file = APPPATH . 'modules/vtlgen/assets/vtlUpdateCache.json';
        $cache_data = json_decode(file_get_contents($cache_file), true);
        $cache_data['update_available'] = false;
        $cache_data['last_check'] = time();
        $cache_data['new_version'] = $newVersion;
        file_put_contents($cache_file, json_encode($cache_data));
    }
```

Note that a new Module directory is created, meaning that once the download has succeeded the end user will need to delete to old module directory and rename the new one to the actual Module Name.

For the VTL Data Generator this was important as the end user may well have a highly customised version and they would want to preserve their customised files.

