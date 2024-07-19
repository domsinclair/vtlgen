<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        <?= ucfirst($view_module) ?> Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);

        $formFields = json_decode('{{formFields}}', true);

        foreach ($formFields as $field) {
            $fieldName = $field['Field'];
            $fieldType = $field['Type'];
            $fieldKey = $field['Key'];
            $fieldExtra = $field['Extra'];

            // Skip fields that are primary keys and auto-increment
            if ($fieldKey === 'PRI' && strpos($fieldExtra, 'auto_increment') !== false) {
                continue;
            }

            echo form_label(ucfirst($fieldName));

            $fieldValue = '';
            if (isset($data[0][0]->$fieldName)) {
                $fieldValue = $data[0][0]->$fieldName;
            }

            if (strpos($fieldType, 'int') !== false) {
                echo form_input($fieldName, $fieldValue, array("placeholder" => "Enter " . ucfirst($fieldName), "type" => "number"));
            } elseif (strpos($fieldType, 'text') !== false || strpos($fieldType, 'varchar') !== false) {
                echo form_input($fieldName, $fieldValue, array("placeholder" => "Enter " . ucfirst($fieldName), "autocomplete" => "off"));
            } elseif (strpos($fieldType, 'date') !== false) {
                $attr = array("class" => "datetime-picker", "autocomplete" => "off", "placeholder" => "Select Date");
                echo form_input($fieldName, $fieldValue, $attr);
            } else {
                echo form_input($fieldName, $fieldValue, array("placeholder" => "Enter " . ucfirst($fieldName)));
            }
        }

        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>
