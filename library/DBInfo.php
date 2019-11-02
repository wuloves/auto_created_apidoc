<?php

class DBInfo
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public static function db($db)
    {
        return new self($db);
    }

    // 显示所有的表
    public function getTable($tables = null)
    {
        if ($tables){
            $sql = 'SHOW FULL FIELDS FROM `' . $tables .'`;';
            return $this->db->query($sql)->rows;
        }else{
            $sql = 'SHOW TABLES';
            $databaseName = $this->db->getDatabaseName();
            $r = [];
            foreach ($this->db->query($sql)->rows as $row) {
                $r[] = $row['Tables_in_' . $databaseName];
            }
            return $r;
        }
    }


}

