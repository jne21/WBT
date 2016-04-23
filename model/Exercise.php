<?php
namespace WBT;

use \common\Registry;

final class Exercise extends \common\SimpleObject
{
    const
        TABLE = 'exercise',
        DB    = 'db'
    ;

    public
        $id, $name, $description, $controller, $configTemplate;

    function load($data)
    {
        $this->id             = $data->id;
        $this->name           = $data->name;
        $this->description    = $data->description;
        $this->controller     = $data->controller;
        $this->configTemplate = $data->config_template;
    }

    function save()
    {
        $db = Registry::getInstance()->get(self::DB);
        $record = [
            'name'            => $this->name,
            'description'     => $this->description,
            'controller'      => $this->controller,
            'config_template' => $this->configTemplate
        ];
        if ($this->id) {
            $db->update (self::TABLE, $record, "`id`=".intval($this->id));
        }
        else {
            $db->insert(self::TABLE, $record) or die($db->lastError);
            $this->id = $db->insertId();
        }
    }
}
