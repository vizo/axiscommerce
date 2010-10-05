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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Field extends Axis_Db_Table 
{
    protected $_name = 'account_customer_field';
    protected $_dependentTables = array(
        'Axis_Account_Model_Customer_Field_Label'
    );
    
    /**
     * 
     * @static
     * @var const array
     */
    public static $fieldMulti = array(
        'radio', 'select', 'multiselect', 'multiCheckbox'
    );

    /**
     *
     * @param int $groupId
     * @return array
     */
    public function getFieldsByGroup($groupId)
    {        
        $list = array();
        
        $i = 0;
        $rowset = $this->fetchAll('customer_field_group_id = ' . $groupId);
        foreach ($rowset as $row) {
            $list[$i] = $row->toArray();

            $labels = $row->findDependentRowset(
                'Axis_Account_Model_Customer_Field_Label'
            );
            
            foreach ($labels as $label) {
                $list[$i]['field_label' . $label->language_id] = $label->field_label;
            }
            ++$i;
        }
        
        return $list;
    }

    /**
     *
     * @return const array
     */
    public function getFieldTypes()
    {
        return array(
            'text'        => 'text',
            'radio'       => 'radio',
            'select'      => 'select',
            'multiselect' => 'multiselect',
            'textarea'    => 'textarea',
            'checkbox'    => 'checkbox',
            'multiCheckbox' => 'multiCheckbox'   
        );
    }

    /**
     *
     * @return const array
     */
    public function getValidators()
    {
        return array(
            '' => 'Don\'t validate',
            'Alnum' => 'Letters & digits',
            'Alpha' => 'Letters only',
            'Ccnum' => 'Credit card number',
            'Date' => 'Date',
            'Digits' => 'Digits only',
            'EmailAddress' => 'Email',
            'HostName' => 'Host',
            'Ip' => 'Ip address' 
        );
    }

    /**
     *
     * @param array $data
     * @return void
     */
    public function save($data)
    {
        $db = $this->getAdapter();
        if (!sizeof($data)) {
            return;
        }
        $label = Axis::single('account/Customer_Field_Label');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($data as $values) {
            
            $required = 1;
            if (!isset($values['required']) || !$values['required']) {
                $required = 0;
            }
                
            $isActive = 1;
            if (!isset($values['is_active']) || !$values['is_active']) {
                $isActive = 0;
            }
            
            $row = array(
                'customer_field_group_id' => $values['customer_field_group_id'],
                'name'          => $values['name'],
                'field_type'    => $values['field_type'],
                'required'      => $required,
                'sort_order'    => $values['sort_order'],
                'is_active'     => $isActive,
                'customer_valueset_id' => $values['customer_valueset_id'] ? 
                    $values['customer_valueset_id'] : new Zend_Db_Expr('NULL'),
                'validator' => $values['validator'] == '' ?
                    new Zend_Db_Expr('NULL') : $values['validator'],
                'axis_validator' => $values['axis_validator'] == '' ?
                    new Zend_Db_Expr('NULL') : $values['axis_validator']
            );
            
            if (isset($values['new']) || $values['id'] == 'new') {
                $id = $this->insert($row);
                foreach ($languageIds as $langId) {
                    $label->insert(array(
                        'customer_field_id' => $id,
                        'language_id' => $langId,
                        'field_label' => $values['field_label' . $langId]
                    ));
                }
            } else {
                $this->update($row, $db->quoteInto('id = ?', $values['id']));
                //labels update at customer_field_label
                foreach ($languageIds as $langId) {
                    if (!$record = $label->find($values['id'], $langId)->current()) {
                        $record = $label->createRow(array(
                            'customer_field_id' => $values['id'],
                            'language_id' => $langId,
                            'field_label' => $values['field_label' . $langId]
                        ));
                    } else {
                        $record->setFromArray(array(
                            'field_label' => $values['field_label' . $langId]
                        ));
                    }
                    $record->save();
                }
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
    }

    /**
     *
     * @return array
     */
    public function getFields()
    {
        $query = "SELECT cf.*, cfl.field_label FROM " . $this->_prefix . 'account_customer_field' . " cf
            INNER JOIN " . $this->_prefix . "account_customer_fieldgroup cfg ON cfg.id = cf.customer_field_group_id
            INNER JOIN " . $this->_prefix . "account_customer_field_label cfl ON cfl.customer_field_id = cf.id
            WHERE cf.is_active = 1 AND cfg.is_active = 1 AND cfl.language_id = ?
            ORDER BY cf.sort_order";
        return $this->getAdapter()->fetchAll($query, Axis_Locale::getLanguageId());
    }
    
    /**
     * @param mixed $name array(name => Label)
     * @param mixed $group array(name => Label)
     * @param array $fieldInfo account_customer_field row
     * @param array $groupInfo account_customer_fieldgroup row
     * @return Axis_Account_Model_Customer_Field Provides fluent interface
     */
    public function add(
        $field, $group, $fieldInfo = array(), $groupInfo = array())
    {
        if (!is_array($field)) {
            $field = array($field => $field);
        }
        if (!is_array($group)) {
            $group = array($group => $group);
        }
        
        $languageIds = array_keys(Axis_Collect_Language::collect());

        $modelFieldGroup = Axis::single('account/customer_FieldGroup');
        /* create field group */
        if (!($groupId = $modelFieldGroup->getIdByName(key($group)))) {

            $defaultGroupInfo = array(
                'name' => key($group), 'sort_order' => 5, 'is_active' => 1
            );
            $groupId = $modelFieldGroup->insert(
                array_merge($defaultGroupInfo, $groupInfo)
            );
            $groupLabel = current($group);
            $modelFieldGroupLabel = Axis::single(
                'account/customer_FieldGroup_Label'
            );
            foreach ($languageIds as $languageId) {
                $modelFieldGroupLabel->insert(array(
                    'customer_field_group_id' => $groupId, 
                    'language_id' => $languageId, 
                    'group_label' => $groupLabel
                ));
            }
        }
        
        /* create field */
        if (!($fieldId = $this->getIdByName(key($field)))) {
            $defaultFieldInfo = array(
                'name' => key($field),
                'customer_field_group_id' => $groupId,
                'field_type' => 'text', 
                'required' => 0, 
                'sort_order' => 5,
                'is_active' => 1,
                'customer_valueset_id' => new Zend_Db_Expr('NULL'),
                'validator' => new Zend_Db_Expr('NULL'),
                'axis_validator' => new Zend_Db_Expr('NULL')
            );
            $fieldId = $this->insert(
                array_merge($defaultFieldInfo, $fieldInfo)
            );
            $fieldLabel = current($field);
            foreach ($languageIds as $languageId) {
                Axis::single('account/customer_field_label')->insert(array(
                    'customer_field_id' => $fieldId, 
                    'language_id' => $languageId, 
                    'field_label' => $fieldLabel
                ));
            }
        }
        
        return $this;
    }
    
    /**
     * Removes field by name
     * @param string $name
     * @return Axis_Account_Model_Customer_Field Provides fluent interface
     */
    public function remove($name)
    {
        $this->delete("name = '$name'");
        return $this;
    }
}