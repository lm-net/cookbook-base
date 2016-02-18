<?php

namespace App\Model;

use Nette;

/**
 * Zdroje receptů
 */
class Ingredient extends CookbookTable {

    protected static $table_name = 'ingredient';
    protected static $pk_name = 'id_ingredient';
    protected static $order_by = 'name';

    /**
     * Vrátí všechny suroviny v tabulce včetně četnosti jejich použití
     * 
     * @return Nette\Database\Table\Selection
     */
    public function listTableWithCount() {
        return $this->database->table('ingredient_recipe_count_vw')->order(static::$order_by);
    }

    /**
     * Vrátí pro zadanou surovinu všechny recepty, ve kterých se vyskytuje.
     * 
     * @param type $ingredient
     * @return type
     */
    public function getRecipes($ingredient) {
        return $ingredient->related('recipe_ingredient');
    }

        public function getMergeList() {
            foreach ($this->listTable() as $ingredient) {
                //if ($ingredient->id_ingredient != $id_ingredient) {
                $selectList[$ingredient->id_ingredient] = $ingredient->name .' ('. $ingredient->id_ingredient . ')';
                //}
            }

            return $selectList;
        }

}
