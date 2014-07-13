bjhaze 使用说明。
===================================
应用配置(Application)
-----------------------------------
### 应用配置(Application)实例
```php
$config = array(
    'basePath' => dirname(__DIR__),
    'composer' => USE_COMPOSER,
    'timezone' => 'PRC',
    'site_name' => 'bjhaze demo',
    'site_keywords' => 'bjhaze,php5.4 framework',
    'site_description' => 'bjhaze is a simple php5.4 mvc framework',
    'modules' => array('admin'),
    'components' => array(
        'sessionHandler' => array(
            'class' => 'BJHaze\Session\Handler\File',
            'path' => dirname(__DIR__) . '/tmp'
        ),
        'db' => require 'db.php',
        'router' => require 'routes.php',
        'cache' => require 'cache.php',
        'response' => array(
            'charset' => 'gbk',
            'cachePaths' => array()
        )
    )
)
```
### 应用配置(Application)基础定义
		basePath：Application的根文件夹。
		composer：是否使用composer实现php类的自动加载。
		timezone：时区设置。
		modules：站点模块(module)集合。
		components：组件配置集合。组件项可使用componentName => array('class' => 'classname')替换默认加载类。
### bjhaze默认加载组件
		request：BJHaze\Http\Request
		response：BJHaze\Http\Response
		router：BJHaze\Routing\RegexRouter
		db：BJHaze\Database\Manager
		cache：BJHaze\Cache\CacheManager
		session：BJHaze\Session\Manager
		sessionHandler：BJHaze\Session\Handler\File
		exceptionHandler：BJHaze\Exception\Handler
		encrypter：BJHaze\Encryption\Encrypter
		validator：BJHaze\Validation\Validator
路由(Router)
-----------------------------------
控制器(Controller)
-----------------------------------
挂件(Widget)
-----------------------------------
页面输出(Response)
-----------------------------------
模型(Model)
-----------------------------------
PDO数据库(Database)
-----------------------------------
缓存(Cache)
-----------------------------------

