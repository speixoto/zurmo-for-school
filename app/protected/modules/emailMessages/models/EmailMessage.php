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
     * Model for storing an email message.
     */
    class EmailMessage extends OwnedSecurableItem implements MashableActivityInterface
    {
        public static function getMashableActivityRulesType()
        {
            return 'EmailMessage';
        }

        public static function getAllByFolderType($type)
        {
            assert('is_string($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'folder',
                    'relatedAttributeName' => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        public function __toString()
        {
            if (trim($this->subject) == '')
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $this->subject;
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'subject',
                    //'type',
                    'sendAttempts',
                    'sentDateTime',
                    'sendOnDateTime',
                    'headers',
                ),
                'relations' => array(
                    'folder'        => array(static::HAS_ONE,  'EmailFolder', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'folder'),
                    'content'       => array(static::HAS_ONE,  'EmailMessageContent',    static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'content'),
                    'files'         => array(static::HAS_MANY, 'FileModel',              static::OWNED,
                                                static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'sender'        => array(static::HAS_ONE,  'EmailMessageSender',     static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'sender'),
                    'recipients'    => array(static::HAS_MANY, 'EmailMessageRecipient',  static::OWNED),
                    'error'         => array(static::HAS_ONE,  'EmailMessageSendError' , static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'error'),
                    'account'       => array(static::HAS_ONE,  'EmailAccount', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'account'),
                ),
                'rules' => array(
                    array('subject',         'required'),
                    array('subject',         'type',    'type' => 'string'),
                    array('subject',         'length',  'min'  => 1, 'max' => 255),
                    array('folder',          'required'),
                    array('sender',          'required'),
                    array('sendAttempts',    'type',    'type' => 'integer'),
                    array('sendAttempts',    'numerical', 'min' => 0),
                    array('sentDateTime',    'type', 'type' => 'datetime'),
                    array('sendOnDateTime',  'type', 'type' => 'datetime'),
                    array('headers',         'type', 'type' => 'string'),
                ),
                'elements' => array(
                    'sentDateTime'  => 'DateTime',
                    'files'         => 'Files',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function hasRelatedItems()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'account'      => Zurmo::t('EmailMessagesModule', 'Email Account',  array(), null, $language),
                    'content'      => Zurmo::t('Core', 'Content',  array(), null, $language),
                    'error'        => Zurmo::t('Core',                'Error',  array(), null, $language),
                    'folder'       => Zurmo::t('ZurmoModule',         'Folder',  array(), null, $language),
                    'files'        => Zurmo::t('ZurmoModule',         'Files',  array(), null, $language),
                    'recipients'   => Zurmo::t('EmailMessagesModule', 'Recipients',  array(), null, $language),
                    'sender'       => Zurmo::t('EmailMessagesModule', 'Sender',  array(), null, $language),
                    'sendAttempts' => Zurmo::t('EmailMessagesModule', 'Send Attempts',  array(), null, $language),
                    'sentDateTime' => Zurmo::t('EmailMessagesModule', 'Sent Date Time',  array(), null, $language),
                    'subject'      => Zurmo::t('Core', 'Subject',  array(), null, $language),
                    //'type'         => Zurmo::t('Core',                'Type',  array(), null, $language),
                )
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Emails', array(), null, $language);
        }

        public function hasSendError()
        {
            return !($this->error == null || $this->error->id < 0);
        }

        public function save($runValidation = true, array $attributeNames = null)
        {
            return parent::save($runValidation, $attributeNames);
            if ($attributeNames !== null)
            {
                throw new NotSupportedException();
            }
            if ($this->isSaving) // Prevent cycles.
            {
                return true;
            }
            $this->isSaving = true;
            try
            {
                if (!$runValidation || $this->validate())
                {
                    if ($this->beforeSave())
                    {
                        $beans = array_values($this->modelClassNameToBean);
                        $this->linkBeans();
                        // The breakLink/link is deferred until the save to avoid
                        // disconnecting or creating an empty row if the model was
                        // never actually saved.
                        foreach ($this->unlinkedRelationNames as $key => $relationName)
                        {
                            $bean                      = $this->attributeNameToBeanAndClassName[$relationName][0];
                            $relationAndOwns           = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
                            $relatedModelClassName     = $relationAndOwns[$relationName][1];
                            $tempRelatedModelClassName = $relatedModelClassName;
                            self::resolveModelClassNameForClassesWithoutBeans($tempRelatedModelClassName);
                            $relatedTableName          = $tempRelatedModelClassName::getTableName();
                            $linkName = strtolower($relationName);
                            if (static::getRelationType($relationName) == self::HAS_ONE &&
                                static::getRelationLinkType($relationName) == self::LINK_TYPE_SPECIFIC)
                            {
                                $linkName = strtolower(static::getRelationLinkName($relationName));
                            }
                            elseif ($linkName == strtolower($relatedModelClassName))
                            {
                                $linkName = null;
                            }
                            ZurmoRedBeanLinkManager::breakLink($bean, $relatedTableName, $linkName);
                            //Check the $this->{$relationName} second in the if clause to avoid accidentially getting
                            //a relation to now save. //todo: this needs to be properly handled.
                            if (isset($this->unlinkedOwnedRelatedModelsToRemove[$relationName]) && $this->{$relationName} !== null)
                            {
                                //Remove hasOne owned related models that are no longer needed because they have
                                //been replaced with another hasOne owned model.
                                if ($this->unlinkedOwnedRelatedModelsToRemove[$relationName]->id > 0)
                                {
                                    $this->unlinkedOwnedRelatedModelsToRemove[$relationName]->unrestrictedDelete();
                                }
                                unset($this->unlinkedOwnedRelatedModelsToRemove[$relationName]);
                            }
                            unset($this->unlinkedRelationNames[$key]);
                        }
                        assert('count($this->unlinkedRelationNames) == 0');
                        foreach ($this->relationNameToRelatedModel as $relationName => $relatedModel)
                        {
                            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
                            $relationType = $relationAndOwns[$relationName][0];
                            if (!in_array($relationType, array(self::HAS_ONE_BELONGS_TO,
                                self::HAS_MANY_BELONGS_TO)))
                            {
                                if ($relatedModel->isModified() ||
                                    ($this->isAttributeRequired($relationName)))
                                {
                                    //If the attribute is required, but already exists and has not been modified we do
                                    //not have to worry about saving it.
                                    if ($this->isSavableFromRelation &&
                                        !($this->isAttributeRequired($relationName) &&
                                            !$relatedModel->isModified() &&
                                            $relatedModel->id > 0))
                                    {
                                        if (!$relatedModel->save(false))
                                        {
                                            $this->isSaving = false;
                                            return false;
                                        }
                                    }
                                    elseif ($relatedModel->isModified())
                                    {
                                        throw new NotSuportedException();
                                    }
                                }
                            }
                            if ($relatedModel instanceof RedBeanModel)
                            {
                                $bean                  = $this->attributeNameToBeanAndClassName                [$relationName][0];
                                $relationAndOwns       = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
                                $relatedModelClassName = $relationAndOwns[$relationName][1];
                                $linkName = strtolower($relationName);
                                if ($relationType == self::HAS_ONE &&
                                    static::getRelationLinkType($relationName) == self::LINK_TYPE_SPECIFIC)
                                {
                                    $linkName = strtolower(static::getRelationLinkName($relationName));
                                }
                                elseif (strtolower($linkName) == strtolower($relatedModelClassName)  ||
                                    static::getRelationLinkType($relationName) == self::LINK_TYPE_ASSUMPTIVE)
                                {
                                    $linkName = null;
                                }
                                elseif ($relationType == static::HAS_MANY_BELONGS_TO ||
                                    $relationType == static::HAS_ONE_BELONGS_TO)
                                {
                                    $label = 'Relations of type HAS_MANY_BELONGS_TO OR HAS_ONE_BELONGS_TO must have the relation name ' .
                                        'the same as the related model class name. Relation: {relationName} ' .
                                        'Relation model class name: {relationModelClassName}';
                                    throw new NotSupportedException(Zurmo::t('Core', $label,
                                        array('{relationName}' => $linkName,
                                            '{relationModelClassName}' => $relatedModelClassName)));
                                }
                                //Needed to exclude HAS_ONE_BELONGS_TO because an additional column was being created
                                //on the wrong side.
                                if ($relationType != static::HAS_ONE_BELONGS_TO && ($relatedModel->isModified() ||
                                        $relatedModel->id > 0       ||
                                        $this->isAttributeRequired($relationName)))
                                {
                                    $relatedModel = $this->relationNameToRelatedModel[$relationName];
                                    $relatedBean  = $relatedModel->getClassBean($relatedModelClassName);
                                    //Exclude HAS_MANY_BELONGS_TO because if the existing relation is unlinked, then
                                    //this link should not be reactivated, because it will improperly create the bean
                                    //in the database.
                                    if (!($relationType == static::HAS_MANY_BELONGS_TO && $this->{$relationName}->id < 0))
                                    {
                                        ZurmoRedBeanLinkManager::link($bean, $relatedBean, $linkName);
                                    }
                                }
                            }
                        }
                        $baseModelClassName = null;
                        foreach ($this->modelClassNameToBean as $modelClassName => $bean)
                        {
                            ZurmoRedBean::store($bean);
                            assert('$bean->id > 0');
                        }
                        $this->modified = false;
                        $this->afterSave();
                        $calledModelClassName = get_called_class();
                        if ($calledModelClassName::isCacheable())
                        {
                            RedBeanModelsCache::cacheModel($this);
                        }
                        $this->isSaving = false;
                        return true;
                    }
                }
                $this->isSaving = false;
                return false;
            }
            catch (Exception $e)
            {
                $this->isSaving = false;
                throw $e;
            }
        }
    }
?>