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