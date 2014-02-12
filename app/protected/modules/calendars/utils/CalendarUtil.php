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

    class CalendarUtil
    {
        /**
         * Make calendar items by model.
         * @param RedBeanModel $model
         * @param SavedCalendar $savedCalendar
         * @return CalendarItem
         */
        public static function makeCalendarItemByModel(RedBeanModel $model, SavedCalendar $savedCalendar)
        {
            $calendarItem   = new CalendarItem();
            $startAttribute = $savedCalendar->startAttributeName;
            $endAttribute   = $savedCalendar->endAttributeName;
            $calendarItem->setTitle($model->name);
            $calendarItem->setStartDateTime($model->$startAttribute);
            if ($endAttribute != null)
            {
                $calendarItem->setEndDateTime($model->$endAttribute);
            }
            $calendarItem->setCalendarId($savedCalendar->id);
            $calendarItem->setModelClass(get_class($model));
            $calendarItem->setModelId($model->id);
            $calendarItem->setColor($savedCalendar->color);
            $calendarItem->setModuleClassName($savedCalendar->moduleClassName);
            return $calendarItem;
        }

        /**
         * Gets date range type.
         * @return string
         */
        public static function getDateRangeType()
        {
            return SavedCalendar::DATERANGE_TYPE_MONTH;
        }

        /**
         * Gets start date.
         * @param string $dateRangeType
         * @return string
         */
        public static function getStartDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                return DateTimeUtil::getFirstDayOfAMonthDate();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                return DateTimeUtil::getFirstDayOfAWeek();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTodaysDate();
            }
        }

        /**
         * Gets end date.
         * @param string $dateRangeType
         * @return string
         */
        public static function getEndDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                $dateTime = new DateTime();
                $dateTime->modify('first day of next month');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                $dateTime       = new DateTime('Monday next week');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTomorrowsDate();
            }
        }

        /**
         * Get saved calendars for user.
         * @param User $user
         * @return array
         */
        public static function getUserSavedCalendars(User $user)
        {
            $metadata = array();
            $metadata['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                )
            );
            $metadata['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('SavedCalendar');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendar', $metadata, $joinTablesAdapter);
            return SavedCalendar::getSubset($joinTablesAdapter, null, null, $where);
        }

        /**
         * Process user calendars and get data provider.
         * @param mixed $myCalendarIds
         * @return CalendarItemsDataProvider
         */
        public static function processUserCalendarsAndMakeDataProviderForCombinedView($myCalendarIds = null,
                                                                                      $mySubscribedCalendarIds = null,
                                                                                      $dateRangeType = null,
                                                                                      $startDate = null,
                                                                                      $endDate = null)
        {
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser(Yii::app()->user->userModel,
                                                                                 $myCalendarIds,
                                                                                 $mySubscribedCalendarIds);
            if($dateRangeType == null)
            {
                $dateRangeType  = CalendarUtil::getDateRangeType();
                ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarDateRangeType', $dateRangeType);
            }
            if($startDate == null)
            {
                $startDate      = CalendarUtil::getStartDate($dateRangeType);
                ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarStartDate', $startDate);
            }
            if($endDate == null)
            {
                $endDate        = CalendarUtil::getEndDate($dateRangeType);
                ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarEndDate', $endDate);
            }
            return CalendarItemsDataProviderFactory::getDataProviderByDateRangeType($savedCalendarSubscriptions,
                                                                                                $startDate, $endDate, $dateRangeType);
        }

        /**
         * Get full calendar items.
         * @param CalendarItemsDataProvider $dataProvider
         * @return array
         */
        public static function getFullCalendarItems(CalendarItemsDataProvider $dataProvider)
        {
            $calendarItems = $dataProvider->getData();
            $fullCalendarItems = array();
            for($k = 0; $k < count($calendarItems); $k++)
            {
                $fullCalendarItem = array();
                $calItem = $calendarItems[$k];
                $fullCalendarItem['title'] = $calItem->getTitle();
                $fullCalendarItem['start'] = self::getFullCalendarFormattedDateTimeElement($calItem->getStartDateTime());
                if($calItem->getEndDateTime() != null)
                {
                    $fullCalendarItem['end'] = self::getFullCalendarFormattedDateTimeElement($calItem->getEndDateTime());
                }
                $fullCalendarItem['color'] = $calItem->getColor();
                $modelClass                = $calItem->getModelClass();
                $model                     = $modelClass::getById($calItem->getModelId());
//                if($model instanceof CalendarItemInterface)
//                {
//                    $fullCalendarItem['description'] = self::renderFullCalendarItems($model);
//                }
//                else
//                {
                    $fullCalendarItem['description'] = '';
                //}
                $fullCalendarItems[] = $fullCalendarItem;
            }
            return $fullCalendarItems;
        }

        /**
         * Gets full calendar formatted date time.
         * @param string $dateTime
         * @return string formatted in datetime format required for full calendar widget
         */
        public static function getFullCalendarFormattedDateTimeElement($dateTime)
        {
            assert('is_string($dateTime)');
            $dateTimeObject = new DateTime($dateTime);
            return Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm',
                        $dateTimeObject->getTimestamp());
        }

        /**
         * Gets used color by user.
         *
         * @param User $user
         * @return array
         */
        public static function getUsedCalendarColorsByUser(User $user, $modelClassName, $attributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            $selectDistinct            = false;
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $selectQueryAdapter->addClause($modelClassName::getTableName(), 'color');
            $metadata                  = array();
            $metadata['clauses']       = array(
                                                    1 => array(
                                                        'attributeName'        => $attributeName,
                                                        'relatedAttributeName' => 'id',
                                                        'operatorType'         => 'equals',
                                                        'value'                => $user->id,
                                                    )
                                                );
            $metadata['structure'] = '1';
            $where   = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            $sql     = SQLQueryUtil::makeQuery($modelClassName::getTableName(), $selectQueryAdapter, $joinTablesAdapter, null, null, $where);
            $records = ZurmoRedBean::getAll($sql);
            $colors  = array();
            foreach($records as $record)
            {
                if($record['color'] != null && $record['color'] != '')
                {
                    $colors[] = $record['color'];
                }
            }
            return $colors;
        }

        /**
         * @return string
         */
        public static function getModalContainerId()
        {
            return ModalContainerView::ID;
        }

        /**
         * @return array
         */
        public static function resolveAjaxOptionsForModalView()
        {
            $title = Zurmo::t('Calendarsmodule', 'Shared Calendars');
            return   ModalView::getAjaxOptionsForModalLink($title, self::getModalContainerId(), 'auto', 600,
                     'center top+25', $class = "''");
        }

        /**
         * Get task modal script
         * @param string $url
         * @param string $selector
         * @return string
         */
        public static function registerSharedCalendarModalScript($url, $selector)
        {
            assert('is_string($url)');
            assert('is_string($selector)');
            $modalId     = CalendarUtil::getModalContainerId();
            $ajaxOptions = CalendarUtil::resolveAjaxOptionsForModalView();
            $ajaxOptions['beforeSend'] = new CJavaScriptExpression($ajaxOptions['beforeSend']);
            return "$(document).on('click', '{$selector}', function()
                         {
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}',
                                'beforeSend' : {$ajaxOptions['beforeSend']},
                                'update'     : '{$ajaxOptions['update']}',
                                'success': function(html){jQuery('#{$modalId}').html(html)}
                            });
                          }
                        );";
        }

        /**
         * Get the items by user
         * @param User $user
         * @return integer
         */
        public static function getUserSubscribedCalendars(User $user)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'user',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($user->id),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter('SavedCalendarSubscription');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendarSubscription', $searchAttributeData, $joinTablesAdapter);
            $models = SavedCalendarSubscription::getSubset($joinTablesAdapter, null, null, $where, null);
            return $models;
        }

        /**
         * Make calendar items list.
         * @param array $data
         * @param string $field
         * @param string $itemClass
         * @param string $type
         * @return string
         */
        public static function makeCalendarItemsList($data, $field, $itemClass, $type)
        {
            assert('is_array($data)');
            assert('is_string($field)');
            assert('is_string($itemClass)');
            assert('is_string($type)');
            $itemsContent = null;
            foreach($data as $calendarArray)
            {
                $isChecked = false;
                if($calendarArray[1] === true)
                {
                    $isChecked = true;
                }
                $input          = ZurmoHtml::checkBox($field,
                                                      $isChecked,
                                                      array('value' => $calendarArray[0]->id,
                                                            'class' => $itemClass));
                $color          = ZurmoHtml::tag('span', array('class' => 'cal-color', 'style' => 'background:' .
                                                                                                        $calendarArray[0]->color), '');
                if($type == 'saved')
                {
                    $label          = $calendarArray[0]->name;
                    $options        = self::getSavedCalendarOptions($calendarArray[0]->id);
                    $subscriptionData = null;
                }
                else
                {
                    $savedCalendar    = $calendarArray[0]->savedcalendar;
                    $label            = $savedCalendar->name;
                    $options          = self::getSharedCalendarOptions($calendarArray[0]->id);
                    $subscriptionData = CalendarUtil::getCalendarSubscriberData($calendarArray[0]->savedcalendar);
                }
                $label = ZurmoHtml::tag('strong', array('class' => 'cal-name'), $label);
                $itemsContent   .= ZurmoHtml::tag('li', array(), $input . $color . $label . $subscriptionData . $options);
            }
            return ZurmoHtml::tag('ul', array('class' => 'calendars-list'), $itemsContent);
        }

        /**
         * Get shared calendar options.
         * @param int $savedCalendarSubscriptionId
         * @return string
         */
        public static function getSharedCalendarOptions($savedCalendarSubscriptionId)
        {
            assert('is_int($savedCalendarSubscriptionId)');
            $elementContent = ZurmoHtml::tag('li', array(),
                                            ZurmoHtml::link(ZurmoHtml::tag('span', array(), Zurmo::t('CalendarsModule', 'Unsubscribe')), '#',
                                                    array('data-value'  => $savedCalendarSubscriptionId,
                                                          'class'       => 'shared-cal-unsubscribe')));
            $elementContent = ZurmoHtml::tag('ul', array(), $elementContent);
            $content        = ZurmoHtml::tag('li', array('class' => 'parent last'),
                                                   ZurmoHtml::link('<span></span>', 'javascript:void(0);') . $elementContent);
            $content        = ZurmoHtml::tag('ul', array('class' => 'options-menu edit-row-menu nav'), $content);
            return $content;
        }

        /**
         * Get saved calendar options.
         * @param int $calendarId
         * @return string
         */
        public static function getSavedCalendarOptions($calendarId)
        {
            assert('is_int($calendarId)');
            $editUrl         = Yii::app()->createUrl('calendars/default/edit', array('id' => $calendarId));
            $editLinkContent = ZurmoHtml::tag('li', array(),
                                            ZurmoHtml::link(ZurmoHtml::tag('span', array(), Zurmo::t('Core', 'Edit')), $editUrl,
                                                    array('data-value'  => $calendarId,
                                                          'class'       => 'my-cal-edit')));
            $deleteLinkContent = ZurmoHtml::tag('li', array(),
                                            ZurmoHtml::link(ZurmoHtml::tag('span', array(), Zurmo::t('Core', 'Delete')), '#',
                                                    array('data-value'  => $calendarId,
                                                          'class'       => 'my-cal-delete')));
            $elementContent = ZurmoHtml::tag('ul', array(), $editLinkContent . $deleteLinkContent);
            $content        = ZurmoHtml::tag('li', array('class' => 'parent last'),
                                                   ZurmoHtml::link('<span></span>', 'javascript:void(0);') . $elementContent);
            $content        = ZurmoHtml::tag('ul', array('class' => 'options-menu edit-row-menu nav'), $content);
            return $content;
        }

        /**
         * Get calendar items data provider.
         * @return CalendarItemsDataProvider
         */
        public static function getCalendarItemsDataProvider()
        {
            $mySavedCalendarIds         = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarSelections');
            $mySubscribedCalendarIds    = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'mySubscribedCalendarSelections');
            $dateRangeType              = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarDateRangeType');
            $startDate                  = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarStartDate');
            $endDate                    = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarEndDate');
            return CalendarUtil::processUserCalendarsAndMakeDataProviderForCombinedView($mySavedCalendarIds,
                                                                                        $mySubscribedCalendarIds,
                                                                                        $dateRangeType,
                                                                                        $startDate,
                                                                                        $endDate);
        }

        /**
         * Get users subscribed for calendar.
         * @param SavedCalendar $subscribedCalendar
         * @return array
         */
        public static function getUsersSubscribedForCalendar(SavedCalendar $subscribedCalendar)
        {
            $searchAttributeData = array();
            $users               = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'savedcalendar',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($subscribedCalendar->id),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter('SavedCalendarSubscription');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendarSubscription', $searchAttributeData, $joinTablesAdapter);
            $models = SavedCalendarSubscription::getSubset($joinTablesAdapter, null, null, $where, null);
            foreach($models as $model)
            {
                $users[] = $model->user;
            }
            return $users;
        }

        /**
         * Get shared calendar subscriber data
         * @param Task $task
         * @return string
         */
        public static function getCalendarSubscriberData(SavedCalendar $subscribedCalendar)
        {
            $users    = CalendarUtil::getUsersSubscribedForCalendar($subscribedCalendar);
            $content  = null;
            $alreadySubscribedUsers = array();
            foreach ($users as $user)
            {
                //Take care of duplicates if any
                if (!in_array($user->id, $alreadySubscribedUsers))
                {
                    $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 25);
                    $alreadySubscribedUsers[] = $user->id;
                }
            }

            return $content;
        }

        /**
         * Register script whick would be invoked on click of any calendar item in my calendars or shared calendars
         */
        public static function registerSelectCalendarScript($startDate, $endDate)
        {
            //refer to http://stackoverflow.com/questions/9801095/jquery-fullcalendar-send-custom-parameter-and-refresh-calendar-with-json
            $url    = Yii::app()->createUrl('calendars/default/getEvents');
            $script = "$(document).on('click', '.mycalendar,.sharedcalendar',
                                        function(){
                                                    $('#calendar').fullCalendar('removeEventSource', getCalendarEvents('{$url}', 'calendar'));
                                                    $('#calendar').fullCalendar('addEventSource', getCalendarEvents('{$url}', 'calendar'));
                                                   }
                                     );";
            $cs = Yii::app()->getClientScript();
            $cs->registerScript('mycalendarselectscript', $script);
        }

        /**
         * Get already used colors by user.
         * @param User $user
         * @return array
         */
        public static function getAlreadyUsedColorsByUser(User $user)
        {
            $savedCalUsedColors      = CalendarUtil::getUsedCalendarColorsByUser($user, 'SavedCalendar', 'createdByUser');
            $sharedCalUsedColors     = CalendarUtil::getUsedCalendarColorsByUser($user, 'SavedCalendarSubscription', 'user');
            return CMap::mergeArray($savedCalUsedColors, $sharedCalUsedColors);
        }

        /**
         * Sets my calendar color.
         * @param SavedCalendar $savedCalendar
         */
        public static function setMyCalendarColor(SavedCalendar $savedCalendar)
        {
            if($savedCalendar->color == null)
            {
                $usedColors      = CalendarUtil::getAlreadyUsedColorsByUser(Yii::app()->user->userModel);
                self::processAndSaveColor($savedCalendar, $usedColors);
            }
        }

        /**
         * Sets shared calendar color.
         * @param SavedCalendarSubscription $sharedCalendar
         */
        public static function setSharedCalendarColor(SavedCalendarSubscription $sharedCalendar)
        {
            if($sharedCalendar->color == null)
            {
                $usedColors      = CalendarUtil::getAlreadyUsedColorsByUser(Yii::app()->user->userModel);
                self::processAndSaveColor($sharedCalendar, $usedColors);
            }
        }

        /**
         * Process and save the color for the model.
         * @param SavedCalendar|SavedCalendarSubscription $calendar
         * @param array $usedColors
         */
        public static function processAndSaveColor($calendar, $usedColors)
        {
            assert('$calendar instanceof SavedCalendar || $calendar instanceof SavedCalendarSubscription');
            assert('is_array($usedColors)');
            $availableColors = SavedCalendar::$colorsArray;
            $filteredColors  = array_diff($availableColors, $usedColors);
            $color           = array_shift($filteredColors);
            $calendar->color = $color;
            $calendar->save();
        }

        /**
         * Register my calendar delete script
         */
        public static function registerSavedCalendarDeleteScript($startDate, $endDate)
        {
            assert('is_string($startDate)');
            assert('is_string($endDate)');
            $url = Yii::app()->createUrl('calendars/default/delete');
            $eventsUrl  = Yii::app()->createUrl('calendars/default/getEvents');
            $params = LabelUtil::getTranslationParamsForAllModules();
            $confirmTitle  = Zurmo::t('Core', 'Are you sure you want to delete this {modelLabel}?',
            array('{modelLabel}' => Zurmo::t('CalendarsModule', 'CalendarsModuleSingularLabel', $params)));
            // Begin Not Coding Standard
            
            $script = "$(document).on('click', '.my-cal-delete', function()
                         {
                            if (!confirm('{$confirmTitle}'))
                            {
                                return false;
                            }
                            var calId = $(this).data('value');
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}' + '?id=' + calId,
                                beforeSend: function(xhr)
                                            {
                                                $('#my-calendars-list').html('');
                                                $(this).makeLargeLoadingSpinner(true, '#my-calendars-list');
                                            },
                                success : function(data)
                                          {
                                                $('#my-calendars-list').html(data);
                                                $(this).makeLargeLoadingSpinner(false, '#my-calendars-list');
                                                $('#calendar').fullCalendar('removeEventSource', getCalendarEvents('{$eventsUrl}', 'calendar'));
                                                $('#calendar').fullCalendar('addEventSource', getCalendarEvents('{$eventsUrl}', 'calendar'));
                                          }
                            });

                          }
                        );";
                        
             // End Not Coding Standard
             $cs         = Yii::app()->getClientScript();
             if($cs->isScriptRegistered('calDeleteScript', ClientScript::POS_END) === false)
             {
                $cs->registerScript('calDeleteScript', $script, ClientScript::POS_END);
             }
        }

        /**
         * Registers calendar unsubscription script.
         * @param string $startDate
         * @param string $endDate
         */
        public static function registerCalendarUnsubscriptionScript($startDate, $endDate)
        {
            assert('is_string($startDate)');
            assert('is_string($endDate)');
            $url        = Yii::app()->createUrl('/calendars/default/unsubscribe');
            $eventsUrl  = Yii::app()->createUrl('calendars/default/getEvents');
            // Begin Not Coding Standard
            
            $script     = "$(document).on('click', '.shared-cal-unsubscribe', function(){
                            $.ajax(
                            {
                                type : 'GET',
                                url  : '{$url}',
                                data : {'id' : $(this).data('value')},
                                beforeSend: function(xhr)
                                            {
                                                $('#shared-calendars-list').html('');
                                                $(this).makeLargeLoadingSpinner(true, '#shared-calendars-list');
                                            },
                                success : function(data)
                                          {
                                                $('#shared-calendars-list').html(data);
                                                $(this).makeLargeLoadingSpinner(false, '#shared-calendars-list');
                                                $('#calendar').fullCalendar('removeEventSource', getCalendarEvents('{$eventsUrl}', 'calendar'));
                                                $('#calendar').fullCalendar('addEventSource', getCalendarEvents('{$eventsUrl}', 'calendar'));
                                          }
                            }
                            );
                      })";
                      
            // End Not Coding Standard
            $cs         = Yii::app()->getClientScript();
            $cs->registerScript('calunsubscribescript', $script, ClientScript::POS_END);
        }

        /**
         * Gets model attributes for selected module.
         * @param string $moduleClassName
         * @return array
         */
        public static function getModelAttributesForSelectedModule($moduleClassName)
        {
            $modelClassName         = $moduleClassName::getPrimaryModelName();
            $adapter                = new ModelAttributesAdapter(new $modelClassName(false));
            $attributes             = $adapter->getAttributes();
            $selectedAttributes     = array();
            foreach ($attributes as $attribute => $value)
            {
                if ($value['elementType'] == 'DateTime' || $value['elementType'] == 'Date')
                {
                    $selectedAttributes[$attribute] = $value['attributeLabel'];
                }
            }
            return $selectedAttributes;
        }

        /**
         * @param array $componentFormsData
         * @param Report $report
         * @param null|string $componentPrefix
         */
        public static function makeComponentFormAndPopulateReportFromData($componentFormsData, Report $report, $componentPrefix)
        {
            assert('is_string($componentPrefix)');
            assert('is_array($componentFormsData)');
            $moduleClassName    = $report->getModuleClassName();
            $addMethodName      = 'add' . $componentPrefix;
            $componentClassName = $componentPrefix . 'ForReportForm';
            $rowKey             = 0;
            foreach ($componentFormsData as $componentFormData)
            {
                $component      = new $componentClassName($moduleClassName,
                                                          $moduleClassName::getPrimaryModelName(),
                                                          $report->getType(),
                                                          $rowKey);
                $component->setAttributes($componentFormData);
                $report->{$addMethodName}($component);
                $rowKey++;
            }
        }

        /**
         * Save calendar with serialized data.
         * @param Report $report
         * @param SavedCalendar $savedCalendar
         * @param array $wizardFormPostData
         * @throws FailedToSaveModelException
         */
        public static function saveCalendarWithSerializedData(Report $report, SavedCalendar $savedCalendar, $wizardFormPostData)
        {
            $filtersData          = ArrayUtil::getArrayValue($wizardFormPostData, ComponentForReportForm::TYPE_FILTERS);
            if ($filtersData != null)
            {
                $sanitizedFiltersData = DataToReportUtil::sanitizeFiltersData($report->getModuleClassName(),
                                                                              $report->getType(),
                                                                              $filtersData);
                $unserializedData   = array(ComponentForReportForm::TYPE_FILTERS => $sanitizedFiltersData,
                                        'filtersStructure' => $report->getFiltersStructure());
                $savedCalendar->serializedData = serialize($unserializedData);
            }
            if (!$savedCalendar->save())
            {
                throw new FailedToSaveModelException();
            }
        }

        /**
         * Resolve report by saved calendar post data
         * @param string $type
         * @param int $id
         * @return Report
         */
        public static function resolveReportBySavedCalendarPostData($type, $id = null)
        {
            assert('is_string($type)');
            $postData = PostUtil::getData();
            if ($id == null)
            {
                $report = new Report();
                $report->setType($type);
            }
            else
            {
                $savedCalendar              = SavedCalendar::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
                $report                     = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
            }
            if (isset($postData['SavedCalendar']) && isset($postData['SavedCalendar']['moduleClassName']))
            {
                $report->setModuleClassName($postData['SavedCalendar']['moduleClassName']);
            }
//            else
//            {
//                throw new NotSupportedException();
//            }
            DataToReportUtil::resolveReportByWizardPostData($report, $postData,
                                                                ReportToWizardFormAdapter::getFormClassNameByType($type));
            return $report;
        }

        /**
         * Gets module class name and display labels.
         * @return array
         */
        public static function getAvailableModulesForCalendar()
        {
            $moduleClassNames = array();
            foreach (self::getCalendarModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                $label                              = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
                $moduleClassNames[$moduleClassName] = $label;
            }
            return $moduleClassNames;
        }

        /**
         * @return array of module class names and display labels the current user has access to
         */
        public static function getCalendarModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module::canShowOnCalendar())
                {
                    if (RightsUtil::canUserAccessModule(get_class($module), Yii::app()->user->userModel))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        /**
         * Render full calendar items for a model.
         * @param Object $model
         * @return string
         */
        public static function renderFullCalendarItems($model)
        {
            assert('$model instanceof CalendarItemInterface');
            $descriptionArray = $model->getCalendarItemData();
            $content          = ZurmoHtml::tag('div', array('class' => 'itemDescription'), '');
            foreach ($descriptionArray as $key => $value)
            {
                $content .= ZurmoHtml::tag('span', array(), $key . ':' . $value) . "<br/>";
            }
            return $content;
        }
    }
?>