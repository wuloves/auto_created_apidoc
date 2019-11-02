<?php

date_default_timezone_set('Asia/Shanghai');

require 'library/DB.php';
require 'library/DBInfo.php';
require 'library/Request.php';
require 'library/Response.php';
require 'library/AutoCode.php';

$configPath = 'db_config.php';

function getDbConfigs($configPath, &$configDb, &$configSelect)
{
    $configDb = [];
    $configSelect = [];
    if (file_exists($configPath)) {
        $configDb = file_get_contents($configPath);
        $configDb = json_decode($configDb, true);
        foreach ($configDb as $key => $item) {
            $configSelect[$key] = $item['config_name'];
        }
    }
}

getDbConfigs($configPath, $configDb, $configSelect);


switch (Request::get('act', '')) {
    case 'db_list':
        return Response::item($configSelect);
        break;
    case 'db_add':
        // 表配置添加
        $configItem = [
            'hostname' => Request::post('addmysql_localhost'),
            'username' => Request::post('addmysql_username'),
            'password' => Request::post('addmysql_password'),
            'port' => Request::post('addmysql_port'),
            'database' => Request::post('addmysql_database'),
        ];
        $configDbKey = md5(json_encode($configItem, 256));
        if (!empty($configDb[$configDbKey])) {
            return Response::error('完全一样的连接已经存在');
        }
        $configItem['config_name'] = Request::post('addmysql_config_name');
        $hostname = $configItem['hostname'];
        $username = $configItem['username'];
        $password = $configItem['password'];
        $database = $configItem['database'];
        $port = $configItem['port'];
        $db = new DB($hostname, $username, $password, $database, $port);
        $now = $db->query('SELECT NOW() now')->row['now'];
        $configItem['created_at'] = $now;
        $configDb[$configDbKey] = $configItem;
        file_put_contents($configPath, json_encode($configDb, 256));
        getDbConfigs($configPath, $configDb, $configSelect);
        return Response::item($configSelect);
        break;
}

$useDb = Request::get('connect', key($configDb));
$hostname = $configDb[$useDb]['hostname'];
$username = $configDb[$useDb]['username'];
$password = $configDb[$useDb]['password'];
$database = $configDb[$useDb]['database'];
$port = $configDb[$useDb]['port'];

$db = new DB($hostname, $username, $password, $database, $port);

switch (Request::get('act', '')) {
    case 'table':
        return Response::item(DBInfo::db($db)->getTable());
        break;
    case 'code':
        $allTables = DBInfo::db($db)->getTable();
        $table = Request::get('table');
        if (!in_array($table, $allTables)) {
            return Response::error('table[' . $table . '] 不存在');
        }
        $tableInfo = DBInfo::db($db)->getTable($table);
        $tableAllInfo = [
            'table_name' => $table,
            'full_fields' => $tableInfo,
            'table_info' => $db->query("SHOW TABLE STATUS FROM `" . $db->getDatabaseName() . "` LIKE '" . $table . "'")->row,
            'db' => $db,

        ];
        $data = '请选择 输出数据类型';
        switch (Request::get('codetype', '')) {
            case 'apidoc':
                $data = AutoCode::apidoc($tableAllInfo, ['response']);
                break;
            default:
        }
        return Response::item(['data' => $data]);
        break;
}
return Response::item($db->query("SELECT NOW() now")->row);
