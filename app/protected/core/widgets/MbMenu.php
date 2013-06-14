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

    Yii::import('zii.widgets.CMenu');

    /**
     * MbMenu class file.
     *
     * @author Mark van den Broek (mark@heyhoo.nl)
     * @copyright Copyright &copy; 2010 HeyHoo
     *
     */
    class MbMenu extends CMenu
    {
        private $baseUrl;

        protected $themeUrl;

        protected $theme;

        protected $cssFile;

        protected $cssIeStylesFile = null;

        private $nljs;

        public $activateParents    = true;

        public $navContainerClass  = 'nav-container';

        public $navBarClass        = 'nav-bar';

        public $labelPrefix        = null;

        public $linkPrefix         = null;

        /**
         * The javascript needed.
         */
        protected function createJsCode()
        {
            $js  = '';
            $js .= '  $(".nav li").hover('                   . $this->nljs;
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      if ($(this).hasClass("parent")) {' . $this->nljs; // Not Coding Standard
            $js .= '        $(this).addClass("over");'       . $this->nljs;
            $js .= '      }'                                 . $this->nljs;
            $js .= '    },'                                  . $this->nljs; // Not Coding Standard
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      $(this).removeClass("over");'      . $this->nljs;
            $js .= '    }'                                   . $this->nljs;
            $js .= '  );'                                    . $this->nljs;
            return $js;
        }

        /**
        * Give the last items css 'last' style.
        */
        protected function cssLastItems($items)
        {
            $i = max(array_keys($items));
            $item = $items[$i];
            if (isset($item['itemOptions']['class']))
            {
                $items[$i]['itemOptions']['class'] .= ' last';
            }
            else
            {
                $items[$i]['itemOptions']['class'] = 'last';
            }
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->cssLastItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Give the last items css 'parent' style.
        */
        protected function cssParentItems($items)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' parent';
                    }
                    else
                    {
                    $items[$i]['itemOptions']['class'] = 'parent';
                    }
                    $items[$i]['items'] = $this->cssParentItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Initialize the widget.
        */
        public function init()
        {
            if (!$this->getId(false))
            {
                $this->setId('nav');
            }
            $this->themeUrl = Yii::app()->themeManager->baseUrl;
            $this->theme = Yii::app()->theme->name;
            $this->nljs = "\n";
            $this->items = $this->cssParentItems($this->items);
            $this->items = $this->cssLastItems($this->items);
            $route = $this->getController()->getRoute();
            $hasActiveChild = null;
            $this->items = $this->normalizeItems(
                $this->items,
                $this->getController()->getRoute(),
                $hasActiveChild
            );
            $this->resolveNavigationClass();
        }

        /**
        * Registers the external javascript files.
        */
        public function registerClientScripts()
        {
            // add the script
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $js = $this->createJsCode();
            $cs->registerScript('mbmenu_' . $this->getId(), $js, CClientScript::POS_READY);
        }

        public function registerCssFile()
        {
            $cs = Yii::app()->getClientScript();
            if ($this->cssFile != null)
            {
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssFile, 'screen');
            }
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 8 && $this->cssIeStylesFile != null)
            {
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssIeStylesFile, 'screen');
            }
        }

        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
                echo ZurmoHtml::openTag('li', isset($item['itemOptions']) ? $item['itemOptions'] : array());
                if (isset($item['linkOptions']))
                {
                     $htmlOptions = $item['linkOptions'];
                }
                else
                {
                    $htmlOptions = array();
                }
                $resolvedLabelContent = $this->renderLabelPrefix() . '<span>' . $item['label'] .
                                        static::resolveAndGetSpanAndDynamicLabelContent($item) . '</span>';
                if ((isset($item['ajaxLinkOptions'])))
                {
                    echo ZurmoHtml::ajaxLink($resolvedLabelContent, $item['url'], $item['ajaxLinkOptions'], $htmlOptions);
                }
                elseif (isset($item['url']))
                {
                    echo ZurmoHtml::link($this->renderLinkPrefix() . $resolvedLabelContent, $item['url'], $htmlOptions);
                }
                else
                {
                    echo ZurmoHtml::link($resolvedLabelContent, "javascript:void(0);", $htmlOptions);
                }
                if (isset($item['items']) && count($item['items']))
                {
                    echo "\n" . ZurmoHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                    $this->renderMenuRecursive($item['items']);
                    echo ZurmoHtml::closeTag('ul') . "\n";
                }
                echo ZurmoHtml::closeTag('li') . "\n";
            }
        }

        protected static function resolveAndGetSpanAndDynamicLabelContent($item)
        {
            if (isset($item['dynamicLabelContent']))
            {
                return ZurmoHtml::tag('span', array(), $item['dynamicLabelContent']);
            }
        }

        protected function resolveNavigationClass()
        {
            if (isset($this->htmlOptions['class']))
            {
                $this->htmlOptions['class'] .= ' nav';
            }
            else
            {
                $this->htmlOptions['class'] = 'nav';
            }
        }

        protected function normalizeItems($items, $route, &$active, $ischild = 0)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['visible']) && !$item['visible'])
                {
                    unset($items[$i]);
                    continue;
                }
                if ($this->encodeLabel)
                {
                    $items[$i]['label'] = Yii::app()->format->text($item['label']);
                }
                $hasActiveChild = false;
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->normalizeItems($item['items'], $route, $hasActiveChild, 1);
                    if (empty($items[$i]['items']) && $this->hideEmptyItems)
                    {
                        unset($items[$i]['items']);
                    }
                }
                if (!isset($item['active']))
                {
                    if (($this->activateParents && $hasActiveChild) || $this->isItemActive($item, $route))
                    {
                        $active = $items[$i]['active'] = true;
                    }
                    else
                    {
                        $items[$i]['active'] = false;
                    }
                }
                elseif ($item['active'])
                {
                    $active = true;
                }
                if ($items[$i]['active'] && $this->activeCssClass != '' && !$ischild)
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' ' . $this->activeCssClass;
                    }
                    else
                    {
                        $items[$i]['itemOptions']['class'] = $this->activeCssClass;
                    }
                }
            }
            return array_values($items);
        }

        protected function renderLabelPrefix()
        {
            if ($this->labelPrefix)
            {
                return ZurmoHtml::tag($this->labelPrefix, array(), '');
            }
        }

        protected function renderLinkPrefix()
        {
            if ($this->linkPrefix)
            {
                return ZurmoHtml::tag($this->linkPrefix, array(), '');
            }
        }

        /**
        * Run the widget.
        */
        public function run()
        {
            $this->registerClientScripts();
            $this->registerCssFile();
            parent::run();
        }
    }
?>
