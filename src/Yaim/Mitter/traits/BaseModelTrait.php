<?php

namespace Yaim\Mitter;

trait BaseModelTrait {

	public function getEditUrl()
	{		
		if(isset($this->id)) {
			$controller = $this->getController();

			if(class_exists($controller)) {
				$edit_url = action($controller.'@edit', array('id' => $this->id));
			} else {
				$edit_url = '';
			}

			return $edit_url;
		}
	}

	public function getController()
	{
		return $controller = get_called_class()."Controller";
	}
}