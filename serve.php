<?php

if (isset($_SERVER['OS']) && strpos($_SERVER['OS'], 'Windows') !== false) {
    echo '系统类型: ' . $_SERVER['OS'] . PHP_EOL;
    for ($port = 4000; $port < 10000; $port++) {
        $hasPrograme = exec('netstat -ano|findstr ' . $port);
        if (empty($hasPrograme)) {
            exec('start "" http://127.0.0.1:' . $port);
            exec('php -S 127.0.0.1:' . $port);
        }
    }
} else {
    // mac下
    $port = 4000;
    exec('open "http://127.0.0.1:' . $port . '"');
    exec('php -S 127.0.0.1:' . $port);
}
