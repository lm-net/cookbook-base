<?php

namespace App\Presenters;

use Nette\Application\UI\Form,
    Nette\Application\Responses\JsonResponse,
    App\Model\CookbookUtil,
    Tracy\Debugger;

/**
 * Homepage presenter.
 */
class IngredientPresenter extends BasePresenter {

    protected $mergeFlag = false;

    protected function createComponentIngredientForm() {
        $form = new Form;

        $form->getElementPrototype()->class('ajax pure-form pure-form-aligned');

        $form->addHidden('id_ingredient', 'ID')
                ->setDefaultValue(null);

        $form->addText('name', 'Název')
                ->setRequired()
                ->setAttribute('placeholder', 'název')
                ->setAttribute('class', 'pure-input-1-4');

        $form->addText('english_name', 'Anglicky')
                ->setAttribute('placeholder', 'anglicky')
                ->setAttribute('class', 'pure-input-1-4');

        $form->addText('italian_name', 'Italsky')
                ->setAttribute('placeholder', 'italsky')
                ->setAttribute('class', 'pure-input-1-4');

        $form->addSelect('id_ingredient_category', 'Kategorie', $this->ingredientCategory->getSelectList())
                ->setPrompt('Vyber kategorii');

        $form->addTextArea('description', 'Popis')
                ->setAttribute('placeholder', 'popis')
                ->setAttribute('class', 'pure-input-1-2');

        $form->addSubmit('submit', 'Uložit')
                ->setAttribute('class', 'pure-button pure-button-primary');
        $form->addSubmit('cancel', 'Zrušit')->setValidationScope(false)
                ->setAttribute('class', 'pure-button');
        $form->onSuccess[] = $this->ingredientFormSucceeded;

        return $form;
    }

    public function ingredientFormSucceeded($form) {
        if ($form['submit']->isSubmittedBy()) {
            $values = $form->getValues(true);
            $id_ingredient = $this->getParameter('id_ingredient');

            if ($id_ingredient > 0) {
                $ingredient = $this->ingredient->getById($id_ingredient);
                $ingredient->update($values);
                $this->flashMessage('upraveno');
            } else {
                $ingredient = $this->ingredient->insert($values);
                $this->flashMessage('přidáno');
                $this->redirect('show', $ingredient->id_ingredient);
            }

            $this->editFlag = false;
            if ($this->isAjax()) {
                $this->invalidateControl('data');
                $this->invalidateControl('flashMessages');
            }
        } elseif ($form['cancel']->isSubmittedBy()) {

            if ($this->isAjax()) {
                $this->invalidateControl('data');
            }
        }
    }

    public function renderList() {
        $this->redirect('Ingredient:listByName');
    }

    /**
     * Vykreslí seznam surovin začínajících zadaným písmenem
     */
    public function renderListByName($prefix) {
        if (!$prefix) {
            $prefix = 'a';
        }

        // seznam surovin
        $this->template->ingredients = $this->ingredient->listTableWithCount()->where('name LIKE ?', $prefix . '%');
        // seznam českých písmen
        $this->template->letters = CookbookUtil::$CZECH_LETTERS;
        // první písmeno v názvu suroviny
        $this->template->prefix = $prefix;
    }

    /**
     * Vykreslí seznam surovin začínajících zadaným písmenem
     */
    public function renderListByCategory($id_ingredient_category) {
        $this->template->categories = $this->ingredientCategory->listTable();
        
        if (!$id_ingredient_category) {
            $firstCategory = $this->template->categories->fetch();
            $name = $firstCategory->name;
            $id_ingredient_category = $firstCategory->id_ingredient_category;
        } else {
            $name = $this->ingredientCategory->getById($id_ingredient_category)->name;
        }
        
        // seznam surovin
        $this->template->ingredients = $this->ingredient->listTableWithCount()->where('id_ingredient_category = ?', $id_ingredient_category);
        // první písmeno v názvu suroviny
        $this->template->name = $name;
    }

    public function renderListCategories() {
        $this->template->categories = $this->ingredientCategory->listTable();
    }

    public function renderShow($id_ingredient) {
        $ingredient = $this->ingredient->getById($id_ingredient);
        if (!$ingredient) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->ingredient = $ingredient;
        $this['ingredientForm']->setDefaults($ingredient->toArray());

        $this->template->edit = $this->editFlag;
        $this->template->merge = $this->mergeFlag;

        $this->template->recipes = $this->ingredient->getRecipes($ingredient);
    }

    public function renderAdd() {
        
    }

    public function actionAutocomplete($whichData, $typedText = '') {
        $a = $this->database->table('cookbook.ingredient')
                ->select('name')
        //	->where('name LIKE ?', $typedText . '%')
        //	->order('name');
        ;
        $this->sendResponse(new JsonResponse($a));
    }

    public function handleMerge() {
        $this->mergeFlag = true;
        $this['ingredientMergeForm']['id_ingredient']->setValue($this->getParameter('id_ingredient'));

        if ($this->isAjax()) {
            $this->invalidateControl('data');
        }
    }

    protected function createComponentIngredientMergeForm() {
        $form = new Form;

        $form->getElementPrototype()->class('ajax pure-form pure-form-aligned');

        $form->addHidden('id_ingredient', 'ID');

        $form->addSelect('id_ingredient_merge_with', 'Sloučit s', $this->ingredient->getMergeList())
                ->setPrompt('Vyber surovinu ke sloučení');

        $form->addSubmit('submit', 'Uložit')
                ->setAttribute('class', 'pure-button pure-button-primary');
        $form->addSubmit('cancel', 'Zrušit')->setValidationScope(false)
                ->setAttribute('class', 'pure-button');
        $form->onSuccess[] = $this->ingredientMergeFormSucceeded;

        return $form;
    }

    public function ingredientMergeFormSucceeded($form) {
        if ($form['submit']->isSubmittedBy()) {
           $values = $form->getValues();
           
           // převáže všechny výskyty první suroviny suroviny
           $this->database->query('UPDATE recipe_ingredient SET id_ingredient = ? WHERE id_ingredient = ?', $values->id_ingredient_merge_with, $values->id_ingredient);

           // smaže surovinu
           $this->ingredient->getById($values->id_ingredient)->delete();

           // přesměruje
           $this->redirect('show', $values->id_ingredient_merge_with);
        }

        if ($this->isAjax()) {
            $this->mergeFlag = false;
            $this->invalidateControl('data');
            $this->invalidateControl('flashMessages');
        }
    }
}
