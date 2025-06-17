<?php

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');


$portName = '\\\\.\\COM10';      
$rawPort  = 'COM10';            
$baudRate = 9600;
$sensorId = 3;                  

$db = [
    'host' => 'bddprojetcommun.mysql.database.azure.com',
    'user' => 'adminprojet',
    'pass' => '9UVxldpsUF&4',
    'name' => 'projetcommun',
    'port' => 3306
];

$cmd = sprintf('mode %s: BAUD=%d PARITY=N DATA=8 STOP=1', $rawPort, $baudRate);
exec($cmd, $modeOut, $modeRet);
if ($modeRet !== 0) {
    echo "⚠️  mode command failed, please check if the serial port number is correct：$rawPort\n";
}


$fd = dio_open($portName, O_RDWR);
if (!$fd) {
    exit("❌ Cannot open COM $portName\n");
}
echo "✅ COM $portName Already opean，BaudRate $baudRate\n";


$mysqli = new mysqli($db['host'], $db['user'], $db['pass'],$db['name'], $db['port']);

if ($mysqli->connect_error) {
    exit("❌ MySQL connecter failure：".$mysqli->connect_error."\n");
}
$mysqli->query("SET time_zone = '+02:00'");
echo "✅ Already connecter BDD：".$mysqli->host_info."\n";

$stmt = $mysqli->prepare(
    "INSERT INTO sensorData (sensorId, timeRecorded, value) VALUES (?, NOW(), ?)"
);
if (!$stmt) {
    exit("❌ prepare failure：".$mysqli->error."\n");
}


echo "Listen $portName @ $baudRate，Ctrl+C Out\n";
$buffer = '';
while (true) {
    $chunk = dio_read($fd, 256);
    if ($chunk !== '' && $chunk !== false) {
        $buffer .= $chunk;

        while (preg_match('/^(.*?)(\r?\n)/', $buffer, $m)) {
            $line   = trim($m[1]);
            $buffer = substr($buffer, strlen($m[0]));

            if (stripos($line, 'distance:') === 0) {           
                if (preg_match('/([-+]?[0-9]*\.?[0-9]+)/', $line, $num)) {
                    $val = floatval($num[1]);                  
                    $stmt->bind_param('id', $sensorId, $val);
                    if ($stmt->execute()) {
                        echo date('H:i:s')."  ➜ $val cm Already wirtten in \n";
                    } else {
                        echo date('H:i:s')."  ❌ Failure write：".$stmt->error."\n";
                    }
                }
            }
        
        }
    } else {
        usleep(200000);
    }
}
