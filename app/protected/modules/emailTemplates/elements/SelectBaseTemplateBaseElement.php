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
        const GENERIC_THUMBNAIL_PATH = '/default/images/zurmo-zapier.png';

        public $editableTemplate = '{content}';

        abstract protected function resolveBaseTemplates();

        abstract protected function resolveThumbnailByModel(EmailTemplate $template);

        protected function renderControlEditable()
        {
            $content = null;
            $data    = $this->resolveData();
            if (!empty($data))
            {
                $content .= $this->form->radioButtonList(
                    $this->model,
                    $this->attribute,
                    $data,
                    $this->getEditableHtmlOptions()
                );
            }
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions              = array();
            $htmlOptions['id']        = $this->getEditableInputId($this->attribute);
            $htmlOptions['separator'] = '';
            $htmlOptions['template']  = ZurmoHtml::tag('li',
                                                        array('class' => 'base-template-selection'),
                                                        '{input}{label}');
            if (isset($this->params['inputPrefix']) && $this->params['inputPrefix'])
            {
                $htmlOptions['dataInputPrefix'] = $this->params['inputPrefix'];
            }
            return $htmlOptions;
        }

        protected function resolveData()
        {
            $templates  = $this->resolveBaseTemplates();
            $data       = array();
            foreach ($templates as $template)
            {
                //there are 1->5 template icon types
                $thumbnail           = $this->resolveThumbnail($template);
                $label               = ZurmoHtml::tag('h4', array('class' => 'name'),  $template->name);
                $data[$template->id] = $thumbnail . $label;
            }
            return $data;
        }

        protected function resolveThumbnail(EmailTemplate $template)
        {
            $thumbnail  = $this->resolveThumbnailByModel($template);
            if (empty($thumbnail))
            {
                $thumbnail  = $this->resolveGenericThumbnail();
            }
            return $thumbnail;
        }

        protected function resolveGenericThumbnail()
        {
            return ZurmoHtml::image($this->resolveGenericThumbnailUrl());
        }

        protected function resolveGenericThumbnailUrl()
        {
            return Yii::app()->themeManager->baseUrl . static::GENERIC_THUMBNAIL_PATH;
        }
    }
?>