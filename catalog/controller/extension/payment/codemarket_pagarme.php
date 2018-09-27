<?php

class ControllerExtensionPaymentCodemarketPagarme extends Controller
{
    public function confirm()
    {
        require_once DIR_SYSTEM . 'library/pagarme/Pagarme.php';
        $this->load->model('module/codemarket_module');
        $conf = $this->model_module_codemarket_module->getModulo('441');
        $this->load->model('checkout/order');
        $this->load->model('account/customer');
        $this->load->model('extension/payment/codemarket_pagarme');
        $this->language->load('extension/payment/codemarket_pagarme');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $cliente    = $this->model_account_customer->getCustomer($order_info['customer_id']);
        $produtos   = $this->cart->getProducts();

        $cpf = "";

        if (isset($order_info['payment_tax_id'])) {
            $cpf = preg_replace("/[^0-9]/", '', trim($order_info['payment_tax_id']));
        }

        if (isset($order_info['tax_id'])) {
            $cpf = preg_replace("/[^0-9]/", '', trim($order_info['tax_id']));
        }

        /*
        $conf->cb1 = 3;
        $conf->cb2 = 1;
        $conf->cb3 = 2;
         */

        if (isset($order_info['custom_field'][$conf->cb1])) {
            $cpf = preg_replace("/[^0-9]/", '', (trim($order_info['custom_field'][$conf->cb1])));
        }

        $numero      = '1';
        $complemento = 'Sem Complemento';

        if (!empty($conf->cb2)) {
            if (isset($order_info['payment_custom_field'][$conf->cb2])) {
                $numero = $order_info['payment_custom_field'][$conf->cb2];
            }
        }

        if (!empty($conf->cb3)) {
            if (isset($order_info['payment_custom_field'][$conf->cb3])) {
                $complemento = $order_info['payment_custom_field'][$conf->cb3];
            }
        }

        //DADOS
        $nome     = trim($order_info['payment_firstname']) . ' ' . trim($order_info['payment_lastname']);
        $email    = trim($order_info['email']);
        $telefone = preg_replace("/[^0-9]/", '', $order_info['telephone']);
        $telefone = ltrim(trim($telefone), '0');
        $dd       = mb_substr($telefone, 0, 2);
        $telefone = mb_substr($telefone, 2);
        $endereco = explode(",", trim($order_info['payment_address_1']));

        if (!empty($order_info['payment_address_2'])) {
            $bairro = $order_info['payment_address_2'];
        } else {
            $bairro = "Sem Bairro";
        }

        if (isset($endereco[1])) {
            $numero = $endereco[1];
        }
        if (isset($endereco[2])) {
            $complemento = $endereco[2];
        }
        $rua = $endereco[0];

        //Produtos
        foreach ($produtos as $product) {
            $options_names = '';
            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        $value = $upload_info['name'];
                    } else {
                        $value = '';
                    }
                }
                $options_names .= ' - ' . $option['name'] . ': ' . $value;
            }
            //Até 80 caracteres para a descrição do Produto
            $description = mb_substr($product['name'] . $options_names, 0, 80, 'UTF-8');
            if (($this->currency->format($product['price'], $order_info['currency_code'], false, false) * 100) >= 1) {
                $itens[] = [
                    'id'         => $product['product_id'],
                    'title'      => $description,
                    'unit_price' => (int) ($this->currency->format($product['price'], $order_info['currency_code'], false, false) * 100),
                    'quantity'   => (int) $product['quantity'],
                    'tangible'   => 'true',
                    'category'   => 'Loja',
                ];
            }
        }

        //Chamando a forma de Pagamento
        if (isset($_POST['payment_method'])) {
            $pagarme = $_POST;

            if (!empty($conf->ca1)) {
                $api = $conf->ca1;
            }

            if (!empty($conf->ca3) and $conf->cc2 == 1) {
                $api = $conf->ca3;
            }

            Pagarme::setApiKey($api);

            if ($pagarme['payment_method'] == 'credit_card') {
                $cc1 = "true";
                if ($conf->cc1 == 1) {
                    $cc1 = "false";
                }

                $transaction = new PagarMe_Transaction(array(
                    'amount'          => $pagarme['amount'],
                    'card_hash'       => $pagarme['card_hash'],
                    "postback_url"    => $this->url->link('extension/payment/codemarket_pagarme/callback', '', true),
                    "async"           => false,
                    "soft_descriptor" => $conf->cb9,
                    "capture"         => $cc1,
                    "payment_method"  => 'credit_card',
                    "installments"    => $pagarme['installments'],
                    "metadata"        => array(
                        "Pedido ID" => $this->session->data['order_id'],
                        //"Produtos" => $item,
                    ),
                    "customer"        => array(
                        "name"            => $nome,
                        "external_id" => $cliente['customer_id'],
                        "type" => "individual",
                        "country" => "br",
                        "documents" => [
                            [
                                "type" => "cpf",
                                "number" => $cpf
                            ]
                        ],
                        "email"           => $email,
                        "phone_numbers" => array("+55".$dd.$telefone),
                    ),
                    "billing" => array(
                        "name" => $nome,
                        "address"         => array(
                            'country' => 'br',
                            "street"        => $rua,
                            "neighborhood"  => $bairro,
                            "zipcode"       => preg_replace("/[^0-9]/", '', $order_info['payment_postcode']),
                            "street_number" => $numero,
                            "state" => $order_info['payment_zone_code'],
                            "city" => $order_info['payment_city'],
                            "complementary" => $complemento
                        ),
                    ),
                    "items" => $itens
                ));

                try {
                    $transaction->charge();
                } catch (Exception $e) {
                    echo "<div class='alert alert-warning'>
                    Foram encontrados alguns erros, veja abaixo: <br>
                    " . $e->getMessage() . "
                    <button type='button' class='close' data-dismiss='alert'>×</button></div>";
                    $this->log->write($e->getMessage());
                }

                if (isset($transaction->status)) {
                    $status = $conf->cs1;

                    switch ($transaction->status) {
                        case 'processing':
                            $status = $conf->cs1;
                            break;
                        case 'authorized':
                            $status = $conf->cs2;
                            break;
                        case 'paid':
                            $status = $conf->cs3;
                            break;
                        case 'refused':
                            $status = $conf->cs7;
                            break;
                    }

                    //$status = 15;

                    $cartao_parcelas = $pagarme['installments'];
                    $parcelas        = number_format($this->currency->format((($pagarme['amount'] / $pagarme['installments']) / 100), $order_info['currency_code'], false, false), 2, ',', '');
                    $id              = $transaction->id;
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $status, 'Pagamento por Cartão de Crédito em ' . $cartao_parcelas . ' X de R$ ' . $parcelas, true);

                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET pagarme_order_id = '" . $id . "' WHERE order_id = '" . (int) $this->session->data['order_id'] . "'");

                    $this->session->data['code_pagarme_id'] = $id;
                    //$this->response->redirect($this->url->link('checkout/success', '',  true));
                }
            }

            if ($pagarme['payment_method'] == 'boleto') {

                if (!empty($conf->ca13)) {
                    $prazo = ((int) $conf->ca13) - 1;
                    $ca13  = date('Y-m-d', strtotime("+" . $conf->ca13 . " days"));
                    $prazo = date('d/m/Y', strtotime("+" . $prazo . " days"));
                } else {
                    $ca13  = date('Y-m-d', strtotime("+ 7 days"));
                    $prazo = date('d/m/Y', strtotime("+ 6 days"));
                }
                $transaction = new PagarMe_Transaction(array(
                    'amount'                 => $pagarme['amount'],
                    "postback_url"           => $this->url->link('extension/payment/codemarket_pagarme/callback', '', true),
                    "async"                  => false,
                    "soft_descriptor"        => $conf->cb9,
                    "payment_method"         => 'boleto',
                    "installments"           => 1,
                    "boleto_expiration_date" => $ca13,
                    "metadata"               => array(
                        "Pedido ID" => $this->session->data['order_id'],
                        //"Produtos" => $item,
                    ),
                    "customer"        => array(
                        "name"            => $nome,
                        "external_id" => $cliente['customer_id'],
                        "type" => "individual",
                        "country" => "br",
                        "documents" => [
                            [
                                "type" => "cpf",
                                "number" => $cpf
                            ]
                        ],
                        "email"           => $email,
                        "phone_numbers" => array("+55".$dd.$telefone),
                    ),
                    "billing" => array(
                        "name" => $nome,
                        "address"         => array(
                            'country' => 'br',
                            "street"        => $rua,
                            "neighborhood"  => $bairro,
                            "zipcode"       => preg_replace("/[^0-9]/", '', $order_info['payment_postcode']),
                            "street_number" => $numero,
                            "state" => $order_info['payment_zone_code'],
                            "city" => $order_info['payment_city'],
                            "complementary" => $complemento
                        ),
                    ),
                    "items" => $itens
                ));

                try {
                    $transaction->charge();
                } catch (Exception $e) {
                    echo "<div class='alert alert-warning'>
                    Foram encontrados alguns erros, veja abaixo: <br>
                    " . $e->getMessage() . "
                    <button type='button' class='close' data-dismiss='alert'>×</button></div>";
                    $this->log->write($e->getMessage());
                }

                if (isset($transaction->boleto_url)) {
                    switch ($transaction->status) {
                        case 'processing':
                            $status = $conf->cs1;
                            break;
                        case 'waiting_payment':
                            $status = $conf->cs5;
                            break;
                        case 'paid':
                            $status = $conf->cs3;
                            break;
                        case 'refused':
                            $status = $conf->cs7;
                            break;
                    }

                    $url   = $transaction->boleto_url;
                    $barra = $transaction->boleto_barcode;

                    $h = HTTPS_SERVER . 'image/codePagarmeBoleto.png';

                    $boleto = "
                        Pagamento por Boleto
                        Se não tiver pago, pague o Boleto para confirmar o Pagamento<br>
                        <a href='$url' rel='nofollow' target='_blank'><img src='$h'></a><br>
                        Pagar o Boleto até dia " . $prazo . "<br>
                    ";
                    $id = $transaction->id;

                    //$status = 1;
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $status, $boleto, true);

                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET pagarme_order_id = '" . $id . "' WHERE order_id = '" . (int) $this->session->data['order_id'] . "'");

                    $code_boleto = array(
                        'link'    => "<a href='$url' rel='nofollow' target='_blank'><img src='$h'></a>",
                        'total'   => 'R$ ' . number_format($this->currency->format($pagarme['amount'], $order_info['currency_code'], false, false), 2, ',', ''),
                        'barcode' => $barra,
                    );

                    $this->session->data['code_pagarme_boleto'] = $code_boleto;
                    //$this->response->redirect($this->url->link('checkout/success', '',  true));
                }
            }
        }
    }

    public function index()
    {
        $this->language->load('extension/payment/codemarket_pagarme');
        $this->load->model('module/codemarket_module');
        $conf = $this->model_module_codemarket_module->getModulo('441');

        $data['conf']   = $this->language->get('button_confirm');
        $button_confirm = $conf->cb7;
        if (!empty($conf->cb7)) {
            $button_confirm = $conf->cb7;
        }

        $data['button_confirm']      = $this->language->get('button_confirm');
        $data['text_wait']           = $this->language->get('text_wait');
        $data['text_erro_pagamento'] = $this->language->get('text_erro_pagamento');
        $data['url']                 = $this->url->link('extension/payment/codemarket_pagarme/confirm', '', true);
        $data['sucesso']             = $this->url->link('checkout/success', '', true);

        $this->load->model('checkout/order');
        $this->load->model('account/customer');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $cliente    = $this->model_account_customer->getCustomer($order_info['customer_id']);

        $data['c441total'] = (number_format($order_info['total'], 2, '.', '')) * 100;

        $desconto_boleto   = $conf->ca12;
        $data['c441_ca12'] = "";

        $total = number_format($order_info['total'], 2, '.', '');
        if (($conf->cc3 == 1) and ($conf->ca10 < $total) and ($conf->ca11 > $total)) {
            $pagamentos = "boleto";
        }

        if (($conf->cc4 == 1) and ($conf->ca8 < $total) and ($conf->ca9 > $total)) {
            if (isset($pagamentos)) {
                $pagamentos = "credit_card,boleto";
            } else {
                $pagamentos = "credit_card";
            }
        }

        $data['c441pagamentos'] = $pagamentos;

        if (!empty($conf->ca2)) {
            $data['c441key'] = $conf->ca2;
        }

        if (!empty($conf->ca4) and $conf->cc2 == 1) {
            $data['c441key'] = $conf->ca4;
        }

        if (!empty($desconto_boleto)) {
            $c441total2        = $data['c441total'];
            $data['c441_ca12'] = number_format(($c441total2 * $desconto_boleto) / 100, 0, '.', '');
        }

        $data['c441_cb7'] = "Confirmar Pedido e Abrir Pagamento";
        if (!empty($conf->cb7)) {
            $data['c441_cb7'] = $conf->cb7;
        }
        $data['c441_cb5'] = "";
        if (!empty($conf->cb5)) {
            $data['c441_cb5'] = $conf->cb5;
        }
        $data['c441_cb6'] = "";
        if (!empty($conf->cb6)) {
            $data['c441_cb6'] = $conf->cb6;
        }

        $data['c441_cb10'] = "#1a6ee1";
        if (!empty($conf->cb10)) {
            $data['c441_cb10'] = $conf->cb10;
        }

        $data['c441_ca6'] = "1";
        if (!empty($conf->ca6)) {
            $data['c441_ca6'] = $conf->ca6;
        }

        $data['c441_ca7'] = "1";
        if (!empty($conf->ca7)) {
            $data['c441_ca7'] = $conf->ca7;
        }

        $data['c441_ca5'] = "0";
        if (!empty($conf->ca5)) {
            $data['c441_ca5'] = $conf->ca5;
        }

        $data['c441_cb11'] = "";
        if (!empty($conf->cb11)) {
            $data['c441_cb11'] = $conf->cb11;
        }

        return $this->load->view('extension/payment/codemarket_pagarme', $data);
    }

    public function callback()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') and (!empty($_POST["current_status"]))) {
            $dados = $_POST;
            $this->log->write('Pagamento Code Market Checkout Pagar.me - Entrou no callback(). <br>ID do Pedido ' . $dados['id'] . ' Status ' . $dados['current_status']);
            $this->load->model('extension/payment/codemarket_pagarme');
            //Buscando o Pedido com base no ID do Pedido da IUGU
            $query = $this->db->query("SELECT order_id FROM  `" . DB_PREFIX . "order`  WHERE pagarme_order_id = '" . $dados['id'] . "'");

            if (!empty($query->row)) {
                $order_id = $query->row['order_id'];
                $this->load->model('checkout/order');
                $order = $this->model_checkout_order->getOrder($order_id);
                $this->log->write('Ordem ID ' . $order['order_id']);
            }

            if (!empty($order)) {
                $this->load->model('module/codemarket_module');
                $conf = $this->model_module_codemarket_module->getModulo('441');

                $this->log->write('Pedido retornado com sucesso - Status ID ' . $order['order_status_id']);
                $update_status_alert = false;
                if ($conf->cc6 == 1) {
                    $update_status_alert = true;
                }

                switch ($dados['current_status']) {
                    case 'processing':
                        $order_status_id = $conf->cs1;
                        break;
                    case 'authorized':
                        $order_status_id = $conf->cs2;
                        break;
                    case 'paid':
                        $order_status_id = $conf->cs3;
                        break;
                    case 'refunded':
                        $order_status_id = $conf->cs4;
                        break;
                    case 'waiting_payment':
                        $order_status_id = $conf->cs5;
                        break;
                    case 'pending_refund':
                        $order_status_id = $conf->cs6;
                        break;
                    case 'refused':
                        $order_status_id = $conf->cs7;
                        break;
                    case 'chargedback':
                        $order_status_id = $conf->cs8;
                        break;
                }

                if (!empty($order_status_id)) {
                    $order_id = $order['order_id'];
                    $query    = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int) $order_id . "' AND order_status_id = '" . (int) $order_status_id . "'");
                    if (empty($query->row['order_id'])) {
                        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, '', $update_status_alert);
                        $this->log->write('Histórico do Pedido mudado - ' . $dados['current_status']);
                    }
                }
            }
        }
    }
}
