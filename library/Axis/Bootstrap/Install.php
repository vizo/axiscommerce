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
 * @package     Axis_Test
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Test
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Bootstrap_Install extends Axis_Bootstrap
{
    protected function _initLoader()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace(array(
            'Axis'
        ));
        return $autoloader;
    }
    
    protected function _initView()
    {
        return Zend_Layout::startMvc();
    }

    protected function _initSession()
    {
        Zend_Registry::set(
            'session', new Zend_Session_Namespace('install', true)
        );
        return Zend_Registry::get('session');
    }

    protected function _initFrontController()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noViewRenderer', true);
        $front->setControllerDirectory('app/controllers');
        $front->dispatch();
        return $front;
    }
}