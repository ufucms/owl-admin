<?php

namespace Slowlyo\OwlAdmin\Traits;

/**
 * 静态数据转换Trait类
 */
trait StaticTrait
{

    /**
     * 转换source
     *
     * @return string
     */
    public static function toSource($attr)
    {
        $data = self::${$attr};
        $source = [];
        foreach ($data as $key => $val) {
            $source[$val['value']] = $val;
        }
        return $source;
    }

    /**
     * 过滤无效值
     *
     * @return string
     */
    public static function filterData($attr, $filter)
    {
        $filterArr = [];
        if(is_array($filter)){
            $filterArr = $filter;
        } elseif (is_string($filter) || is_numeric($filter)) {
            $filterArr = [$filter];
        }

        $data = self::${$attr};
        foreach ($data as $key => $val) {
            if(in_array($val['value'], $filterArr)){
                unset($data[$key]);
            }
        }
        return array_values($data);
    }
}
