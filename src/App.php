<?php
namespace ExcelToSql;

use ExcelToSql\Helper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class App
{
    public static function run($param)
    {
        $config = Helper::ele($param, 'config', 'index');
        $excel = Helper::ele($param, 'excel', '../index.xlsx');
        $sqlFile = Helper::ele($param, 'sql', '../index.sql');
        $config = json_decode(file_get_contents($config), true);

        $excel = self::excelToArr($excel);
        $config = self::excelToConfig($excel, $config);
        $excel = self::excelToLine($excel, $config);
        $config = self::configToSimple($config);
        $sql = self::excelToSql($excel, $config);
        $sql = self::sqlToStr($sql, $config);
        file_put_contents($sqlFile, $sql);
    }

    public static function sqlToStr($sql, $config)
    {
        $fields = array_keys($config['fields']);
        $fields = implode('`, `', $fields);
        $sqls = array();
        foreach($sql as $value) {
            $sqls[] = 'INSERT INTO `' . $config['tableName'] . '` (`' . $fields . '`) VALUES' . implode(', ', $value) . ';';
        }
        $sqls = implode(PHP_EOL, $sqls);
        return $sqls;
    }

    public static function excelToSql($excel, $config)
    {
        $sqls = array();
        foreach ($config['fields'] as $key => $value) {
            $config['fields'][$key] = $value == 'string' ? '"' : '';
        }
        $i = 0;
        $tmpSqls = array();
        foreach ($excel as $key => $value) {
            if ($i >= $config['maxInsert']) {
                $sqls[] = $tmpSqls;
                $i = 0;
                $tmpSqls = array();
            }
            foreach($value as $vkey => $vvalue) {
                $value[$vkey] = $config['fields'][$vkey] . $vvalue . $config['fields'][$vkey];
            }
            $tmpSqls[] = '(' . implode(', ', $value) . ')';
            $i++;
        }
        $sqls[] = $tmpSqls;
        return $sqls;
    }

    public static function configToSimple($config)
    {
        $fields = array();
        foreach ($config['database']['fields'] as $value) {
            if ( ! (is_null($value['excel']) && is_null($value['default']))) {
                $fields[$value['name']] = $value['type'];
            }
        }
        $config = array(
            'maxInsert' => $config['maxInsert']['num'],
            'tableName' => $config['database']['tableName'],
            'fields' => $fields,
        );
        if ($config['maxInsert'] == 0) {
            $config['maxInsert'] = 100000;
        }
        return $config;
    }

    public static function excelToLine($excel, $config)
    {
        $lines = array();
        for ($i = $config['excel']['startLine'], $len = count($excel) + 1; $i < $len; $i++) {
            $line = array();
            foreach ($config['database']['fields'] as $key => $value) {
                if (is_null($value['excel']) && is_null($value['default'])) {
                    continue;
                }
                if ( ! is_null($value['excel'])) {
                    if ($value['type'] == 'int' && $excel[$i][$value['excel']] == '') {
                        $line[$value['name']] = floatval($value['default']);
                    } else {
                        $line[$value['name']] = $excel[$i][$value['excel']];
                    }
                } else {
                    $line[$value['name']] = $value['default'];
                }
            }
            $lines[] = $line;
        }
        return $lines;
    }

    public static function excelToConfig($excel, $config)
    {
        $headline = $config['excel']['headLine'];
        if ($headline == 0) {
            return $config;
        }
        $head = $excel[$headline];
        foreach ($config['database']['fields'] as $key => $value) {
            $flag = true;
            foreach ($excel[$headline] as $ekey => $evalue) {
                if ($evalue == $value['excel']) {
                    $config['database']['fields'][$key]['excel'] = $ekey;
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $config['database']['fields'][$key]['excel'] = null;
            }
        }
        return $config;
    }

    public static function excelToArr($filename)
    {
        $spreadsheet = IOFactory::load($filename);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        return $sheetData;
    }
}