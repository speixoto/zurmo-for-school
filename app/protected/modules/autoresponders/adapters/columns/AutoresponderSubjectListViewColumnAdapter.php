<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class AutoresponderSubjectListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            $className  = get_class($this);
            $value      = $className . '::resolveSubjectAndMetricsSummary($data, "' . $this->view->redirectUrl . '")';
            return array(
                'name'  => 'Name',
                'value' => $value,
                'type'  => 'raw',
            );
        }

        public static function resolveSubjectAndMetricsSummary(Autoresponder $autoresponder, $redirectUrl)
        {
            $content  = static::resolveSubjectWithRedirectURl($autoresponder->subject, $autoresponder->id, $redirectUrl);
            $content .= static::renderMetricsContent($autoresponder);
            return $content;
        }

        public static function resolveSubjectWithRedirectURl($subject, $id, $redirectUrl)
        {
            $url = Yii::app()->createUrl('/autoresponders/default/edit',
                                                                array('id' => $id, 'redirectUrl' => $redirectUrl));
            return ZurmoHtml::link($subject, $url);
        }

        protected static function renderMetricsContent(Autoresponder $autoresponder)
        {
            //todo: finish
            $sentQuantity   = 50000; //todo: format for integer
            $openQuantity   = 10000;
            $openRate       = $openQuantity / $sentQuantity; //todo: resolve if 0 so we aren't dividing by zero, and round
            $clickQuantity  = 1000;
            $clickRate      = $clickQuantity / $sentQuantity; //todo: resolve if 0 so we aren't dividing by zero, and round
            $optOutQuantity = 100;
            $optOutRate     = $optOutQuantity / $sentQuantity; //todo: resolve if 0 so we aren't dividing by zero, and round


            $content = null;
            $content .= ZurmoHtml::tag('div', array(), Zurmo::t('MarketingModule', '{quantity} sent',
                                        array('{quantity}' => $sentQuantity)));
            $content .= ZurmoHtml::tag('div', array(), Zurmo::t('MarketingModule', '{quantity} opens ({openRate}%)',
                                        array('{quantity}' => $openQuantity, '{openRate}' => $openRate)));
            $content .= ZurmoHtml::tag('div', array(), Zurmo::t('MarketingModule', '{quantity} unique clicks ({clickRate}%)',
                                        array('{quantity}' => $clickQuantity, '{clickRate}' => $clickRate)));
            $content .= ZurmoHtml::tag('div', array(), Zurmo::t('MarketingModule', '{quantity} Opt-outs ({optOutRate}%)',
                                        array('{quantity}' => $optOutQuantity, '{optOutRate}' => $optOutRate)));
            return $content;
        }
    }
?>