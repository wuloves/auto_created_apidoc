<?php

class AutoCode
{


    public static function apidoc(DBTable $dbTable, $ext = [])
    {
        $fullFields = $dbTable->getFullFields();
        $tableName = $dbTable->getTableName();
        $tableMaxName = $dbTable->getModelName();
        $tableInfo = $dbTable->getTableInfo();
        $tableInfo['Comment'] = $dbTable->getTableComment();
        $tableField = $dbTable->getTableField();


        $needControllerFunctions = ['index', 'show', 'store', 'update', 'destory']; // 需要显示的方法路由表
        $apidocText = '';
        $responseShow = '';
        $responseIndex = '';
        if (in_array('response', $ext)) {
            $rowData = $dbTable->getRowData();
            if (!empty($rowData)) {
                $responseShow = Response::jsonBeautify($rowData, '     *');
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
                $responseIndex = Response::jsonBeautify($rowData2, '     *');
                unset($rowData);
                unset($rowData2);
            }
        }

        foreach ($needControllerFunctions as $needControllerFunction) {
            $apidocTextParam = '';
            $apidocTextSuccess = '';
            switch ($needControllerFunction) {
                case 'index':
                    $apidocText .= '
    /**
     * @api {get} /' . $tableName . ' 1.列表[' . $tableInfo['Comment'] . ']
     * @apiName index_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {number} [page=1] 当前页码
     * @apiParam {number} [perPage=15] 每页记录数';
                    //  * @apiParam {string} [include] 可选值：courses 信息(需要增加is_last=1作为条件)
                    foreach ($tableField as $field => $item) {
                        if (in_array($field, ['deleted_at', 'trans_table', 'trans_table_primary_key', 'trans_primary_id'])) {
                            continue;
                        }
                        $apidocTextParam .= PHP_EOL . '     * @apiParam {' . $item['type'] . '} [' . $field . '] ' . $item['comment'];
                        $apidocTextSuccess .= PHP_EOL . '     * @apiSuccess {' . $item['type'] . '} ' . $field . ' '
                            . $item['comment'] . ($item['type'] === 'varchar' && $item['length'] > 0 ? '<br>长度: <code>' . $item['length'] . '</code>' : '');
                    }
                    $apidocText .= $apidocTextParam . $apidocTextSuccess . '
     *
     * @apiSuccess {type} field desc
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 200 OK
' . $responseIndex . '
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
     * @api {post} /' . $tableName . ' 3.添加[' . $tableInfo['Comment'] . ']
     * @apiName store_' . $tableName . '
     * @apiGroup ' . $tableMaxName;
                    $apidocTextParam = '';
//                    foreach ($tableField as $field => $item) {
//                        if (in_array($field, ['updated_at', 'created_at'])) {
//                            continue;
//                        }
//                        $apidocTextParam .= PHP_EOL . '     * @apiParam {' . $item['type'] . '} ' . ((!empty($item['null']) || !is_null($item['default'])) ? '[' . $field . ']' : $field) . ' ' . $item['comment'];
//                    }

                    foreach ($tableField as $field => $item) {
                        if ($item['Key'] === 'PRI' || in_array($field, ['deleted_at', 'trans_table', 'trans_table_primary_key', 'trans_primary_id'])) {
                            continue;
                        }
                        $f = '[' . $field . ']';
                        if ($item['null'] == 0 && is_null($item['default'])) {
                            $f = $field;
                        }
                        if (!is_null($item['Default']) && $item['Default'] != '') {
                            $f = '[' . $field . '=' . $item['Default'] . ']';
                        }
//                        $apidocTextParam .= PHP_EOL . '     * @apiParam {' . $item['type'] . '} [' . $field . '] ' . $item['comment'];
                        $apidocTextParam .= PHP_EOL . '     * @apiParam {' . $item['type'] . '} ' . $f . ' '
                            . $item['comment'] . ($item['type'] === 'varchar' && $item['length'] > 0 ? '<br>长度: <code>' . $item['length'] . '</code>' : '');
                    }

                    $apidocText .= $apidocTextParam . '
     * @apiSuccess {type} field 默认同列表成功返回参数
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
     * @api {get} /' . $tableName . '/{id} 2.详情[' . $tableInfo['Comment'] . ']
     * @apiName show_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID
     * @apiSuccess {type} field 无特殊说明默认同列表成功返回参数
     * @apiSuccessExample 成功返回：
     * HTTP/1.1 200 OK
' . $responseShow . '
     *
     * @apiError {type} field desc
     * @apiErrorExample 失败返回：
     * HTTP/1.1 404 Not Found
     * 记录不存在
     *
     */' . PHP_EOL . PHP_EOL . PHP_EOL;
                    break;
                case 'update':
                    foreach ($tableField as $field => $item) {
                        if (in_array($field, ['deleted_at', 'trans_table', 'trans_table_primary_key', 'trans_primary_id'])) {
                            continue;
                        }
                        $apidocTextParam .= PHP_EOL . '     * @apiParam {' . $item['type'] . '} ' . '[' . $field . ']' . ' ' . $item['comment'];
                    }
                    $apidocText .= '
    /**
     * @api {patch} /' . $tableName . '/{id} 4.修改[' . $tableInfo['Comment'] . ']
     * @apiName update_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID [ 下面的参数都为可选,需要更新哪个传哪个,未列出来的默认不支持 ]
' . $apidocTextParam . '
     * @apiSuccess {type} field 默认同列表成功返回参数
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
     * @api {delete} /' . $tableName . '/{id} 5.删除[' . $tableInfo['Comment'] . ']
     * @apiName destory_' . $tableName . '
     * @apiGroup ' . $tableMaxName . '
     * @apiParam {int} id ' . $tableInfo['Comment'] . '的ID
     * @apiSuccess {type} field 成功无返回
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

    public static function markdownDoc(DBTable $dbTable, $ext = [])
    {
        $fullFields = $dbTable->getFullFields();
        $tableName = $dbTable->getTableName();
        $tableInfo = $dbTable->getTableInfo();
        $tableInfo['Comment'] = $dbTable->getTableComment();

        $needControllerFunctions = ['index', 'show', 'store', 'update', 'destory']; // 需要显示的方法路由表
        $baseUrl = '{{url}}/' . $tableName;
        if (isset($ext['apiprefix'])) {
            $baseUrl = $ext['apiprefix'] . $tableName;
        }
        $baseUrl = '{{url}}/course/' . $tableName;

        $priInfo = $dbTable->getPriFieldInfo();

        $apidocText = '';
        $responseShow = '';
        $responseIndex = '';
        if (in_array('response', $ext)) {
            $rowData = $dbTable->getRowData();
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

    public static function codeModel(DBTable $dbTable, $ext = [])
    {
        $fullFields = $dbTable->getFullFields();
        $tableInfo = $dbTable->getTableInfo();
        $tableMaxName = $dbTable->getModelName();
        $tableInfo['Comment'] = $dbTable->getTableComment();
        $deleteKey = $dbTable->getDeleteField();
        $castsText = '';
        foreach ($fullFields as $fullField) {
            if ($fullField['Type'] == 'json') {
                $castsText .= PHP_EOL . '        \'' . $fullField['Field'] . '\' => \'array\',';
            } else if (strpos($fullField['Type'], 'varchar') !== false) {
                // $castsText .= PHP_EOL . '        \'' . $fullField['Field'] . '\' => \'string\',';
            } else if (strpos($fullField['Type'], 'int') !== false) {
                // $castsText .= PHP_EOL . '        \'' . $fullField['Field'] . '\' => \'integer\',';
                $casts[$fullField['Field']] = 'int';
            } else if (strpos($fullField['Type'], 'timestamp') !== false || strpos($fullField['Type'], 'datetime') !== false) {
                // $castsText .= PHP_EOL . '        \'' . $fullField['Field'] . '\' => \'timestamp\',';
            } else if (strpos($fullField['Type'], 'decimal') !== false) {
                // $castsText .= PHP_EOL . '        \'' . $fullField['Field'] . '\' => \'decimal:' . explode(')', explode(',', $fullField['Type'])[1])[0] . '\',';
            }
        }

        $seAttributeText = '';
        foreach ($fullFields as $fullField) {
            if ($fullField['Key'] == 'PRI') {
                continue;
            }

            $field = $fullField['Field'];
            $fieldTF = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            if ($fullField['Type'] == 'json') {
                $seAttributeText .= PHP_EOL . '
    // ' . $field . '
    public function set' . $fieldTF . 'Attribute($value)
    {
        $this->attributes[\'' . $field . '\'] = json_encoded($value);
    }';
            } else if (strpos($fullField['Type'], 'int') !== false || strpos($fullField['Type'], 'bigint') !== false || strpos($fullField['Type'], 'decimal') !== false) {
                if ($fullField['Null'] == 'NO') {
                    $seAttributeText .= PHP_EOL . '
    // ' . $field . '
    public function set' . $fieldTF . 'Attribute($value)
    {
        $this->attributes[\'' . $field . '\'] = empty($value) ? 0 : $value;
    }';

                }
            } else if (strpos($fullField['Type'], 'varchar') !== false) {
                if ($fullField['Null'] == 'NO') {
                    $seAttributeText .= PHP_EOL . '
    // ' . $field . '
    public function set' . $fieldTF . 'Attribute($value)
    {
        $this->attributes[\'' . $field . '\'] = empty($value) ? \'\' : $value;
    }';
                }
            }
        }
        // 开始数据处理
        $codeText = file_get_contents(BASE_PATH . '/resources/laravel_template/TemplateMode.php');
        $fillable = [];
        foreach ($fullFields as $fullField) {
            $fillable [] = "        '" . $fullField['Field'] . "',";
        }
        $codeText = str_replace('\'{$fillable}\'', PHP_EOL . implode(PHP_EOL, $fillable), $codeText);
        $codeText = str_replace('TemplateMode', $tableMaxName, $codeText);
        if ($deleteKey) {
            $codeText = str_replace('use SoftDeletes;', 'use SoftDeletes;' . PHP_EOL, $codeText);
            $codeText = str_replace(PHP_EOL . 'use Hyperf\Database\Model\SoftDeletes;', PHP_EOL . PHP_EOL . 'use Hyperf\Database\Model\SoftDeletes;', $codeText);
            $codeText = str_replace('protected $datas = [\'deleted_at\'];', 'protected $datas = [\'' . $deleteKey . '\'];', $codeText);
        } else {
            $codeText = str_replace('use SoftDeletes;', '', $codeText);
            $codeText = str_replace(PHP_EOL . 'use Hyperf\Database\Model\SoftDeletes;', '', $codeText);
            $codeText = str_replace('protected $datas = [\'deleted_at\'];', '', $codeText);
        }
        $codeText = str_replace('\'{$castsText}\'', $castsText, $codeText);
        $codeText = str_replace('    public $seAttribute;', $seAttributeText, $codeText);
        return $codeText;
    }


}
