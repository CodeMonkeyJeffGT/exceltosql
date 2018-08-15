<?php
namespace ExcelToSql;

class Helper
{
    public static function ele($arr, $name, $default = null)
    {
        if (isset($arr[$name])) {
            return $arr[$name];
        } else {
            return $default;
        }
    }

    public static function fileFullName($file, $create = false)
    {
        if ($create) {
            if (file_exists($file)) {
                echo $file . ' 已存在，是否重新生成?(y/n)：';
                if (fgetc(STDIN) != 'y') {
                    static::endPut();
                }
                fgets(STDIN);
            }
            touch($file);
        }
        if ( ! file_exists($file)) {
            Helper::endPut('文件 ' . $file . ' 不存在');
        } else {
            return realpath($file);
        }
    }

    public static function endPut($output = '程序结束')
    {
        echo $output;
        echo PHP_EOL;
        die;
    }
}
