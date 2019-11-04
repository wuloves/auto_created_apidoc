<?php

if (empty($_SERVER)) {
    for ($port = 4000; $port < 5000; $port++) {
        $hasPrograme = exec('lsof -i:' . $port);
        if (empty($hasPrograme)) {
            exec('start "" http://127.0.0.1:' . $port);
            exec('php -S 0.0.0.0:' . $port);
        }
    }
} else if (strpos($_SERVER['OS'], 'Windows') !== false) {
    echo '系统类型: ' . $_SERVER['OS'] . PHP_EOL;
    for ($port = 4000; $port < 10000; $port++) {
        $hasPrograme = exec('netstat -ano|findstr ' . $port);
        if (empty($hasPrograme)) {
            exec('start "" http://127.0.0.1:' . $port);
            exec('php -S 0.0.0.0:' . $port);
        }
    }
} else {
    exec('start "" http://127.0.0.1:' . $port);
    exec('php -S 0.0.0.0:' . $port);
}
