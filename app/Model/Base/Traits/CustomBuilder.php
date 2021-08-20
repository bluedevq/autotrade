<?php

namespace App\Model\Base\Traits;

use Illuminate\Support\Arr;

trait CustomBuilder
{
    protected $_operators = [
        'eq' => '_equal', 'neq' => '_notEqual', 'gt' => '_greaterThan', 'gteq' => '_greaterThanOrEqual', 'lt' => '_lessThan', 'lteq' => '_lessThanOrEqual',
        'in' => '_in', 'nin' => '_notIn',
        'cons' => '_contains', 'cons_f' => '_containsFirst', 'cons_l' => '_containsLast',
        'isnull' => '_isNull', 'notnull' => '_notNull'
    ];
    protected $_builder = null;
    protected $_oldBuilder = null;
    protected $_queryParams = [];
    protected $_sortField;
    protected $_sortType = 'DESC';
    protected $_limit = 10;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $sortField = $this->getSortField() ? $this->getSortField() : $this->getKeyName();
        $this->setSortField($this->getTable() . '.' . $sortField);
        $this->setBuilder($this);
        $this->_oldBuilder = $this;
    }

    public function setBuilder($builder)
    {
        $this->_builder = $builder;
    }

    public function getBuilder()
    {
        return $this->_builder;
    }

    public function setQueryParams($queryParams)
    {
        $this->_queryParams = $queryParams;
    }

    public function getQueryParams()
    {
        return $this->_queryParams;
    }

    public function setSortField($sortField)
    {
        $sortField = explode(':', $sortField);
        $sortField = isset($sortField[1]) ? $sortField[0] . '.' . $sortField[1] : $sortField[0];
        $sortField = str_replace('[', '.', str_replace(']', '', $sortField));
        $this->_sortField = $sortField;
    }

    public function getSortField()
    {
        return $this->_sortField;
    }

    public function setSortType($sortType)
    {
        if (in_array(strtoupper($sortType), array('DESC', 'ASC'))) {
            $this->_sortType = $sortType;
        }
    }

    public function getSortType()
    {
        return $this->_sortType;
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function hasParam($key)
    {
        return Arr::has($this->_queryParams, $key);
    }

    public function notEmpty($key)
    {
        return Arr::get($this->_queryParams, $key, false);
    }

    public function hasSortField($field)
    {
        if ($this->getSortField() == $field) {
            return true;
        }
        return $this->_getKey($this->getSortField(), '.');
    }

    public function countFromBuilder($builder)
    {
        $count = $builder->getConnection()->select("select count(*) as count from (" . toSql($builder) . ")");
        if (empty($count)) {
            return 0;
        }
        return $count[0]->count;
    }

    public function search($query = array(), $columns = [])
    {
        $this->_resetBuilder();
        $this->setQueryParams($query);
        if (empty($query)) {
            return $this->select($this->_buildColumn($columns))->orderBy($this->getSortField(), $this->getSortType());
        }
        // set sort
        isset($query['sort_type']) ? $this->setSortType($query['sort_type']) : null;
        isset($query['sort_field']) ? $this->setSortField($query['sort_field']) : null;
        // build sql
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $this->_needWhereInOrNotIn($key, $value) ? $this->_buildInOrNotInConditions($key, $value) : $this->_buildConditions($key, $value);
                continue;
            }

            if (trim($value) !== '') {
                $this->_buildConditions($key, $value);
            }
        }
        return $this->getBuilder()->select($this->_buildColumn($columns))->orderBy($this->getSortField(), $this->getSortType());
    }

    protected function _resetBuilder()
    {
        $this->setBuilder($this->_oldBuilder);
    }

    protected function _needWhereInOrNotIn($fieldName, $value)
    {
        if (is_multi_array($value)) {
            return true;
        }
        return strpos($fieldName, '_in') !== false || strpos($fieldName, '_nin') !== false;
    }

    protected function _buildInOrNotInConditions($fieldName, $value)
    {
        $table = '';
        if (is_multi_array($value)) {
            $table = $fieldName;
            foreach ($value as $field => $v) {
                if (!$this->_needWhereInOrNotIn($field, $v)) {
                    continue;
                }
                $this->_mapCondition($field, $v, $table);
            }
            return true;
        }
        $this->_mapCondition($fieldName, $value, $table);
    }

    protected function _buildConditions($fieldName, $value, $table = '')
    {
        if (!is_array($value) && trim($value) !== '') {
            return $this->_mapCondition($fieldName, $value, $table);
        }
        if (empty($value)) {
            return false;
        }
        foreach ($value as $field => $val) {
            $this->_buildConditions($field, $val, $fieldName);
        }
    }

    protected function _mapCondition($fieldName, $value, $table)
    {
        $item = explode('_', $fieldName);
        if (count($item) < 2) {
            return false;
        }
        $operator = end($item);
        array_pop($item);
        $item = implode('_', $item);
        $field = $table ? $table . '.' . $item : $item;
        array_key_exists($operator, $this->_operators) ? $this->{$this->_operators[$operator]}($field, $value) : null;
        return true;
    }

    protected function _buildColumn($columns)
    {
        $tableName = $this->getTable();
        empty($columns) ? $columns = [$tableName . '.*'] : null;
        foreach ($columns as &$column) {
            $column = strpos($column, '.') === false ? $tableName . '.' . $column : $column;
        }
        return $columns;
    }

    protected function _equal($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, $value));
    }

    protected function _notEqual($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, '!=', $value));
    }

    protected function _greaterThan($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, '>', $value . '%'));
    }

    protected function _greaterThanOrEqual($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, '>=', $value));
    }

    protected function _lessThan($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, '<', $value));
    }

    protected function _lessThanOrEqual($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, '<=', $value));
    }

    protected function _in($field, $value)
    {
        $this->setBuilder($this->getBuilder()->whereIn($field, (array)$value));
    }

    protected function _notIn($field, $value)
    {
        $this->setBuilder($this->getBuilder()->whereNotIn($field, (array)$value));
    }

    protected function _contains($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, 'LIKE', '%' . $value . '%'));
    }

    protected function _containsFirst($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, 'LIKE', '%' . $value));
    }

    protected function _containsLast($field, $value)
    {
        $this->setBuilder($this->getBuilder()->where($field, 'LIKE', $value . '%'));
    }

    protected function _isNull($field, $value)
    {
        $this->setBuilder($this->getBuilder()->whereNull($field));
    }

    protected function _notNull($field, $value)
    {
        $this->setBuilder($this->getBuilder()->whereNotNull($field));
    }

    protected function _getKey($key, $prefix = ':')
    {
        $keys = explode($prefix, $key);
        return count($keys) > 1 ? $keys[1] == $key : $keys[0] == $key;
    }
}
