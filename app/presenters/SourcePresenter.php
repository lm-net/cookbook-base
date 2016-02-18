<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

/**
 * Source presenter.
 */
class SourcePresenter extends BasePresenter {

	public function renderList() {
		$this->template->sources = $this->source->listTable();
	}

	public function renderShow($id_source) {
		$source = $this->source->getById($id_source);

		if (!$source) {
			$this->error('Stránka nebyla nalezena');
		}

                $values['data'] = $source->toArray();
		$this['sourceForm']->setDefaults($values);

		$this->template->edit = $this->editFlag;
		$this->template->source = $source;
		$this->template->recipes = $this->recipe->getBySource($id_source);
	}

	public function actionDelete($id_source) {
		$source = $this->source->getById($id_source);

		if (!$source) {
			$this->error('Stránka nebyla nalezena');
		}

		$source->delete();
		$this->flashMessage('Kuchařka byla odstraněna');
		$this->redirect('list');
	}

	public function createComponentSourceForm() {
		$form = new Form;

		$form->getElementPrototype()->class('ajax pure-form pure-form-aligned');

		$formData = $form->addContainer('data');

		$formData->addHidden('id_source', 'ID')
			->setDefaultValue(null);

		$formData->addText('name', 'Název')
			->setRequired()
			->setAttribute('placeholder', 'název');

		$formData->addText('author', 'Autor')
			->setAttribute('placeholder', 'autor');

		$formData->addText('original', 'Originální název')
			->setAttribute('placeholder', 'originální název');

		$formData->addText('publisher', 'Vydavatelství')
			->setAttribute('placeholder', 'vydavatelství');

		$formData->addTextArea('comment', 'Poznámky')
			->setAttribute('placeholder', 'poznámky');

		//$formImage = $form->addContainer('image');

		//$formImage->addUpload('thumbnail', 'Náhled');

		$form->addSubmit('submit', 'Uložit')
			->setAttribute('class', 'pure-button pure-button-primary');
		$form->addSubmit('cancel', 'Zrušit')->setValidationScope(false)
			->setAttribute('class', 'pure-button');
		$form->onSuccess[] = $this->sourceFormSucceeded;

		return $form;
	}

	public function sourceFormSucceeded($form) {
		if ($form['submit']->isSubmittedBy()) {
			$values = $form['data']->getValues(true);
			$id_source = $this->getParameter('id_source');

			if ($id_source > 0) {
				$source = $this->source->getById($id_source);
				$source->update($values);
				$this->flashMessage('Kuchařka byla upravena');
			} else {
				$source = $this->source->insert($values);
				$this->flashMessage('Kuchařka byla vložena');
				$this->redirect('show', $source->id_source);
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

}
