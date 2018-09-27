<?php

class ModelExtensionPaymentCodemarketPagarme extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/codemarket_pagarme');
        $this->load->model('module/codemarket_module');

        $conf        = $this->model_module_codemarket_module->getModulo('441');
        $method_data = array();

        if ((isset($conf->cd1)) and (isset($conf->cd2)) and (!empty($conf->cd2)) and (isset($conf->cd3)) and ($conf->cd3 == 1)) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $conf->cd1 . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

            if ($conf->cd1 == 0) {
                $status = true;
            } elseif ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }

            $ativa = 0;
            if (($conf->cc3 == 1) and (($conf->ca10 > $total) or ($conf->ca11 < $total))) {
                $ativa++;
            }

            if (($conf->cc4 == 1) and (($conf->ca8 > $total) or ($conf->ca9 < $total))) {
                $ativa++;
            }

            //Retorne false, ae ambos estão ativos e não passaram no teste acima
            //Caso ambos estejam ativos
            if (($conf->cc3 == 1) and ($conf->cc4 == 1)) {
                if ($ativa == 2) {
                    $status = false;
                }
            } elseif (($conf->cc3 == 1) or ($conf->cc4 == 1)) {
                if ($ativa > 0) {
                    $status = false;
                }
            } else {
                $status = false;
            }

            $titulo = $this->language->get('text_title');
            if (!empty($conf->cb8)) {
                $titulo = trim($conf->cb8);
            }

            $key = "";
            if (!empty($conf->ca2)) {
                $key = true;
            }

            if (!empty($conf->ca4) and $conf->cc2 == 1) {
                $key = true;
            }

            if (empty($key)) {
                $status = false;
            }

            if ($status) {
                $method_data = array(
                    'code'       => 'codemarket_pagarme',
                    'title'      => $titulo,
                    'terms'      => '',
                    'sort_order' => $conf->cd2,
                );
            }

        }

        return $method_data;
    }
}
