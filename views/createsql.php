<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="color-scheme" content="dark light">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/tabulator_midnight.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/prismEditor.css">

    <script src="<?= BASE_URL ?>vtlgen_module/js/prismEditor.js"></script>
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/code-input.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/code-input.css">
    <script src="<?= BASE_URL ?>vtlgen_module/js/auto-close-brackets.js"></script>
    <script src="<?= BASE_URL ?>vtlgen_module/js/indent.js"></script>
    <script type="text/javascript" src="<?= BASE_URL ?>vtlgen_module/js/tabulator.js"></script>

    <script>

        codeInput.registerTemplate("syntax-highlighted", codeInput.templates.prism(Prism, [
            new codeInput.plugins.AutoCloseBrackets(),
            new codeInput.plugins.Indent(true, 2) // 2 spaces indentation
        ] ));
    </script>
    <title>Vtl_Data_Generator</title>
</head>
<body>
<h2 class="text-center"><?= $headline ?></h2>
<section>
    <div class="container">
        <div class="flex" style="margin-bottom: 15px">
            <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <div id="datatable"></div
    </div>
</section>
<section>
   <div class="container">
   <code-input template="syntax-highlighted" language ="sql"></code-input>
   </div>
</section>


</body>
</html>

<style>
    code-input {
        width: calc(100% - 40px); /* 100% - 2*margin */
        margin: 20px;
        --padding: 20px;
    }
</style>
