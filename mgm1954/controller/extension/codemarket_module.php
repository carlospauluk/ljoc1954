<?php
require_once DIR_SYSTEM . 'library/codemarketVal.php';

//PROIBIDO MODIFICAR ESTE ARQUIVO
class ControllerExtensionCodemarketModule extends Controller
{
    public function index()
    {
        $this->load->model('setting/setting');

        $codemarket = new CodemarketVal();

        //para limpar
        //$this->model_setting_setting->editSetting('codemarket_val', '');

        $conf = $this->model_setting_setting->getSetting('codemarket_val');
        if (!empty($conf)) {
            $email = $conf['codemarket_val_email'];
            $token = $conf['codemarket_val_token'];
            $data['cliente'] = $token;
            $data['email'] = $email;
            $url = 'https://www.codemarket.com.br/api/modulo/cliente/criar/dados';
            $dados = [
                "email" => $email,
                "token" => $token,
                "dados" => self::dados(),
            ];
            $codemarket->post($url, $dados, 8, false);
        }

        $data['dom'] = "http://loja.casabonsucesso.com.br/";// HTTP_CATALOG;

        $data['validar'] = $this->url->link('extension/codemarket_module/validar&user_token=' . $this->session->data['user_token'], '', true);
        $data['code_modulos'] = 'https://www.codemarket.com.br/api/modulo/cliente/listar';
        $data['url_ticket'] = $this->url->link('extension/codemarket_ticket&user_token=' . $this->session->data['user_token'], '', true);
        $data['url_cache'] = $this->url->link('extension/codemarket_module/cache&user_token=' . $this->session->data['user_token'], '', true);

        $usuario = $this->db->query("SELECT u.user_id,ug.name,ug.user_group_id  FROM `" . DB_PREFIX . "user` u
        INNER JOIN " . DB_PREFIX . "user_group ug ON (u.user_group_id = ug.user_group_id)
        WHERE u.user_id = '" . (int) $this->user->getId() . "' LIMIT 0,1
        ");
        $usuario = $usuario->row;

        $data['usuario_id'] = $usuario['user_id'];
        $data['grupo_nome'] = $usuario['name'];
        $data['grupo_id'] = $usuario['user_group_id'];

        $this->document->setTitle('Code Market - Módulos');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/codemarket_module', $data));
    }

    public function cache()
    {
        $r = 'module';
        if (!empty($this->request->get['produto'])) {
            $r = 'module.' . $this->request->get['produto'];
        }
        $codemarket = new CodemarketVal();
        $codemarket->delete($r);
        $this->response->redirect($this->url->link('extension/codemarket_module', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function dados()
    {
        $user_groups = $this->db->query("SELECT user_group_id,name FROM " . DB_PREFIX . "user_group");
        $user_groups = $user_groups->rows;

        $customer_groups = $this->db->query("SELECT cg.customer_group_id,cgd.name FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int) $this->config->get('config_language_id') . "'");
        $customer_groups = $customer_groups->rows;

        //Regiões Geográficas
        $this->load->model('localisation/geo_zone');
        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();

        //Estados do Brasil
        $this->load->model('localisation/zone');
        $zones = $this->model_localisation_zone->getZonesByCountryId(30);

        //Moedas
        $this->load->model('localisation/currency');
        $currency = $this->model_localisation_currency->getCurrencies();

        //Estoque Status
        $this->load->model('localisation/stock_status');
        $stock_status = $this->model_localisation_stock_status->getStockStatuses();

        //Status Pedidos
        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        //Grupo de Impostos
        $this->load->model('localisation/tax_class');
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();

        //Layout
        $this->load->model('design/layout');
        $layouts = $this->model_design_layout->getLayouts();

        //Custom Field
        $this->load->model('customer/custom_field');
        $custom_fields = $this->model_customer_custom_field->getCustomFields();

        $pagamentos = $this->db->query("SELECT code FROM " . DB_PREFIX . "extension WHERE `type` = 'payment' ORDER BY code");
        $pagamentos = $pagamentos->rows;

        $pagamento = [];

        foreach ($pagamentos as $p) {
            $status = $this->config->get($p['code'] . '_status');
            if (!empty($status) and $status == 1) {
                $this->load->language('payment/' . $p['code']);
                $pagamento[] = [
                    'payment_id' => $p['code'],
                    'name'       => trim($this->language->get('heading_title')),
                ];
            }
        }

        $dados = [
            'payments'        => $pagamento,
            'user_groups'     => $user_groups,
            'customer_groups' => $customer_groups,
            'geo_zones'       => $geo_zones,
            'zones'           => $zones,
            'currency'        => $currency,
            'stack_status'    => $stock_status,
            'order_statuses'  => $order_statuses,
            'tax_classes'     => $tax_classes,
            'layouts'         => $layouts,
            'custom_fields'   => $custom_fields,
        ];

        return json_encode($dados);
    }

    public function validar()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $dados = $this->request->post;
            if ((!empty($dados["codemarket_val_email"])) and (!empty($dados["codemarket_val_token"]))) {
                $codemarket = new CodemarketVal();
                $url = 'https://www.codemarket.com.br/api/modulo/cliente/validar';
                $data = [
                    "email" => $dados["codemarket_val_email"],
                    "token" => $dados["codemarket_val_token"],
                ];
                $r = $codemarket->post($url, $data, 8, false);
                if (!empty($r->msg)) {
                    $this->load->model('setting/setting');
                    $this->model_setting_setting->editSetting('codemarket_val', $dados);
                    echo 1;
                }
            }
        }
    }
}
