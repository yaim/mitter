<?php

namespace Yaim\Mitter;

trait BaseModelTrait {

    use MitterModelActions;
    use MitterDataTable;

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

	public function revealHidden()
	{
		$this->hidden = [];
		return $this;
	}

	public function getController()
	{
		return $controller = get_called_class()."Controller";
	}

	public function getGuessText()
	{
		return $this->name;
	}
    
    /**
     * Search in model
     * @param $query
     * @param $searchTerm
     * @param array $searchableField
     * @return mixed
     */
    public function scopeSearchByTerm($query, $searchTerm, $searchableField = ['name'])
    {
        if (!$searchTerm) {
            return $query;
        }
        return $query->where(function ($query) use ($searchableField, $searchTerm) {
            foreach ((array)$searchableField as $field) {
                $query->orWhere($field, 'LIKE', "%$searchTerm%");
            }
        });
    }
}
