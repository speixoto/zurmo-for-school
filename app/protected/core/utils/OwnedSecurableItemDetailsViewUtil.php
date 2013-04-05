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
     * Helper functionality for rendering renderAfterFormLayoutForDetailsContent() for ownedsecurableitems
     */
    class OwnedSecurableItemDetailsViewUtil
    {
        public static function renderAfterFormLayoutForDetailsContent($model, $content = null)
        {
            $detailsContent = null;
            if ($model instanceof OwnedSecurableItem)
            {
                if ($content != null)
                {
                    $detailsContent .= ZurmoHtml::tag('br');
                }
                $elements           = array(
                                        array('className' => 'DateTimeModifiedUserElement',
                                               'parameters' => array($model, 'null'),
                                        ),
                                        array('className' => 'DateTimeCreatedUserElement',
                                            'parameters' => array($model, 'null'),
                                        ),
                                        array('className' => 'UserElement',
                                            'parameters' => array($model, 'owner'),
                                        ),
                                        array('className' => 'DerivedExplicitReadWriteModelPermissionsElement',
                                            'parameters' => array($model, 'null'),
                                        ),
                                    );
                $detailsContent     .= static::renderElementsContent($elements);
            }
            $content .= ZurmoHtml::tag('p', array('class' =>'after-form-details-content'), $detailsContent);
            return $content;
        }

        protected static function renderElementsContent($elements)
        {
            $content                = null;
            $elementsCount = count($elements);
            foreach($elements as $index => $elementDetails)
            {
                $elementClassName   = $elementDetails['className'];
                $elementParams      = $elementDetails['parameters'];
                $element            = new $elementClassName($elementParams[0], $elementParams[1]);
                $element->nonEditableTemplate = '{label} {content}';
                $content            .= $element->render();
                $isLast             = (($index +1) == $elementsCount);
                if (!$isLast)
                {
                    $content .= '&#160;|&#160;';
                }
            }
            return $content;
        }
    }
?>