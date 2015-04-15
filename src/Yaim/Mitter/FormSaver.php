<?php

namespace Yaim\Mitter;

use Illuminate\Support\Facades\Form;

class FormSaver
{
	protected $structure;
	protected $inputs;
	protected $model;
	protected $node_model;

	public function __construct($structure, $inputs, $node_model = false)
	{
		$inputs = deepArrayFilter($inputs);
		
		$this->node_model = $node_model;
		$this->structure = $structure;
		$this->inputs = $inputs;
		$this->arrayToJson();

		if(!isset($this->inputs['id']) && !$node_model) {
			$this->model = new $structure['model'];
			$this->model->save();
			$id = $this->model->id;
		} else {
			if ($node_model) {
				if (isset($node_model->id)) {
					$id = $node_model->id;
				} else {
					die('Saved node model does not have an id!');
				}
			} else {
				$id = (int) $this->inputs['id'];
			}

			$this->model = call_user_func(array($structure['model'], 'withTrashed'))->find($id);
		}

		$this->mapper();

		if(isset($id)) {
			$this->model->save();
		}
	}

	public function getModel()
	{
		return $this->model;
	}

	public function arrayToJson()
	{
		$array_dots = array_dot($this->structure);
		$addresses = array();
		foreach ($array_dots as $key => $value) {
			if ($value === "json" && strpos($key, ".type") !== false) {
				$address = substr($key, ($pos = strpos($key, '.')) !== false ? $pos + 1 : 0);
				$address = str_replace(".subs.", ".", $address);
				$address = str_replace(".type", "", $address);
				$addresses[] = $address;
			}
		}

		foreach ($addresses as $address) {
			$address = explode(".", $address);
			if ((bool)count(array_filter(array_keys($this->inputs[$address[0]]), 'is_string'))) {
				$this->inputs[$address[0]][$address[1]] = json_encode($this->inputs[$address[0]][$address[1]]);
			} else {
				foreach ($this->inputs[$address[0]] as $key => $value) {
					$this->inputs[$address[0]][$key][$address[1]] = json_encode($value[$address[1]]);
				}
			}
		}
	}

	public function mapper()
	{
		$structure = $this->structure;

		if (isset($structure['self'])) {
			foreach ($structure['self'] as $name => $field) {
				if ((isset($field['type'])) && ($field['type'] == 'divider')) {
					continue;
				}

				$repeat = (isset($field['repeat'])) ? $field['repeat'] : false;

				$this->self_properties($name, $repeat);
			}
		}

		if (isset($structure['relations'])) {
			foreach ($structure['relations'] as $name => $field) {
				$repeat = (isset($field['repeat'])) ? $field['repeat'] : false;

				if ((isset($field['type'])) && ($field['type'] == 'divider')) {
					continue;
				}

				if(isset($field['key'])) {
					$pass = true;
					if($repeat) {
						if(isset($this->inputs[$name])) {
							foreach ($this->inputs[$name] as $key => $input) {
								if (!isset($input[$field['key']]) || empty($input[$field['key']])) {
									unset($this->inputs[$name][$key]);
								}
							}
						}

						if (empty($this->inputs[$name])) {
							$pass = false;
						}
					} else {
						if (!isset($this->inputs[$name][$field['key']]) || empty($this->inputs[$name][$field['key']])) {
							$pass = false;
						}
					}

					if (!$pass) {
						continue;
					}
				}

				$relation_type = last(explode("\\", get_class(call_user_func(array($this->model, $name)))));
				$repeat = (isset($field['repeat'])) ? $field['repeat'] : false;
				$this->relation_properties($name, $relation_type, $repeat);
			}
		}
	}

	public function self_properties($name, $repeat = false)
	{
		if($repeat) {
			return false;
		}

		if(isset($this->inputs[$name])) {
			$this->model->$name = trim($this->inputs[$name]);
		}
	}

