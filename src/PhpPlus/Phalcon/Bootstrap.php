<?php

namespace PhpPlus\Phalcon;


use Phalcon\Config;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as LoggerFile;
use Phalcon\Logger\Adapter\Stream as LoggerStream;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Events\Manager as EventsManager;

class Bootstrap
{
    /**
     * @var \Phalcon\DI\FactoryDefault
     */
    protected $di;
    /**
     * @var \Phalcon\Mvc\Application
     */
    protected $app;
    /**
     * @var \Phalcon\Config
     */
    protected $config;
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @param array $config
     * @return Bootstrap
     */
    public static function Instance(array $config)
    {
        return new self($config);
    }

    protected function onBefore()
    {

    }

    protected function onAfter()
    {

    }

    /**
     * @param array $config 配置文件
     */
    public function run(array $config)
    {
        $this->di = new FactoryDefault();
        $this->app = new Application();
        if(defined('APP_ENV') && APP_ENV == 'product') {
            $this->debug = false;
            error_reporting(0);
        } else {
            $this->debug = true;
            error_reporting(E_ALL);
        }
        $this->initConfig($config);
        try {
            $this->onBefore();

            $this->initRouters();
            $this->initUrl();
            $this->initView();
            $this->initDatabase();
            $this->initModelsMetadata();
            $this->initCookie();
            $this->initCrypt();
            $this->initLogger();

            $this->onAfter();

            $this->app->setDI($this->di);

            $this->registerModules();

            echo $this->app->handle()->getContent();

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 初始化配置文件
     * @param $config
     */
    protected function initConfig(array $config)
    {
        $this->config = new Config($config);
        $this->di['appConfig'] = $this->config;
    }

    /**
     * 初始化 loader
     * @param array $config 配置数组
     */
    public function initLoader(array $config)
    {
        $loader = new Loader();
        // 读取配置中的namespace
        $loaderVendorNamespaces = function($arr, $config) {
            foreach($config as $k=>$v) {
                $k = trim($k, '\\');
                if (!isset($arr[$k])) {
                    $dir = '/' . str_replace('\\', '/', $k) . '/';
                    $arr[$k] = implode($dir . ';', $v) . $dir;
                }
            }
            return $arr;
        };
        $namespaces = isset($config['namespace']) ? $config['namespace'] : [];

        if(isset($config['vendorDir'])) {
            try {
                $vendorDir = $config['vendorDir'];
                // 加载vondor中的namespaces
                $namespaces = $loaderVendorNamespaces($namespaces, require $vendorDir . '/composer/autoload_namespaces.php');
                $namespaces = $loaderVendorNamespaces($namespaces, require $vendorDir . '/composer/autoload_psr4.php');
                // 加载vondor中的class
                $vendorClassMap = require $vendorDir . '/composer/autoload_classmap.php';
                $loader->registerClasses($vendorClassMap);
            }catch (\Exception $e){}
        }
        $loader->registerNamespaces($namespaces);

        if(isset($config['loader']) && $config['classDirs']) {
            $loader->registerDirs($config['classDirs']);
        }
        $loader->register();
    }

    /**
     * 初始化router
     */
    protected function initRouters()
    {
        $config = $this->config;
        if($config->offsetExists('router')) {
            $this->di['router'] = function () use ($config)
            {
                return require($config->router);
            };
        }
    }

    /**
     * URL 处理
     */
    protected function initUrl()
    {
        $config = $this->config;
        $baseUri = $config->offsetExists('application') && $config->application->offsetExists('baseUri') ? $config->application->baseUri : '/';
        $this->di['url'] = function () use ($baseUri) {
            $url = new UrlResolver();
            $url->setBaseUri($baseUri);
            return $url;
        };
    }

    /**
     * 初始化view
     */
    protected function initView()
    {
        $config = $this->config;
        $debug = $this->debug;
        $this->di->setShared('view', function () use ($config, $debug) {
            $view = new View();
            $view->setViewsDir($config->application->viewsDir);
            $viewEngines = [
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ];
            if($config->offsetExists('volt')) {
                $viewEngines['.volt'] = function ($view, $di) use ($config, $debug) {
                    $volt = new VoltEngine($view, $di);
                    $volt->setOptions(array(
                        'compiledPath' => $config->volt->cacheDir,
                        'compiledExtension' => ".compiled",
                        'compiledSeparator' => '_',
                        'compileAlways' => $debug
                    ));

                    $compiler = $volt->getCompiler();

                    foreach($config->volt->extension as $k=>$v) {
                        $compiler->addExtension($v);
                    }

                    foreach($config->volt->func as $k=>$v) {
                        $compiler->addFunction($k, $v);
                    }
                    $filterList = $config->volt->filter;
                    foreach($filterList as $k=>$v) {
                        $compiler->addFilter($k, $v);
                    }

                    return $volt;
                };
            }
            $view->registerEngines($viewEngines);
            return $view;
        });
    }

    /**
     * 数据库连接
     */
    protected function initDatabase()
    {
        $config = $this->config;
        $debug = $this->debug;
        if($config->offsetExists('database') == false) {
            return;
        }
        $this->di['db'] = function () use ($config, $debug) {
            if($debug && $config->offsetExists('logger') && $config->logger->offsetExists('sqlPath')) {
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

    /**
     * 数据库表结构处理
     */
    protected function initModelsMetadata()
    {
        $this->di['modelsMetadata'] = function () {
            return new \Phalcon\Mvc\Model\Metadata\Memory();
        };
    }

    /**
     * cookie 设置
     */
    protected function initCookie()
    {
        $config = $this->config;
        if($config->offsetExists('cookie') && $config->cookie->offsetExists('encry') && $config->cookie->encry) {
            $this->di['cookies'] = function () {
                $cookies = new \Phalcon\Http\Response\Cookies();
                $cookies->useEncryption(true); // 是否加密
                return $cookies;
            };

        }
    }

    /**
     * 初始化crypt
     */
    protected function initCrypt()
    {
        $config = $this->config;
        $this->di['crypt'] = function () use ($config) {
            $crypt = new \Phalcon\Crypt();
            $cryptKey = $config->application->offsetExists('cryptKey') ? $config->application->cryptKey : 'a2e957d1517c0bba66f861b525d87a53';
            $crypt->setKey($cryptKey);
            return $crypt;
        };
    }

    /**
     * 日志处理
     */
    protected function initLogger()
    {
        $config = $this->config;
        $this->di['logger'] = function () use ($config) {
            $logLevel = $this->debug ? Logger::DEBUG : Logger::ERROR;
            if($config->offsetExists('logger'))
            {
                try
                {
                    if($config->logger->offsetExists('path') == false) {
                        throw new \Exception('logger path not in config.');
                    }
                    $path = $config->logger->path;
                    $path = str_replace('{{date}}', date("Ymd"), $path);
                    if($config->logger->offsetExists('formatter'))
                    {
                        $formatter = new LineFormatter($config->logger->formatter);
                    } else {
                        $formatter = new LineFormatter('%date%[%type%] - %message%');
                    }
                    $logger = new LoggerFile($path);
                    $logger->setFormatter($formatter);
                    $logger->setLogLevel($logLevel);
                    return $logger;
                } catch (\Exception $e) {
                }
            }
            $logger = new LoggerStream("php://stderr");
            $logger->setLogLevel($logLevel);
            return $logger;
        };
    }

    /**
     * 注册模块
     */
    protected function registerModules()
    {
        if($this->config->offsetExists('modules')) {
            $this->app->registerModules($this->config->modules->toArray());
        }
    }
}
