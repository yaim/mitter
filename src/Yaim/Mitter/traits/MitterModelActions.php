<?php

namespace Yaim\Mitter;


trait MitterModelActions
{
    public function getIndexAction()
    {
        return $this->getAction('index', false);
    }

    public function getCreateAction()
    {
        return $this->getAction('create', false);
    }

    public function getStoreAction()
    {
        return $this->getAction('store', false);
    }

    public function getShowAction()
    {
        return $this->getAction('show');
    }

    public function getEditAction()
    {
        return $this->getAction('edit');
    }

    public function getUpdateAction()
    {
        return $this->getAction('update');
    }

    public function getDeleteAction()
    {
        return $this->getAction('destroy');
    }

    public function getAction($actionName, $withModel = true)
    {
        if(!$this->controller)
        {
            $aliases = mitterGetAliasesByModelName(static::class);
            $id = $withModel ? $this->id : null;
            return action("\\Yaim\\Mitter\\BaseController@{$actionName}", ['model' => $aliases, 'id' => $id]);
        }
        return action("{$this->controller}@{$actionName}");
    }
}