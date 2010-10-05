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
 * @package     Axis_Community
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Box_ReviewProduct extends Axis_Catalog_Box_Product_Abstract
{
    protected $_title = 'Reviews';
    protected $_class = 'box-review-product';
    
    public function initData()
    {
        if (!$this->productId) {
            return false;
        }
        
        if ($this->lastProductId == $this->productId
            && $this->hasReviews() && $this->hasCount()) {
            
            return true;
        }
        
        $data = Axis::single('community/review')->getTinyList(
            'cr.product_id = ' . (int)$this->productId,
            $this->order ? $this->order : 'cr.date_created',
            $this->dir   ? $this->dir : 'DESC',
            $this->limit ? $this->limit : 5,
            $this->page  ? $this->page : null
        );
        
        $this->lastProductId = $this->productId;
        $this->reviews = $data['reviews'];
        $this->count = $data['count'];
    }
}