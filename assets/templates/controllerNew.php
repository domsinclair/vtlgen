<?php
class {{ModuleName}} extends Trongate {

    private $default_limit = 20;

    private $per_page_options = array(10, 20, 50, 100);

    protected $table_headers = '{{tableHeaders}}';

    protected $primary_key = '{{primaryKey}}';

    function index () {
        $data['view_module'] = '{{moduleName}}';
        $this->view('display', $data);
        /* Uncomment the lines below,
         * Change the template method name,
         * Remove lines above, if you want to load to the template
         */
        //$data['view_module'] = '{{moduleName}}';
        //$data['view_file'] = 'display';
        //$this->template('template method here', $data);
    }


    function manage() {


        $data['headline'] = 'Manage {{moduleName}}';
        $all_rows = $this->model->get($this->primary_key . ' asc');


        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = '{{moduleName}}/'.segment(2);
        $pagination_data['record_name_plural'] = '{{moduleName}}';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = '{{moduleName}}';


        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();
        $template_to_use = 'admin';
        $view_file_to_use = 'manage';


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

}
