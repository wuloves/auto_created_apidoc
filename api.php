<?php

date_default_timezone_set('Asia/Shanghai');
define('BASE_PATH', __DIR__);

require 'library/DB.php';
require 'library/DBInfo.php';
require 'library/DBTable.php';
require 'library/Request.php';
require 'library/Response.php';
require 'library/AutoCode.php';

$configPath = 'db_config.php';

function getDbConfigs($configPath, &$configDb, &$configSelect, &$safeChar)
{
    // 如果觉得不需要考虑本地的数据被暴露的风险, 可以将其值设为空字符
    $safeChar = '<?php
echo \'<h1>PHP[文档/代码]自动生成配置文件</h1>\';
exit;
//  ';
    $configDb = [];
    $configSelect = [];
    if (file_exists($configPath)) {
        $configDb = file_get_contents($configPath, null, null, strlen($safeChar));
        $configDb = json_decode($configDb, true);
        if (empty($configDb)) {
            $configDb = [];
        }
        foreach ($configDb as $key => $item) {
            $configSelect[$key] = $item['config_name'];
        }
    }
}

getDbConfigs($configPath, $configDb, $configSelect, $safeChar);
switch (Request::get('act', '')) {
    case 'connect_list':
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
        file_put_contents($configPath, $safeChar . json_encode($configDb, 256));
        getDbConfigs($configPath, $configDb, $configSelect, $safeChar);
        return Response::item($configSelect);
        break;
}

$useDb = Request::get('connect', key($configDb));
$hostname = $configDb[$useDb]['hostname'];
$username = $configDb[$useDb]['username'];
$password = $configDb[$useDb]['password'];
$database = $configDb[$useDb]['database'];
if ($database == '*' || empty($database)) {
    $database = Request::get('db');
}
$port = $configDb[$useDb]['port'];

$db = new DB($hostname, $username, $password, $database, $port);

switch (Request::get('act', '')) {
    case 'db_list':
        $connect = Request::get('connect');
        if (empty($connect)) {
            return Response::error(['error' => '请选择连接']);
        }
        // 判断是否填写了表名, 如果填写了, 则只能单选
        if (!empty($configDb[$useDb]['database']) && $configDb[$useDb]['database'] != '*') {
            return Response::item([$configDb[$useDb]['database']]);
        }
        $dbb = [];
        foreach (DBInfo::db($db)->getDatabase() as $item) {
            if (!in_array($item['Database'], ['performance_schema', 'information_schema', 'sys', 'mysql'])) {
                $dbb[] = $item['Database'];
            }
        }
        return Response::item($dbb);
        break;
    case 'table_list':
        return Response::item(DBInfo::db($db)->getTable());
        break;
    case 'code':
        $allTables = DBInfo::db($db)->getTable();
        $table = Request::get('table');
        // 表存在检查
        $errorTable = [];
        $tableList = explode(',', $table);
        foreach ($tableList as $tableItem) {
            if (!in_array($tableItem, $allTables)) {
                $errorTable[] = $tableItem;
            }
        }
        if (!empty($errorTable)) {
            return Response::error('table[' . $errorTable . '] 不存在');
        }
        $data = '';
        $tableAllInfoObject = [];
        foreach ($tableList as $table) {

            switch (Request::get('codetype', '')) {
                case 'apidoc':
                    $data .= AutoCode::apidoc(new DBTable($db, $table), ['response']);
                    break;
                case 'markdown_doc':
                    $data .= AutoCode::markdownDoc(new DBTable($db, $table), ['response']);
                    break;
                case 'code_model':
                    $data .= AutoCode::codeModel(new DBTable($db, $table), ['response']);
                    break;
                default:
                    $data .= '请选择 输出数据类型';
            }
        }
        return Response::item(['data' => $data]);
        break;
}
return Response::item($db->query("SELECT NOW() now")->row);
