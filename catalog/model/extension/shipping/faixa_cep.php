<?php
class ModelExtensionShippingFaixaCep extends Model {
    public function getQuote($address) {
        if ($this->config->get('shipping_faixa_cep_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_faixa_cep_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('shipping_faixa_cep_geo_zone_id')) {
                $status = true;
            } elseif ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            $produtos = $this->getProdutos();

            if ($produtos) {
                $cep_entrega = preg_replace('/[^0-9]/', '', $address['postcode']);

                $faixas_cep = $this->getFaixasCep($cep_entrega);

                if ($faixas_cep) {
                    $currency_code = $this->session->data['currency'];

                    $peso_carrinho = $this->cart->getWeight();
                    $subtotal = $this->currency->format($this->cart->getSubTotal(), $currency_code, '', false);
                    $faixa_custo = (!$this->config->get('shipping_faixa_cep_custo')) ? 0 : $this->config->get('shipping_faixa_cep_custo');
                    $frete = 0.00;

                    $i = 0;

                    function cmp($a, $b) {
                        return floatval($a['total_minimo']) <= floatval($b['total_minimo']);
                    }
                    usort($faixas_cep,"cmp");

                    foreach ($faixas_cep as $faixa) {
                        $cep_inicial = preg_replace('/[^0-9]/', '', $faixa['cep_inicial']);
                        $cep_final = preg_replace('/[^0-9]/', '', $faixa['cep_final']);
                        $cep_final = ($cep_final) ? $cep_final : $cep_inicial;
                        $peso_minimo = ($faixa['peso_minimo']) ? $faixa['peso_minimo'] : '0';
                        $peso_maximo = ($faixa['peso_maximo']) ? $faixa['peso_maximo'] : '1000';
                        $total_minimo = ($faixa['total_minimo'])  ? $faixa['total_minimo']  : '0';

                        if ($subtotal > $total_minimo) {
                            if ($peso_carrinho >= $peso_minimo && $peso_carrinho <= $peso_maximo) {
                                $descricao = $faixa['descricao'];
                                $frete = $faixa['preco'];

                                if ($frete > 0) {
                                    $custo = 0;
                                    if ($faixa_custo) {
                                        if ($this->config->get('shipping_faixa_cep_tipo_custo') == 1) {
                                            $custo = (($frete * $faixa_custo)/100);
                                        } else {
                                            $custo = $faixa_custo;
                                        }
                                    }
                                    $frete = $frete + $custo;
                                    $tax_class_id = $this->config->get('shipping_faixa_cep_tax_class_id');
                                    $text = $this->currency->format($this->tax->calculate($frete, $tax_class_id, $this->config->get('config_tax')), $currency_code, 1.00, true);
                                } else {
                                    $frete = 0.00;
                                    $tax_class_id = 0;
                                    $text = $this->currency->format(0.00, $currency_code);
                                }

                                $quote_data[$i] = array(
                                    'code' => 'faixa_cep.'.$i,
                                    'title' => $descricao,
                                    'cost' => $frete,
                                    'tax_class_id' => $tax_class_id,
                                    'text' => $text
                                );
                                break; // Somente adiciono uma opção de frete
                            }
                        }
                    $i++;
                    }
                }
            }

            if (!empty($quote_data)) {
                if (strlen(trim($this->config->get('shipping_faixa_cep_imagem'))) > 0) {
                    $title = '<img src="'.HTTPS_SERVER.'image/'.$this->config->get('shipping_faixa_cep_imagem').'" alt="'.$this->config->get('shipping_faixa_cep_titulo').'" title="'.$this->config->get('shipping_faixa_cep_titulo').'" />';
                } else {
                    $title = $this->config->get('shipping_faixa_cep_titulo');
                }

                $method_data = array(
                    'code' => 'faixa_cep',
                    'title' => $title,
                    'quote' => $quote_data,
                    'sort_order' => $this->config->get('shipping_faixa_cep_sort_order'),
                    'error' => false
                );
            }
        }

        return $method_data;
    }

    private function getProdutos() {
        $lista = (is_array($this->config->get('faixa_cep_produtos'))) ? $this->config->get('faixa_cep_produtos') : array();

        if (count($lista)) {
            $produtos = $this->cart->getProducts();

            foreach ($produtos as $produto) {
                if (!in_array($produto['product_id'], $lista)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getFaixasCep($cep) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "faixa_cep` WHERE `cep_inicial` <= '" . $this->db->escape($cep) . "' AND `cep_final` >= '" . $this->db->escape($cep) . "'";
        // $sql .= " ORDER BY total_minimo DESC";
        $qry = $this->db->query($sql);

        if ($qry->num_rows) {
            return $qry->rows;
        } else {
            return false;
        }
    }
}