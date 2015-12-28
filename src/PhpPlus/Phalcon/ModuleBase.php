<?php

namespace PhpPlus\Phalcon;

use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Logger;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as LoggerFile;

/**
 * Module基础类
 * @package PhpPlus\Phalcon
 */
class ModuleBase implements ModuleDefinitionInterface
{
    const DIR = __DIR__;
    protected $config;
    protected $debug;

    public function __construct()
    {
        $this->config = new Config(require $this::DIR . '/config/config.php');
        if(defined('APP_ENV') && APP_ENV == 'product') {
            $this->debug = false;
        } else {
            $this->debug = true;
        }
    }

    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $config = $this->config;
        $debug = $this->debug;

        $namespace = substr(get_class($this), 0, strripos(get_class($this), '\\'));

        $baseDir = $this::DIR . '/';

        $regs = [
            $namespace  => $baseDir,
        ];

        $loader = new Loader();

        // 配置中的名字空间设置
        $namespaceConfig = $config->offsetExists('namespaces') ? $config->namespaces->toArray() : [];
        foreach($namespaceConfig as $n=>$d) {
            $regs[$n] = $d;
        }

        // 检查 controllers 和 models是否存在
        $moduleControllers = $namespace . '\Controllers';
        $moduleModels = $namespace . '\Models';
        if(!isset($regs[$moduleControllers])) {
            $regs[$moduleControllers] = $baseDir . 'controllers/';
        }
        if(!isset($regs[$moduleModels])) {
            $regs[$moduleModels] = $baseDir . 'models/';
        }

        $loader->registerNamespaces($regs);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        $config = $this->config;
        $debug = $this->debug;
        /**
         * Setting up the view component
         */
        $di['view'] = function () use ($config, $debug) {
            $view = new View();
            $viewDir = $this::DIR . '/views/';
            if($config->offsetExists('application') && $config->application->offsetExists('viewsDir') && $config->application->offsetExists('viewsDir')) {
                $viewDir = $config->application->viewsDir;
            }
            $view->setViewsDir($viewDir);

            $viewEngines = [
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ];
            $viewEngines['.volt'] = function ($view, $di) use ($config, $debug) {
                $cacheDir = $this::DIR . '/../cache/';
                $appConfig = $di['appConfig'];

                if($appConfig->offsetExists('volt') && $appConfig->volt->offsetExists('cacheDir')) {
                    $cacheDir = $appConfig->volt->cacheDir;
                } elseif($config->offsetExists('application') && $config->application->offsetExists('cacheDir') && $config->application->offsetExists('cacheDir')) {
                    $cacheDir = $config->application->cacheDir;
                }

                $volt = new VoltEngine($view, $di);
                $volt->setOptions(array(
                    'compiledPath' => $cacheDir,
                    'compiledExtension' => ".compiled",
                    'compiledSeparator' => '_',
                    'compileAlways' => $debug
                ));

                $compiler = $volt->getCompiler();

                // 扩展
                if($config->offsetExists('volt') && $config->volt->offsetExists('extension')) {
                    foreach($config->volt->extension as $k=>$v) {
                        $compiler->addExtension($v);
                    }
                }
                if($appConfig->offsetExists('volt') && $appConfig->volt->offsetExists('extension')) {
                    foreach($appConfig->volt->extension as $k=>$v) {
                        $compiler->addExtension($v);
                    }
                }

                // 函数
                if($config->offsetExists('volt') && $config->volt->offsetExists('func')) {
                    foreach($config->volt->func as $k=>$v) {
                        $compiler->addFunction($k, $v);
                    }
                }
                if($appConfig->offsetExists('volt') && $appConfig->volt->offsetExists('func')) {
                    foreach($appConfig->volt->func as $k=>$v) {
                        $compiler->addFunction($k, $v);
                    }
                }

                // 过滤器
                if($config->offsetExists('volt') && $config->volt->offsetExists('filter')) {
                    foreach($config->volt->filter as $k=>$v) {
                        $compiler->addFilter($k, $v);
                    }
                }
                if($appConfig->offsetExists('volt') && $appConfig->volt->offsetExists('filter')) {
                    foreach($appConfig->volt->filter as $k=>$v) {
                        $compiler->addFilter($k, $v);
                    }
                }

                return $volt;
            };

            $view->registerEngines($viewEngines);
            return $view;
        };

        if($config->offsetExists('database')) {
            $di['db'] = function () use ($config, $di, $debug) {
                $appConfig = $di['appConfig'];
                if($debug && $appConfig->offsetExists('logger') && $appConfig->logger->offsetExists('sqlPath')) {
                    $eventsManager = new EventsManager();

                    $path = $config->logger->sqlPath;
                    $path = str_replace('{{date}}', date("Ymd"), $path);
                    $logger = new LoggerFile($path);

                    //Listen all the database events
                    $eventsManager->attach('db', function ($event, $connection) use ($logger) {
                        if ($event->getType() == 'beforeQuery') {
                            $logger->log($connection->getSQLStatement(), Logger::INFO);
                        }
                    });

                    $connection = new DbAdapter($config->database->toArray());

                    //Assign the eventsManager to the db adapter instance
                    $connection->setEventsManager($eventsManager);

                    return $connection;
                } else {
                    return new DbAdapter($config->database->toArray());
                }
            };
        }

        if($config->offsetExists('application') && $config->application->offsetExists('baseUri')) {
            $di['url'] = function () use ($config) {
                $url = new \Phalcon\Mvc\Url();
                $url->setBaseUri($config->application->baseUri);

                return $url;
            };
        }

        $di['moduleConfig'] = function () use ($config) {
            return $config;
        };
    }

    /**
     *
     * 获取需要给volt添加的function，key为方法名，value为处理函数
     * @param $di
     * @return array
     */
    public function getVoltCompileFunction(DiInterface $di)
    {
        return [];
    }

    /**
     *
     * 获取需要给volt添加的filter，key为方法名，value为处理函数
     * @param $di
     * @return array
     */
    public function getVoltCompileFilter(DiInterface $di)
    {
        return [];
    }
}