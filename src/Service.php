<?php

declare (strict_types=1);

namespace plugin\library;

use think\admin\Plugin;

/**
 * 插件注册服务
 * @class Service
 * @package plugin\library
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '组件服务';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/plugs-library';

    public static function menu(): array
    {
        return [];
    }
}