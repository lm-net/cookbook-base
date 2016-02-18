<?php

namespace App\Model;

use Nette;


/**
 * Zdroje receptů
 */
class RecipeCategory extends CookbookTable
{
	protected static $table_name = 'recipe_category';
        protected static $pk_name = 'id_recipe_category';
        protected static $order_by = 'name';
}
