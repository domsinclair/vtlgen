<?php
$table_headers = json_decode('{{tableHeaders}}', true);
$primaryKey = '{{primaryKey}}';
?>
<h1><?= $headline ?></h1>
<?php
flashdata();
echo validation_errors();
echo '<p>';
echo anchor('{{moduleName}}/create', 'Create New {{singularModuleName}} Record', array("class" => "button"));
if (strtolower(ENV) === 'dev') {
    echo anchor('api/explorer/{{moduleName}}', 'API Explorer', array("class" => "button alt"));
}
echo '</p>';

echo Pagination::display($pagination_data);

if (count($rows) > 0) { ?>
    <table id="results-tbl">
        <thead>
        <tr>
            <th colspan="<?= count($table_headers) + 1 ?>">
                <div class="search-header">
                    <div class="search-form">
                        <?php
                        echo form_open('{{moduleName}}/manage/1/', array("method" => "get"));
                        ?>
                        <div class="search-item">
                            <?= form_label('Search Field:', 'search_field') ?>
                            <?php
                            $search_field_options = json_decode('{{searchFieldOptions}}', true);
                            echo form_dropdown('search_field', $search_field_options, '', array('id' => 'search_field'));
                            ?>
                        </div>

                        <div class="search-item">
                            <?= form_label('Operator:', 'search_operator') ?>
                            <?php
                            $search_operator_options = json_decode('{{searchOperatorOptions}}', true);
                            echo form_dropdown('search_operator', $search_operator_options, '', array('id' => 'search_operator'));
                            ?>
                        </div>

                        <div class="search-item">
                            <?= form_label('Search Term:', 'search_term') ?>
                            <?= form_input('search_term', '', array('id' => 'search_term', 'placeholder' => 'Enter Search Term...')) ?>
                        </div>
                        <div class="search-item">
                            <?= form_submit('search_submit', 'Search', array('class' => 'button ')) ?>
                        </div>
                        <?php
                        echo form_close();
                        ?>
                    </div>
                    <div class="records-per-page">
                        Records Per Page:
                        <?php
                        $dropdown_attr = array('onchange' => 'setPerPage()');
                        echo form_dropdown('per_page', $per_page_options, $selected_per_page, $dropdown_attr);
                        ?>
                    </div>
                </div>
            </th>
        </tr>
        <tr>
            <?php foreach ($table_headers as $header): ?>
                <th><?= ucfirst($header) ?></th>
            <?php endforeach; ?>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($table_headers as $header): ?>
                    <td><?= $row->$header ?? '' ?></td>
                <?php endforeach; ?>
                <td><?= anchor('{{moduleName}}/show/'.$row->$primaryKey, 'View', array('class' => 'button alt')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
} else {
    echo '<p>No {{singularModuleName}} records were found.</p>';
}

// Display pagination links again if there are many records
if (count($rows) > 10) {
    echo Pagination::display($pagination_data);
}
?>

<style>
    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-left: 10px;
    }

    .search-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .search-item {
        display: flex;
        align-items: center;
    }

    .search-item label {
        font-size: 16px;
        margin-right: 10px;
        margin-left: 10px;
        margin-bottom: 10px;
        white-space: nowrap;
        height: 30px;
    }

    .search-form select,
    .search-form input,
    .search-form button {
        padding: 5px;
        height: 30px;
    }

    .search-form button {
        color: #1a1a1a;
        margin-left: 10px;
        height: 30px;
        line-height: 20px;
        margin-bottom: 15px;
        background-color: var(--primary-color);
        padding: 5px;
        text-align: center;
        border-radius: 10px;
    }

    .records-per-page {
        display: flex;
        align-items: center;
    }

    .records-per-page select {
        margin-left: 10px;
        padding: 5px;
        height: 30px;
    }

    #results-tbl th,
    #results-tbl td {
        padding: 10px;
        text-align: left;
    }
</style>
