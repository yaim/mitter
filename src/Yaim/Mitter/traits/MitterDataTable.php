<?php

namespace Yaim\Mitter;

trait MitterDataTable
{
    /**
     * get table columns from database
     * @return mixed
     */
    private function getDatabaseTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * return table columns after filters
     * @param bool $withTitle
     * @return mixed
     */
    public function getDatatableColumns($withTitle = false)
    {
        if (method_exists($this, 'datatableColumns')) {
            $cols = $this->datatableColumns();
        } else {
            // get all columns
            $cols = $this->getDatabaseTableColumns();
            // merge model hidden columns with dataTableExceptColumns
            $except = array_merge($this->getHidden(), ($this->dataTableBlackList ?: []));
            $append = $this->dataTableWhiteList ?: [];
            $cols = array_merge(array_diff($cols, $except), $append);
        }
        // unique result
        $result = array_unique($cols);

        if (method_exists($this, 'editDatatableColumns')) {
            $result = $this->editDatatableColumns($cols);
        }

        if ($withTitle) {
            $fieldsTitle = collect((array)$this->dataTableColumnsTitle);
            $res = [];
            foreach ($result as $field) {
                $res[$field] = $fieldsTitle->get($field, $field);
            }
            return $res;
        }
        return $result;
    }

    /**
     * transform model
     * @param $dataTableCols
     * @return array
     */
    public function toDatatableRow($dataTableCols)
    {
        $result = [];
        array_map(function ($key) use (&$result) {
            if (array_key_exists($key, (array)$this->dataTableRelations)) {
                $relation = $this->{$key}()->first();
                if ($relation) {
                    $result[$key] = $relation->{$this->dataTableRelations[$key]};
                }
            } elseif (array_key_exists($key,(array) $this->dataTableFunctions)) {
                $result[$key] = $this->{$this->dataTableFunctions[$key]}();
            } else {
                $result[$key] = $this->{$key};
            }
        }, $dataTableCols);
        if (method_exists($this, 'editDataTableRow')) {
            return $this->editDataTableRow($result);
        }
        return $result;
    }

    /**
     * return transformed models with used columns
     * @param $models
     * @return array
     */
    public function getDatatableRows($models)
    {
        $dataTableCols = $this->getDatabaseTableColumns();
        return array_map(function ($model) use ($dataTableCols) {
            return collect($model->toDatatableRow($dataTableCols));
        }, $models);
    }

    /**
     * get model title
     * @return mixed
     */
    public function getDatatableTitle()
    {
        return $this->datatableTitle ?: studly_case(class_basename($this));
    }

    /**
     * return Datatable
     * @return array
     * @internal param int $datatablePerPage
     */
    public function renderTable()
    {
        if (method_exists($this, 'datatableQuery')) {
            $query = $this->datatableQuery();
        } else {
            $query = $this
                ->SearchByTerm(request('search'))
                ->orderBy('id', 'desc');
        }
        $query = $query->paginate($this->datatablePerPage ?: 20);
        return [
            'title' => $this->getDatatableTitle(),
            'createUrl' => $this->getCreateAction(),
            'head' => $this->getDatatableColumns(true),
            'items' => $this->getDatatableRows($query->items()),
            'paginate' => $query->links()
        ];
    }

//    /**
//     * dataTable query
//     * notice : you can use this
//     * @return mixed
//     */
//    public function datatableQuery()
//    {
//        return $this->query();
//
//    }

}
