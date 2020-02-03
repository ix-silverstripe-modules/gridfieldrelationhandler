<?php

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;

class GridFieldHasOneRelationHandler extends GridFieldRelationHandler {
	protected $onObject;
	protected $relationName;

	protected $targetObject;

	public function __construct(DataObject $onObject, $relationName, $targetFragment = 'before') {
		$this->onObject = $onObject;
		$this->relationName = $relationName;

		$hasOne = $onObject->hasOne($relationName);
		if(!$hasOne) {
			user_error('Unable to find a has_one relation named ' . $relationName . ' on ' . $onObject->ClassName, E_USER_WARNING);
		}
		$this->targetObject = $hasOne;

		parent::__construct(false, $targetFragment);
	}

	protected function setupState($state, $extra = null) {
		parent::setupState($state, $extra);
		if($state->FirstTime) {
			$state->RelationVal = $this->onObject->{$this->relationName}()->ID;
		}
	}

	public function getColumnContent($gridField, $record, $columnName) {
		$class = $gridField->getModelClass();
		
		//@todo This requires fixing, targetObject is an array now
		//if(!($class == $this->targetObject || is_subclass_of($class, $this->targetObject))) {
		//	user_error($class . ' is not a subclass of ' . $this->targetObject . '. Perhaps you wanted to use ' . $this->targetObject . '::get() as the list for this GridField?', E_USER_WARNING);
		//}

		$state = $this->getState($gridField);
		
		$checked = $state->RelationVal == $record->ID;
		$field = ArrayData::create(array('Checked' => $checked, 'Value' => $record->ID, 'Name' => $this->relationName . 'ID'));
		return $field->renderWith('GridFieldHasOneRelationHandlerItem');
	}

	protected function saveGridRelation(GridField $gridField, $arguments, $data) {
		$field = $this->relationName . 'ID';
		$state = $this->getState($gridField);
		$id = intval($state->RelationVal);
		$this->onObject->{$field} = $id;
		$this->onObject->write();
		parent::saveGridRelation($gridField, $arguments, $data);
	}
}
