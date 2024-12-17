<?php


class ArrayExtend
{

    public static function arrayToList(array $fields, array $array)
    {
        foreach ($fields as $field) {
            $result[$field] = implode(',', array_column($array, $field));
        }
        return $result ?? [];
    }
}