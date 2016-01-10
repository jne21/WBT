<?php

namespace common;

class RedirectQuery {

    const
        DB = 'db',
        TABLE = 'redirect_query'
    ;

    public
        $id,
        $redirectId,
        $date,
        $HTTP_REFERER,
        $REMOTE_ADDR,
        $HTTP_USER_AGENT,
        $REDIRECT_URL,
        $REDIRECT_QUERY_STRING
    ;
        
    function __construct($instanceId = NULL) {
        if ($id = intval($instanceId)) {
            $db = Registry::getInstance()->get('db');
            $rs = $db->query("SELECT * FROM `".self::TABLE."` WHERE `id`=$id");
            if ($sa = $db->fetch($rs)) {
                $this->loadDataFromArray($sa);
            }
        }
    }

    function loadDataFromArray($data) {
        $this->id = $data['id'];
        $this->redirectId = $data['redirect_id'];
        $this->date = strtotime($data['dt']);
        $this->HTTP_REFERER = $data['HTTP_REFERER'];
        $this->REMOTE_ADDR = $data['REMOTE_ADDR'];
        $this->HTTP_USER_AGENT = $data['HTTP_USER_AGENT'];
        $this->REDIRECT_URL = $data['REDIRECT_URL'];
        $this->REDIRECT_QUERY_STRING = $data['REDIRECT_QUERY_STRING'];
    }

    static function getList($redirectId) {
        $list = [];
        if ($id = intval($redirectId)) {
            $db = Registry::getInstance()->get('db');
            $rs = $db->query("SELECT * FROM `".self::TABLE."` WHERE `redirect_id`=$id ORDER BY `date`");
            while($data = $db->fetch($rs)) {
                $entity = new RedirectQuery();
                $entity->loadDataFromArray($data);
                $list[$entity->id] = $entity;
            }
        }
        return $list;
    }

    static function register($redirectId) {
        $record = [
            'redirect_id' => $this->redirectId,
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'],
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
            'REDIRECT_URL' => $_SERVER['REDIRECT_URL'],
            'REDIRECT_QUERY_STRING' => $_SERVER['REDIRECT_QUERY_STRING']
        ];
        $db->insert(self::TABLE, $record);
    }

    static function delete($entityId) {
        if ($id = intval($entityId)) {
            Registry::getInstance()->get('db')->query("DELETE FROM `".self::TABLE."` WHERE `id`=$id");
        }
    }

    static function purge($redirectId) {
        if ($id = intval($redirectId)) {
            Registry::getInstance()->get('db')->query("DELETE FROM `".self::TABLE."` WHERE `redirect_id`=$id");
        }
    }
    
}