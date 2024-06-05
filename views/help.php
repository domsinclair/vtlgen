<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/vtl.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vtlgen_module/css/prism.css">
    <title>Vtl Data Generator</title>
</head>
<body>
<h2 class="container, text-center"><?= $headline ?></h2>
<section>
    <div class="container">
        <div class="flex">
            <?php echo anchor('vtlgen', 'Back', array("class" => "button")); ?>
        </div>
        <section>
            <div class="container">
                <div><?php echo $markdown; ?></div>
            </div>
        </section>
    </div>
</section>
<a href="#" class="back-to-top">
    <span><img src="vtlgen_module/help/images/vtluparrow.svg" </span>
</a>
<script src="<?= BASE_URL ?>vtlgen_module/js/prism.js"></script>
</body>
</html>
