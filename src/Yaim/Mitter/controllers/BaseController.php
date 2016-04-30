<?php namespace Yaim\Mitter;

use Illuminate\Routing\Controller;

class BaseController extends Controller {

	protected $structure;
	protected $nodeModel;
	protected $apiController;
	protected $view;
	protected $paginate;

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
	 * @param $model
	 * @return View
	 */
	public function index($model)
	{
		// get model instance
		$model = new $this->getModel($model);
		// render table
		$table = view('mitter::layouts.table', $model->renderTable())->render();
		// view file
		$viewFile = $model->indexView ?: config('mitter.views.index');

		return view($viewFile, compact('table'));
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
		$modelData = (isset($model))? $modelData = array_filter($model->revealHidden()->toArray(), 'mitterNullFilter') : null;
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

	/**
	 * @param $model
	 * @param null $id
	 * @return mixed
	 */
	private function getModel($model, $id = null)
	{
		if (!$model instanceof Model) {
			if (hasMitterModelAliases($model)) {
				$model = getMitterModelByAliasesName($model);
			}
		}
		if ($model instanceof Model) {
			if ($id) {
				$model = $model->findOrFail($id);
			}
			return $model;
		}
		return abort(404);
	}

}
