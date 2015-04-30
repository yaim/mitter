<?php namespace Yaim\Mitter;

class FormBuilder
{
	protected $structure;
	protected $apiController;
	protected $html;
	protected $oldData = null;
	protected $isDeleted;

	public function __construct($structure, $apiController = null, $oldData = null, $id = null )
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$this->structure = $structure;
		$this->apiController = $apiController;
		$this->oldData = $oldData;
		$this->isDeleted = (isset($oldData['deleted_at'])) ? true : false;
		$this->id = $id;

		$this->formPrefix();

		if (isset($structure['self'])) {
			$this->formContent($structure['self']);
		}

		if (isset($structure['relations'])) {
			$this->formContent($structure['relations']);
		}

		$this->formPostfix();
	}

	public function formPrefix()
	{
		$structure = $this->structure;

		$this->html ='
			<div class="row">
				<div class="col-xs-6">
					<h2>'.$structure['title'].'</h2>
				</div>';

		if ($this->isDeleted) {
			$this->html .= \Form::open(['action' => [$structure['controller']."@store", $this->id]]);
			$this->html .='
					<div class="col-xs-6">
						<input type="submit" class="btn btn-info pull-right" value="Restore Item">
					</div>';
			$this->html .= \Form::close();

			$formTitle = 'Deleted '.$structure['title'];
		} elseif (isset($this->id)) {
			$this->html .= \Form::open(['action' => [$structure['controller']."@destroy", $this->id], 'method' => 'delete']);
			$this->html .='
					<div class="col-xs-6">
						<input type="submit" class="btn btn-danger pull-right" onclick="return confirm(`Deleting this item means other models\'s relations to this item, wouldn\'t work anymore! Are you sure you want to delete this?`); return false;" value="Delete Item">
					</div>';
			$this->html .= \Form::close();

			$formTitle = 'Edit '.$structure['title'];
		} else {
			$formTitle = 'Add '.$structure['title'];
		}

		$this->html .='
			</div>';

		$this->html .= \Form::open(['action' => $structure['controller']."@store", 'parsley-validate', 'novalidate', 'files' => true]);

		if (isset($this->id)) {
			$this->html .="<input type='hidden' name='id' value='$this->id'>";
		}

		$this->html .='
				<div class="row mitter">
					<div class="col-sm-12 col-md-12">
						<div class="row">
							<h3 class="col-xs-6">'.$formTitle.'</h3>
						</div>
						<div class="tab-container block-web">';
	}

	public function formContent($structure)
	{
		foreach ($structure as $name => $field) {
			$title = $field['title'];

			if(isset($field['type'])) {
				if ($field['type'] == "divider") {
					$this->divider($title);
					continue;
				}
			}

			$oldData = (isset($this->oldData[snake_case($name)]))? $this->oldData[snake_case($name)] : false;
			$repeat = (isset($field['repeat']))? $field['repeat'] : false;

			if($oldData) {
				if($repeat) {
					$count = count($oldData);
					$i = 1;
					$repeat = false;

					if (@$field['type'] != "locked") {
						$this->html .= '<input type="hidden" name="'.$name.'" value="1" data-hidden-placeholder/>';
					}

					foreach ($oldData as $data) {
						if($i == $count) {
							$repeat = true;
						}

						$this->rowPrefix($title, $name, $repeat);
						$this->rowContent($name, $field, true, $data, $num = $i);
						$this->rowPostfix(true);

						$i++;
					}
					$this->html .='<hr/>';
				} else {
					$this->rowPrefix($title, $name, $repeat);
					$this->rowContent($name, $field, false, $oldData);
					$this->rowPostfix();
				}
			} else {
				$this->rowPrefix($title, $name, $repeat);
				$this->rowContent($name, $field, $repeat);
				$this->rowPostfix();
			}
		}
	}

	public function formPostfix()
	{
		$structure = $this->structure;

		$this->html .='
				</div>
			</div>
		</div>';

		$this->html .= \Form::submit("Submit ".$structure['title'], array('class' => 'btn btn-primary'));

		if(isset($structure['links'])) {
			$this->html .= $structure['links'];
		}

		$this->html .= \Form::close();
	}

	public function rowPrefix($title, $name, $repeat = false)
	{
		$extraInputs = '';
		$extraAttributes = '';

		if ($repeat) {
			$extraAttributes .=" data-repeat data-name='$name' ";
		}

		$this->html .="
		<div class='form-group row' $extraAttributes>
			<label for='$name' class='form-horizontal row-border col-sm-3 control-label'>$title</label>
			<div class='col-sm-9'>
				<div class='row'>";
	}

	public function rowContent($name, $field, $repeat, $oldData = null, $num = 1)
	{
		$model = (isset($field['model']))? $field['model'] : null;
		$key = (isset($field['key']))? $field['key'] : null;

		if(isset($field['subs'])) {
			$namePrefix = $name;

			foreach ($field['subs'] as $name => &$subField) {
				$data = $oldData;

				if(isset($oldData)) {
					if(@$key == $name) {
						$data = (isset($oldData[$key]))? $oldData[$key] : null;

						$name = $this->nameFixer($name, $repeat, $namePrefix, $num);
						call_user_func(array($this, $subField['type']), $name, @$subField['title'], $subField, $data, $model);
						continue;
					}

					if(isset($oldData['pivot'])) {
						if(array_key_exists($name, $oldData['pivot'])) {
							$data = $oldData['pivot'][$name];
						}
					} else {
						if(array_key_exists($name, $oldData)) {
							$data = $oldData[$name];

							//Dummy Hack Fix For Poly Morphic Ajax Guess start
							
							if (strpos($name, "_type")) {
								$inputIdName = explode("_type", $name);
								$inputIdName = $inputIdName[0]."_id";

								if(isset($field['subs'][$inputIdName])) {
									$field['subs'][$inputIdName]['model'] = $data;
								}
							}

							//Dummy Hack Fix For Poly Morphic Ajax Guess end
						}
					}

					$model = (isset($subField['model'])) ? $subField['model'] : null;
				}

				$name = $this->nameFixer($name, $repeat, $namePrefix, $num);
				call_user_func(array($this, $subField['type']), $name, @$subField['title'], $subField, $data, $model);
			}
		} else {
			$name = $this->nameFixer($name, $repeat, null, $num);
			call_user_func(array($this, $field['type']), $name, $field['title'], $field, $oldData, $model);
		}
	}

	public function rowPostfix($continious = false)
	{
		$this->html .='
				</div>
			</div>
		</div>';

		if (!$continious)
			$this->html .='<hr/>';
	}

	public function rowGroupPostfix()
	{
		$this->html .='
				</div>
			</div>
		</div>';
	}

	public function nameFixer($name, $repeat = false, $namePrefix = null, $num = 1)
	{

		if($repeat && !isset($namePrefix))
			$name = $name."[$num]";
		elseif (isset($namePrefix) && !$repeat)
			$name = $namePrefix."[".$name."]";
		elseif ($repeat && isset($namePrefix))
			$name = $namePrefix."[$num][".$name."]";

		return $name;
	}

	public function get()
	{
		return $this->html;
	}

	public function text($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<input class='form-horizontal row-border form-control' value='$oldData' name='$name' type='text' id='$name' placeholder='$title'>
		</div>";
	}

	public function image($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		if($oldData) {
			$width = ($width >= 3)? $width-2 : 1;

			$this->html .= "
			<div class='col-sm-$width'>
				<input class='form-horizontal row-border form-control' name='$name' type='file' id='$name' placeholder='$title'>
			</div>
			<div class='col-sm-1 btn' data-toggle='modal' data-target='#$name-modal'>
				<img class='form-horizontal row-border' width='100%' src='$oldData' alt='$name'/>
			</div>
			<div class='col-sm-1 btn-group' data-toggle='buttons'>
				<label class='btn btn-danger fa fa-remove' title='remove'>
					<input type='checkbox' name='$name-remove' id='$name-remove' autocomplete='off'>
				</label>
			</div>
			<div class='modal fade' id='$name-modal' tabindex='-1' role='dialog' aria-labelledby='$name-modal-lable' aria-hidden='true'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header'>
							<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
							<h4 class='modal-title' id='$name-modal-lable'>$title</h4>
						</div>
						<div class='modal-body'>
							<img class='form-horizontal row-border' width='100%' src='$oldData' alt='$name' />
						</div>
						<div class='modal-footer'>
							<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
						</div>
					</div>
				</div>
			</div>";
		} else {
			$this->html .= "
			<div class='col-sm-$width'>
				<input class='form-horizontal row-border form-control' name='$name' type='file' id='$name' placeholder='$title'>
			</div>";

		}

	}

	public function password($name, $title, $field, $oldData = null)
	{
		return;
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<input class='form-horizontal row-border form-control' type='password' value='$oldData' name='$name' id='$name' placeholder='$title'>
		</div>";
	}

	public function locked($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			if(isset($oldData['id'])) {
				$relationName = explode('[',$name)[0];
				$relationEditLink = $this->getSelfModel()->$relationName->find($oldData['id'])->getEditUrl();
			}

			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}


		if(isset($relationEditLink) && !empty(@$relationEditLink)) {
			$width = (!isset($width))? 11 : $width-1;
			$this->html .="
			<div class='col-sm-$width col-xs-11'>
				<input class='form-horizontal row-border form-control' value='$oldData' type='text' placeholder='$title' locked disabled>
			</div>
			<div class='col-xs-1'>
				<a class='btn btn-sm btn-info link-to-relation' target='_blank' href='$relationEditLink'><i class='fa fa-external-link'></i></a>
			</div>";
		} else {
			$width = (!isset($width))? 12 : $width;
			$this->html .="
			<div class='col-sm-$width'>
				<input class='form-horizontal row-border form-control' value='$oldData' type='text' placeholder='$title' locked disabled>
			</div>";
		}
	}

	public function hidden($name, $title = null, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			if(isset($oldData['id'])) {
				$relationName = explode('[',$name)[0];
				$relationEditLink = $this->getSelfModel()->$relationName->find($oldData['id'])->getEditUrl();
			}

			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$this->html .="
		<div class='hidden'>
			<input value='$oldData' type='hidden' placeholder='$title'>
		</div>";
	}

	public function dateTime($name, $title, $field, $oldData = null)
	{
		extract($field);
		$default = (@$default) ? "data-default" : "";

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<input class='form-horizontal row-border form-control' value='$oldData' name='$name' type='text' id='$name' placeholder='$title' data-dateTimePicker $default>
		</div>";
	}

	public function date($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<input class='form-horizontal row-border form-control' value='$oldData' name='$name' type='text' id='$name' placeholder='$title' data-datePicker>
		</div>";
	}

	public function time($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<input class='form-horizontal row-border form-control' value='$oldData' name='$name' type='text' id='$name' placeholder='$title' data-timePicker>
		</div>";
	}

	public function json($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		$name = (isset($name)) ? $name."[]" : null;

		if (isset($oldData)) {
			$oldData = json_decode($oldData);
			foreach ($oldData as $data) {
				$this->html .="
				<div class='col-sm-$width'>
					<input data-group='$title' class='form-horizontal row-border form-control' value='$data' name='$name' type='text' id='$name' placeholder='$title' />
				</div>
				";
			}
		} else {
			$this->html .="
			<div class='col-sm-$width'>
				<input data-group='$title' class='form-horizontal row-border form-control' value='' name='$name' type='text' id='$name' placeholder='$title' />
			</div>
			";
		}
	}

	public function select($name, $title, $field, $oldData = null)
	{
		if (strpos($name, "_type") && strpos($name, "[")) {
			preg_match('#\[(.*?)\]#', $name, $match);
			$match = $match[1];

			if (isset($this->oldData[$match])) {
				$oldData = $this->oldData[$match];
			}
		}

		extract($field);

		$width = (!isset($width))? 12 : $width;

		$this->html .="
			<div class='col-sm-$width'>
				<select id='$name' class='form-control' name='$name'>";

		foreach ($field['options'] as $key => $value) {
			$selected = '';

			if(isset($oldData['name'])) {
				if($oldData['name'] == $value) {
					$selected = ' selected="selected" ';
				}
			} elseif($oldData == $key) {
				$selected = ' selected="selected" ';
			}

			$this->html .="<option value='$key' $selected> $value </option>";
		};

		$this->html .="
				</select>
			</div>";
	}

	public function textarea($name, $title, $field, $oldData = "")
	{
		extract($field);

		$width = (!isset($width))? 12 : $width;

		$this->html .="
		<div class='col-sm-$width'>
			<textarea class='form-horizontal row-border form-control' name='$name' placeholder='$title' cols='50' rows='5' id='$name'>$oldData</textarea>
		</div>";

	}

	public function bool($name, $title, $field, $oldData = null)
	{
		$checked = "";

		if (isset($oldData)) {
			if ($oldData == 1) {
				$checked = " checked='true' ";
			}
		} else {
			if(isset($field['default'])) {
				if ($field['default']) {
					$checked = " checked='true' ";
				}
			}
		}

		extract($field);
		$width = (!isset($width))? 12 : $width;

		$this->html .="
			<div class='col-sm-$width'>
				<label class='checkbox-inline'>
					<input type='hidden' name='$name' value='0'>
					<input type='checkbox' $checked value='1' name='$name' id='$name'>
					<span class='custom-checkbox'></span>$title
				</label>
			</div>";
	}

	public function ajaxTag($name, $title, $field, $oldData = null, $createNew = false)
	{
		extract($field);
		$oldDataArray = [];
		$name .= '[]';

		if (isset($oldData)) {
			foreach ($oldData as $data) {
				$oldDataArray[] = array_only($data, array('id', 'name'));
			}

			foreach ($oldDataArray as $k => $v) {
				$oldDataArray[$k]['text'] = $oldDataArray[$k]['name'];
				unset($oldDataArray[$k]['name']);
			}
		}

		$width = (!isset($width))? 12 : $width;
		$api = $this->getPreFixedAPI($api);
		$attributes = "data-selectAjax";

		if($createNew) {
			$attributes .= " data-tags	='true'";
		}

		$this->html .="
		<div class='col-sm-$width'>
			<select $attributes data-minimum-input-length='1' data-placeholder='$title' data-allow-clear='true' data-ajax--url='$api' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' multiple='multiple' name='$name' id='$name' data-api='$api' placeholder='$title'>";

		foreach ($oldDataArray as $value) {
			$this->html .='
				<option selected="selected" value="'.$value['id'].'">'.$value['text'].'</option>';
		}

		$this->html .="
			</select>
		</div>";
	}

	public function createAjaxTag($name, $title, $field, $oldData = null)
	{
		$this->ajaxTag($name, $title, $field, $oldData, $createNew = true);
	}

	public function ajaxGuess($name, $title, $field, $oldData = null, $model = null, $createNew = false)
	{
		$default = "";
		$text = "";
		$id = "";

		if (isset($oldData)) {
			if(isset($oldData['id'])) {
				$relationId = $oldData['id'];
			} elseif(!is_array($oldData)) {
				$relationId = $oldData;
			}

			if(isset($relationId)) {
				if(!isset($model)) {
					$path = str_replace("/", "", $field["api"]);
					$model = call_user_func([$this->apiController, 'getModelName'], $path);
				}

				$relationModel = call_user_func(array($model, 'find'), $relationId);

				if (isset($relationModel)) {
					$relationEditLink = $relationModel->getEditUrl();

					$id = @$relationModel->id;
					$text = @$relationModel->getGuessText();
				}
			}
		}

		extract($field);

		/*
			// @todo create a conditional ajaxGuess for Polyrophic Relations 

			$conditional = "";

			if(isset($field['conditional']))
			{
				if($field['conditional'])
					$conditional = "data-conditional";
			}
		*/

		$api = $this->getPreFixedAPI($api);
		$attributes = "data-selectAjax";

		if($createNew) {
			$attributes .= " data-tags	='true'";
		}


		if(isset($relationEditLink) && !empty(@$relationEditLink)) {
			$width = (!isset($width))? 11 : $width - 1;
			$this->html .="
			<div class='col-sm-$width col-xs-11'>
				<select data-minimum-input-length='1' $attributes data-placeholder='$title' data-allow-clear='true' data-ajax--url='$api' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' name='$name' id='$name'>
					<option value='$id'>$text</option>
				</select>
			</div>";
			$this->html .="
			<div class='col-xs-1'>
				<a class='btn btn-sm btn-info link-to-relation' target='_blank' href='$relationEditLink'><i class='fa fa-external-link'></i></a>
			</div>";
		} else {
			$width = (!isset($width))? 12 : $width;
			$this->html .="
			<div class='col-sm-$width'>
				<select data-minimum-input-length='1' $attributes data-placeholder='$title' data-allow-clear='true' data-ajax--url='$api' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' name='$name' id='$name'>
					<option value='$id'>$text</option>
				</select>
			</div>";

		}
	}

	public function createAjaxGuess($name, $title, $field, $oldData = null, $model = null)
	{
		$this->ajaxGuess($name, $title, $field, $oldData, $model, $createNew = true);
	}

	public function divider($title)
	{
		$this->html .="
		<h4>$title</h4>
		<hr/>";
	}

	public function getSelfModel()
	{
		return call_user_func(array($this->structure['model'], 'find'), $this->id);
	}

	public function getPreFixedAPI($api)
	{
		$prefix = (isset($this->structure['apiPrefix'])) ? $this->structure['apiPrefix'] : '';
		return $prefix.$api;
	}
}