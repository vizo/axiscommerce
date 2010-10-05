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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Controller_Front_Secure extends Axis_Core_Controller_Front
{
    public function preDispatch()
    {
        $request = $this->getRequest();
        $currentUrl = $request->getScheme() . '://'
            . $request->getHttpHost() 
            . $request->getRequestUri();
        
        if (Axis::config('core/frontend/ssl') 
            && 0 !== strpos($currentUrl, $this->view->secureUrl)) {
            
            $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
            $requestUri = substr($request->getRequestUri(), strlen($baseUrl));
            $this->_redirect($this->view->secureUrl . $requestUri, array(), false);
            die();
        }
    }
}