	public function relation_properties($name, $relation_type, $repeat = false)
	{
		if (!isset($this->inputs[$name])) {
			return;
		}

		if ($repeat) {
			$data = array();

			foreach ($this->inputs[$name] as $input) {
				if (!empty($input['id'])) {
					$id = $input['id'];
					unset($input['id']);
					$data[$id] = $input;
				} else {
					$data[] = $input;
				}
			}
		} else {
			$data = $this->inputs[$name];
		}

		call_user_func(array($this, $relation_type), $name, $data);
	}

	public function get()
	{
		return $this->model;
	}

	public function BelongsToMany($name, $data)
	{
		if (!is_array($data)) {
			if(strpos($data, ',')) {
				$data = explode(',', $data);
			}
		}

		$data = (!is_array($data))? array_filter(array($data)) : array_filter($data);
		$otherKey = last(explode('.', $this->getOtherKey($name)));

		foreach ($data as $key => $item) {
			$item = array_filter($item);
			$data[$key] = $item;

			foreach ($item as $itemKey => $pivot) {
				if(!(strlen($pivot) > 0)) {
					unset($data[$key][$itemKey]);
				}
			}

			if(!(strlen(@$item[$otherKey]) > 0)) {
				unset($data[$key]);
			}
		}

		call_user_func(array($this->model, $name))->sync($data);
	}

	public function BelongsTo($name, $data)
	{
		if(empty($data)) {
			$foreignKey = $this->getForeignKey($name);
			$this->model->$foreignKey = null;

			return;
		}

		$related_model = $this->getRelatedModel($name);
		$model = call_user_func(array($related_model, 'find'), $data);

		call_user_func(array($this->model, $name))->associate($model);
	}

	public function MorphMany($name, $data)
	{
		$old_models = call_user_func(array($this->model, $name))->get();

		foreach ($old_models as $model) {
			$model->delete();
		}

		$related_model = $this->getRelatedModel($name);
		$models = array();

		foreach ($data as $values) {
			if (!is_array($values)) {
				$values = [$this->structure['relations'][$name]['name_field'] => $values];
			}

			$models[] = new $related_model($values);
		}

		call_user_func(array($this->model, $name))->saveMany($models);
	}

	public function MorphToMany($name, $data = array())
	{
		foreach ($data as $key => $item) {
			$data[$key] = array_filter($item);
		}

		$data = array_filter($data);
		$oldCollection = $this->model->$name;

		foreach ($oldCollection as $oldRelation) {
			call_user_func(array($this->model, $name))->detach($oldRelation);
		}

		if(!empty($data)) {
			call_user_func(array($this->model, $name))->attach($data);
		}
	}

	public function MorphTo($name, $data)
	{
		$model = call_user_func(array($this->structure['model'], 'withTrashed'))->find($this->model->id);

		foreach ($data as $key => $value) {
			$model->$key = $value;
		}

		$model->update();
	}

	public function HasMany($name, $data)
	{
		$model = $this->getRelatedModel($name);
		$relations = array();

		foreach ($data as $item) {
			if(is_array($item)) {
				$item = array_filter($item);

				if(!empty($item)) {
					$relation = new $model;

					foreach ($item as $key => $value) {
						if(strlen($value) > 0)
							$relation->$key = $value;
						else unset($relation->$key);
					}
				}
			} else {
				$relation = $model::find((int)$item);
			}

			if(isset($relation)) {
				$relations[] = $relation;
			}
		}

		$old_models = call_user_func(array($this->model, $name))->get();

		foreach ($old_models as $old_model) {
			$old_model->delete();
		}

		if (isset($relations[0])) {
			call_user_func(array($this->model, $name))->saveMany($relations);
		}
	}

	public function getRelatedModel($name)
	{
		return get_class(call_user_func(array($this->model, $name))->getQuery()->getModel());
	}

	public function getForeignKey($name)
	{
		return call_user_func(array($this->model, $name))->getForeignKey();
	}

	public function getOtherKey($name)
	{
		return call_user_func(array($this->model, $name))->getOtherKey();
	}

}