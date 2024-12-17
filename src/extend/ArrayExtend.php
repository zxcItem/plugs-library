<?php

namespace think\admin\extend;

class ArrayExtend
{

    public static function ArrayToList(array $fields, array $array)
    {
        foreach ($fields as $field) {
            $result[$field] = implode(',', array_column($array, $field));
        }
        return $result ?? [];
    }
}