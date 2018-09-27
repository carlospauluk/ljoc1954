<?php
//PROIBIDO MODIFICAR ESTE ARQUIVO
class CodemarketVal
{
    private $expire;
    public function __construct($expire = 2629800)
    {
        $this->expire = $expire;

        $files = glob(DIR_CACHE . 'cache_code.*');

        if ($files) {
            foreach ($files as $file) {
                $time = substr(strrchr($file, '.'), 1);
                if ($time < time()) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    public function get($key)
    {
        $files = glob(DIR_CACHE . 'cache_code.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

        if ($files) {
            $handle = fopen($files[0], 'r');

            flock($handle, LOCK_SH);

            $data = fread($handle, filesize($files[0]));

            flock($handle, LOCK_UN);

            fclose($handle);

            return unserialize($data);
        }

        return false;
    }

    public function set($key, $value)
    {
        $this->delete($key);

        $file = DIR_CACHE . 'cache_code.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);

        $handle = fopen($file, 'w');

        flock($handle, LOCK_EX);

        fwrite($handle, serialize($value));

        fflush($handle);

        flock($handle, LOCK_UN);

        fclose($handle);
    }

    public function delete($key)
    {
        $files = glob(DIR_CACHE . 'cache_code.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function post($url, $data, $timeout = 8, $log = false)
    {
        if ((!empty($url)) and (!empty($data))) {
            $r    = 0;
            $code = curl_init($url);
            curl_setopt($code, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($code, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($code, CURLOPT_POST, true);
            curl_setopt($code, CURLOPT_NOBODY, true);
            curl_setopt($code, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($code, CURLOPT_CONNECTTIMEOUT, $timeout);
            if (defined('HTTP_CATALOG')) {
                $dom = HTTP_CATALOG;
            } else {
                $dom = HTTP_SERVER;
            } $dom = "http://loja.casabonsucesso.com.br/";
            curl_setopt($code, CURLOPT_POSTFIELDS, array_merge($data, array("dom" => $dom)));
            $r = curl_exec($code);
            if (!$r) {
                if ($log) {
                    $this->log->write('Curl Post Erro: ' . curl_error($code) . ' - Código: ' . curl_errno($code));
                }
                curl_close($code);
                return false;
            } else {
                curl_close($code);
                return json_decode($r);
            }
        } else {
            $this->log->write('Curl Post Erro: Faltou alguns dados');
        }
    }
}
