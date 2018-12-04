<?php

class ModelJournal3Journal3 extends \Journal3\Opencart\Model {

	private static $TABLES = array(
		'journal3_setting' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`store_id` INT(11) NOT NULL,
				`setting_group` VARCHAR(128) NOT NULL,
				`setting_name` VARCHAR(128) NOT NULL,
				`setting_value` TEXT NOT NULL,
				`serialized` INT(1) NOT NULL,
				PRIMARY KEY (`store_id`, `setting_group`, `setting_name`)
        	) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_layout' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`layout_id` INT(11) NOT NULL AUTO_INCREMENT,
				`layout_data` MEDIUMTEXT NOT NULL,
				PRIMARY KEY (`layout_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_module' => '
			CREATE TABLE IF NOT EXISTS `%s` (
            	`module_id` INT(11) NOT NULL AUTO_INCREMENT,
            	`module_type` VARCHAR(64) NOT NULL,
            	`module_name` VARCHAR(128) NOT NULL,
            	`module_data` MEDIUMTEXT NOT NULL,
            	PRIMARY KEY (`module_id`)
        	) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_skin' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`skin_id` INT(11) NOT NULL AUTO_INCREMENT,
				`skin_name` VARCHAR(128) NOT NULL,			
				PRIMARY KEY (`skin_id`)
        	) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_skin_setting' => '
			CREATE TABLE IF NOT EXISTS `%s` (			  
				`skin_id` INT(11) NOT NULL,
				`setting_name` VARCHAR(128) NOT NULL,			
				`setting_value` TEXT NOT NULL,
				`serialized` INT(1) NOT NULL,
				PRIMARY KEY (`skin_id`, `setting_name`)
        	) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_variable' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`variable_name` VARCHAR(64) NOT NULL,
				`variable_type` VARCHAR(64) NOT NULL,
				`variable_value` TEXT NOT NULL,
				`serialized` INT(1) NOT NULL,
				PRIMARY KEY (`variable_name`, `variable_type`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_style' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`style_name` VARCHAR(64) NOT NULL,
				`style_type` VARCHAR(64) NOT NULL,
				`style_value` MEDIUMTEXT NOT NULL,
				`serialized` INT(1) NOT NULL,
				PRIMARY KEY (`style_name`, `style_type`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		',

		'journal3_blog_category' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`category_id` int(11) NOT NULL AUTO_INCREMENT,
				`parent_id` int(11),
				`image` varchar(256),
				`status` tinyint(1),
				`sort_order` int(11),
				PRIMARY KEY (`category_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_category_description' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`category_id` int(11),
				`language_id` int(11),
				`name` varchar(256),
				`description` mediumtext,
				`meta_title` varchar(256),
				`meta_keywords` varchar(256),
				`meta_description` text,
				`keyword` varchar(256),
				PRIMARY KEY (`category_id`,`language_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_category_to_layout' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`category_id` int(11) NOT NULL AUTO_INCREMENT,
				`store_id` int(11),
				`layout_id` int(11),
				PRIMARY KEY (`category_id`, `store_id`),
				KEY (`layout_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_category_to_store' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`category_id` int(11),
				`store_id` int(11)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11) NOT NULL AUTO_INCREMENT,
				`author_id` int(11),
				`image` varchar(256),
				`comments` tinyint(1),
				`status` tinyint(1),
				`sort_order` int(11),
				`date_created` datetime,
				`date_updated` datetime,
				`views` int(11),
				PRIMARY KEY (`post_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post_description' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11),
				`language_id` int(11),
				`name` varchar(256),
				`description` mediumtext,
				`meta_title` varchar(256),
				`meta_keywords` varchar(256),
				`meta_description` text,
				`keyword` varchar(256),
				`tags` varchar(256),
				PRIMARY KEY (`post_id`,`language_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post_to_category' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11),
				`category_id` int(11),
				PRIMARY KEY (`post_id`,`category_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post_to_layout' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11) NOT NULL AUTO_INCREMENT,
				`store_id` int(11),
				`layout_id` int(11),
				PRIMARY KEY (`post_id`, `store_id`),
				KEY (`layout_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post_to_store' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11),
				`store_id` int(11)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_post_to_product' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`post_id` int(11),
				`product_id` int(11),
				PRIMARY KEY (`post_id`,`product_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_blog_comments' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`comment_id` int(11) NOT NULL AUTO_INCREMENT,
				`parent_id` int(11),
				`post_id` int(11),
				`customer_id` int(11),
				`author_id` int(11),
				`name` varchar(256),
				`email` varchar(256),
				`website` varchar(256),
				`comment` text,
				`status` tinyint(1),
				`date` datetime,
				PRIMARY KEY (`comment_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_newsletter' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`newsletter_id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(256),
				`email` varchar(256),			  
                `store_id` INT(11),
				PRIMARY KEY (`newsletter_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_message' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`message_id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(256),
				`email` varchar(256),
				`fields` TEXT NOT NULL,
                `store_id` INT(11),
                `url` varchar(256),
                `date` datetime,
				PRIMARY KEY (`message_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',

		'journal3_product_attribute' => '
			CREATE TABLE IF NOT EXISTS `%s` (
				`product_id` INT(11) NOT NULL,
				`attribute_id` INT(11) NOT NULL,
				`language_id` INT(11) NOT NULL,
				`text` VARCHAR(256) NOT NULL,
				`sort_order` INT(3) NOT NULL DEFAULT 0,
				PRIMARY KEY (`product_id`,`attribute_id`,`language_id`, `text`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		',
	);

	public function database() {
		foreach (self::$TABLES as $name => $sql) {
			$this->db->query(sprintf($sql, $this->dbPrefix($name)));
		}

		// style table fixes
		$query = $this->db->query("DESCRIBE `{$this->dbPrefix('journal3_style')}`");

		foreach ($query->rows as $row) {
			if ($row['Field'] === 'style_value' && strtolower($row['Type']) !== 'mediumtext') {
				$this->db->query("
					ALTER TABLE `{$this->dbPrefix('journal3_style')}` 
					CHANGE `style_value` `style_value` MEDIUMTEXT
				");
			}
		}
	}

	public function isInstalled() {
		return $this->db->query(str_replace('_', '\_', "SHOW TABLES LIKE '{$this->dbPrefix('journal3_')}%'"))->num_rows >= count(self::$TABLES);
	}

	public function install() {
		$this->database();

		$this->load->model('user/user_group');

		$files = glob(DIR_APPLICATION . 'controller/journal3/*.php');
		$data = $this->model_user_user_group->getUserGroup($this->user->getGroupId());

		foreach ($files as $file) {
			$file = 'journal3/' . str_replace('.php', '', basename($file));

			$data['permission']['access'][] = $file;
			$data['permission']['modify'][] = $file;
		}

		$data['permission']['access'] = array_unique($data['permission']['access']);
		$data['permission']['modify'] = array_unique($data['permission']['modify']);

		$this->model_user_user_group->editUserGroup($this->user->getGroupId(), $data);
	}

	public function uninstall() {
		foreach (self::$TABLES as $name => $sql) {
			$this->db->query("DROP TABLE IF EXISTS `{$this->dbPrefix($name)}`");
		}
	}

	private function _getSearchCondition($field_name, $field_id, $keyword, $id) {
		if ($id) {
			return "{$field_id} = '{$this->dbEscapeInt($id)}'";
		}

		return "{$field_name} LIKE '%{$this->dbEscape($keyword)}%'";
	}

	public function getProducts($keyword, $id) {
		return $this->db->query("
			SELECT
				p.product_id AS id,
			 	pd.name AS name
			FROM 
				`{$this->dbPrefix('product')}` p 
			LEFT JOIN 
				`{$this->dbPrefix('product_description')}` pd ON (p.product_id = pd.product_id) 
			WHERE 
				pd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND {$this->_getSearchCondition('pd.name', 'p.product_id', $keyword, $id)}
			GROUP BY 
				p.product_id
			ORDER BY 
				pd.name ASC	
		")->rows;
	}

	public function getCategories($keyword, $id) {
		return $this->db->query("
			SELECT
				cp.category_id AS id,
			 	GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name
			FROM `{$this->dbPrefix('category_path')}` cp 
			LEFT JOIN `{$this->dbPrefix('category')}` c1 ON (cp.category_id = c1.category_id) 
			LEFT JOIN `{$this->dbPrefix('category')}` c2 ON (cp.path_id = c2.category_id) 
			LEFT JOIN `{$this->dbPrefix('category_description')}` cd1 ON (cp.path_id = cd1.category_id) 
			LEFT JOIN `{$this->dbPrefix('category_description')}` cd2 ON (cp.category_id = cd2.category_id)
			WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND {$this->_getSearchCondition('cd2.name', 'cp.category_id', $keyword, $id)}
			GROUP BY 
				cp.category_id
			ORDER BY 
				cd1.name ASC	
		")->rows;
	}

	public function getManufacturers($keyword, $id) {
		return $this->db->query("
			SELECT
				m.manufacturer_id AS id,
			 	m.name AS name
			FROM 
				`{$this->dbPrefix('manufacturer')}` m
			WHERE 
				{$this->_getSearchCondition('name', 'manufacturer_id', $keyword, $id)}
			GROUP BY 
				m.manufacturer_id
			ORDER BY 
				m.name ASC	
		")->rows;
	}

	public function getInformations($keyword, $id) {
		return $this->db->query("
			SELECT
				i.information_id AS id,
			 	id.title AS name
			FROM 
				`{$this->dbPrefix('information')}` i
			LEFT JOIN 
				`{$this->dbPrefix('information_description')}` id ON (i.information_id = id.information_id) 
			WHERE 
				id.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND {$this->_getSearchCondition('id.title', 'i.information_id', $keyword, $id)}
			GROUP BY 
				i.information_id
			ORDER BY 
				id.title ASC	
		")->rows;
	}

	public function getAttributes($keyword, $id = null) {
		$sql = "
			SELECT
				'attribute' as `type`,
				concat(a.attribute_id, '_', pa.text) as `id`,
				CONCAT(agd.name, ' > ', ad.name, ' > ', pa.text) as `name`
			FROM 
				`{$this->dbPrefix('attribute')}` a
			LEFT JOIN 
				`{$this->dbPrefix('attribute_description')}` ad ON (a.attribute_id = ad.attribute_id)
			LEFT JOIN
				`{$this->dbPrefix('attribute_group')}` ag ON (a.attribute_group_id = ag.attribute_group_id)
			LEFT JOIN
				`{$this->dbPrefix('attribute_group_description')}` agd ON (a.attribute_group_id = agd.attribute_group_id)
			LEFT JOIN
				`{$this->dbPrefix('product_attribute')}` pa ON (a.attribute_id = pa.attribute_id) 
			WHERE 
				ad.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND agd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND pa.text LIKE '%{$this->dbEscape($keyword)}%'
			GROUP BY 
				pa.text
			ORDER BY 
				agd.name, ad.name		
		";

		return $this->db->query($sql)->rows;
	}

	public function getAllAttributes() {
		$sql = "
			SELECT
				'attribute' as `type`,
				a.attribute_id as `id`,
				CONCAT(agd.name, ' > ', ad.name) as `name`
			FROM 
				`{$this->dbPrefix('attribute')}` a
			LEFT JOIN 
				`{$this->dbPrefix('attribute_description')}` ad ON (a.attribute_id = ad.attribute_id)
			LEFT JOIN
				`{$this->dbPrefix('attribute_group')}` ag ON (a.attribute_group_id = ag.attribute_group_id)
			LEFT JOIN
				`{$this->dbPrefix('attribute_group_description')}` agd ON (a.attribute_group_id = agd.attribute_group_id) 
			WHERE 
				ad.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND agd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
			GROUP BY 
				a.attribute_id
			ORDER BY 
				agd.name, ad.name	
		";

		return $this->db->query($sql)->rows;
	}

	public function getOptions($keyword, $id = null) {
		$sql = "
			SELECT			  
				concat(o.option_id, '_', ov.option_value_id) as `id`,			  
				concat(od.name, ' > ', ovd.name) as `name`
			FROM
				`{$this->dbPrefix('option')}` o
			LEFT JOIN 
				`{$this->dbPrefix('option_value')}` ov ON (o.option_id = ov.option_id)
			LEFT JOIN 
				`{$this->dbPrefix('option_description')}` od ON (o.option_id = od.option_id)
			LEFT JOIN 
				`{$this->dbPrefix('option_value_description')}` ovd ON (ov.option_value_id = ovd.option_value_id)
			WHERE 
				ovd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND ovd.name LIKE '%{$this->dbEscape($keyword)}%'
			GROUP BY 
				ov.option_value_id
			ORDER BY 
				od.name, ovd.name ASC	
		";

		return $this->dbQuery($sql)->rows;
	}

	public function getAllOptions() {
		$sql = "
			SELECT
				'option' as `type`,
				o.option_id as `id`,
				od.name as `name`
			FROM 
				`{$this->dbPrefix('option')}` o
			LEFT JOIN 
				`{$this->dbPrefix('option_description')}` od ON (o.option_id = od.option_id)		  
			WHERE 
				od.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND o.type IN ('checkbox', 'radio', 'select')
			GROUP BY 
				o.option_id
			ORDER BY
				od.name	
		";

		return $this->db->query($sql)->rows;
	}


	public function getFilters($keyword, $id = null) {
		$sql = "
			SELECT			  
				concat(fg.filter_group_id, '_', f.filter_id) as `id`,			  
				concat(fgd.name, ' > ', fd.name) as `name`
			FROM
				`{$this->dbPrefix('filter_group')}` fg
			LEFT JOIN 
				`{$this->dbPrefix('filter_group_description')}` fgd ON (fg.filter_group_id = fgd.filter_group_id)
			LEFT JOIN 
				`{$this->dbPrefix('filter')}` f ON (f.filter_group_id = fg.filter_group_id)
			LEFT JOIN 
				`{$this->dbPrefix('filter_description')}` fd ON (f.filter_id = fd.filter_id)
			WHERE 
				fgd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND fd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND fd.name LIKE '%{$this->dbEscape($keyword)}%'
			GROUP BY 
				f.filter_id
			ORDER BY 
				fgd.name, fd.name ASC	
		";

		return $this->dbQuery($sql)->rows;
	}

	public function getAllFilters() {
		$sql = "
			SELECT
				'filter' as `type`,
				fg.filter_group_id as `id`,
				fgd.name as `name`
			FROM 
				`{$this->dbPrefix('filter_group')}` fg
			LEFT JOIN
				`{$this->dbPrefix('filter_group_description')}` fgd ON (fg.filter_group_id = fgd.filter_group_id) 
			WHERE 
				fgd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
			GROUP BY 
				fg.filter_group_id	
		";

		return $this->db->query($sql)->rows;
	}

	public function getOutOfStockStatuses($keyword) {
		return array();
	}

	public function getModules() {
		// Journal Modules
		$query = $this->db->query("SELECT * FROM `{$this->dbPrefix('journal3_module')}` ORDER BY module_name ASC");

		$results = array();

		foreach ($query->rows as $row) {
			$results[$row['module_type']][$row['module_id']] = array(
				'id'    => $row['module_id'],
				'value' => $row['module_name'],
			);
		}

		// Opencart Modules
		foreach ($this->getOpencartModules() as $module) {
			$results['opencart'][$module['id']] = array(
				'id'    => $module['id'],
				'value' => $module['name'],
			);
		}

		return $results;
	}

	public function getOpencartModules() {
		$results = array();

		if ($this->journal3->isOC2()) {
			$this->load->model('extension/extension');
			$this->load->model('extension/module');

			// Get a list of installed modules
			$extensions = $this->model_extension_extension->getInstalled('module');

			// Add all the modules which have multiple settings for each module
			foreach ($extensions as $code) {
				$this->load->language('extension/module/' . $code);

				$modules = $this->model_extension_module->getModulesByCode($code);

				$items = false;

				foreach ($modules as $module) {
					$items = true;

					$results[] = array(
						'name' => strip_tags($this->language->get('heading_title')) . ' - ' . strip_tags($module['name']),
						'id'   => $code . '/' . $module['module_id'],
					);
				}

				if (!$items && $this->config->has($code . '_status')) {
					$results[] = array(
						'name' => strip_tags($this->language->get('heading_title')),
						'id'   => $code,
					);
				}
			}
		} else {
			$this->load->model('setting/extension');
			$this->load->model('setting/module');

			// Get a list of installed modules
			$extensions = $this->model_setting_extension->getInstalled('module');

			// Add all the modules which have multiple settings for each module
			foreach ($extensions as $code) {
				$this->load->language('extension/module/' . $code);

				$modules = $this->model_setting_module->getModulesByCode($code);

				$items = false;

				foreach ($modules as $module) {
					$items = true;

					$results[] = array(
						'name' => strip_tags($this->language->get('heading_title')) . ' - ' . strip_tags($module['name']),
						'id'   => $code . '/' . $module['module_id'],
					);
				}

				if (!$items && $this->config->has('module_' . $code . '_status')) {
					$results[] = array(
						'name' => strip_tags($this->language->get('heading_title')),
						'id'   => $code,
					);
				}
			}
		}

		return $results;
	}

	public function getVariables() {
		$query = $this->db->query("SELECT * FROM `{$this->dbPrefix('journal3_variable')}` ORDER BY variable_name ASC");

		$results = array();

		foreach ($query->rows as $row) {
			$results[$row['variable_type']][$row['variable_name']] = $this->decode($row['variable_value'], $row['serialized']);
		}

		return $results;
	}

	public function getStyles() {
		$query = $this->db->query("SELECT * FROM `{$this->dbPrefix('journal3_style')}` ORDER BY style_name ASC");

		$results = array();

		foreach ($query->rows as $row) {
			$results[$row['style_type']][$row['style_name']] = $this->decode($row['style_value'], $row['serialized']);
		}

		return $results;
	}

	public function getBlogCategories($keyword, $id) {
		return $this->db->query("
			SELECT
				c.category_id AS id,
			 	cd.name AS name
			FROM 
				`{$this->dbPrefix('journal3_blog_category')}` c 
			LEFT JOIN 
				`{$this->dbPrefix('journal3_blog_category_description')}` cd ON (c.category_id = cd.category_id) 
			WHERE 
				cd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND {$this->_getSearchCondition('cd.name', 'c.category_id', $keyword, $id)}
			GROUP BY 
				c.category_id
			ORDER BY 
				cd.name ASC	
		")->rows;
	}

	public function getBlogPosts($keyword, $id) {
		return $this->db->query("
			SELECT
				p.post_id AS id,
			 	pd.name AS name
			FROM 
				`{$this->dbPrefix('journal3_blog_post')}` p 
			LEFT JOIN 
				`{$this->dbPrefix('journal3_blog_post_description')}` pd ON (p.post_id = pd.post_id) 
			WHERE 
				pd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'
				AND {$this->_getSearchCondition('pd.name', 'p.post_id', $keyword, $id)}
			GROUP BY 
				p.post_id
			ORDER BY 
				pd.name ASC	
		")->rows;
	}

	public function authors() {
		return $this->db->query("
            SELECT
                user_id,
                username,
                firstname,
                lastname
            FROM `{$this->dbPrefix('user')}`
        ")->rows;
	}

}
