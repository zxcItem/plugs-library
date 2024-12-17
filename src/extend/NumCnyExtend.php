<?php


namespace plugin\library\extend;

/**
 * 金额转换
 * Class NumCnyExtend
 * @package plugin\library\extend
 */
class NumCnyExtend
{
    const uppers = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];

    const units = ['分', '角'];

    const grees = ['元', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿', '拾', '佰', '仟', '万', '拾', '佰'];

    /**
     * 金额转大写
     * 如果小数部分是2位以上的，会四舍五入。
     * 64821.23
     * 陆万肆仟捌佰贰拾壹元贰角叁分
     * @param $money
     * @return string
     */
    public static function toCapital($money): string
    {
        if (!(is_float($money) || is_numeric($money) || is_int($money))) {
            throw new \InvalidArgumentException($money);
        }
        $money = number_format($money, 2, '.', '');
        @list($intPart, $decimalPart) = explode('.', $money, 2);
        if (0.0 === floatval($money)) {
            return '零元';
        }
        $result = self::getIntPart($intPart);
        $result .= self::getDecimalPart($money,$decimalPart);

        return $result;
    }

    public static function getIntPart($intPart)
    {
        $result = '';
        $gree = strlen($intPart) - 1;
        if ($intPart > 0) {
            for ($i = 0; $i < strlen($intPart); ++$i) {
                $num = $intPart[$i];
                $result .= self::uppers[$num].self::grees[$gree--];
            }
        }
        $result = str_replace('零亿', '亿零', $result);
        $result = str_replace('零万', '万零', $result);
        $result = str_replace('零拾', '零', $result);
        $result = str_replace('零佰', '零', $result);
        $result = str_replace('零仟', '零', $result);
        $result = str_replace('零零', '零', $result);
        $result = str_replace('零零', '零', $result);
        $result = str_replace('零亿', '亿', $result);
        $result = str_replace('零万', '万', $result);
        $result = str_replace('零元', '元', $result);
        return $result;
    }

    public static function getDecimalPart($money,$decimalPart)
    {
        $result = '';
        if ($decimalPart > 0) {
            //处理小数部分
            $unit = strlen($decimalPart) - 1;
            for ($i = 0; $i < strlen($decimalPart); ++$i) {
                $num = $decimalPart[$i];
                $result .= self::uppers[$num].self::units[$unit--];
            }
        }
        $result = str_replace('零分', '', $result);
        if ($money > 1) {
            $result = str_replace('零角', '零', $result);
        } else {
            $result = str_replace('零角', '', $result);
        }
        return $result ?? '';
    }
}