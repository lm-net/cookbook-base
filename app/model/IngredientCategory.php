<?php

namespace App\Model;

use Nette;


/**
 * Zdroje receptů
 */
class IngredientCategory extends CookbookTable
{
	protected static $table_name = 'ingredient_category';
        protected static $pk_name = 'id_ingredient_category';
        protected static $order_by = 'name';

        public function getSelectList() {
            foreach ($this->listTable() as $category) {
                $selectList[$category->id_ingredient_category] = $category->name;
            }

            return $selectList;
        }
}
