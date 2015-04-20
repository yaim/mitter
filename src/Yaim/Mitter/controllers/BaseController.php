<?php

namespace Yaim\Mitter;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;

class BaseController extends Controller {
	protected $node_model;

	public function __construct($structure = array(), $node_model = false)
	{
		if (!empty($structure)) {
			$this->structure = $structure;
		}

		$this->node_model = $node_model;
	}

	public function getStructure()
	{
		return $this->structure;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$search = (null !== Input::get('search')) ? Input::get('search') : "";
		$structure = $this->structure;
		$required = array();
		$models = array();
		$rows = array();


		if(strlen($search) >= 0) {
			if(isset($structure['index']['self'])) {
				if (is_array($structure['index']['self'])) {
					foreach ($structure['index']['self'] as $self => $title) {
						$required[$self]['title'] = $title;
					}
				}
			}

			if(isset($structure['index']['relations'])) {
				if (is_array($structure['index']['relations'])) {
					foreach ($structure['index']['relations'] as $relation => $array) {
						foreach ($array as $key => $value) {
							$required[$relation.'.'.$key]['title'] = $value;
						}

						$relations[] = $relation;
					}
				}
			}

			if(isset($relations)) {
				$models = call_user_func(array($structure['model'], 'with'), $relations)->where('name', 'LIKE', "%$search%")->get();
			} else {
				$models = call_user_func(array($structure['model'], 'where'), 'name', 'LIKE', "%$search%")->get();
			}

			$models = $models->toArray();

			list($required_keys) = array_divide($required);

			foreach ($models as $model_key => $model) {
				foreach ($required_keys as $required_key) {
					$title = $required[$required_key]['title'];
					$name = array_get($model, $required_key);

					if (!isset($name)) {
						$address = explode('.', "$required_key");

						if(is_array($model[$address[0]])) {
							foreach ($model[$address[0]] as $sub) {
								$name .= array_get($sub, $address[1]).", ";
							}
						}
					}
					$rows[array_get($model, 'id')][$title] = $name;
				}
			}
		}

		return View::make('users.researcher.model.models')->with(array('rows' => $rows, 'search' => $search, 'structure' => $structure));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$apiController = new \ApiController;

		$html = new FormBuilder($this->structure, $apiController);
		$form = $html->get();

		return View::make('users.researcher.model.model-create')->with('form', $form);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$model = new FormSaver($this->structure, Input::all(), $this->node_model);
		$id = $model->getModel()->id;

		$url = URL::action($this->structure['controller']."@edit", ['id' => $id]);
		return Redirect::to($url);

	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$url = URL::action($this->structure['controller']."@edit", ['id' => $id]);
		return Redirect::to($url);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$relations = array();

		if (isset($this->structure['relations'])) {
			foreach ($this->structure['relations'] as $key => $value) {
				if (isset($value['type'])) {
					if ($value['type'] == 'divider') {
						continue;
					}
				} 
				$relations[] = $key;
			}
		}

		$model = call_user_func(array($this->structure['model'], 'withTrashed'))->with($relations)->find($id);
		$modelData = (isset($model))? $modelData = array_filter($model->toArray(), 'nullFilter') : null;
		// $modelData = array_filter(call_user_func(array($this->structure['model'], 'withTrashed'))->with($relations)->find($id)->toArray());
		if(!isset($modelData)) {
			return Response::view('errors.missing', array(), 404);
		}

		$apiController = new \ApiController;
		$html = new FormBuilder($this->structure, $apiController, $modelData, $id);
		$form = $html->get();

		return View::make('users.researcher.model.model-create')->with('form', $form);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		new FormSaver($this->structure, Input::all(), $this->node_model);
		return Redirect::back();
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$model = call_user_func([$this->structure['model'], 'find'], $id);
		$model->delete();

		$url = URL::action($this->structure['controller']."@index");

		return Redirect::to($url);
	}

}