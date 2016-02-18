<?php

namespace App\Presenters;

use Nette,
	App\Model, Tracy\Debugger,
        Nette\Application\Responses\JsonResponse, App\Model\CookbookUtil,
    App\Model\Recipe,
    App\Model\Source,
    App\Model\RecipeCategory,
    App\Model\Ingredient,
        Nette\Application\UI\Form,
        Nette\Utils\Html;


/**
 * Homepage presenter.
 */
class SearchPresenter extends BasePresenter
{
	/** @var \App\Model\Recipe @inject */
	protected $recipe;
	/** @var \App\Model\Source @inject */
	protected $source;
	/** @var \App\Model\RecipeCategory @inject */
	protected $category;
	/** @var \App\Model\Ingredient @inject */
	protected $ingredient;

	public function __construct(Recipe $recipe, Source $source, RecipeCategory $category, Ingredient $ingredient)
	{
		$this->recipe = $recipe;
		$this->source = $source;
                $this->category = $category;
                $this->ingredient = $ingredient;
	}
    
    public function renderSearch($term) {
        if (preg_match('/^cat:/', $term)) {
            $category = preg_replace('/^cat:(.*)$/', '\1', $term);
            $this->template->recipes = $this->recipe->searchByCategory($category);
            $this->template->term = 'Kategorie '. $category;
        } else {
            $this->template->recipes = $this->recipe->searchByText($term);
            $this->template->term = $term;
        }
    }

}
