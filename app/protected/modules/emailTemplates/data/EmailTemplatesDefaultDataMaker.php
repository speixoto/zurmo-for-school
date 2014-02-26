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

    class EmailTemplatesDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $this->makeBuilderEmailTemplate('Template with styles', $this->resolveSerializedDataForDummyTemplateWithStyles());
            $this->makeBuilderEmailTemplate('Template without styles', $this->resolveSerializedDataForDummyTemplateWithoutStyles());
        }

        protected function makeBuilderEmailTemplate($name, $serializedData, $subject = null, $modelClassName = null, $language = null, $type = null, $isDraft = 0, $textContent = null, $htmlContent = null)
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = $type;//EmailTemplate::TYPE_WORKFLOW;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE;
            $emailTemplate->isDraft         = $isDraft;
            $emailTemplate->modelClassName  = $modelClassName;
            $emailTemplate->name            = $name;
            if (empty($subject))
            {
                $subject                    = $name;
            }
            $emailTemplate->subject         = $subject;
            if (!isset($language))
            {
                $language           = Yii::app()->languageHelper-> getForCurrentUser();
            }
            $emailTemplate->language        = $language;
            $emailTemplate->htmlContent     = $htmlContent;
            $emailTemplate->textContent     = $textContent;
            $emailTemplate->serializedData  = CJSON::encode($serializedData);
            $saved      = $emailTemplate->save(false);
            assert('$saved');
        }

        protected function resolveSerializedDataForDummyTemplateWithStyles()
        {
            return array(
                'baseTemplateId'    => null,
                // TODO: @Shoaibi/@Amit: Critical: thumbnails
                'thumbnailUrl'      => 'some-thumbnail-url-here',
                'dom'               => array(
                    'canvas1'     => array(
                        'class'       => 'BuilderCanvasElement',
                        'properties'  => array('frontend' => array('inlineStyles' => array('background-color' =>  '#aaaaaa', 'color' => '#ff0000', 'font-size' => '18px'))),
                        'content'     => array(
                            'row1'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 1), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#0fff00', 'font-size' => '16px'))),
                                'content'       => array(
                                    'row1column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row1column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 1 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row1column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' =>  '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 1 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row2'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 2), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#fff000', 'font-size' => '16px'))),
                                'content'       => array(
                                    'row2column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row2column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 2 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row2column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 2 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row2column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row2column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 2 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row2column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 2 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row3'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 3), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#0fff00', 'font-size' => '16px'))),
                                'content'       => array(
                                    'row3column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row3column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row3column3'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column3text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 3 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column3text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 3 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row4'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 4), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#0fff00', 'font-size' => '16px'))),
                                'content'       => array(
                                    'row4column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column3'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column3text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 3 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column3text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 3 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column4'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column4text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 4 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column4text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 4 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row5'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => '1:2'), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#0fff00', 'font-size' => '16px'))),
                                'content'       => array(
                                    'row5column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row5column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 5 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row5column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 5 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row5column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row5column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 5 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row5column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 5 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row6'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => '2:1'), 'frontend' => array('inlineStyles' => array('background-color' =>  '#dddddd', 'color' => '#0fff00', 'font-size' => '14px'))),
                                'content'       => array(
                                    'row6column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row6column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 6 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row6column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 6 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row6column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row6column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 6 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row6column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array('frontend' => array('inlineStyles'=> array('background-color' => '#555555','color' => '#0000ff', 'font-size' => '10px'))),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 6 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }

        protected function resolveSerializedDataForDummyTemplateWithoutStyles()
        {
            return array(
                'baseTemplateId'    => null,
                // TODO: @Shoaibi/@Amit: Critical: thumbnails
                'thumbnailUrl'      => 'some-thumbnail-url-here',
                'dom'               => array(
                    'canvas1'     => array(
                        'class'       => 'BuilderCanvasElement',
                        'properties'  => array(),
                        'content'     => array(
                            'row1'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 1)),
                                'content'       => array(
                                    'row1column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row1column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 1 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row1column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 1 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row2'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 2)),
                                'content'       => array(
                                    'row2column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row2column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 2 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row2column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 2 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row2column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row2column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 2 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row2column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 2 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row3'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 3)),
                                'content'       => array(
                                    'row3column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row3column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row3column3'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row3column3text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 3 Col 3 Text 1</strong>',
                                                ),
                                            ),
                                            'row3column3text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 3 Col 3 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row4'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => 4)),
                                'content'       => array(
                                    'row4column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column3'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column3text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 3 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column3text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 3 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row4column4'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row4column4text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 4 Col 4 Text 1</strong>',
                                                ),
                                            ),
                                            'row4column4text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 4 Col 4 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row5'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => '1:2')),
                                'content'       => array(
                                    'row5column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row5column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 5 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row5column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 5 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row5column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row5column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 5 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row5column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 5 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'row6'    => array(
                                'class'         => 'BuilderRowElement',
                                'properties'    =>  array('backend' => array('configuration' => '2:1')),
                                'content'       => array(
                                    'row6column1'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row6column1text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 6 Col 1 Text 1</strong>',
                                                ),
                                            ),
                                            'row6column1text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 6 Col 1 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'row6column2'   => array(
                                        'class'         => 'BuilderColumnElement',
                                        'properties'    => array(),
                                        'content'       => array(
                                            'row6column2text1'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<strong>Row 6 Col 2 Text 1</strong>',
                                                ),
                                            ),
                                            'row6column2text2'     => array(
                                                'class'         => 'BuilderTextElement',
                                                'properties'    => array(),
                                                'content'       => array(
                                                    'text'      => '<ul><li>Row 6 Col 2 Text 2</li></ul>',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }
    }
?>