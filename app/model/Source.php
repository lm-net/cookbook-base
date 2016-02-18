<?php

namespace App\Model;

/**
 * Zdroje receptů
 */
class Source extends CookbookTable {

	protected static $table_name = 'source';
	protected static $pk_name = 'id_source';
	protected static $order_by = 'name';

	public function getSelectList() {
		foreach ($this->listTable() as $source) {
			$selectList[$source->id_source] = $source->name;
		}

		return $selectList;
	}

}
