<?php

class DateTimeUserModel extends RedBeanModel
{

    public static function getDefaultMetadata()
    {
        $metadata = parent::getDefaultMetadata();
        $metadata[__CLASS__] = array(
            'members' => array(
                'modifiedDateTime',
                'createdDateTime',
                'createdByUser',
                'modifiedByUser'
            ),
            'rules' => array(
                array('modifiedDateTime', 'type', 'type' => 'datetime'),
                array('modifiedDateTime', 'type', 'type' => 'datetime'),
                array('createdByUser'   , 'type', 'type' => 'integer'),
                array('modifiedByUser'  , 'type', 'type' => 'integer')
            ),
            'relations' => array(
                'createdByUser'  => array(RedBeanModel::HAS_ONE, 'User'),
                'modifiedByUser' => array(RedBeanModel::HAS_ONE, 'User'),
            ),
        );
        return $metadata;
    }

}