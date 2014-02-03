<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     *
     * Helper class to manage changing the panelsDisplayType for a layout in the designer user interface.
     */
    class PanelsDisplayTypeLayoutMetadataUtil
    {
        /**
         * Given metadata make a LayoutPanelsTypeForm.
         * @param array $metadata
         * @return object LayoutPanelsTypeForm
         */
        public static function makeFormFromEditableMetadata($metadata)
        {
            assert('isset($metadata["global"]["panelsDisplayType"])');
            $formModel = new LayoutPanelsTypeForm();
            $panelsDisplayType = $metadata['global']['panelsDisplayType'];
            assert('$panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_ALL ||
                    $panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_FIRST ||
                    $panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_TABBED');
            $formModel->type = $metadata['global']['panelsDisplayType'];
            return $formModel;
        }

        /**
         * Given savable metadata for a layout, populate that array from the posted data.
         * @param array $savableMetadata
         * @return boolean true if the savable metadata was populated successfully.
         */
        public static function populateSaveableMetadataFromPostData(& $savableMetadata, $layoutPanelsTypeFormPostData)
        {
            assert('is_array($savableMetadata)');
            assert('is_array($layoutPanelsTypeFormPostData)');
            $panelsDisplayTypeForm = new LayoutPanelsTypeForm();
            $panelsDisplayTypeForm->setAttributes($layoutPanelsTypeFormPostData);
            $validated = $panelsDisplayTypeForm->validate();
            if (!$validated)
            {
                return false;
            }
            $savableMetadata['panelsDisplayType'] = (int)$panelsDisplayTypeForm->type;
            return true;
        }
    }
?>