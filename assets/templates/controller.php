<?php
class {{ModuleName}} extends Trongate {

    private $default_limit = 20;

    private $per_page_options = array(10, 20, 50, 100);

    private $columns = [];

    private $validationRules = [];

   public function __construct() {
       parent::__construct();
       $this->columns = json_decode('{{columns}}', true);
        $this->validationRules = json_decode('{{validationRules}}', true);

   }

    public function index () {
        $data['view_module'] = '{{stlModuleName}}';
        $this->view('manage', $data);

    }

    public function manage() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $data['headline'] = 'Manage {{ModuleName}}';

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';

            // Access search parameters from the GET request
            $searchField = $_GET['search_field'];
            $searchOperator = $_GET['search_operator'];
            $searchTerm = $_GET['search_term'];

            // Execute the dynamic search query
            $all_rows = $this->execute_search_query($searchField, $searchOperator, $searchTerm);
        } else {
            $all_rows = $this->model->get('{{primaryKey}} asc');
        }

        // Pagination configuration
        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = '{{stlModuleName}}/manage';
        $pagination_data['record_name_plural'] = '{{stlModuleName}}';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = '{{stlModuleName}}';

        $template_to_use = 'admin';
        $view_file_to_use = 'manage';

        $data['table_headers'] = '{{tableHeaders}}';
        $data['view_file'] = $view_file_to_use;
        $this->template($template_to_use, $data);
    }






    function _get_limit() {
        if (isset($_SESSION['selected_per_page'])) {
            $limit = $this->per_page_options[$_SESSION['selected_per_page']];
        } else {
            $limit = $this->default_limit;
        }

        return $limit;
    }

    function _get_selected_per_page() {
        if (!isset($_SESSION['selected_per_page'])) {
            $selected_per_page = $this->per_page_options[1];
        } else {
            $selected_per_page = $_SESSION['selected_per_page'];
        }

        return $selected_per_page;
    }

    function _reduce_rows($all_rows) {
        $start_index = $this->_get_offset();
        $limit = $this->_get_limit();

        return array_slice($all_rows, $start_index, $limit);
    }

    function _get_offset() {
        $page_num = segment(3);

        if (!is_numeric($page_num)) {
            $page_num = 0;
        }

        if ($page_num>1) {
            $offset = ($page_num-1)*$this->_get_limit();
        } else {
            $offset = 0;
        }

        return $offset;
    }

    public function create() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $update_id = segment(3);
        $submit = post('submit');

        if ((is_numeric($update_id)) && ($submit == '')) {
            $data[0] = $this->getDataFromDB($update_id); // Adjusted to match expected data structure
        } else {
            $data[0] = $this->getDataFromPost(); // Adjusted to match expected data structure
        }

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update {{moduleName}} Record';
            $data['cancel_url'] = BASE_URL . '{{stlModuleName}}/show/' . $update_id;
        } else {
            $data['headline'] = 'Create New {{moduleName}} Record';
            $data['cancel_url'] = BASE_URL . '{{stlModuleName}}/manage';
        }

        $data['form_location'] = BASE_URL . '{{stlModuleName}}/submit/' . $update_id;
        $data['formFields'] = json_encode($this->columns); // Pass columns to view
        $data['view_file'] = 'create';
        $data['view_module'] = '{{stlModuleName}}';
        $this->template('admin', $data);
    }



    public function submit() {

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit', true);

        if ($submit == 'Submit') {
            // Dynamically set validation rules based on columns
            foreach ($this->columns as $column) {
                if ($column['Field'] !== '{{primaryKey}}' && isset($this->validationRules[$column['Field']])) {
                    $this->validation->set_rules($column['Field'], ucfirst($column['Field']), $this->validationRules[$column['Field']]);
                }
            }

            $result = $this->validation->run();

            if ($result == true) {
                $update_id = (int) segment(3);
                $data = $this->getDataFromPost();
                $data['{{primaryKey}}'] = $update_id;

                if ($update_id > 0) {
                    $this->model->update_where('{{primaryKey}}', $update_id, $data, '{{stlModuleName}}');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    $update_id = $this->model->insert($data, '{{stlModuleName}}');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('{{stlModuleName}}'.'/show/'.$update_id);
            } else {
                $this->create();
            }
        }

    }

    public function submit_delete(): void {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();


        $submit = post('submit');
        $params['update_id'] = (int) segment(3);
        $moduleName = '{{stlModuleName}}';
        if (($submit == 'Yes - Delete Now') && ($params['update_id'] > 1)) {
            //delete all of the comments associated with this record
            $sql = 'delete from trongate_comments where target_table = :module and update_id = :update_id';
            $params['module'] = $stlModuleName;
            $this->model->query_bind($sql, $params);

            // Create a custom delete query to cater for primary keys that are not named id

            $primaryKey = '{{primaryKey}}'; // Replace with the actual primary key
            $sqlDelete = 'DELETE FROM ' . $stlModuleName . ' WHERE ' . $primaryKey . ' = ' .$params['update_id'];
            $this->model->query($sqlDelete);


            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('{{stlModuleName}}/manage');
        } elseif ($params['update_id'] === 1) {
            $form_submission_errors['update_id'][] = 'Deletion of the homepage record is not permitted.';
            $_SESSION['form_submission_errors'] = $form_submission_errors;
            redirect('{{stlModuleName}}/manage');
        }
    }

    private function getDataFromPost(){
        $data = [];
        foreach ($this->columns as $column) {
            $fieldName = $column['Field'];
            $data[$fieldName] = post($fieldName);
        }
        return $data;
    }

    private function getDataFromDb($update_id) {
        $record = $this->model->get_where_custom('{{primaryKey}}', $update_id,  order_by:'{{primaryKey}}');
        return (array) $record;
    }

    private function execute_search_query($searchField, $searchOperator, $searchTerm) {
        if ($searchOperator == 'LIKE') {
            $searchTerm = '%' . $searchTerm . '%';
        }
        return $this->model->get_where_custom($searchField, $searchTerm, $searchOperator, '{{primaryKey}} asc');
    }


    public function show() {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = (int) segment(3);

        if ($update_id == 0) {
            redirect('{{stlModuleName}}/manage');
        }

        $data = $this->getDataFromDB($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect(strtolower('Customers/manage'));
        } else {
            $data['draw_picture_uploader'] = $this->hasPictureField();
            $picture_settings = null;
            $picture_path = '';

            if ($data['draw_picture_uploader']) {
                $picture_settings = $this->_init_picture_settings();
            }

            if ($picture_settings) {
                $column_name = $picture_settings['target_column_name'];
                if (isset($data[0]->$column_name) && !empty($data[0]->$column_name)) {
                    // Picture exists
                    $picture = $data[0]->$column_name;
                    if ($picture_settings['upload_to_module'] == true) {
                        $module_assets_dir = BASE_URL . segment(1) . MODULE_ASSETS_TRIGGER;
                        $picture_path = $module_assets_dir . '/' . $picture_settings['destination'] . '/' . $update_id . '/' . $picture;
                    } else {
                        $picture_path = BASE_URL . $picture_settings['destination'] . '/' . $update_id . '/' . $picture;
                    }
                    $data['draw_picture_uploader'] = false;
                } else {
                    $data['draw_picture_uploader'] = true;
                }
            }

            if ($data['draw_picture_uploader']) {
                if (!$picture_settings) {
                    $picture_settings = $this->_init_picture_settings();
                }
                $this->_make_sure_got_destination_folders($update_id, $picture_settings);
                if ($picture_settings['upload_to_module'] == true) {
                    $module_assets_dir = BASE_URL . segment(1) . MODULE_ASSETS_TRIGGER;
                    $picture_path = $module_assets_dir . '/' . $picture_settings['destination'] . '/' . $update_id . '/';
                } else {
                    $picture_path = BASE_URL . $picture_settings['destination'] . '/' . $update_id . '/';
                }
            }

            $data['picture_path'] = $picture_path;
            $data['columns'] = $this->columns;
            $data['update_id'] = $update_id;
            $data['headline'] = 'View {{moduleName}} Record';
            $data['view_file'] = 'show';
            $this->template('admin', $data);
        }
    }






    ///////////////////////////////////////////////////////////////////////////
    /// Picture Uploader Related Functions
    ///////////////////////////////////////////////////////////////////////////

    private function hasPictureField() {
        foreach ($this->columns as $column) {
            if (strpos($column['Field'], 'picture') !== false) {
                return true;
            }
        }
        return false;
    }

    private function getPicturePath($update_id) {
        $picture_settings = $this->_init_picture_settings();
        $destination = $picture_settings['destination'];
        $destination = 'modules/'.segment(1).'/assets/'.$destination;
        $target_dir = APPPATH.$destination.'/'.$update_id;
        return $target_dir;
    }

    function _init_picture_settings() {
        $picture_settings['max_file_size'] = 2000;
        $picture_settings['max_width'] = 1200;
        $picture_settings['max_height'] = 1200;
        $picture_settings['resized_max_width'] = 450;
        $picture_settings['resized_max_height'] = 450;
        $picture_settings['destination'] = '{{stlModuleName}}_pics';
        $picture_settings['target_column_name'] = 'picture';
        $picture_settings['thumbnail_dir'] = '{{stlModuleName}}_pics_thumbnails';
        $picture_settings['thumbnail_max_width'] = 120;
        $picture_settings['thumbnail_max_height'] = 120;
        $picture_settings['upload_to_module'] = true;
        $picture_settings['make_rand_name'] = false;
        return $picture_settings;
    }

    function _make_sure_got_destination_folders($update_id, $picture_settings) {
        $destination = $picture_settings['destination'];
        $destination = 'modules/'.segment(1).'/assets/'.$destination;
        $target_dir = APPPATH.$destination.'/'.$update_id;

        if (!file_exists($target_dir)) {
            //generate the image folder
            mkdir($target_dir, 0777, true);
        }

    }

    function submit_upload_picture($update_id) {

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if ($_FILES['picture']['name'] == '') {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $submit = post('submit');

        if ($submit == 'Upload') {
            $picture_settings = $this->_init_picture_settings();
            extract($picture_settings);

            $validation_str = 'allowed_types[gif,jpg,jpeg,png]|max_size['.$max_file_size.']|max_width['.$max_width.']|max_height['.$max_height.']';
            $this->validation_helper->set_rules('picture', 'item picture', $validation_str);

            $result = $this->validation_helper->run();

            if ($result == true) {

                $config['destination'] = $destination.'/'.$update_id;
                $config['max_width'] = $resized_max_width;
                $config['max_height'] = $resized_max_height;

                //upload the picture
                $this->upload_picture_alt($config);

                //update the database
                $data[$target_column_name] = $_FILES['picture']['name'];
                $this->model->update_where('{{primaryKey}}', $update_id, $data, '{{stlModuleName}}');

                $flash_msg = 'The picture was successfully uploaded';
                set_flashdata($flash_msg);
                redirect($_SERVER['HTTP_REFERER']);

            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

    }

    function upload_picture_alt($data) {
        //check for valid image width and mime type
        $userfile = array_keys($_FILES)[0];
        $target_file = $_FILES[$userfile];

        $dimension_data = getimagesize($target_file['tmp_name']);
        $image_width = $dimension_data[0];

        if (!is_numeric($image_width)) {
            die('ERROR: non numeric image width');
        }

        $content_type = mime_content_type($target_file['tmp_name']);

        $str = substr($content_type, 0, 6);
        if ($str !== 'image/') {
            die('ERROR: not an image.');
        }

        $tmp_name = $target_file['tmp_name'];
        $data['image'] = new Image($tmp_name);

        $dir_path = 'modules/'.segment(1).'/assets/';
        $data['destination'] = $dir_path.$data['destination'];
        $data['filename'] = '../'.$data['destination'].'/'.$target_file['name'];
        $data['tmp_file_width'] = $data['image']->get_width();
        $data['tmp_file_height'] = $data['image']->get_height();

        if (!isset($data['max_width'])) {
            $data['max_width'] = NULL;
        }

        if (!isset($data['max_height'])) {
            $data['max_height'] = NULL;
        }

        $this->save_that_pic_alt($data);

    }

    function save_that_pic_alt($data) {
        extract($data);
        $reduce_width = false;
        $reduce_height = false;

        if (!isset($data['compression'])) {
            $compression = 100;
        } else {
            $compression = $data['compression'];
        }

        if (!isset($data['permissions'])) {
            $permissions = 775;
        } else {
            $permissions = $data['permissions'];
        }

        //do we need to resize the picture?
        if ((isset($max_width)) && ($tmp_file_width>$max_width)) {
            $reduce_width = true;
        }

        if ((isset($max_height)) && ($tmp_file_width>$max_height)) {
            $reduce_height = true;
        }

        //resize rules figured out, let's rock...
        if (($reduce_width == true) && ($reduce_height == false)) {
            $image->resize_to_width($max_width);
            $image->save($filename, $compression);
        }

        if (($reduce_width == false) && ($reduce_height == true)) {
            $image->resize_to_height($max_height);
            $image->save($filename, $compression);
        }

        if (($reduce_width == false) && ($reduce_height == false)) {
            $image->save($filename, $compression);
        }

        if (($reduce_width == true) && ($reduce_height == true)) {
            $image->resize_to_width($max_width);
            $image->resize_to_height($max_height);
            $image->save($filename, $compression);
        }
    }

    function ditch_picture($update_id) {

        if (!is_numeric($update_id)) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $result = $this->model->get_where_custom('{{primaryKey}}', $update_id,  order_by:'{{primaryKey}}');

        if ($result == false) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $target_dir = APPPATH.strtolower('modules/{{moduleName}}/assets/{{moduleName}}_pics/').$update_id;
        $this->_rrmdir($target_dir);

        $picture_settings = $this->_init_picture_settings();
        $target_column_name = $picture_settings['target_column_name'];
        $data[$target_column_name] = '';
        $this->model->update_where('{{primaryKey}}', $update_id, $data, '{{stlModuleName}}');

        $flash_msg = 'The picture was successfully deleted';
        set_flashdata($flash_msg);
        redirect($_SERVER['HTTP_REFERER']);
    }

    function _rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        $this->_rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }

}
