<?php

class AutoCode
{

    public static function apidoc($tableAllInfo, $ext = [])
    {
        $fullFields = $tableAllInfo['full_fields'];
        $tableName = $tableAllInfo['table_name'];
        $tableMaxName = preg_replace_callback('/_+([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $tableName);
        $tableMaxName = ucfirst($tableMaxName);
        $tableInfo = $tableAllInfo['table_info'];
        $tableInfo['Comment'] = str_replace('表', '', $tableInfo['Comment']);
        if (empty($tableInfo['Comment'])) {
            $tableInfo['Comment'] = $tableMaxName;
        }
        $needControllerFunctions = ['index', 'show', 'store', 'update', 'destory']; // 需要显示的方法路由表
        $apidocText = '';
        $responseShow = '';
        $responseIndex = '';
        if (in_array('response', $ext)) {
            $rowData = $tableAllInfo['db']->query(" SELECT * FROM `" . $tableName . "` LIMIT 0,1")->row;
            if (!empty($rowData)) {
                $responseShow = Response::jsonBeautify($rowData);
                $rowData2 = [
                    'data' => [
                        $rowData
                    ],
                    'meta' => [
                        'params' => [],
                        'pagination' => [
                            "total" => 43,
                            "count" => 15,
                            "per_page" => 15,
                            "current_page" => 15,
                            "total_pages" => 3
                        ]
                    ]
                ];
                $responseIndex = Response::jsonBeautify($rowData2);
                unset($rowData);
                unset($rowData2);
            }
        }

        foreach ($needControllerFunctions as $needControllerFunction) {
            switch ($needControllerFunction) {
                case 'index':
                    $apidocText .= '
    /**
     * @api {get} /' . $tableName . ' ' . $tableInfo['Comment'] . '
     * @apiName index_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {number} [perPage=15] 每页记录数';
                    //  * @apiParam {string} [include] 可选值：courses 信息(需要增加is_last=1作为条件)
                    foreach ($fullFields as $item) {
                        $field = $item['Field'];
                        $type = $item['Type'];
                        $comment = $item['Comment'];
                        if (in_array($field, ['updated_at', 'created_at'])) {
                            continue;
                        }
                        $datatypeLength = 0;
                        if (count(explode('(', $type)) > 1) {
                            $datatype = explode('(', $type)[0];
                            $datatypeLength = str_replace(')', '', explode('(', $type)[1]);
                            if (str_replace(',', '', $datatypeLength) != $datatypeLength) {
                                $datatypeLength = 0;
                            }
                        } else {
                            $datatype = $type;
                        }
                        if ($item['Key'] == 'PRI') {
                            $apidocText .= PHP_EOL . '     * @apiParam {' . $datatype . '} ' . $field . ' ' . $tableInfo['Comment'] . '的ID';
                        } else if (is_null($item['Default'])) {
                            $apidocText .= PHP_EOL . '     * @apiParam {' . $datatype . '} ' . $field . ' ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '长度0-' . $datatypeLength : '');
                        } else {
                            $apidocText .= PHP_EOL . '     * @apiParam {' . $datatype . '} [' . $field . (is_null($item['Default']) ? '' : '=' . (is_numeric($item['Default']) ? $item['Default'] * 1 : $item['Default'])) . '] ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '长度0-' . $datatypeLength : '');
                        }
                    }
                    $apidocText .= '
     *
     * @apiSuccess {type} field desc
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 200 OK
     * ' . $responseIndex . '
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 文档更新于 [' . date("Y-m-d H:i:s") . ']
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'store':
                    $apidocText .= '
    /**
     * @api {post} /' . $tableName . ' 添加' . $tableInfo['Comment'] . '
     * @apiName store_' . $tableName . '
     * @apiGroup ' . $tableMaxName;
                    foreach ($fullFields as $item) {
                        $field = $item['Field'];
                        $type = $item['Type'];
                        $comment = $item['Comment'];
                        if (in_array($field, ['updated_at', 'created_at'])) {
                            continue;
                        }
                        $datatypeLength = 0;
                        if (count(explode('(', $type)) > 1) {
                            $datatype = explode('(', $type)[0];
                            $datatypeLength = str_replace(')', '', explode('(', $type)[1]);
                            if (str_replace(',', '', $datatypeLength) != $datatypeLength) {
                                $datatypeLength = 0;
                            }
                        } else {
                            $datatype = $type;
                        }
                        if (is_null($item['Default']) && $item['Key'] == '') {
                            $apidocText .= PHP_EOL . '     * @apiParam {' . $datatype . '} ' . $field . ' ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '长度0-' . $datatypeLength : '');
                        } else if (!empty($item['Default'])) {
                            $apidocText .= PHP_EOL . '     * @apiParam {' . $datatype . '} [' . $field . (is_null($item['Default']) ? '' : '=' . $item['Default']) . '] ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '长度0-' . $datatypeLength : '');
                        }
                    }
                    $apidocText .= '
     * @apiSuccess {type} field 默认同资源详情
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 201 Created
     * 默认同详情
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 记录不存在
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;

                    break;

                case 'show':
                    $apidocText .= '
    /**
     * @api {get} /' . $tableName . '/{id} ' . $tableInfo['Comment'] . '详情
     * @apiName show_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID
     * @apiSuccess {type} field 默认同资源详情
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 200 OK
     * ' . $responseShow . '
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 记录不存在
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'update':
                    $apidocText .= '
    /**
     * @api {patch} /' . $tableName . '/{id} 更新' . $tableInfo['Comment'] . '
     * @apiName update_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID
     * @apiSuccess {type} field 默认同资源详情
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 200 OK
     * 默认同详情
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 记录不存在
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'destory':
                    $apidocText .= '
    /**
     * @api {delete} /' . $tableName . '/{id} 删除' . $tableInfo['Comment'] . '
     * @apiName destory_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID
     * @apiSuccess {type} field 默认同资源详情
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 204 OK
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 记录不存在
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
            }
        }
        return $apidocText;
    }

    public static function markdownDoc($tableAllInfo, $ext = [])
    {
        $fullFields = $tableAllInfo['full_fields'];
        $tableName = $tableAllInfo['table_name'];
        $tableMaxName = preg_replace_callback('/_+([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $tableName);
        $tableMaxName = ucfirst($tableMaxName);
        $tableInfo = $tableAllInfo['table_info'];
        $tableInfo['Comment'] = str_replace('表', '', $tableInfo['Comment']);
        if (empty($tableInfo['Comment'])) {
            $tableInfo['Comment'] = $tableMaxName;
        }
        $needControllerFunctions = ['index', 'show', 'store', 'update', 'destory']; // 需要显示的方法路由表
        $baseUrl = '{{url}}/' . $tableName;
        if (isset($ext['apiprefix'])) {
            $baseUrl = $ext['apiprefix'] . $tableName;
        }
        $baseUrl = '{{url}}/course/' . $tableName;

        $priInfo = self::getPriKeyInfo($fullFields);

        $apidocText = '';
        $responseShow = '';
        $responseIndex = '';
        if (in_array('response', $ext)) {
            $rowData = $tableAllInfo['db']->query(" SELECT * FROM `" . $tableName . "` LIMIT 0,1")->row;
            if (!empty($rowData)) {
                $responseShow = Response::jsonBeautify($rowData);
                $rowData2 = [
                    'data' => [
                        $rowData
                    ],
                    'meta' => [
                        'params' => [],
                        'pagination' => [
                            "total" => 43,
                            "count" => 15,
                            "per_page" => 15,
                            "current_page" => 15,
                            "total_pages" => 3
                        ]
                    ]
                ];
                $responseIndex = Response::jsonBeautify($rowData2);
                unset($rowData);
                unset($rowData2);
            }
        }
        $apidocText .= '## ' . $tableInfo['Comment'] . PHP_EOL;


        // $needControllerFunctions = [ 'show']; // 需要显示的方法路由表
        foreach ($needControllerFunctions as $needControllerFunction) {
            switch ($needControllerFunction) {
                case 'index':
                    $apidocText .= '
### ' . $tableInfo['Comment'] . '列表              

*   **请求URL**

```
GET
' . $baseUrl . '
```

*   **成功返回**


| 字段 | 类型 | 描述 |
| -------- | ----- | ----- |
| perPage | number | 每页记录数<br>默认值: <code style="color: #c7254e;font-size: 90%;border-radius: 4px;padding: 2px 4px;">15</code> |';
                    foreach ($fullFields as $item) {
                        $field = $item['Field'];
                        $type = $item['Type'];
                        $comment = $item['Comment'];
                        if (in_array($field, ['updated_at', 'created_at'])) {
                            continue;
                        }
                        $datatypeLength = 0;
                        if (count(explode('(', $type)) > 1) {
                            $datatype = explode('(', $type)[0];
                            $datatypeLength = str_replace(')', '', explode('(', $type)[1]);
                            if (str_replace(',', '', $datatypeLength) != $datatypeLength) {
                                $datatypeLength = 0;
                            }
                        } else {
                            $datatype = $type;
                        }
                        if ($item['Key'] == 'PRI') {
                            $apidocText .= PHP_EOL . '| ' . $field . ' | ' . $datatype . ' | ' . $tableInfo['Comment'] . '的ID |';
                        } else if (is_null($item['Default'])) {
                            $apidocText .= PHP_EOL . '| ' . $field . ' | ' . $datatype . ' | ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '<br>长度: <code>' . $datatypeLength . '</code>' : '') . '|';
                        } else {
                            $apidocText .= PHP_EOL . '| ' . $field . ' | ' . $datatype . ' | ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '<br>长度: <code>' . $datatypeLength . '</code>' : '') . ((is_null($item['Default']) || $item['Default'] == '') ? '' : '<br>默认值: <code style="color: #c7254e;font-size: 90%;border-radius: 4px;padding: 2px 4px;">' . (is_numeric($item['Default']) ? $item['Default'] * 1 : $item['Default']) . '</code>') . ' |';
                        }
                    }
                    $apidocText .= '

*   **返回demo**

```
Success 200
HTTP/1.1 200 OK
' . $responseIndex . '

失败返回：
HTTP/1.1 404 Not Found
文档更新于 [' . date("Y-m-d H:i:s") . ']
```
' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'store':
                    $apidocText .= '
### 添加' . $tableInfo['Comment'] . '              

*   **请求URL**

```
POST
' . $baseUrl . '
```

| 字段 | 类型 | 描述 |
| -------- | ----- | ----- |';
                    foreach ($fullFields as $item) {
                        $field = $item['Field'];
                        $type = $item['Type'];
                        $comment = $item['Comment'];
                        if (in_array($field, ['updated_at', 'created_at'])) {
                            continue;
                        }
                        $datatypeLength = 0;
                        if (count(explode('(', $type)) > 1) {
                            $datatype = explode('(', $type)[0];
                            $datatypeLength = str_replace(')', '', explode('(', $type)[1]);
                            if (str_replace(',', '', $datatypeLength) != $datatypeLength) {
                                $datatypeLength = 0;
                            }
                        } else {
                            $datatype = $type;
                        }
                        if (is_null($item['Default']) && $item['Key'] == '') {
                            $apidocText .= PHP_EOL . '| ' . $field . ' | ' . $datatype . ' | ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '<br>长度: <code>' . $datatypeLength . '</code>' : '') . '|';
                        } else if (!empty($item['Default'])) {
                            $apidocText .= PHP_EOL . '| ' . $field . ' | ' . $datatype . ' | ' . ($comment == '' ? $field : $comment) . ($datatype == 'varchar' && $datatypeLength > 0 ? '<br>长度: <code>' . $datatypeLength . '</code>' : '') . ((is_null($item['Default']) || $item['Default'] == '') ? '' : '<br>默认值: <code style="color: #c7254e;font-size: 90%;border-radius: 4px;padding: 2px 4px;">' . (is_numeric($item['Default']) ? $item['Default'] * 1 : $item['Default']) . '</code>') . ' |';
                        }
                    }
                    $apidocText .= '

*   **返回demo**

```
Success 200
HTTP/1.1 200 OK
' . $responseShow . '

失败返回：
HTTP/1.1 404 Not Found
文档更新于 [' . date("Y-m-d H:i:s") . ']
```
' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'show':
                    $apidocText .= '
### ' . $tableInfo['Comment'] . '详情

*   **请求URL**

```
GET
' . $baseUrl . '/{' . (empty($priInfo['Field']) ? 'id' : $priInfo['Field']) . '}
```

*   **成功返回**


| 字段 | 类型 | 描述 |
| -------- | ----- | ----- |
' . (!empty($priInfo['Field']) ? '| ' . $priInfo['Field'] . ' | ' . $priInfo['Type'] . ' | ' . (empty($priInfo['Comment']) ? $tableInfo['Comment'] . '的ID' : $priInfo['Comment']) . ' |' : '');
                    $apidocText .= '

*   **返回demo**

```
Success 200
HTTP/1.1 200 OK
' . $responseShow . '

失败返回：
HTTP/1.1 404 Not Found
文档更新于 [' . date("Y-m-d H:i:s") . ']
```
' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'update':
                    $apidocText .= '
### 修改' . $tableInfo['Comment'] . '             

*   **请求URL**

```
PATCH
' . $baseUrl . '/{' . (empty($priInfo['Field']) ? 'id' : $priInfo['Field']) . '}
```

*   **参数请参考列表的成功返回字段**

*   **返回demo**

```
Success 200
HTTP/1.1 200 OK
同详情

失败返回：
HTTP/1.1 404 Not Found
```
' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'destory':


                    $apidocText .= '
### 删除' . $tableInfo['Comment'] . '             

*   **请求URL**

```
DELETE
' . $baseUrl . '/{' . (empty($priInfo['Field']) ? 'id' : $priInfo['Field']) . '}
```

*   **返回demo**

```
Success
HTTP/1.1 204 OK

失败返回：
HTTP/1.1 404 Not Found
记录不存在
```
' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
            }
        }
        return $apidocText;
    }

    private static function getPriKeyInfo($fullFields)
    {
        foreach ($fullFields as $item) {
            if ($item['Key'] == 'PRI') {
                return $item;
                break;
            }
        }
    }


}
