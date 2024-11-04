<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        <?= '{{singularModuleName}}' ?> Details
    </div>

    <div class="card-body">
        <?php
            echo form_open($form_location);

            $formFields = json_decode('{{formFields}}', true);

            foreach ($formFields as $field) {
                $fieldName = $field['name'];
                $fieldType = $field['type'];
                $fieldKey = $field['key'];
                $fieldExtra = isset($field['extra']) ? $field['extra'] : '';
                $foreignKey = isset($field['foreign_key']) ? $field['foreign_key'] : null;

                // Exclude auto-increment primary keys
                if ($fieldKey === 'PRI' && strpos($fieldExtra, 'auto_increment') !== false) {
                    continue;
                }

                // Determine the label
                if (strpos($fieldType, 'date') !== false) {
                    echo form_label(ucfirst($fieldName) . ' (enter as yyyy-mm-dd)');
                } elseif (strpos($fieldType, 'datetime') !== false || (strpos($fieldType, 'int') !== false && $fieldType === 'int(11)')) {
                    echo form_label(ucfirst($fieldName) . ' (enter as yyyy-mm-dd hh:mm:ss)');
                } else {
                    echo form_label(ucfirst($fieldName));
                }

                $fieldValue = '';
                if (isset($data[0][0]->$fieldName)) {
                    $fieldValue = $data[0][0]->$fieldName;
                }

                $fieldAttributes = ["placeholder" => "Enter " . ucfirst($fieldName), "autocomplete" => "off"];
                if ($foreignKey) {
                    $fieldAttributes['class'] = "form-control foreign-key-input";
                    $fieldAttributes['data-foreign-table'] = $foreignKey['table'];
                    $fieldAttributes['data-foreign-column'] = $foreignKey['column'];
                } else {
                    $fieldAttributes['class'] = "form-control";
                }

                // Generate the field input based on type
                if (strpos($fieldType, 'int') !== false && $fieldType === 'tinyint(1)') {
                    $isChecked = ($fieldValue == 1) ? true : false;
                    echo '<div>';
                    echo ucfirst($fieldName) . ' ';
                    echo form_checkbox($fieldName, '1', $isChecked);
                    echo '</div>';
                } elseif (strpos($fieldType, 'varchar') !== false) {
                    preg_match('/varchar\((\d+)\)/', $fieldType, $matches);
                    if (isset($matches[1])) {
                        $fieldAttributes['maxlength'] = $matches[1];
                    }
                    echo form_input($fieldName, $fieldValue, $fieldAttributes);
                } elseif (strpos($fieldType, 'text') !== false) {
                    echo form_textarea($fieldName, $fieldValue, array_merge($fieldAttributes, ["rows" => 5]));
                } elseif (strpos($fieldType, 'date') !== false) {
                    $fieldAttributes['type'] = 'date';
                    echo form_input($fieldName, $fieldValue, $fieldAttributes);
                } elseif (strpos($fieldType, 'datetime') !== false || (strpos($fieldType, 'int') !== false && $fieldType === 'int(11)')) {
                    $fieldAttributes['type'] = 'datetime-local';
                    echo form_input($fieldName, $fieldValue, $fieldAttributes);
                } else {
                    echo form_input($fieldName, $fieldValue, $fieldAttributes);
                }

                // Add expandable area for foreign key fields
                if ($foreignKey) {
                    echo '<div id="' . $fieldName . '_details" class="expandable-field"></div>';
                }
            }

            echo form_submit('submit', 'Submit');
            echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
            echo form_close();
        ?>

        <!--Used for those modules that have foreign keys-->
        <script type="text/javascript">
            window.baseUrl = "<?= BASE_URL ?>";
        </script>

    </div>
</div>
