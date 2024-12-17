<?php


namespace plugin\library\extend;


class DistanceExtend
{
    /**
     * 计算两个经纬度坐标之间的距离（单位：千米）
     * @param array $location1
     * @param array $location2
     * @return float
     */
    public static function distance(array $location1, array $location2): float
    {
        $lon1 = deg2rad($location1['lng']);
        $lat1 = deg2rad($location1['lat']);
        $lon2 = deg2rad($location2['lng']);
        $lat2 = deg2rad($location2['lat']);
        $delta_lon = $lon2 - $lon1;
        $delta_lat = $lat2 - $lat1;
        $a = sin($delta_lat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($delta_lon / 2) ** 2;
        $c = 2 * asin(sqrt($a));
        return round((6371 * $c * 1000) / 1000, 2);
    }

    /**
     * 计算一组经纬度之间的距离并返回距离数组
     * @param array $locations
     * @return array
     */
    public static function distanceCalculation(array $locations): array
    {
        $distances = [];
        for ($i = 0; $i < count($locations) - 1; $i++) {
            $distance = self::distance($locations[$i], $locations[$i + 1]);
            $distances[] = $distance;
        }
        return $distances;
    }

    /**
     * 对一组经纬度按照距离排序并返回排序后的数组和距离数组
     * @param array $locations
     * @return array
     */
    public static function distanceSorting(array $locations): array
    {
        usort($locations, function ($a, $b) use ($locations) {
            $distanceA = $a === $locations[0] ? 0 : self::distance($locations[0], $a);
            $distanceB = $b === $locations[0] ? 0 : self::distance($locations[0], $b);
            return $distanceA <=> $distanceB;
        });
        $distances = [];
        for ($i = 0; $i < count($locations) - 1; $i++) {
            $distance = self::distance($locations[$i], $locations[$i + 1]);
            $distances[] = $distance;
        }
        return [$locations, $distances];
    }


    /**
     * 判断一组坐标是否在区域坐标集中
     * @param string $coordinates 区域坐标集
     * @param array $list 检查坐标
     * @return array
     */
    public static function CoordinateFormatting(string $coordinates,array $list)
    {
        $coordinates = self::StringTtoArray($coordinates);
        return self::checkCoordinate($coordinates,$list);
    }


    /**
     * 区域坐标转换格式
     * @param string $coordinates
     * @return array
     */
    public static function StringTtoArray(string $coordinates)
    {
        // 将字符串坐标集分割为数组
        $coordinatesArray = explode(",", $coordinates);
        // 创建一个空数组来存储键值对
        $result = array();
        // 使用循环将每个坐标添加到结果数组中
        for ($i = 0; $i < count($coordinatesArray); $i += 2) {
            $lng = $coordinatesArray[$i];
            $lat = $coordinatesArray[$i + 1];
            $result[] = array('lng' => $lng, 'lat' => $lat);
        }
        return $result;
    }

    /**
     * 检查坐标：最后，你可以使用循环来检查每个坐标是否在区域内部
     * @param array $areaCoordinates 定义区域坐标集
     * @param array $checkCoordinates 定义要检查的坐标
     * @return array
     */
    public static function checkCoordinate(array $areaCoordinates,array $checkCoordinates)
    {
        $filteredCoordinates = array();
        foreach ($checkCoordinates as $coordinate) {
            if (self::isCoordinateInArea($coordinate, $areaCoordinates)) {
                $filteredCoordinates[] = $coordinate;
            }
        }
        return $filteredCoordinates;
    }


    /**
     * 来判断坐标是否在区域内部。
     * @param $coordinate
     * @param $areaCoordinates
     * @return bool
     */
    public static function isCoordinateInArea($coordinate, $areaCoordinates) {
        $x = $coordinate['lng'];
        $y = $coordinate['lat'];
        $isInside = false;
        $j = count($areaCoordinates) - 1;
        for ($i = 0; $i < count($areaCoordinates); $i++) {
            if (($areaCoordinates[$i]['lat'] > $y) != ($areaCoordinates[$j]['lat'] > $y) &&
                ($x < ($areaCoordinates[$j]['lng'] - $areaCoordinates[$i]['lng']) * ($y - $areaCoordinates[$i]['lat']) /
                    ($areaCoordinates[$j]['lat'] - $areaCoordinates[$i]['lat']) + $areaCoordinates[$i]['lng'])) {
                $isInside = !$isInside;
            }
            $j = $i;
        }
        return $isInside;
    }
}