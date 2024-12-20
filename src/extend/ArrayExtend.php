<?php

namespace plugin\library\extend;

/**
 * 数组转换
 * Class ArrayExtend
 * @package plugin\library\extend
 */
class ArrayExtend
{

    /**
     * 数组转换每个字段以逗号分割组成字符串
     * [{"name": "name1","age": 23},{"name": "name2","age": 20},{"name": "name3","age": 18}
     * "name": "name1,name2,name3","age": "23,20,18"
     * @param array $fields
     * @param array $array
     * @return array
     */
    public static function ArrayToList(array $fields, array $array):array
    {
        foreach ($fields as $field) {
            $result[$field] = implode(',', array_column($array, $field));
        }
        return $result ?? [];
    }


    /**
     * 数组转换字符串以自定义符号隔开
     * [{"name": "name1","age": 23},{"name": "name2","age": 20},{"name": "name3","age": 18}
     * name1,23;name2,20;name3,18
     * @param array $array
     * @param string $sym1
     * @param string $sym2
     * @return string
     */
    public static function ArrayToString(array $array,string $sym1 = ',',string $sym2 = ';' ): string
    {
        return implode($sym2, array_map(function ($item) use( $sym1 ) {
            return implode($sym1, $item);
        }, $array));
    }

    /**
     * 多维数组转换字符串
     * @param array $array
     * @param string $key
     * @param string $value
     * @param string $sym1
     * @param string $sym2
     * @return string
     */
    public static function ArrayConvert(array $array,string $key,string $value,string $sym1 = ',',string $sym2 = ';' )
    {
        return implode($sym2, array_map(function ($item) use ($key,$value,$sym1) {
            return implode($sym1, array_column($item[$key], $value));
        }, $array));
    }

    /**
     * 数组排序
     * @param array $array
     * @param string $field
     * @return array
     */
    public static function ArraySort(array $array,string $field)
    {
        usort($array, function($a, $b) use ($field) {
            return $b[$field] <=> $a[$field];
        });
        return $array;
    }
}