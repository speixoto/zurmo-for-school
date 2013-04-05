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

    class SelectContactOrReportElement extends Element
    {
        const SELECT_CONTACT_SEARCH_BOX_ID          = 'select-contact-or-lead-search-box';

        const SELECT_REPORT_SEARCH_BOX_ID           = 'select-report-search-box';

        const SELECT_CONTACT_OR_REPORT_RADIO_ID     = 'select-contact-or-report-radio';

        public function getActionType()
        {
            return 'Details';
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('Default', 'Select');
        }

        protected function getDefaultRoute()
        {
            return null;
        }

        public function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        public function renderControlEditable()
        {
            $selectRadioParams              = CMap::mergeArray($this->params,
                                                        array('selectContactSearchBoxId' => static::SELECT_CONTACT_SEARCH_BOX_ID,
                                                            'selectReportSearchBoxId' => static::SELECT_REPORT_SEARCH_BOX_ID));
            $selectContactOrReportParams    = CMap::mergeArray($this->params,
                                                        array('radioButtonClass' => 'selectContactOrReportRadioButton'));
            $content                        = $this->renderSelectContactOrReportRadioButton($selectRadioParams);;
            $content                        .= $this->renderSelectContactOrLeadSearchBox($selectContactOrReportParams);
            $content                        .= $this->renderSelectReportSearchBox($selectContactOrReportParams);
            return $content;
        }

        protected function renderSelectContactOrLeadSearchBox($params)
        {
            $selectContact = new MarketingListMemberSelectContactOrLeadAutoCompleteElement($this->model,
                                                                                            'selectContactOrLeadSearchBox',
                                                                                            $this->form,
                                                                                            $params);
            return ZurmoHtml::tag('div', array('id' => static::SELECT_CONTACT_SEARCH_BOX_ID),
                                                                        $selectContact->render()
                                                                    );
        }

        protected function renderSelectReportSearchBox($params)
        {
            $selectReport = new MarketingListMemberSelectReportAutoCompleteElement($this->model,
                                                                                    'selectReportSearchBox',
                                                                                    $this->form,
                                                                                    $params);
            return ZurmoHtml::tag('div', array('id' => static::SELECT_REPORT_SEARCH_BOX_ID),
                                                                        $selectReport->render()
                                                                    );
        }

        protected function renderSelectContactOrReportRadioButton($params)
        {
            $selectRadio = new MarketingListMemberSelectRadioElement($this->model,
                                                                        'selectContactOrReportRadioButton',
                                                                        $this->form,
                                                                        $params);
            return ZurmoHtml::tag('div', array('id' => static::SELECT_CONTACT_OR_REPORT_RADIO_ID),
                                                                        $selectRadio->render());
        }
    }
?>
