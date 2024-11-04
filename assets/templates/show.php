
<?php
$draw_picture_uploader = true;
if (isset($data[0]->picture)) {
    $picture_path = BASE_URL.'uploads/images/thumbnails/'.$data[0]->picture;
    $draw_picture_uploader = false;
}
?>
<div class="breadcrumb">
    <a href="<?= BASE_URL ?>">Home</a>
<h1><?= out($headline) ?> <span class="smaller hide-sm">(Record ID: <?= out($update_id) ?>)</span></h1>
<?= flashdata() ?>
<div class="card">
    <div class="card-heading">Options</div>
    <div class="card-body">
        <?php
        echo anchor('{{moduleName}}/manage', 'View All {{moduleName}}', array("class" => "button alt"));
        echo anchor('{{moduleName}}/create/'.$update_id, 'Update Details', array("class" => "button"));
        $attr_delete = array(
            "class" => "danger go-right",
            "id" => "btn-delete-modal",
            "onclick" => "openModal('delete-modal')"
        );
        echo form_button('delete', 'Delete', $attr_delete);
        ?>
    </div>
</div>
<div class="three-col">

    <div class="card">
        <div class="card-heading"> <?= '{{singularModuleName}}' ?> Details</div>
        <div class="card-body">
            <div class="record-details">
                <?php foreach ($columns as $column): ?>
                    <?php if ($column['extra'] != 'auto_increment'): ?>
                        <div class="row">
                            <div><?= $column['name'] ?></div>
                            <div><?= out($data[0]->{$column['name']}) ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <?php if (isset($picture_path) && $picture_path != ""): ?>
        <div class="card">
            <div class="card-heading">Picture</div>
            <div class="card-body picture-preview">
                <?php if ($draw_picture_uploader == true): ?>
                    <?= form_open_upload(segment(1).'/submit_upload_picture/'.$update_id) ?>
                    <?= validation_errors() ?>
                    <p>Please choose a picture from your computer and then press 'Upload'.</p>
                    <?= form_file_select('picture') ?>
                    <?= form_submit('submit', 'Upload') ?>
                    <?= form_close() ?>
                <?php else: ?>
                    <p class="text-center">
                        <button class="danger" onclick="openModal('delete-picture-modal')"><i class="fa fa-trash"></i> Delete Picture</button>
                    </p>
                    <p class="text-center">
                        <img src="<?= $picture_path ?>" alt="picture preview">
                    </p>
                    <div class="modal" id="delete-picture-modal" style="display: none;">
                        <div class="modal-heading danger"><i class="fa fa-trash"></i> Delete Picture</div>
                        <div class="modal-body">
                            <?= form_open(segment(1).'/ditch_picture/'.$update_id) ?>
                            <p>Are you sure?</p>
                            <p>You are about to delete the picture. This cannot be undone. Do you really want to do this?</p>
                            <p>
                                <button type="button" name="close" value="Cancel" class="alt" onclick="closeModal()">Cancel</button>
                                <button type="submit" name="submit" value="Yes - Delete Now" class="danger">Yes - Delete Now</button>
                            </p>
                            <?= form_close() ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    {{multiFileUploader}}
    <div class="card">
        <div class="card-heading">Comments</div>
        <div class="card-body">
            <div class="text-center">
                <p><button class="alt" onclick="openModal('comment-modal')">Add New Comment</button></p>
                <div id="comments-block"><table></table></div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="comment-modal" style="display: none;">
    <div class="modal-heading"><i class="fa fa-commenting-o"></i> Add New Comment</div>
    <div class="modal-body">
        <p><textarea placeholder="Enter comment here..."></textarea></p>
        <p>
            <?php
            $attr_close = array(
                "class" => "alt",
                "onclick" => "closeModal()"
            );
            echo form_button('close', 'Cancel', $attr_close);
            echo form_button('submit', 'Submit Comment', array("onclick" => "submitComment()"));
            ?>
        </p>
    </div>
</div>
<div class="modal" id="delete-modal" style="display: none;">
    <div class="modal-heading danger"><i class="fa fa-trash"></i> Delete Record</div>
    <div class="modal-body">
        <?= form_open('{{moduleName}}/submit_delete/'.$update_id) ?>
        <p>Are you sure?</p>
        <p>You are about to delete a <?= ucfirst('{{moduleName}}') ?> record. This cannot be undone. Do you really want to do this?</p>
        <?php
        echo '<p>'.form_button('close', 'Cancel', $attr_close);
        echo form_submit('submit', 'Yes - Delete Now', array("class" => 'danger')).'</p>';
        echo form_close();
        ?>
    </div>
</div>
<script>
    const token = '<?= $token ?>';
    const baseUrl = '<?= BASE_URL ?>';
    const segment1 = '<?= segment(1) ?>';
    const updateId = '<?= $update_id ?>';
    const drawComments = true;
</script>
