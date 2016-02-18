<?php

namespace App\Presenters;

use Nette,
    Nette\Application\UI\Form,
    Nette\Application\Responses\JsonResponse,
    App\Model\CookbookUtil;

/**
 * Homepage presenter.
 */
class RecipePresenter extends BasePresenter {

	public function renderList() {
		$this->template->recipes = $this->recipe->listTable();
	}

	public function renderShow($id_recipe) {
		$recipe = $this->recipe->getById($id_recipe);
		if (!$recipe) {
			$this->error('Stránka nebyla nalezena');
		}

		$this->template->recipe = $recipe;
		$this['recipeForm']->setDefaults($recipe->toArray());

		$this->template->edit = $this->editFlag;
	}

	public function renderAdd() {
		
	}

	public function renderRate($id_recipe, $rating) {
		$recipe = $this->recipe->getById($id_recipe);
		$recipe->update(array('rating' => $rating));
	}

	public function actionCategories($term) {
		if ($this->isAjax()) {
			$i = 0;
			$data = array();
			foreach ($this->recipeCategory->listTable()->select('name')->where('UPPER(name) LIKE UPPER(?)', '%' . $term . '%') as $category) {
				$data[$i++] = $category->name;
			}
			$this->sendResponse(new JsonResponse($data));
		}
	}

	public function actionFilterIngredients($term) {
		if ($this->isAjax()) {
			foreach ($this->ingredient->listTable()->select('name')->where('UPPER(name) LIKE UPPER(?)', '%' . $term . '%') as $ingredient) {
				$data[] = $ingredient->name;
			}
			$this->sendResponse(new JsonResponse($data));
		}
	}

	protected function createComponentRecipeForm() {
		$form = new Form;

		$form->getElementPrototype()->class('ajax pure-form pure-form-aligned');

		$form->addHidden('id_recipe', 'ID')
			->setDefaultValue(null);

		$form->addText('name', 'Název')
			->setRequired()
			->setAttribute('placeholder', 'název')
			->setAttribute('class', 'pure-input-1-2');

		$form->addText('original_name', 'Originální název')
			->setAttribute('placeholder', 'originální název')
			->setAttribute('class', 'pure-input-1-2');

		$form->addSelect('id_source', 'Kuchařka', $this->source->getSelectList())
			->setPrompt('Vyber kuchařku');

		$form->addText('cooking_time', 'Doba přípravy')
			->setAttribute('placeholder', 'doba přípravy')
			->setAttribute('class', 'pure-input-1-4');

		$form->addText('number_of_servings', 'Počet porcí')
			->setAttribute('placeholder', 'počet porcí')
			->setAttribute('class', 'pure-input-1-4')
			->addCondition(Form::FILLED)
			->addRule(Form::INTEGER);

		$form->addCheckbox('cooked_flag', ' Vyzkoušeno');

		$form->addCheckbox('vegetarian_flag', ' Vegetariánské');

		$form->addCheckbox('todo_flag', ' Vyzkoušet');

		$form->addCheckbox('confirmed_flag', ' Zkontrolováno');

		$form->addTextArea('directions', 'Postup')
			->setAttribute('placeholder', 'postup')
			->setAttribute('rows', 10)
			->setAttribute('class', 'pure-input-1-2');

		$form->addTextArea('comments', 'Komentáře')
			->setAttribute('placeholder', 'komentáře')
			->setAttribute('rows', 5)
			->setAttribute('class', 'pure-input-1-2');

		$form->addTextArea('additional_comments', 'Postřehy')
			->setAttribute('placeholder', 'postřehy')
			->setAttribute('rows', 5)
			->setAttribute('class', 'pure-input-1-2');

		$form->addSubmit('submit', 'Uložit')
			->setAttribute('class', 'pure-button pure-button-primary');
		$form->addSubmit('cancel', 'Zrušit')->setValidationScope(false)
			->setAttribute('class', 'pure-button');
		$form->onSuccess[] = $this->recipeFormSucceeded;

		return $form;
	}

	public function recipeFormSucceeded($form) {
		if ($form['submit']->isSubmittedBy()) {
			$values = $form->getValues(true);
			$id_recipe = $this->getParameter('id_recipe');

			if ($id_recipe > 0) {
				$recipe = $this->recipe->getById($id_recipe);
				$recipe->update($values);
				$this->flashMessage('upraveno');
			} else {
				$recipe = $this->recipe->insert($values);
				$this->flashMessage('přidáno');
			}

			// kategorie
			$recipe->related('recipe_recipe_category')->delete();
			if (array_key_exists('categories', $this->getHttpRequest()->getPost())) {
				foreach ($this->getHttpRequest()->getPost()['categories'] as $name) {
					$recipe_category = $this->recipeCategory->getByName($name)->fetch();
					if ($recipe_category) {
						$recipe->related('recipe_recipe_category')->insert(array('id_recipe' => $id_recipe,
						    'id_recipe_category' => $recipe_category->id_recipe_category));
					} else {
						$new_category = $this->recipeCategory->insert(array('name' => $name));
						$recipe->related('recipe_recipe_category')->insert(array('id_recipe' => $id_recipe,
						    'id_recipe_category' => $new_category->id_recipe_category));
					}
				}
			}

			// suroviny
			$recipe->related('recipe_ingredient')->delete();
			$post = $this->getHttpRequest()->getPost();
			for ($i = 0; $i < count($post['ingredient']); $i++) {
				if (!empty($post['ingredient'][$i])) {
					$ingredient = $this->ingredient->getByName($post['ingredient'][$i])->fetch();
					if (!$ingredient) {
						$ingredient = $this->ingredient->insert(array('name' => $post['ingredient'][$i]));
                                                $this->flashMessage('Přidána nová surovina '. $ingredient->name);
					}

					$recipe->related('recipe_ingredient')->insert(
						array(
						    'id_recipe' => $id_recipe,
						    'id_ingredient' => $ingredient->id_ingredient,
						    'amount' => CookbookUtil::to_number($post['amount'][$i]),
						    'unit' => $post['unit'][$i],
						    'comments' => $post['ingredient_comments'][$i],
						    'row_order' => $i));
				}
			}


			if ($this->addFlag) {
				$this->redirect('show', $recipe->id_recipe);
			}
			$this->editFlag = false;
			if ($this->isAjax()) {
				$this->invalidateControl('data');
				$this->invalidateControl('flashMessages');
			}
		} elseif ($form['cancel']->isSubmittedBy()) {
			if ($this->addFlag) {
				$this->redirect('list');
			}

			if ($this->isAjax()) {
				$this->invalidateControl('data');
			}
		}
	}

	public function actionDelete($id_recipe) {
		$recipe = $this->recipe->getById($id_recipe);

		if (!$recipe) {
			$this->error('Stránka nebyla nalezena');
		}

		$recipe->delete();
		$this->flashMessage('Recept byl odstraněn');
		$this->redirect('list');
	}

}
