<?php namespace Yaim\Mitter;

include __DIR__.'/../functions.php';

use Illuminate\Routing\Controller;

class BaseController extends Controller {

	protected $structure;
	protected $nodeModel;
	protected $apiController;
	protected $view;

	public function __construct()
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}
	}

	public function getStructure()
	{
		return $this->structure;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{
		$search = (null !== \Input::get('search')) ? \Input::get('search') : "";
		$structure = $this->structure;
		$searchableField = isset($structure['searchable_fields'])? $structure['searchable_fields'] : 'name';
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

			$models = collect();
			if(isset($relations)) {
				if(is_array($searchableField)) {
					foreach ($searchableField as $field) {
						$models = $models->merge(call_user_func(array($structure['model'], 'with'), $relations)->where($field, 'LIKE', "%$search%")->get());
					}
				} else {
					$models = $models->merge(call_user_func(array($structure['model'], 'with'), $relations)->where($searchableField, 'LIKE', "%$search%")->get());
				}
			} else {
				if(is_array($searchableField)) {
					foreach ($searchableField as $field) {
						$models = $models->merge(call_user_func(array($structure['model'], 'where'), $field, 'LIKE', "%$search%")->get());
					}
				} else {
					$models = $models->merge(call_user_func(array($structure['model'], 'where'), $searchableField, 'LIKE', "%$search%")->get());
				}
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

		return \View::make($this->view['index'])->with(array('rows' => $rows, 'search' => $search, 'structure' => $structure));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
		$html = new FormBuilder($this->structure, $this->apiController);
		$form = $html->get();

		return \View::make($this->view['create'])->with('form', $form);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Redirect
	 */
	public function store()
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$model = new FormSaver($this->structure, \Input::all(), $this->nodeModel);
		$id = $model->getModel()->id;

		$url = action($this->structure['controller']."@edit", ['id' => $id]);
		return \Redirect::to($url);

	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Redirect
	 */
	public function show($id)
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$url = action($this->structure['controller']."@edit", ['id' => $id]);
		return \Redirect::to($url);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return View
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
		$modelData = (isset($model))? $modelData = array_filter($model->toArray(), 'mitterNullFilter') : null;
		// $modelData = array_filter(call_user_func(array($this->structure['model'], 'withTrashed'))->with($relations)->find($id)->toArray());
		if(!isset($modelData)) {
			return \Response::view('errors.missing', array(), 404);
		}

		$html = new FormBuilder($this->structure, $this->apiController, $modelData, $id);
		$form = $html->get();

		return \View::make($this->view['create'])->with('form', $form);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Redirect
	 */
	public function update($id)
	{
		new FormSaver($this->structure, \Input::all(), $this->nodeModel);
		return \Redirect::back();
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Redirect
	 */
	public function destroy($id)
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$model = call_user_func([$this->structure['model'], 'find'], $id);
		$model->delete();

		$url = action($this->structure['controller']."@index");

		return \Redirect::to($url);
	}

}