<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    abstract class SelectBaseTemplateBaseElement extends Element
    {
        // TODO: @Shoaibi: Critical: Remember to populate thumbnailUrl property for predefined templates

        // TODO: @Shoaibi/Amit: Critical: Update this path.
        const GENERIC_THUMBNAIL_PATH = '/default/images/1x1-pixel.png';

        public $editableTemplate = '{content}';

        abstract protected function resolveBaseTemplates();

        protected function renderControlEditable()
        {
            $content = null;
            $content .= $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                $this->resolveData(),
                $this->getEditableHtmlOptions()
            );
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions              = array();
            $htmlOptions['separator'] = '';
            $htmlOptions['template']  = '<li class="radio-input base-template-selection {value}">{input}{label}</li>';
            return $htmlOptions;
        }

        protected function resolveData()
        {
            $templates  = $this->resolveBaseTemplates();
            $data       = array();
            foreach ($templates as $template)
            {
                $thumbnailUrl           = $this->resolveThumbnailUrl($template);
                $label                  = "<img src='${thumbnailUrl}' class='thumb' />" .
                                                "<h6 class='name'><span>" . $template->name . "</span></h6>";
                $data[$template->id]    = $label;
            }
            return $data;
        }

        protected function resolveThumbnailUrl(EmailTemplate $template)
        {
            $unserializedData = unserialize($template->serializedData);
            if (!empty($unserializedData['thumbnailUrl']))
            {
                return $unserializedData['thumbnailUrl'];
            }
            return $this->resolveGenericThumbnailUrl();
        }

        protected function resolveGenericThumbnailUrl()
        {
            return 'http://www.shellplus.com/examples/thumbnail-image-handler/img/ex_extractimage_example_thumbnail.gif';
            // TODO: @Shoaibi: Critical5: enable this
            //return Yii::app()->themeManager->baseUrl . static::GENERIC_THUMBNAIL_PATH;
        }
    }
?>