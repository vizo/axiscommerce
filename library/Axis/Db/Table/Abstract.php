<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_Db
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @author      Axis Core Team <core@axiscommerce.com>
 */
abstract class Axis_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = null;

    protected $_rowClass = 'Axis_Db_Table_Row';

    protected $_rowsetClass = 'Axis_Db_Table_Rowset';

    const SELECT_CLASS = 'selectClass';

    protected $_selectClass = 'Axis_Db_Table_Select';

    const PREFIX = 'prefix';

    /**
     * @var string
     */
    protected $_prefix = null;

    /**
     * Initialize table and schema names.
     *
     * If the table name is not set in the class definition,
     * use the class name itself as the table name.
     *
     * A schema name provided with the table name (e.g., "schema.table") overrides
     * any existing value for $this->_schema.
     *
     * @return void
     */
    protected function _setupTableName()
    {
        parent::_setupTableName();
        $this->_prefix = Axis::config()->db->prefix;
        $this->_name = $this->_prefix . $this->_name;
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Axis_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        //@todo now never used, need create Axis_Db factory with
        if (isset($options[self::PREFIX])) {
            $this->_prefix = $options[self::PREFIX];
        }
        if (isset($options[self::SELECT_CLASS])) {
            $this->setSelectClass($options[self::SELECT_CLASS]);
        }
        return parent::setOptions($options);
    }

    /**
     * Returns table information.
     *
     * You can elect to return only a part of this information by supplying its key name,
     * otherwise all information is returned as an array.
     *
     * @param  $key The specific info part to return OPTIONAL
     * @return mixed
     */
    public function info($key = null)
    {
        $this->_setupPrimaryKey();

        $info = array(
            self::SCHEMA           => $this->_schema,
            self::NAME             => $this->_name,
            self::PREFIX           => $this->_prefix,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array) $this->_primary,
            self::METADATA         => $this->_metadata,
            self::ROW_CLASS        => $this->getRowClass(),
            self::ROWSET_CLASS     => $this->getRowsetClass(),
            self::SELECT_CLASS     => $this->getSelectClass(),
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
            self::SEQUENCE         => $this->_sequence
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception(
                'There is no table information for the key "' . $key . '"'
            );
        }

        return $info[$key];
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function getTableName($name = null)
    {
        if (null === $name) {
            return $this->_name;
        }
        return $this->_prefix . $name;
    }

    /**
     * @param  string $classname
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
    public function setSelectClass($selectClass)
    {
        $this->_selectClass = $selectClass;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSelectClass()
    {
        return $this->_selectClass;
    }

    /**
     * @param mixed $columns array, string, Zend_Db_Expr
     * @return Axis_Db_Table_Select
     * @throws Axis_Exception
     */
    public function select($columns = array())
    {
        $className = $this->getSelectClass();

        $select = new $className($this);

        if (!$select instanceof Axis_Db_Table_Select) {
            throw new Axis_Exception(Axis::translate('core')->__(
                'Instance of %s expected, %s given',
                'Axis_Db_Table_Select' ,
                $className
            ));
        }

        if (!empty($columns)) {
            $select->from(
                substr($this->_name, strlen($this->_prefix)),
                $columns,
                $this->info(Zend_Db_Table_Abstract::SCHEMA)
            );
        }

        return $select;
    }

    /**
     *
     * @method string hasName
     * @method int getName
     * @method int getNameById
     * @method string cntName
     * @example Axis::single('core/template')->getNameById(1)
     *                   -//-   ->getName(1) used  first primary key
     *                   -//-   ->getIdByName('default')
     *                   -//-   ->hasName('default')
     *                   -//-   ->cntFieldName('try') count by field "field_name" = 'try'
     */
    public function __call($call, $argv)
    {
//        $columns = array_keys($this->_db->describeTable(
//          $this->_prefix . $this->_name, $this->_schema)
//        );
//        $columns = $this->info('cols');
        $fields = explode('By', substr($call, 3));

//        if(false === function_exists('camelize')) {
//            function camelize($str) {
//                $str = str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
//                return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
//            }
//        }
        if(false === function_exists('underscore')) {
            function underscore($str = null) {
                return strtolower(
                    preg_replace(
                        array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $str
                    )
                );
            }
        }

        $ruturnField = underscore($fields[0]);

        if (isset($fields[1])) {
            $conditionField = underscore($fields[1]);
        }
        if (!isset($conditionField)
            || null === $conditionField
            || !$conditionField) {

            $conditionField = current($this->info('primary'));
        }
//        if (!in_array($conditionField, $columns)
//            || !in_array($ruturnField, $columns)) {
//
//            throw new Axis_Exception(
//                Axis::translate('core')->__('Incorrect condition ') .
//                    $call . ' ( ' . $ruturnField . ', ' .$conditionField . ' )'
//            );
//        }

        $conditionValue = current($argv);
        if (null === $conditionValue) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    'Condition "%" is null', $conditionField
                )
            );
        }
        switch (substr($call, 0, 3)) {
            case 'get':
                return $this->getAdapter()->fetchOne(
                   "SELECT {$ruturnField} FROM " . $this->_name
                       . " WHERE {$conditionField} = ? ",
                   $conditionValue
                );
            case 'has':
                $count = $this->getAdapter()->fetchOne(
                   'SELECT COUNT(*) FROM ' . $this->_name
                        . " WHERE {$ruturnField} = ?",
                    $conditionValue
                );
                return $count ? true : false;
            case 'cnt':
                $count = $this->getAdapter()->fetchOne(
                   'SELECT COUNT(*) FROM ' . $this->_name
                        . " WHERE {$ruturnField} = ?",
                    $conditionValue
                );
                return $count;
        }

        throw new Axis_Exception(Axis::translate('core')->__(
            "Call to undefined method %s", get_class($this) . '::' . $call
        ));
    }

    /**
     * @todo remove this, need use Axis::message in controllers
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data)
    {
        try {
            return parent::insert($data);
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            return false;
        }
    }

    /**
     *   * @todo remove this, need use Axis::message in controllers
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where)
    {
        try {
            return parent::update($data, $where);
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            return false;
        }
    }

    /**
     * @todo remove this, need use Axis::message in controllers
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        try {
            return parent::delete($where);
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            return false;
        }
    }

    /**
     *
     * @return Axis_Db_Table_Abstract
     */
    public function cache()
    {
        $frontend = Axis::single('Axis_Cache_Frontend_Query');

        if (func_num_args()) {
            $args = serialize(func_get_args());
            return $frontend->setInstance($this, $args);
        }
        return $frontend->setInstance($this);
    }

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select()
                ->setUseCollerationName(false)
                ->setIntegrityCheck();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($count !== null || $offset !== null) {
                $select->limit($count, $offset);
            }

        } else {
            $select = $where;
        }
        $rows = $this->_fetch($select);

        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }
        return new $rowsetClass($data);
    }

    /**
     * Fetches one row in an object of type Zend_Db_Table_Row_Abstract,
     * or returns null if no row matches the specified criteria.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select()
                ->setUseCollerationName(false)
                ->setIntegrityCheck();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1);

        } else {
            $select = $where->limit(1);
        }

        $rows = $this->_fetch($select);

        if (count($rows) == 0) {
            return null;
        }

        $data = array(
            'table'   => $this,
            'data'     => $rows[0],
            'readOnly' => $select->isReadOnly(),
            'stored'  => true
        );

        $rowClass = $this->getRowClass();
        if (!class_exists($rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowClass);
        }
        return new $rowClass($data);
    }
}