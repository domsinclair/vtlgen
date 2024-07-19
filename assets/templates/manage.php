<?php
$table_headers = json_decode('{{tableHeaders}}', true);
$primaryKey = '{{primaryKey}}';
?>
<h1><?= $headline ?></h1>
<?php
flashdata();
echo validation_errors();
echo '<p>';
echo anchor('{{moduleName}}/create', 'Create New {{moduleName}} Record', array("class" => "button")).'</p>';
if (strtolower(ENV) === 'dev') {
    echo anchor('api/explorer/{{moduleName}}', 'API Explorer', array("class" => "button alt"));
}
echo '</p>';

echo Pagination::display($pagination_data);

if (count($rows) > 0) { ?>
    <table id="results-tbl">
        <thead>
        <tr>
            <?php
            // Assuming $table_headers is an array of column names
            foreach ($table_headers as $header): ?>
                <th><?= $header ?></th>
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
                <td>
                    <?= anchor('{{moduleName}}/show/'.$row->{{primaryKey}}, 'View', array('class' => 'button alt')) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
} else {
    echo '<p>No {{moduleName}} records were found.</p>';
}

// Display pagination links again if there are many records
if (count($rows) > 10) {
    echo Pagination::display($pagination_data);
}
?>