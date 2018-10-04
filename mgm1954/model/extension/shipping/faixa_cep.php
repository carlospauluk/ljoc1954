<?php
class ModelExtensionShippingFaixaCep extends Model {
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "faixa_cep` (
            `faixa_cep_id` INT(11) NOT NULL AUTO_INCREMENT,
            `descricao` VARCHAR(200) NULL,
            `cep_inicial` VARCHAR(8) NULL,
            `cep_final` VARCHAR(8) NULL,
            `peso_minimo` VARCHAR(20) NULL,
            `peso_maximo` VARCHAR(20) NULL,
            `total_minimo` VARCHAR(20) NULL,
            `preco` VARCHAR(20) NULL,
            PRIMARY KEY (`faixa_cep_id`) );
        ");
    }

    public function updateTable() {
        $this->install();

        $fields = array(
            'faixa_cep_id' => 'int(11)',
            'descricao' => 'varchar(200)',
            'cep_inicial' => 'varchar(9)',
            'cep_final' => 'varchar(9)',
            'peso_minimo' => 'varchar(20)',
            'peso_maximo' => 'varchar(20)',
            'total_minimo' => 'varchar(20)',
            'preco' => 'varchar(20)'
        );

        $table = DB_PREFIX . "faixa_cep";

        $field_query = $this->db->query("SHOW COLUMNS FROM `" . $table . "`");
        foreach ($field_query->rows as $field) {
            $field_data[$field['Field']] = $field['Type'];
        }

        foreach ($field_data as $key => $value) {
            if (!array_key_exists($key, $fields)) {
                $this->db->query("ALTER TABLE `" . $table . "` DROP COLUMN `" . $key . "`");
            }
        }

        $this->session->data['after_column'] = 'faixa_cep_id';
        foreach ($fields as $key => $value) {
            if (!array_key_exists($key, $field_data)) {
                $this->db->query("ALTER TABLE `" . $table . "` ADD `" . $key . "` " . $value . " AFTER `" . $this->session->data['after_column'] . "`");
            }
            $this->session->data['after_column'] = $key;
        }
        unset($this->session->data['after_column']);

        foreach ($fields as $key => $value) {
            if ($key == 'faixa_cep_id') {
                $this->db->query("ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $key . "` `" . $key . "` " . $value . " NOT NULL AUTO_INCREMENT");
            } else {
                $this->db->query("ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $key . "` `" . $key . "` " . $value);
            }
        }
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "faixa_cep`;");
    }

    public function getFaixasCep($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "faixa_cep`";

        if (!empty($data['filter_value'])) {
            $sql .= " WHERE descricao LIKE '%" . $this->db->escape($data['filter_value']) . "%' OR cep_inicial LIKE '%" . $this->db->escape($data['filter_value']) . "%' ";
        }

        if ($data['start'] < 0) {
            $data['start'] = 0;
        }

        if ($data['limit'] < 1) {
            $data['limit'] = 20;
        }

        $sql .= " ORDER BY faixa_cep_id DESC LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalFaixasCep($data = array()) {
        $sql = "SELECT COUNT(DISTINCT faixa_cep_id) AS total FROM `" . DB_PREFIX . "faixa_cep`";

        if (!empty($data['filter_value'])) {
            $sql .= " WHERE descricao LIKE '%" . $this->db->escape($data['filter_value']) . "%' OR cep_inicial LIKE '%" . $this->db->escape($data['filter_value']) . "%' ";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getFaixaCep($faixa_cep_id) {
        $qry = $this->db->query("
            SELECT * 
            FROM `" . DB_PREFIX . "faixa_cep` 
            WHERE `faixa_cep_id` = '" . (int)$faixa_cep_id . "'
        ");

        if ($qry->num_rows) {
            return $qry->row;
        } else {
            return false;
        }
    }

    public function addFaixaCep($data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "faixa_cep
            SET descricao = '" . $this->db->escape($data['descricao']) . "',
                cep_inicial = '" . $this->db->escape($data['cep_inicial']) . "',
                cep_final = '" . $this->db->escape($data['cep_final']) . "',
                peso_minimo = '" . $this->db->escape($data['peso_minimo']) . "',
                peso_maximo = '" . $this->db->escape($data['peso_maximo']) . "',
                total_minimo = '" . $this->db->escape($data['total_minimo']) . "',
                preco = '" . $this->db->escape($data['preco']) . "'
        ");

        $faixa_cep_id = $this->db->getLastId();

        return $faixa_cep_id;
    }

    public function editFaixaCep($data) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "faixa_cep
            SET descricao = '" . $this->db->escape($data['descricao']) . "',
                cep_inicial = '" . $this->db->escape($data['cep_inicial']) . "',
                cep_final = '" . $this->db->escape($data['cep_final']) . "',
                peso_minimo = '" . $this->db->escape($data['peso_minimo']) . "',
                peso_maximo = '" . $this->db->escape($data['peso_maximo']) . "',
                total_minimo = '" . $this->db->escape($data['total_minimo']) . "',
                preco = '" . $this->db->escape($data['preco']) . "'
            WHERE faixa_cep_id = '" . (int)$data['faixa_cep_id'] . "'
        ");
    }

    public function deleteFaixaCep($faixa_cep_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "faixa_cep` WHERE faixa_cep_id = '" . (int)$faixa_cep_id . "'");
    }
}