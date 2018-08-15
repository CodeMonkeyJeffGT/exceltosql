#!/usr/local/bin/php
<?php

use ExcelToSql\App;
use ExcelToSql\Helper;

include 'vendor/autoload.php';

if (isset($argc)) {
    $excel = Helper::ele($argv, 1, 'index.xlsx');
    $sql = Helper::ele($argv, 2, 'index.sql');
    $config = 'src/config/' . Helper::ele($argv, 3, 'index') . '.json';
    $excel = Helper::fileFullName($excel);
    $sql = Helper::fileFullName($sql, true);
    $config = Helper::fileFullName($config);
    App::run(array(
        'excel'  => $excel,
        'sql'    => $sql,
        'config' => $config,
    ));
} else {
    ob_clean();
    echo '请执行 ./index.php [待读取excel文件 [生成sql路径 [配置文件名称]]]' . '<br />';
    echo '如： ./index.php index.xlsx index.sql index';
}

?>