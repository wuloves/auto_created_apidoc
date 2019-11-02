<?php

class Response
{

    public static function item($data)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo json_encode($data, 256);
        exit;
    }

    public static function error($message = null, $statusCode = 422)
    {

        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:*');
        //请求头
        header('Access-Control-Allow-Headers:*');
        // 响应头设置
        header('Access-Control-Allow-Credentials:false');

        switch ($statusCode) {
            case 200:
                header('HTTP/1.1 200 OK');
                break;
            case 401:
                header('HTTP/1.1 401 Unauthorized');
                break;
            case 422:
                header('HTTP/1.1 422 Unprocessable Entity');
                break;
            case 423:
                header('HTTP/1.1 423 Unprocessable Entity');
                break;
            default:
                $statusCode = 422;
                header('HTTP/1.1 422 Unprocessable Entity');
        }
        echo json_encode(['message' => $message, 'code' => 1, 'status_code' => $statusCode], 256);
        exit;
    }

    public static function jsonBeautify($json)
    {
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = NULL;
        if (is_array($json)){
            $json = json_encode($json, 256);
        }
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if ($ends_line_level !== NULL) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ($char === '"' && $prev_char != '\\') {
                $in_quotes = !$in_quotes;
            } else if (!$in_quotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            }
            if ($new_line_level !== NULL) {
                $result .= "\n" . str_repeat("    ", $new_line_level);
            }
            $result .= $char . $post;
            $prev_char = $char;
        }
        return $result;
    }

}
