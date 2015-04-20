<?php

namespace Yaim\Mitter;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

abstract class BaseNodeController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : null;

		$nodes = call_user_func(array($this->model_name,'all'));
		$nodes = $nodes->toHierarchy()->first();

		if(count($nodes))
			$nodeList = $nodes->tree_to_list($nodes, $this->model_controller_name);
		else $nodeList = array();

		return View::make('users.researcher.node.nodes')->with(array(
			'nodeList' => $nodeList,
			'model_controller_name' => $this->model_controller_name,
			'model_name' => $this->model_name,
			'model_readable_name' => $this->model_readable_name,
			'models_readable_name' => $this->models_readable_name,
			'model_api_address' => $this->model_api_address
		));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(isset($this->structure)) {
			return View::make('users.researcher.model.model-create')->with('structure', $this->structure);
		}

		return View::make('users.researcher.node.node-create')->with(array(
			'model_controller_name' => $this->model_controller_name,
			'model_name' => $this->model_name,
			'model_readable_name' => $this->model_readable_name,
			'models_readable_name' => $this->models_readable_name,
			'model_api_address' => $this->model_api_address
		));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if (Input::get("id") !== null)
		{
			return $this->update(Input::get("id"));
		}

		$node = new $this->model_name;

		$parent_id = Input::get("parent_id");

		$name			= (null	!== Input::get("name")) ? Input::get("name") : "";
		$parent_id		= (null	!== Input::get("parent_id")) ? Input::get("parent_id") : null;

		$node->parent_id = $parent_id;

		if (strlen($name) > 0) {
			$node->name = $name;
		} else {
			return false;
		}

		$node->save();

		if(isset($this->structure)) {
			$controller = new BaseController($this->structure, $node);
			$controller->store();
			return Redirect::back();
		}

		return Redirect::back();
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		return $this->edit($id);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if(isset($this->structure)) {
			$controller = new BaseController($this->structure);
			return $controller->edit($id);
		}

		$node = call_user_func( array($this->model_name,'find'), $id);
		$parent = $node->parent()->first();

		if ($parent->isRoot()) {
			$parent->name="Top level parent.";	
		}

		return View::make('users.researcher.node.node-edit')->with(array(
			'node' => $node,
			'parent' => $parent,
			'model_controller_name' => $this->model_controller_name,
			'model_name' => $this->model_name,
			'model_readable_name' => $this->model_readable_name,
			'models_readable_name' => $this->models_readable_name,
			'model_api_address' => $this->model_api_address,
		));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$node = call_user_func(array($this->model_name,'find'), $id);

		if(isset($this->structure)) {
			$controller = new BaseController($this->structure, $node);
			$controller->update($id);
			return Redirect::back();
		}


		$name			= (null !== Input::get("name")) ? Input::get("name") : "";
		$parent_id		= (null	!== Input::get("parent_id")) ? Input::get("parent_id") : null;
		$node->parent_id = $parent_id;

		if (strlen($name) > 0) {
			$node->name = $name;
		} else {
			return false;
		}

		$node->save();
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
		//
	}


}
