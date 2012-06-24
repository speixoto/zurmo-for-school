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
     * Adapter class to manipulate searchAttribute information into DataProvider metadata.
     * Takes either a RedBeanModel or a SearchForm model.
     */
    class SearchDataProviderMetadataAdapter extends DataProviderMetadataAdapter
    {
        protected $appendStructureAsAnd;
        /**
         * Override to make sure the model is a RedBeanModel or a SearchForm model.
         */
        public function __construct($model, $userId, $metadata)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            parent::__construct($model, $userId, $metadata);

        }

        /**
         * Convert metadata which is just an array
         * of posted searchAttributes into metadata that is
         * readable by the RedBeanModelDataProvider
         * @param $appendStructureAsAnd - true/false. If false, then the structure will be appended as OR.
         */
        public function getAdaptedMetadata($appendStructureAsAnd = true)
        {
            assert('is_bool($appendStructureAsAnd)');
            $this->appendStructureAsAnd = $appendStructureAsAnd;
            $adaptedMetadata = array('clauses' => array(), 'structure' => '');
            $clauseCount = 1;
            $structure = '';
            foreach ($this->metadata as $attributeName => $value)
            {
                //If attribute is a pseudo attribute on the SearchForm
                if ($this->model instanceof SearchForm && $this->model->isAttributeOnForm($attributeName))
                {
                    $this->populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                             $value,
                                                                             $adaptedMetadata['clauses'],
                                                                             $clauseCount,
                                                                             $structure);
                }
                else
                {
                    $this->populateClausesAndStructureForAttribute($attributeName,
                                                                    $value,
                                                                    $adaptedMetadata['clauses'],
                                                                    $clauseCount,
                                                                    $structure);
                }
            }
            $adaptedMetadata['structure'] = $structure;
            return $adaptedMetadata;
        }

        protected function populateClausesAndStructureForAttribute( $attributeName,
                                                                    $value,
                                                                    & $adaptedMetadataClauses,
                                                                    & $clauseCount,
                                                                    & $structure,
                                                                    $operatorType = null)
        {
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            //non-relation attribute that has single data value
            if (!is_array($value))
            {
                if ($value !== null)
                {
                    $adaptedMetadataClauses[($clauseCount)] = array();
                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(  $this->model,
                                                                                           $attributeName,
                                                                                           $operatorType,
                                                                                           $value,
                                                                                           $adaptedMetadataClauses[($clauseCount)],
                                                                                           $structure,
                                                                                           $clauseCount);
                }
            }
            //non-relation attribute that has array of data
            elseif (!$this->model->isRelation($attributeName))
            {
                if (isset($value['value']) && $value['value'] != '')
                {
                    $adaptedMetadataClauses[($clauseCount)] = array();
                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(  $this->model,
                                                                                           $attributeName,
                                                                                           $operatorType,
                                                                                           $value['value'],
                                                                                           $adaptedMetadataClauses[($clauseCount)],
                                                                                           $structure,
                                                                                           $clauseCount);
                }
            }
            //relation attribute that is relatedData
            elseif(isset($value['relatedData']) && $value['relatedData'] == true)
            {
                $adaptedMetadataClauseBasePart = array(
                    'attributeName'        => $attributeName,
                    'relatedModelData' => array());
                $depth = 1;
                unset($value['relatedData']);
                $this->populateClausesAndStructureForAttributeWithRelatedModelData(
                    $this->model->$attributeName,
                    $value,
                    $adaptedMetadataClauseBasePart,
                    $adaptedMetadataClauses,
                    $clauseCount,
                    $structure,
                    $depth,
                    $operatorType);
            }
            //relation attribute that has array of data
            else
            {
                foreach ($value as $relatedAttributeName => $relatedValue)
                {
                    if(static::resolveRelatedValueWhenArray( $this->model->$attributeName,
                                                             $relatedAttributeName,
                                                             $relatedValue,
                                                             $operatorType))
                    {
                        $this->populateClausesAndStructureForRelatedAttributeThatIsArray(  $this->model,
                                                                                            $attributeName,
                                                                                            $relatedAttributeName,
                                                                                            $relatedValue,
                                                                                            $adaptedMetadataClauses,
                                                                                            $clauseCount,
                                                                                            $structure,
                                                                                            $operatorType);
                    }
                }
            }
        }

        protected static function resolveRelatedValueWhenArray($model,
                                                               $relatedAttributeName,
                                                               & $relatedValue,
                                                               & $operatorType)
        {
            if (is_array($relatedValue))
            {
                if (isset($relatedValue['value']) && $relatedValue['value'] != '')
                {
                    $relatedValue = $relatedValue['value'];
                }
                elseif (($model instanceof RedBeanManyToManyRelatedModels ||
                        $model instanceof RedBeanOneToManyRelatedModels ) &&
                       is_array($relatedValue) && count($relatedValue) > 0)
                {
                    //Continue on using relatedValue as is.
                }
                elseif ($model->$relatedAttributeName instanceof RedBeanModels &&
                       is_array($relatedValue) && count($relatedValue) > 0)
                {
                    //Continue on using relatedValue as is.
                }
                elseif ($model instanceof CustomField && count($relatedValue) > 0)
                {
                    //Handle scenario where the UI posts or sends a get string with an empty value from
                    //a multi-select field.
                    if (count($relatedValue) == 1 && $relatedValue[0] == null)
                    {
                        return false;
                    }
                    //Continue on using relatedValue as is.
                    if ($operatorType == null)
                    {
                        $operatorType = 'oneOf';
                    }
                }
            }
            return true;
        }

        protected function populateClausesAndStructureForRelatedAttributeThatIsArray($model,
                                                                                            $attributeName,
                                                                                            $relatedAttributeName,
                                                                                            $relatedValue,
                                                                                            & $adaptedMetadataClauses,
                                                                                            & $clauseCount,
                                                                                            & $structure,
                                                                                            $operatorType = null)

        {
            if ($relatedValue !== null)
            {
                if ($model->isRelation($attributeName))
                {
                    $adaptedMetadataClauses[($clauseCount)] = array();
                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(
                                                                                   $model->$attributeName,
                                                                                   $relatedAttributeName,
                                                                                   $operatorType,
                                                                                   $relatedValue,
                                                                                   $adaptedMetadataClauses[($clauseCount)],
                                                                                   $structure,
                                                                                   $clauseCount,
                                                                                   $attributeName);
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
        }

        protected static function resolveAsRedBeanModel($model)
        {
            if ($model instanceof RedBeanOneToManyRelatedModels || $model instanceof RedBeanManyToManyRelatedModels)
            {
                $relationModelClassName = $model->getModelClassName();
                return new $relationModelClassName(false);
            }
            else
            {
                return $model;
            }
        }


        protected function populateClausesAndStructureForAttributeWithRelatedModelData(RedBeanModel $model,
                                                                                              $relatedData,
                                                                                              $adaptedMetadataClauseBasePart,
                                                                                              & $adaptedMetadataClauses,
                                                                                              & $clauseCount,
                                                                                              & $structure,
                                                                                              $depth,
                                                                                              $operatorType = null)
        {
            assert('is_array($relatedData)');
            assert('is_int($depth) && $depth > 0');
            $startingOperatorType = $operatorType;
            foreach($relatedData as $attributeName => $value)
            {
             $operatorType            = $startingOperatorType;
             $basePartAtRequiredDepth = static::
                                       getAdaptedMetadataClauseBasePartAtRequiredDepth($adaptedMetadataClauseBasePart, $depth);
              //non-relation attribute that has single data value
                if (!is_array($value))
                {
                    if ($value !== null)
                    {
                       // $d = static::makeAndGetDeepestRelatedModelDataEmptyArray($adaptedMetadataClauseBasePart);
                        $currentClauseCount = $clauseCount;
                        $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(  $model,
                                                                                               $attributeName,
                                                                                               $operatorType,
                                                                                               $value,
                                                                                               $basePartAtRequiredDepth,
                                                                                               $structure,
                                                                                               $clauseCount);
                        $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                    $adaptedMetadataClauseBasePart,
                                                                                    $basePartAtRequiredDepth,
                                                                                    $depth);

                    }
                }
                //non-relation attribute that has array of data
                elseif (!$model->isRelation($attributeName))
                {
                    if (isset($value['value']) && $value['value'] != '')
                    {
                        //static::makeAndGetDeepestRelatedModelDataEmptyArray($adaptedMetadataClauseBasePart);
                        $currentClauseCount = $clauseCount;
                        $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(  $model,
                                                                                               $attributeName,
                                                                                               $operatorType,
                                                                                               $value['value'],
                                                                                               $basePartAtRequiredDepth,
                                                                                               $structure,
                                                                                               $clauseCount);
                        $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                    $adaptedMetadataClauseBasePart,
                                                                                    $basePartAtRequiredDepth,
                                                                                    $depth);
                    }
                }
                //relation attribute that is relatedData
                elseif(isset($value['relatedData']) && $value['relatedData'] == true)
                {

                    $partToAppend                    = array('attributeName'    => $attributeName,
                                                             'relatedModelData' => array());
                    $appendedClauseToPassRecursively = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                    $adaptedMetadataClauseBasePart,
                                                                    $partToAppend,
                                                                    $depth);
                    unset($value['relatedData']);
                    $this->populateClausesAndStructureForAttributeWithRelatedModelData(
                        static::resolveAsRedBeanModel($model->$attributeName),
                        $value,
                        $appendedClauseToPassRecursively,
                        $adaptedMetadataClauses,
                        $clauseCount,
                        $structure,
                        ($depth + 1),
                        $operatorType);
                }
                //relation attribute that has array of data
                else
                {
                    foreach ($value as $relatedAttributeName => $relatedValue)
                    {
                        $currentClauseCount = $clauseCount;
                        if(static::resolveRelatedValueWhenArray( $model->$attributeName,
                                                                 $relatedAttributeName,
                                                                 $relatedValue,
                                                                 $operatorType))
                        {
                            if ($relatedValue !== null)
                            {
                                if ($model->isRelation($attributeName))
                                {
                                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(
                                                                                                   $model->$attributeName,
                                                                                                   $relatedAttributeName,
                                                                                                   $operatorType,
                                                                                                   $relatedValue,
                                                                                                   $basePartAtRequiredDepth,
                                                                                                   $structure,
                                                                                                   $clauseCount,
                                                                                                   $attributeName);
                                        $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                                    $adaptedMetadataClauseBasePart,
                                                                                                    $basePartAtRequiredDepth,
                                                                                                    $depth);
                                }
                                else
                                {
                                    throw new NotSupportedException();
                                }
                            }
                        }
                    }
                }
            }
        }

        protected function resolveOperatorAndCastsAndAppendClauseAsAndToStructureString($model,
                                                                                               $attributeName,
                                                                                               $operatorType,
                                                                                               $value,
                                                                                               & $adaptedMetadataClause,
                                                                                               & $structure,
                                                                                               & $clauseCount,
                                                                                               $previousAttributeName = null)
        {
            assert('$previousAttributeName == null || is_string($previousAttributeName)');
            $modelForTypeOperations = static::resolveAsRedBeanModel($model);
            if ($operatorType == null)
            {
                $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType($modelForTypeOperations, $attributeName);
            }
            if (is_array($value) && $model instanceof CustomField)
            {
                //do nothing, the cast is fine as is. Maybe eventually remove this setting of cast.
            }
            else
            {
                $value        = ModelAttributeToCastTypeUtil::resolveValueForCast($modelForTypeOperations, $attributeName, $value);
            }
            if ($model instanceof RedBeanModel)
            {
                $mixedType = ModelAttributeToMixedTypeUtil::getType($model, $attributeName);
                static::resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                        $value,
                                                                                        $operatorType);
            }
            if($previousAttributeName == null)
            {
                $adaptedMetadataClause['attributeName'] = $attributeName;
            }
            else
            {
                $adaptedMetadataClause['attributeName']        = $previousAttributeName;
                $adaptedMetadataClause['relatedAttributeName'] = $attributeName;
            }

            $adaptedMetadataClause['operatorType']  = $operatorType;
            $adaptedMetadataClause['value']         = $value;
            $this->resolveAppendClauseAsAndToStructureString($structure,
                                                              $clauseCount);
        }

        protected function resolveAppendClauseAsAndToStructureString(& $structure, & $clauseCount)
        {
            if ($this->appendStructureAsAnd)
            {
                static::appendClauseAsAndToStructureString($structure, $clauseCount);
            }
            else
            {
                static::appendClauseAsOrToStructureString($structure, $clauseCount);
            }
            $clauseCount++;
        }

        /**
         * Method for populating clauses for concated attributes.  The first concated attribute $attributeNames[0]
         * will be used to determine the operator types.
         */
        protected function populateClausesAndStructureForConcatedAttributes($attributeNames,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure,
                                                                            $operatorType = null)
        {
            assert('is_array($attributeNames) && count($attributeNames) == 2');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            if ($value !== null)
            {
                if ($operatorType == null)
                {
                    $operatorType        = ModelAttributeToOperatorTypeUtil::getOperatorType($this->model, $attributeNames[0]);
                    $operatorTypeCompare = ModelAttributeToOperatorTypeUtil::getOperatorType($this->model, $attributeNames[1]);
                    if ($operatorType != $operatorTypeCompare)
                    {
                        throw New NotSupportedException();
                    }
                }
                $value = ModelAttributeToCastTypeUtil::resolveValueForCast($this->model, $attributeNames[0], $value);
                $adaptedMetadataClauses[($clauseCount)] = array(
                    'concatedAttributeNames' => $attributeNames,
                    'operatorType'           => $operatorType,
                    'value'                  => $value,
                );
                if ($this->appendStructureAsAnd)
                {
                    static::appendClauseAsAndToStructureString($structure, $clauseCount);
                }
                else
                {
                    static::appendClauseAsOrToStructureString($structure, $clauseCount);
                }
                $clauseCount++;
            }
        }

        protected function populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure)
        {
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            $tempStructure = null;
            $metadataFromSearchFormAttributes = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata(
                                                $this->model, $attributeName, $value);
            foreach ($metadataFromSearchFormAttributes as $searchFormClause)
            {
                if (isset($searchFormClause['concatedAttributeNames']))
                {
                    assert('is_array($searchFormClause["concatedAttributeNames"][0]) &&
                             count($searchFormClause["concatedAttributeNames"][0]) == 2');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["operatorType"])');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["appendStructureAsAnd"])');
                    $this->populateClausesAndStructureForConcatedAttributes($searchFormClause['concatedAttributeNames'][0],
                                                                             $searchFormClause['concatedAttributeNames']['value'],
                                                                             $adaptedMetadataClauses,
                                                                             $clauseCount,
                                                                             $tempStructure,
                                                                             false);
                }
                else
                {
                    foreach ($searchFormClause as $searchFormAttributeName => $searchFormStructure)
                    {
                        if (isset($searchFormStructure['operatorType']))
                        {
                            $operatorType = $searchFormStructure['operatorType'];
                        }
                        else
                        {
                            $operatorType = null;
                        }
                        //setting a temp value is not ideal. But it avoids passing the parameter.
                        $oldAppendStructureAsAndValue = $this->appendStructureAsAnd;
                        if (isset($searchFormStructure['appendStructureAsAnd']))
                        {
                            $this->appendStructureAsAnd = $searchFormStructure['appendStructureAsAnd'];
                        }
                        else
                        {
                            $this->appendStructureAsAnd = false;
                        }
                        $this->populateClausesAndStructureForAttribute($searchFormAttributeName,
                                                                        $searchFormStructure['value'],
                                                                        $adaptedMetadataClauses,
                                                                        $clauseCount,
                                                                        $tempStructure,
                                                                        $operatorType);
                        $this->appendStructureAsAnd = $oldAppendStructureAsAndValue;
                    }
                }
            }
            if ($tempStructure != null)
            {
                $tempStructure = '(' . $tempStructure . ')';
                if ($this->appendStructureAsAnd)
                {
                    static::appendClauseAsAndToStructureString($structure, $tempStructure);
                }
                else
                {
                    static::appendClauseAsOrToStructureString($structure, $tempStructure);
                }
            }
        }

        protected static function appendClauseAsAndToStructureString(& $structure, $clause)
        {
            assert('$structure == null || is_string($structure)');
            assert('$clause != null || (is_string($clause) || is_int(clause))');
            if (!empty($structure))
            {
                $structure .= ' and ' . $clause;
            }
            else
            {
                $structure .= $clause;
            }
        }

        protected static function appendClauseAsOrToStructureString(& $structure, $clause)
        {
            assert('$structure == null || is_string($structure)');
            assert('$clause != null || (is_string($clause) || is_int(clause))');
            if (!empty($structure))
            {
                $structure .= ' or ' . $clause;
            }
            else
            {
                $structure .= $clause;
            }
        }

        protected static function resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($type, & $value,
                                                                                                  & $operatorType)
        {
            assert('is_string($type)');
            assert('is_string($operatorType)');
            if ($type == 'CheckBox' && ($value == '0' || !$value))
            {
                $operatorType = 'doesNotEqual';
                $value        = (bool)1;
            }
        }

        protected static function getAdaptedMetadataClauseBasePartAtRequiredDepth($adaptedMetadataClauseBasePart, $depth)
        {
            assert('is_array($adaptedMetadataClauseBasePart)');
            assert('is_int($depth) && $depth > 0');
            $finalPart = $adaptedMetadataClauseBasePart;
            for ($i = 0; $i < $depth; $i++)
            {
                $finalPart = $finalPart['relatedModelData'];
            }
            return $finalPart;
        }

        protected static function getAppendedAdaptedMetadataClauseBasePart($adaptedMetadataClauseBasePart, $partToAppend, $depth)
        {
            assert('is_array($adaptedMetadataClauseBasePart)');
            assert('is_array($partToAppend)');
            assert('is_int($depth) && $depth > 0');
            $finalPart = & $adaptedMetadataClauseBasePart;
            for ($i = 0; $i < $depth; $i++)
            {
                $finalPart = & $finalPart['relatedModelData'];
            }
            $finalPart = $partToAppend;
            return $adaptedMetadataClauseBasePart;
        }
    }
?>