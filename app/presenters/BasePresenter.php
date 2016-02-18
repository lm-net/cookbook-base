<?php

namespace App\Presenters;

use Nette,
    Nette\Application\UI\Form,
    Nette\Database\Context,
    App\Model\Recipe,
    App\Model\RecipeCategory,
    App\Model\Ingredient,
    App\Model\IngredientCategory,
    App\Model\Source;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /** Editing flag */
    protected $editFlag = false;
    protected $addFlag = false;

    protected $database;

    /** @var Source @inject */
    protected $source;

    /** @var Recipe @inject */
    protected $recipe;

    /** @var RecipeCategory @inject */
    protected $recipeCategory;

    /** @var Ingredient @inject */
    protected $ingredient;

    /** @var IngredientCategory @inject */
    protected $ingredientCategory;

    /**
     * Inicializuje všechny modely.
     * 
     * @param Source $source
     * @param Recipe $recipe
     * @param RecipeCategory $recipeCategory
     * @param Ingredient $ingredient
     * @param IngredientCategory $ingredientCategory
     */
    public function __construct(Context $database, Source $source, Recipe $recipe, RecipeCategory $recipeCategory, Ingredient $ingredient, IngredientCategory $ingredientCategory) {
        $this->database = $database;
        $this->source = $source;
        $this->recipe = $recipe;
        $this->recipeCategory = $recipeCategory;
        $this->ingredient = $ingredient;
        $this->ingredientCategory = $ingredientCategory;
    }

    protected function createComponentSearchForm() {
        $form = new Form;

        $form->getElementPrototype()->class('pure-form');

        $form->addText('term', 'Vyhledat')
                ->setRequired()
                ->setAttribute('placeholder', 'hledat');

        $form->addButton('submit', 'Odeslat');
        $form->onSuccess[] = $this->searchFormSucceeded;

        return $form;
    }

    public function searchFormSucceeded($form) {
        $values = $form->getValues(true);
        $this->redirect('search:search', $values['term']);
    }

    public function actionAdd() {
        $this->addFlag = true;
    }

    public function handleEdit() {
        $this->editFlag = true;
        if ($this->isAjax()) {
            $this->invalidateControl('data');
        }
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->add = $this->addFlag;
    }

}
