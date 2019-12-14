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

    private $tableField = [];

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

    public function getTableField()
    {
        if (empty($this->tableField)) {
            $this->getFullFields();
        }
        return $this->tableField;
    }

    public function getFullFields()
    {
        if (!$this->fullFields) {
            $sql = 'SHOW FULL FIELDS FROM `' . $this->table . '`;';
            $this->fullFields = $this->db->query($sql)->rows;
        }
        $fullFields = $this->fullFields;
        $tableInfo = $this->getTableInfo();
        foreach ($fullFields as $item) {
            $field = $item['Field'];
            $type = $item['Type'];
            $comment = $item['Comment'];
            $item['Key'] == 'PRI' && $comment = $tableInfo['Comment'] . '的ID';
            if (in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }
            $length = 0;
            if (count(explode('(', $type)) > 1) {
                $datatype = explode('(', $type)[0];
                $length = str_replace(')', '', explode('(', $type)[1]);
                if (str_replace(',', '', $length) != $length) {
                    $length = 0;
                }
            } else {
                $datatype = $type;
            }
            $this->tableField[$field] = [
                'type' => $datatype,
                'comment' => $comment,
                'length' => $length,
                'default' => $item['Default'],
                'memo' => '',
                'null' => (!empty($item['Null']) && $item['Null'] === 'YES') ? 1 : 0, // 是否允许为null
            ];
        }
        return $fullFields;
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
        $d = $this->db->query(" SELECT * FROM `" . $this->table . "` LIMIT 0,1")->row;
        if (!empty($d)) {
            foreach ($this->tableField as $field => $item) {
                switch ($item['type']) {
                    case 'json':
                        if (!empty($d[$field])) {
                            $d[$field] = json_decode($d[$field], true);
                        }
                        break;
                }
            }
        }
        return $d;
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
