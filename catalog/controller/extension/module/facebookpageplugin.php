<?php

class ControllerExtensionModulefacebookpageplugin extends Controller {
	private $modulePath;
    public function __construct($registry) {

		
        parent::__construct($registry);
		$this->config->load('isenselabs/facebookpageplugin');
	 	$this->modulePath = $this->config->get('facebookpageplugin_path');

    }

    public function index($settings){

        $this->document->addStyle('catalog/view/theme/default/stylesheet/facebookpageplugin.css');

        $data['moduleData'] = $settings['facebookpageplugin'];
		
        $data['status'] = (isset($settings['status'])) ? $settings['status'] : false;

        $data['language_id'] = $this->config->get('config_language_id');

		return $this->load->view($this->modulePath, $data);

    }
	
}
