<?php


namespace plugin\library\extend;


class CoordinateExtend
{
    const X_PI = 3.14159265358979324 * 3000.0 / 180.0;
    const PI = 3.1415926535897932384626;
    const A = 6378245.0;
    const EE = 0.00669342162296594323;
    const M_PI = 3.1415926535898;
    const R2D = 57.29577951308232; // 弧度转角度的常数
    const LON_PER_DEGREE = 111319.4907932722; // 每度经度对应距离（米）
    const D2R = 0.017453292519943295; // 角度转弧度的常数
    const EARTH_RADIUS_2 = 6378137.0; // 地球赤道半径（单位：米）
    const BD09_LNG_OFFSET = 0.0065;
    const BD09_LAT_OFFSET = 0.006;
    const MCBAND = [12890594.86, 8362377.87, 5591021, 3481989.83, 1678043.12, 0];
    const MC2LL = [
        [1.410526172116255e-8, 0.00000898305509648872, -1.9939833816331, 200.9824383106796, -187.2403703815547, 91.6087516669843, -23.38765649603339, 2.57121317296198, -0.03801003308653, 17337981.2],
        [-7.435856389565537e-9, 0.000008983055097726239, -0.78625201886289, 96.32687599759846, -1.85204757529826, -59.36935905485877, 47.40033549296737, -16.50741931063887, 2.28786674699375, 10260144.86],
        [-3.030883460898826e-8, 0.00000898305509983578, 0.30071316287616, 59.74293618442277, 7.357984074871, -25.38371002664745, 13.45380521110908, -3.29883767235584, 0.32710905363475, 6856817.37],
        [-1.981981304930552e-8, 0.000008983055099779535, 0.03278182852591, 40.31678527705744, 0.65659298677277, -4.44255534477492, 0.85341911805263, 0.12923347998204, -0.04625736007561, 4482777.06],
        [3.09191371068437e-9, 0.000008983055096812155, 0.00006995724062, 23.10934304144901, -0.00023663490511, -0.6321817810242, -0.00663494467273, 0.03430082397953, -0.00466043876332, 2555164.4],
        [2.890871144776878e-9, 0.000008983055095805407, -3.068298e-8, 7.47137025468032, -0.00000353937994, -0.02145144861037, -0.00001234426596, 0.00010322952773, -0.00000323890364, 826088.5]
    ];

    /**
     * 批量 WGS84 坐标系转 WGS84 墨卡托投影坐标系
     * @param array $points WGS84 坐标数组，每个元素包含经度和纬度
     * @return array 转换后的墨卡托坐标数组，每个元素包含横坐标和纵坐标
     */
    public static function wgs84ToMercatorBatch(array $points): array
    {
        foreach ($points as &$point) [$point['lng'], $point['lat']] = self::wgs84ToWebMercator($point['lng'], $point['lat']);
        return $points;
    }

    /**
     * 批量 BD09墨卡托 坐标系转 WGS84 坐标系墨卡托投影坐标系
     * @param array $points BD09 坐标数组，每个元素包含经度和纬度
     * @return array 转换后的 WGS84 坐标系墨卡托投影坐标数组，每个元素包含经度和纬度
     */
    public static function bd09McToWgs84MercatorBatch(array &$points): array
    {
        foreach ($points as &$point) [$point['lng'], $point['lat']] = self::bd09mcToWgs84Mercator($point['lng'], $point['lat']);
        return $points;
    }

    /**
     * 批量 BD09 坐标系转 WGS84 坐标系墨卡托投影坐标系
     * @param array $points BD09 坐标数组，每个元素包含经度和纬度
     * @return array 转换后的 WGS84 坐标系墨卡托投影坐标数组，每个元素包含经度和纬度
     */
    public static function bd09ToWgs84MercatorBatch(array &$points): array
    {
        foreach ($points as &$point) [$point['lng'], $point['lat']] = self::bd09ToWgs84Mercator($point['lng'], $point['lat']);
        return $points;
    }

