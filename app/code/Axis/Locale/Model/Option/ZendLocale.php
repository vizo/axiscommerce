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
 * @package     Axis_Collect
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Model_Option_ZendLocale extends Axis_Config_Option_Array_Abstract
{
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        $options = array();
        $locale  = Axis_Locale::getLocale();

        $locales    = Zend_Locale::getLocaleList();

        $languages  = Zend_Locale::getTranslationList('language', $locale);
        $countries  = Zend_Locale::getTranslationList('territory', $locale, 2);

        if (!$languages || !$countries) {
            $languages  = Zend_Locale::getTranslationList(
                'language', Axis_Locale::DEFAULT_LOCALE
            );
            $countries  = Zend_Locale::getTranslationList(
                'territory', Axis_Locale::DEFAULT_LOCALE, 2
            );
        }

        foreach ($locales as $code => $isActive) {
            if (!strstr($code, '_')) {
                continue;
            }
            list($language, $region) = explode('_', $code);
            if (!isset($languages[$language]) || !isset($countries[$region])) {

                continue;
            }
            $options[$code] = ucfirst($languages[$language]) . ' ('
                . $countries[$region] . ')';
        }
        return $options;
    }
}
