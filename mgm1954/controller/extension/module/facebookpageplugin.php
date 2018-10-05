<?php
class ControllerExtensionModuleFacebookpageplugin extends Controller {
	// Main Variables
	private $modulePath;
	private $moduleNameSmall;
	private $moduleData_module = 'facebookpageplugin_module';
	private $moduleModel;
	private $extensionLink;
	private $language_variables;
	private $version;
	private $error = array();

	public function __construct($registry) {
	 	parent::__construct($registry);
		$this->config->load('isenselabs/facebookpageplugin');
	 	$this->moduleNameSmall = $this->config->get('facebookpageplugin_name');
	 	$this->modulePath = $this->config->get('facebookpageplugin_path');
		$this->version = $this->config->get('facebookpageplugin_version');
		$this->language_variables = $this->load->language($this->modulePath);
		$this->extensionLink = $this->url->link($this->config->get('facebookpageplugin_extensionLink'), 'user_token=' . $this->session->data['user_token'].$this->config->get('facebookpageplugin_extensionLink_type'), 'SSL');

	 }
	
    public function index() { 
		// Main Variables
		$data['moduleNameSmall'] = $this->moduleNameSmall;
		$data['moduleData_module'] = $this->moduleData_module;
		$data['modulePath'] = $this->modulePath;
		$data['moduleModel'] = $this->moduleModel;
	 	// Load language files
        $this->load->language($this->modulePath);
		// Load models
        $this->load->model('setting/store'); // load the store setting model
        $this->load->model('localisation/language');//load the language model for mutilingual functionality
        $this->load->model('design/layout'); //load the design layouts models
		$this->load->model('setting/module');
		
 		// Load script & stylesheets
        $this->document->addStyle('view/stylesheet/'.$this->moduleNameSmall.'/'.$this->moduleNameSmall.'.css');//add the stylesheet 
        // Set main title
		$this->document->setTitle($this->language->get('heading_title'));
		$catalogURL = $this->getCatalogURL();//assign the  store url value to the catalogURL varibale
		 
		//Check for set store_id
        if(!isset($this->request->get['store_id'])) {
           $this->request->get['store_id'] = 0; 
        }
		// Get store info
        $store = $this->getCurrentStore($this->request->get['store_id']);
		// Save module settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) { 
			
			 if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule($this->moduleNameSmall, $this->request->post);    
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->extensionsLink = $this->url->link($this->modulePath . ('&module_id=' . $this->request->get['module_id']), 'user_token=' . $this->session->data['user_token'], 'SSL'); // append the module id to redirect on the same page
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->extensionLink);
			
        }
		
		// Get success message
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		// Get error/warning message
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['error_facebook_url'])) {
			$data['error_facebook_url'] = $this->error['error_facebook_url'];
		} else {
			$data['error_facebook_url'] = '';
		}
		
		// Breadcrumb data
        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->extensionLink,
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );
		foreach ($this->language_variables as $code => $languageVariable) {
		    $data[$code] = $languageVariable;
		}   
		
 		$data['heading_title'] = $this->language->get('heading_title').' '.$this->version;
		// Data for the template files
        $data['stores'] = array_merge(array(0 => array('store_id' => '0', 'name' => $this->config->get('config_name') . ' (Default)', 'url' => HTTP_SERVER, 'ssl' => HTTPS_SERVER)), $this->model_setting_store->getStores());

        $data['store']                  = $store;
		$data['language_id'] = $this->config->get('config_language_id');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        foreach ($data['languages'] as $key => $value) {
            if(version_compare(VERSION, '2.2.0.0', "<")) {
                $data['languages'][$key]['flag_url'] = 'view/image/flags/'.$data['languages'][$key]['image'];

            } else {
                $data['languages'][$key]['flag_url'] = 'language/'.$data['languages'][$key]['code'].'/'.$data['languages'][$key]['code'].'.png"';
            }
        }
        $data['user_token']                  = $this->session->data['user_token'];
        $data['cancel']                 = $this->extensionLink;
		$module_info = array();
        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $data['moduleSettings'] = $module_info;
        $data['moduleData'] = (isset($module_info[$this->moduleNameSmall])) ? $module_info[$this->moduleNameSmall] : array();
		
		 if (!isset($this->request->get['module_id'])) {

            $data['action'] = $this->url->link($this->modulePath, 'store_id='.$this->request->get['store_id'].'&user_token=' . $this->session->data['user_token'], 'SSL');

        } else {

            $data['action'] = $this->url->link($this->modulePath, 'module_id='.$this->request->get['module_id'].'&store_id='.$this->request->get['store_id'].'&user_token=' . $this->session->data['user_token'], 'SSL');

        }
	
		$data['catalog_url']			= $catalogURL;//the store url is saved
		
		if($this->config->has('facebookpageplugin_url'))
			$data['facebookpageplugin_url'] = $this->config->get('facebookpageplugin_url');
		else
			$data['facebookpageplugin_url'] = '';
		
		// Get the the main OpenCart admin styles & design	
		$data['header']					= $this->load->controller('common/header');
		$data['column_left']			= $this->load->controller('common/column_left');
		$data['footer']					= $this->load->controller('common/footer');
		
		// Outputs the data from the function
		$this->response->setOutput($this->load->view($this->modulePath, $data));
    }
	
	// Check for permissions
	 protected function validateForm() {
        if (!$this->user->hasPermission('modify', $this->modulePath)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        $missingRequired = array();

        if (!isset($this->request->post['name']) || empty($this->request->post['name'])) {
            $missingRequired[] = $this->language->get('module_name');
        }

        if (!isset($this->request->post['facebookpageplugin'])) {
            $this->error['warning'] = $this->language->get('error_fill_in_required');
            return false;
        } else {
            if (!isset($this->request->post['facebookpageplugin']['FacebookPageURL']) || empty($this->request->post['facebookpageplugin']['FacebookPageURL'])) {
                $missingRequired[] = preg_replace('[:]', '', $this->language->get('text_facebookpageplugin_url'));
            }

            if (!isset($this->request->post['facebookpageplugin']['PanelName'])) {
                $missingRequired[] = preg_replace('[:]', '', $this->language->get('text_panel_name'));
            } else {
                $warned = false;
                foreach ($this->request->post['facebookpageplugin']['PanelName'] as $key => $val) {
                    if (empty($val) && !$warned) {
                        $missingRequired[] = preg_replace('[:]', '', $this->language->get('text_panel_name'));
                        $warned = true;
                    }
                }
            }
        }

        $missingCount = count($missingRequired);
        if ($missingCount == 1) {
            $this->error['warning'] = str_replace('%field%', $missingRequired[0], $this->language->get('error_single_required_missing'));
        } else if ($missingCount > 1) {
            $this->error['warning'] = str_replace('%fields%', implode(', ', $missingRequired), $this->language->get('error_multiple_required_missing'));
        }

        return !$this->error;
    }

	// Gets the front-end URL
    private function getCatalogURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        } 
        return $storeURL;
    }
   
    // Get the data about a given store
    private function getCurrentStore($store_id) {    
        if($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL(); 
        }
        return $store;
    }
	
    // Module installation
    public function install() {
	   
    }
    
	// Module uninstallation
    public function uninstall() {
	}
}