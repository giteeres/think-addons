<?php
declare(strict_types=1);

namespace think\addons;

use think\Route;
use think\facade\Lang;
use think\facade\Cache;
use think\facade\Event;
use think\facade\Db;
use think\addons\middleware\Addons;

/**
 * 插件服务
 * Class Service
 * @package think\addons
 */
class Service extends \think\Service
{
    protected $addons_path;

    public function register()
    {
        $this->addons_path = $this->getAddonsPath();
        // 加载系统语言包
        $this->loadLangPack();
        // 自动载入插件
        $this->autoload();
        // 加载插件事件
        $this->loadEvent();
        // 加载插件系统服务
        $this->loadService();
        // 绑定插件容器
        $this->app->bind('addons', Service::class);
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            // 路由脚本
            $execute = '\\think\\addons\\Route::execute';

            // 注册插件公共中间件
            if (is_file($this->app->addons->getAddonsPath() . 'middleware.php')) {
                $this->app->middleware->import(include $this->app->addons->getAddonsPath() . 'middleware.php', 'route');
            }

            // 注册控制器路由
            $route->rule("addon/:addon-[:controller]-[:action]", $execute)->middleware(Addons::class);
        });
    }

    /**
     * 加载语言包
     */
    private function loadLangPack(){
        $langset = $this->app->lang->defaultLangSet();
        $files = glob($this->app->getRootPath() . '/vendor/giteeres/think-addons/src/lang' . DIRECTORY_SEPARATOR . $langset . '.*');
        Lang::load($files);
    }

    /**
     * 插件事件
     */
    private function loadEvent()
    {
        $hooks = Cache::get('hooks', []);
        if (empty($hooks)) {
            $dbhooks = Db::name('hooks')->where("addons != ''")->column('name,addons');
            $hooks = [];
            if(count($dbhooks)>0){
                foreach ($dbhooks as $key => $v) {
                    $addons = [];
                    $v['addons'] = explode(',',$v['addons']);
                    foreach ($v['addons'] as $akey => $av) {
                        $addons[] = [get_addon_class($av),$v['name']];
                    }
                    $hooks[$v['name']] = $addons;
                }
            }
            Cache::set('hooks', $hooks);
        }
        //如果在插件中有定义 AddonsInit，则直接执行
        if (isset($hooks['AddonsInit'])) {
            foreach ($hooks['AddonsInit'] as $k => $v) {
                Event::trigger('AddonsInit', $v);
            }
        }
        Event::listenEvents($hooks);
    }

    /**
     * 挂载插件服务
     */
    private function loadService()
    {
        $results = scandir($this->addons_path);
        $bind = [];
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file($this->addons_path . $name)) {
                continue;
            }
            $addonDir = $this->addons_path . $name . DIRECTORY_SEPARATOR;
            if (!is_dir($addonDir)) {
                continue;
            }

            if (!is_file($addonDir . ucfirst($name) . '.php')) {
                continue;
            }

            $service_file = $addonDir . 'service.ini';
            if (!is_file($service_file)) {
                continue;
            }
            $info = parse_ini_file($service_file, true, INI_SCANNER_TYPED) ?: [];
            $bind = array_merge($bind, $info);
        }
        $this->app->bind($bind);
    }

    /**
     * 自动载入插件
     * @return bool
     */
    private function autoload()
    {
        $addons = Cache::get('addons', []);
        if (empty($hooks)) {
            $addons = Db::name('addons')->where('status = 1 and dataFlag = 1')->column('*','name');
            Cache::set('addons', $addons);
        }
    }

    /**
     * 获取 addons 路径
     * @return string
     */
    public function getAddonsPath()
    {
        // 初始化插件目录
        $addons_path = $this->app->getRootPath() . 'addons' . DIRECTORY_SEPARATOR;
        // 如果插件目录不存在则创建
        if (!is_dir($addons_path)) {
            @mkdir($addons_path, 0755, true);
        }

        return $addons_path;
    }

    /**
     * 获取插件的配置信息
     * @param string $name
     * @return array
     */
    public function getAddonsConfig()
    {
        $name = $this->app->request->addon;
        $addon = get_addon_instance($name);
        if (!$addon) {
            return [];
        }

        return $addon->getAddonConfig();
    }
}