    /**
     * 批量 GCJ02 坐标系转 WGS84 墨卡托投影坐标系
     * @param array $points GCJ02 坐标数组，每个元素包含经度和纬度
     * @return array 转换后的 WGS84 墨卡托投影坐标数组，每个元素包含经度和纬度
     */
    public static function gcj02ToWgs84MercatorBatch(array &$points): array
    {
        foreach ($points as &$point) [$point['lng'], $point['lat']] = self::gcj02ToWgs84Mercator($point['lng'], $point['lat']);
        return $points;
    }

    /**
     * WGS84 坐标转 WGS84 Web Mercator 投影坐标 TODO 结果已验证
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return array 转换后的墨卡托坐标数组
     */
    public static function wgs84ToWebMercator(float $lng, float $lat): array
    {
        $x = $lng * self::LON_PER_DEGREE;
        $y = log(tan((90 + $lat)*pi()/360)) / (pi()/180);
        $y = $y * self::LON_PER_DEGREE;
        return [$x,$y];
    }


    /**
     * WGS84 坐标转 GCJ02 TODO 结果已验证
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return array 转换后的经纬度数组
     */
    public static function wgs84ToGcj02(float $lng, float $lat): array
    {
        if (self::outOfChina($lng, $lat)) return [$lng, $lat];
        $dLat = self::transformLat($lng - 105.0, $lat - 35.0);
        $dLng = self::transformLng($lng - 105.0, $lat - 35.0);
        $radLat = $lat / 180.0 * self::M_PI;
        $magic = sin($radLat);
        $magic = 1 - self::EE * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / ((self::A * (1 - self::EE)) / ($magic * $sqrtMagic) * self::M_PI);
        $dLng = ($dLng * 180.0) / (self::A / $sqrtMagic * cos($radLat) * self::M_PI);
        $mgLat = $lat + $dLat;
        $mgLng = $lng + $dLng;
        return [$mgLng, $mgLat];
    }

    /**
     * GCJ02 坐标转 WGS84 TODO 结果已验证 偏差 0.9米
     * @param float $longitude 经度
     * @param float $latitude 纬度
     * @return array 转换后的经纬度数组
     */
    public static function gcj02ToWgs84(float $longitude, float $latitude): array
    {
        if (self::outOfChina($longitude, $latitude)) return [$longitude, $latitude];
        $dLat = self::transformLat($longitude - 105.0, $latitude - 35.0);
        $dLng = self::transformLng($longitude - 105.0, $latitude - 35.0);
        $radLat = $latitude / 180.0 * self::M_PI;
        $magic = sin($radLat);
        $magic = 1 - self::EE * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / ((self::A * (1 - self::EE)) / ($magic * $sqrtMagic) * self::M_PI);
        $dLng = ($dLng * 180.0) / (self::A / $sqrtMagic * cos($radLat) * self::M_PI);
        $mgLat = $latitude - $dLat;
        $mgLng = $longitude - $dLng;
        return [$mgLng, $mgLat];
    }

    /**
     * GCJ02 坐标转 WGS84 墨卡托投影坐标系 TODO 结果已验证
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return array 转换后的墨卡托坐标数组，包含 x 和 y 两个元素
     */
    public static function gcj02ToWgs84Mercator(float $lng, float $lat): array
    {
        [$wg_lng,$wg_lat] = self::gcj02ToWgs84($lng,$lat);
        [$mc_lng,$mc_lat] = self::wgs84ToWebMercator($wg_lng,$wg_lat);
        return [$mc_lng,$mc_lat];
    }

    /**
     * WGS84 坐标转 BD09 TODO 结果已验证
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return array 转换后的经纬度数组
     */
    public static function wgs84ToBd09(float $lng, float $lat): array
    {
        [$cj_lng,$cj_lat] = self::wgs84ToGcj02($lng,$lat);
        [$bd_lng,$bd_lat] = self::gcj02ToBd09($cj_lng,$cj_lat);
        return [$bd_lng,$bd_lat];
    }

