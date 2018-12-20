<?php
class ControllerExtensionTotalPaymentDiscount extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/total/payment_discount');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('total_payment_discount', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['save_stay']) && ($this->request->post['save_stay'] = 1)) {
                $this->response->redirect($this->url->link('extension/total/payment_discount', 'user_token=' . $this->session->data['user_token'], true));
            } else {
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
            }
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $erros = array(
            'warning'
        );

        foreach ($erros as $erro) {
            if (isset($this->error[$erro])) {
                $data['error_'.$erro] = $this->error[$erro];
            } else {
                $data['error_'.$erro] = '';
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/total/payment_discount', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/total/payment_discount', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        $data['versao'] = '1.0.0';

        $campos = array(
            'list' => array(array('payment' => 0, 'total' => '', 'discount' => '')),
            'status' => '',
            'sort_order' => ''
        );

        foreach ($campos as $campo => $valor) {
            if (!empty($valor)) {
                if (isset($this->request->post['total_payment_discount_'.$campo])) {
                    $data['total_payment_discount_'.$campo] = $this->request->post['total_payment_discount_'.$campo];
                } else if ($this->config->get('total_payment_discount_'.$campo)) {
                    $data['total_payment_discount_'.$campo] = $this->config->get('total_payment_discount_'.$campo);
                } else {
                    $data['total_payment_discount_'.$campo] = $valor;
                }
            } else {
                if (isset($this->request->post['total_payment_discount_'.$campo])) {
                    $data['total_payment_discount_'.$campo] = $this->request->post['total_payment_discount_'.$campo];
                } else {
                    $data['total_payment_discount_'.$campo] = $this->config->get('total_payment_discount_'.$campo);
                }
            }
        }

        $this->load->model('setting/extension');
        $payments = $this->model_setting_extension->getInstalled('payment');

        foreach ($payments as $key => $code) {
            $this->load->language('extension/payment/' . $code);
            $data['payments'][] = array(
                'code' => $code,
                'name' => addslashes($this->language->get('heading_title'))
            );
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/total/payment_discount', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/total/payment_discount')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}