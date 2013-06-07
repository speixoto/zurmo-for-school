<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Product related array of random seed data parts.
     */
    function getProductsRandomData()
    {
        $productNames = array(
                                'names' => array(
                                    'Amazing Kid Sample',
                                    'You Can Do Anything Sample',
                                    'A Bend in the River November Issue',
                                    'A Gift of Monotheists October Issue',
                                    'Enjoy Once in a Lifetime Music'
                                )
                            );

        $productTemplates = ProductTemplate::getAll();

        foreach ($productTemplates as $template)
        {
            if ((strpos($template->name, 'Laptop')   !== false) ||
                (strpos($template->name, 'Camera')   !== false) ||
                (strpos($template->name, 'Handycam') !== false))
            {
                for ($i = 1; $i < 3; $i++)
                {
                   $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2);
                   $productNames['names'][] = $template->name . '-P' . $randomString;
                }
            }
        }

        return $productNames;
    }
?>