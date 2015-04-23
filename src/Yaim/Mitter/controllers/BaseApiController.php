<?php

namespace Yaim\Mitter;

use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

abstract class BaseApiController extends Controller {

	protected $route;

	public function index($model = null, $parent = null, $term_id = null)
	{
		$route = $this->route;

		if(strpos($model, "&")) {
			$models = explode("&", $model);
			$arrays = array();

			$results = array('totals' => 0, 'results' => array());

			foreach ($models as $model) {
				$response = $this->index($model);
				if ($response->getStatusCode() == 404) {
					return $response->getData()->message;
				}

				$arrays[] = $response->getData();
			}

			foreach ($arrays as $array) {
				if (!is_object($array)) {
					continue;
				}

				$array->totals = (isset($array->totals))? $array->totals : 0;

				$results['totals'] = $results['totals'] + $array->totals;
				$results['results'] = array_merge($results['results'], $array->results);
			}

			return Response::json($results,200);
		}


		if (!isset($model)) {
			return Response::json(array("message"=>"This is the Application Programming Interface (API) for Mitter."),200);
		}

		$methodName = str_singular(camel_case($model));

		if(!isset($route[$model])) {
			return Response::json(array("error"=>"Model for $model not found!"),404);
		} elseif(method_exists($this, $methodName)) {
			$term = (isset($_REQUEST['q']))? urldecode($_REQUEST['q']) : null;
			return $this->$methodName($term);
		} else {
			$term = (isset($_REQUEST['q']))? urldecode($_REQUEST['q']) : null;

			if ($parent) {
				$parent = call_user_func( array($route[$model],'where'), 'name', '=', $parent)->first();
				return $children = $parent->getDescendants()->toArray();

				if(isset($term)) {
					dd(array_search($term, $children));
				} else {
					return $children;
				}
			}

			if(isset($term_id)) {
				$temp_model = $route[$model]::find($term_id);
				$queries[][$temp_model->name] = $temp_model->id;
			} else {
				$queries = call_user_func( array($route[$model],'where'), 'name', 'like', "%$term%");
				$queries = $queries->select('id', \DB::raw('name AS text'))->get()->toArray();
			}

			$queries = (isset($queries))? $queries : null;

			$results['totals'] = count($queries);
			$results['results'] = $queries;

			return Response::json($results,200);
		}

	}

	public function getModelName($model)
	{
		return $this->route[$model];
	}
}
