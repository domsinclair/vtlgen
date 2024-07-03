<?php
class {{ModuleName}} extends Trongate {

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

}