    /**
     * BD09 坐标系转 WGS84 坐标系 TODO 结果已验证 偏差0.1米
     * @param float $bdLng BD09 坐标系经度值
     * @param float $bdLat BD09 坐标系纬度值
     * @return array WGS84 坐标系经纬度值，包含经度和纬度两个元素
     */
    public static function bd09ToWgs84(float $bdLng, float $bdLat): array
    {
        [$lng,$lat] = self::bd09ToGcj02($bdLng, $bdLat);
        $threshold = 1e-6; // 限定精度
        $delta = [1, 1];
        $maxIterations = 10; // 最大迭代次数
        $wgsLng = $lng;
        $wgsLat = $lat;
        $iterationCount = 0;
        while ((abs($delta[0]) > $threshold || abs($delta[1]) > $threshold) && $iterationCount < $maxIterations) {
            [$gcj['lng'],$gcj['lat']] = self::wgs84ToGcj02($wgsLng, $wgsLat);
            $delta = [$lng - $gcj['lng'], $lat - $gcj['lat']];
            $wgsLng += $delta[0];
            $wgsLat += $delta[1];
            $iterationCount++;
        }
        return [$wgsLng,$wgsLat];
    }

    /**
     * BD09 坐标系转 WGS84 坐标系墨卡托投影坐标系
     * @param float $bdLng BD09 坐标系经度值
     * @param float $bdLat BD09 坐标系纬度值
     * @return array WGS84 坐标系墨卡托投影坐标系，包含 x 和 y 两个元素
     */
    public static function bd09ToWgs84Mercator(float $bdLng, float $bdLat): array
    {
        [$wg_lng,$wg_lat] = self::bd09ToWgs84($bdLng,$bdLat);
        [$mc_lng,$mc_lat] = self::wgs84ToWebMercator($wg_lng,$wg_lat);
        return [$mc_lng,$mc_lat];
    }

