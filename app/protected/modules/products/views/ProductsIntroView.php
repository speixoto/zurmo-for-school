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

    /**
     * View when a user first comes to the marketing dashboard. Provides an overview of how marketing works
     */
    class ProductsIntroView extends View
    {
        const PANEL_ID            = 'product-intro-content';

        const LINK_ID             = 'hide-product-intro';

        const HIDDEN_COOKIE_VALUE = 'hidden';

        /**
         * @var string
         */
        protected $cookieValue;

        protected $activeActionElementType;

        /**
         * @return string
         */
        public static function resolveCookieId()
        {
            return self::PANEL_ID . '-panel';
        }

        public function __construct($cookieValue, $activeActionElementType)
        {
            assert('$cookieValue == null || is_string($cookieValue)');
            $this->cookieValue             = $cookieValue;
            $this->activeActionElementType = $activeActionElementType;
        }

        /**
         * @return bool|string
         */
        protected function renderContent()
        {
            $this->registerScripts();
            if ($this->cookieValue == self::HIDDEN_COOKIE_VALUE)
            {
                $style = "style=display:none;"; // Not Coding Standard
            }
            else
            {
                $style = null;
            }
            $currentClass = $this->resolveSectionName();
            $content  = '<div id="' . self::PANEL_ID . '" class="module-intro-content ' . $currentClass . '" ' . $style . '>';
            $content .= '<h1>' . Zurmo::t('ProductsModule', 'How do Products work in Zurmo?', LabelUtil::getTranslationParamsForAllModules()). '</h1>';
            $content .= '<div id="products-intro-steps" class="module-intro-steps clearfix">';
            $content .= '<div class="third catalog-description"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ProductsModule', 'Catalog') . '</strong>';
            $content .= Zurmo::t('ProductsModule', 'A <em>Catalog</em> is a collection of "things" your business offer.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third catalog-item-description"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ProductsModule', 'Catalog Item') . '</strong>';
            $content .= Zurmo::t('ProductsModule', 'A <em>Catalog Item</em> is a "blueprint" for what you offer. ' .
                                        'It is described using:<br>' .
                                        '· Attributes (megapixels)<br>' .
                                        '· Categories (Photography)<br>' .
                                        '· Price etc. ($299.0)');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third product-description"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ProductsModule', 'Product') . '</strong>';
            $content .= Zurmo::t('ProductsModule', 'A <em>Product</em> is one (or more) catalog items assigned to a ' .
                                                   'accounts/leads/opps and usually contains quantity/price and specific ' .
                                                   'data related only to this order.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '</div>';

            $content .= '<div class="module-intro-examples"><h3>Lets see a real world example..</h3>
                            <ol>
                                <li>Zurmo has a <strong>Catalog</strong> of software, they offer on-demand and on-premise software.</li>
                                <li>Zurmo Group is a <strong>Catalog Item</strong> that can be purchased, it includes features X+Y+Z.</li>
                                <li>When ABC Company purchases Zurmo Group — they now have a Zurmo Group <strong>Product</strong> with 5 users that expires on date X</li>
                            </ol></div>';

            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            return $content;
        }

        protected function resolveSectionName()
        {
            $sectionName = str_replace('link', '', strtolower($this->activeActionElementType));
            return $sectionName;
        }

        /**
         * @return string
         */
        protected function renderHideLinkContent()
        {
            $label    = '<span></span>' . Zurmo::t('Core', 'Dismiss');
            $content  = '<div class="hide-module-intro ' . self::LINK_ID . '">'.ZurmoHtml::link($label, '#');
            $content .= '</div>';
            return $content;
        }

        protected function registerScripts()
        {
            $script = "$('." . self::LINK_ID . "').click(function()
            {
                        $('#" . self::PANEL_ID . "').slideToggle();
                        document.cookie = '" . self::resolveCookieId() . "=" . static::HIDDEN_COOKIE_VALUE . "';
                        $('#" . self::PANEL_ID . "-checkbox-id').attr('checked', false).parent().removeClass('c_on');
                        return false;
            })";
            Yii::app()->clientScript->registerScript(self::LINK_ID, $script);
        }
    }
?>
