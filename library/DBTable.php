<?php

/*
 * @auth wuloves<develop@wuloves.com>
 * */

class DBTable
{

    private $db;

    private $table;

    private $fullFields;

    private $tableInfo;

    public function __construct($db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function getTableComment()
    {
        $tableInfo = $this->getTableInfo();
        $comment = str_replace('表', '', $tableInfo['Comment']);
        if (empty($comment)) {
            $comment = $this->getModelName();
        }
        return $comment;
    }

    public function getFullFields()
    {
        if (!$this->fullFields) {
            $sql = 'SHOW FULL FIELDS FROM `' . $this->table . '`;';
            return $this->fullFields = $this->db->query($sql)->rows;
        }
        return $this->fullFields;
    }

    /**
     * @return array
     */
    public function getTableInfo()
    {
        if (!$this->tableInfo) {
            return $this->tableInfo = $this->db->query("SHOW TABLE STATUS FROM `" . $this->db->getDatabaseName() . "` LIKE '" . $this->table . "'")->row;
        }
        return $this->tableInfo;
    }

    /**
     * @return bool|string|string[]|null
     */
    public function getModelName()
    {
        $tableMaxName = preg_replace_callback('/_+([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $this->table);
        $tableMaxName = ucfirst($tableMaxName);
        if (substr($tableMaxName, -3) == 'ies') {
            return substr($tableMaxName, 0, -3) . 'y';
        } else if (substr($tableMaxName, -1) == 's') {
            return substr($tableMaxName, 0, -1);
        } else {
            return $tableMaxName;
        }
    }

    public function getRowData()
    {
        return $this->db->query(" SELECT * FROM `" . $this->table . "` LIMIT 0,1")->row;
    }

    public function getPriFieldInfo()
    {
        foreach ($this->getFullFields() as $item) {
            if ($item['Key'] == 'PRI') {
                return $item;
                break;
            }
        }
    }

    public function getDeleteField($deletedKey = 'deleted_at')
    {
        foreach ($this->getFullFields() as $item) {
            if ($item['Field'] == $deletedKey) {
                return $deletedKey;
                break;
            }
        }
    }

}