    /**
     * BD09 坐标系转 GCJ02 TODO 结果已验证 偏差0.1米
     * @param float $lng BD09 坐标系经度值
     * @param float $lat BD09 坐标系纬度值
     * @return array GCJ02 坐标系经纬度值，包含经度和纬度两个元素
     */
    public static function bd09ToGcj02(float $lng, float $lat): array
    {
        $x = $lng - self::BD09_LNG_OFFSET;
        $y = $lat - self::BD09_LAT_OFFSET;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * self::X_PI);
        $theta = atan2($y, $x) - 0.000003 * cos($x * self::X_PI);
        $gcj_lng = $z * cos($theta);
        $gcj_lat = $z * sin($theta);
        return [$gcj_lng, $gcj_lat];
    }

    /**
     * GCJ02 坐标转 BD09 TODO 结果已验证
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return array 转换后的经纬度数组
     */
    public static function gcj02ToBd09(float $lng, float $lat): array
    {
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * self::X_PI);
        $theta = atan2($y, $x) + 0.000003 * cos($x * self::X_PI);
        $bd_lng = $z * cos($theta) + self::BD09_LNG_OFFSET;
        $bd_lat = $z * sin($theta) + self::BD09_LAT_OFFSET;
        return [$bd_lng, $bd_lat];
    }

    /**
     * BD09MC坐标系转 WGS84 坐标系 TODO 结果已验证
     * @param float $x 百度墨卡托坐标系 x 值
     * @param float $y 百度墨卡托坐标系 y 值
     * @return array WGS84 坐标系经纬度，包含经度和纬度两个元素
     */
    public static function bd09mcToWgs84(float $x, float $y): array
    {
        [$db_lng,$db_lat] = self::bd09mcToBd09($x,$y);
        [$cg_lng,$cg_lat] = self::bd09ToGcj02($db_lng,$db_lat);
        [$wg_lng,$wg_lat] = self::gcj02ToWgs84($cg_lng,$cg_lat);
        return [$wg_lng,$wg_lat];
    }

    /**
     * bd09mc墨卡托 转 WGS84墨卡托投影坐标系 TODO 结果已验证
     * @param float $x 百度墨卡托坐标系 x 值
     * @param float $y 百度墨卡托坐标系 y 值
     * @return array WGS84 坐标系墨卡托投影坐标系，包含 x 和 y 两个元素
     */
    public static function bd09mcToWgs84Mercator(float $x, float $y): array
    {
        [$wg_lng,$wg_lat] = self::bd09mcToWgs84($x,$y);
        [$mc_lng,$mc_lat] = self::wgs84ToWebMercator($wg_lng,$wg_lat);
        return [$mc_lng,$mc_lat];
    }

    /**
     * BD09MC坐标系转BD09坐标系 TODO 已验证 偏差0.001米
     * @param float $x BD09MC坐标系的X轴坐标
     * @param float $y BD09MC坐标系的Y轴坐标
     * @return array 转换后的BD09坐标系数组 [经度, 纬度]
     */
    public static function bd09mcToBd09(float $x, float $y): array
    {
        [$lng,$lat] = [abs($x),abs($y)];
        $cE = self::getConversionCoefficients($lat);
        [$gcj_lng,$gcj_lat] = self::convertor($lng,$lat,$cE);
        return [$gcj_lng, $gcj_lat];
    }

    /**
     * BD09MC坐标系转GCJ02坐标系 TODO 已验证 偏差0.1米
     * @param float $x BD09MC坐标系的X轴坐标
     * @param float $y BD09MC坐标系的Y轴坐标
     * @return array 转换后的GCJ02坐标系数组 [经度, 纬度]
     */
    public static function bd09mcToGcj02(float $x, float $y): array
    {
        [$lng,$lat] = [abs($x),abs($y)];
        $cE = self::getConversionCoefficients($lat);
        [$t,$ce] = self::convertor($lng,$lat,$cE);
        [$gcj_lng, $gcj_lat] = self::bd09ToGcj02($t,$ce);
        return [$gcj_lng, $gcj_lat];
    }

    /**
     * 坐标转换
     * @param float $lng 经度
     * @param float $lat 纬度
     * @param array $cD 转换参数数组
     * @return array 转换后的经纬度数组 [经度, 纬度]
     */
    private static function convertor(float $lng, float $lat, array $cD): array
    {
        $t = $cD[0] + $cD[1] * abs($lng);
        $cB = abs($lat) / $cD[9];
        $ce = $cD[2] + $cD[3] * $cB + $cD[4] * $cB * $cB +
            $cD[5] * $cB * $cB * $cB + $cD[6] * $cB * $cB * $cB * $cB +
            $cD[7] * $cB * $cB * $cB * $cB * $cB +
            $cD[8] * $cB * $cB * $cB * $cB * $cB * $cB;
        $t *= ($lng < 0 ? -1 : 1);
        $ce *= ($lat < 0 ? -1 : 1);
        return [$t, $ce];
    }

    /**
     * 获取转换系数
     * @param float $lat 纬度
     * @return float[]
     */
    private static function getConversionCoefficients($lat)
    {
        $array = self::MC2LL;
        for ($cD = 0; $cD < count(self::MCBAND); $cD++) {
            if ($lat >= self::MCBAND[$cD]) {
                return $array[$cD];
            }
        }
        return end($array);
    }

    /**
     * 判断经纬度是否在中国范围内
     * @param float $lng 经度
     * @param float $lat 纬度
     * @return bool 是否在中国范围内
     */
    private static function outOfChina(float $lng, float $lat): bool
    {
        return ($lng < 72.004 || $lng > 137.8347 || $lat < 0.8293 || $lat > 55.8271);
    }

    /**
     * 计算经度偏移量
     * @param float $lng 经度偏移量
     * @param float $lat 纬度偏移量
     * @return float 经度偏移量
     */
    private static function transformLng(float $lng, float $lat): float
    {
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * self::PI) + 40.0 * sin($lng / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * self::PI) + 300.0 * sin($lng / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }


    /**
     * 计算纬度偏移量
     * @param float $lng 经度偏移量
     * @param float $lat 纬度偏移量
     * @return float 纬度偏移量
     */
    private static function transformLat(float $lng, float $lat): float
    {
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat + 0.2 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lat * self::PI) + 40.0 * sin($lat / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($lat / 12.0 * self::PI) + 320 * sin($lat * self::PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

}