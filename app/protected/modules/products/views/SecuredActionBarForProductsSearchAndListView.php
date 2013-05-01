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
     * Action bar view for the product search and list user interface. Provides buttons like create, and links to
     * queues.
     */
    class SecuredActionBarForProductsSearchAndListView extends SecuredActionBarForSearchAndListView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'	  => 'ProductCreateLink',
                                'htmlOptions'	  => array('class' => 'icon-create'),
                            ),
                            array(
                                'type'            => 'ProductsLink',
                                'htmlOptions'     => array( 'class' => 'icon-workflows' )
                            ),
                            array(
                                'type'            => 'ProductTemplatesLink',
                                'htmlOptions'     => array( 'class' => 'icon-by-time-workflow-in-queues' )
                            ),
                            array(
                                'type'            => 'ProductCategoriesLink',
                                'htmlOptions'     => array( 'class' => 'icon-by-workflow-message-in-queues' )
                            ),
			    array(
                                'type'            => 'MassDeleteLink',
                                'htmlOptions' => array('class' => 'icon-delete'),
                                'listViewGridId' => 'eval:$this->listViewGridId',
                                'pageVarName' => 'eval:$this->pageVarName'
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }
    }
?>