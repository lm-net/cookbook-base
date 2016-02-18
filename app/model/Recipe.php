<?php

namespace App\Model;

use Nette;

/**
 * Zdroje receptů
 */
class Recipe extends CookbookTable {

    protected static $table_name = 'recipe';
    protected static $pk_name = 'id_recipe';
    protected static $order_by = 'name';

    public function searchByText($searchedText) {
        return $this->database->table(static::$table_name)->where('upper(name) LIKE upper(?)', '%' . $searchedText . '%')->order(static::$order_by);
    }

    public function searchByCategory($searchedText) {
        return $this->database->table(static::$table_name)->where('upper(:recipe_recipe_category.recipe_category.name) LIKE upper(?)', '%'. $searchedText .'%');
    }

    public function getBySource($id_source) {
        return $this->database->table(static::$table_name)->where('id_source = ?', $id_source)->order(static::$order_by);
    }

}
