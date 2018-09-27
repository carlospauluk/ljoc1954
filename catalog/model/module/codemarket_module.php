<?php
require_once DIR_SYSTEM . 'library/codemarketVal.php';
//PROIBIDO MODIFICAR ESTE ARQUIVO
class ModelModuleCodemarketModule extends Model
{

    public function getModulo($id_produto, $versao = 1.5)
    {
        if (!empty($id_produto)) {
            $codemarket = new CodemarketVal();

            $r = $codemarket->get('module.' . $id_produto);
            if (!empty($r)) {
                return $r;
            } elseif ($versao == 1.5 or $versao == 2.0) {
                $this->load->model('setting/setting');
                $conf = $this->model_setting_setting->getSetting('codemarket_val');
                if (!empty($conf)) {
                    $email = $conf['codemarket_val_email'];
                    $token = $conf['codemarket_val_token'];
                    $data  = array(
                        "email"      => $email,
                        "token"      => $token,
                        "id_produto" => $id_produto,
                    );

                    $url = 'https://www.codemarket.com.br/api/modulo/cliente/id/produto';
                    $r   = $codemarket->post($url, $data, 8, false);
                    if (!empty($r)) {
                        $codemarket->set('module.' . $id_produto, $r);
                        return $r;
                    }
                }
            } else {
                return false;
            }
        }
    }
}
