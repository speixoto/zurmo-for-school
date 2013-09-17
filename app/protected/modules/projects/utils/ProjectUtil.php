<?php
class ProjectUtil
{
    public static function getProjectInformationForDashboard($data, $row)
    {
        print $data->tasks->count();
        return $data->name;
    }
}

?>
