<?php

for ($port = 4000; $port < 10000; $port++) {
    $hasPrograme = exec('netstat -ano|findstr ' . $port);
    if (empty($hasPrograme)) {
        exec('start "" http://127.0.0.1:' . $port);
        exec('php -S 0.0.0.0:' . $port);
    }
}
