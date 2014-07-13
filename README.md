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
### 路由(Router)实例
```php
return array(
    'rules' => array(
        '/' => 'home/index',
        '/{page}' => 'home/page/page/{page}',
        '/category/{tag}' => 'home/category/tag/{tag}',
        '/post/{id}' => 'home/post/id/{id}',
        '/comment' => 'home/comment'
    ),
    'patterns' => array(
        'page' => "[0-9]+",
        'tag' => '[0-9]+',
        'id' => '\d+'
    ),
	'separator' => '/'
);
```
### 基础路由(Router)
		bjhaze基础路由(Router)使用类似controller/action/key/value(moudle/controller/action)的方式处理连接请求。连接参数默认使用key/value的形式。router配置的separator项可以修改分隔符。defaultController和defaultAction项对应默认控制器和方法。
### 正则路由(RegexRouter)
		bjhaze默认的请求分发方式。在基础路由(Router)的基础上，正则路由(RegexRouter)提供了对URL进行重写的功能。通过配置项rules和patterns，可以在默认路由的基础上进行URL的新匹配与重写。
控制器(Controller)
-----------------------------------
### 输出页面
		bjhaze控制器可以实现XML,JSON,以及HTML页面的输出。
		eg:$this->renderJSON(array('data' => array(..)));
		$this->renderXML(array('data' => array(..)));
		$this->render('homepage', array('data' => array(..)));
		当进行HTML输出时，默认从appliaction/views/controllername/目录寻找模板使用$this->render('/homepage', array('data' => array(..)));则在views根目录寻找模板。
### 加载挂件
		bjhaze控制器使用widget方法加载挂件。参数分别为Widget类名，和加载参数。eg:$this->widget('Footer', array('friendLinks' => ..));
挂件(Widget)
-----------------------------------
### bjhaze挂件通过实现run方法，并在方法中调用render实现渲染。实例：
```php
use BJHaze\Widget\Widget;

class Footer extends Widget
{

    public function run()
    {
        $this->render('footer', array(
            'categories' => (new Category())->getList()
        ));
    }
}
```
		
页面输出(Response)
-----------------------------------
### 设置输出缓存
```php
'response' => array(
	'cachePaths' => array('home/page' => 60)//页面home/page缓存60秒
),
```
### 设置输出字符集
```php
'response' => array(
	'charset' => 'gbk'
),
```
### 添加header项
```php
$this->response->setHeader('Content-Type', 'text/xml');
```
模型(Model)
-----------------------------------
### BJHaze提供基于BJHaze\Database的模型(model)层。 主要实现了基于主键的增删改查，及字段验证功能。示例:
```php
class Category extends Model
{
    protected $connection = 'access';//对应的数据库配置

    protected $table = '{{category}}';//对应的数据表

    protected $primaryKey = 'id';//主键,可以是数组(联合主键).
    
    public function validations()
    {
        return array(
            'addtime' => 'date'//添加和更新记录时对应的字段验证,具体验证支持请察看BJHaze\Validation\Validator类。
        );
    }
}
```
### 模型(Model)使用实例：
```php
$category = new Category();

$category->create(array('name' => 'catname'));

$category->update(array('id' => 1,  'name' => 'catname'));//必须提供主键id作为更新条件,如果是联合主键则必须提供全部主键相应的键名

$category->delete(1)//如果是单一主键支持数组array(1,2,3)的形式批量删除

$category->read(array(1,2,3));//同上，单一主键支持ID数组获取多条数据。
```
PDO数据库(Database)
-----------------------------------
### BJHaze使用PDO作为数据库操作层。配置实例：
```php
return array(
    'defaultConnection' => 'access',
    'connections' => array(
        'access' => array(
            'masters' => array(
                "odbc:driver={microsoft access driver (*.mdb)};dbq=" . dirname(__DIR__) . "/data/blog.mdb"
                //"mysql:host=localhost;dbname=blog;charset=gbk"
            ),
            'username' => 'root',
            'password' => '',
            'prefix' => 'bjhaze_',
            'attributes' => null
        )
    )
);
```
查询示例:
```php
$this->db->select()
         ->from('{{table}}')// 表名使用"{{ table_name}}"会在SQL执行时自动添加表前缀。
         ->leftJoin('{{table2}}', '{{table}}.id = {{table2}}.id')
         ->where(array( 'id' => 11)) //  这等同于 where('id = ?', 11) 
         ->orWhere('id > ?', 21)//必须使用'?'作为占位符,BJHaze\Database\Manager弃用了:id  方便多批次的参数绑定在同一个SQL。这使得一些不支持批量插入的数据库也可以用insert插入多条数据。
         ->limit(10,10)
         ->queryAll('def');//def为对应的数据库配置 ，如果不填写则使用默认数据库
```
插入示例:
```php
$data = array(
    array('name'=> 'jim','age'=>20),
    array('name'=>'tom','age'=>18)
)
$this->db->insert($table, $data)->execute($connection);
```
修改示例:
```php
$this->db->update($table, array('age' =>19))
         ->where('id = ?', 11)
         ->execute($connection);
```
删除示例:
```php
$this->db->delete($table)
         ->where( 'id BETWEEN  ? AND ?', 100, 200)
         ->execute($connection);
```
缓存(Cache)
-----------------------------------
### 配置示例
```php
return array(
    'default' => 'apc',
    'servers' => array(
        'apc' => array(
            'driver' => 'apc'
        ),
        'redis' => array(
            'driver' => 'redis',
            'server' => array(
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 0,
                'pconnect' => false,
                'auth' => null
            ),
            'prefix' => 'bjhaze_redis'
        ),
        'memcache' => array(
            'driver' => 'memcache',
            'servers' => array(
                array(
                    'host' => 'localhost',
                    'port' => 11211
                )
            )
        )
    )
);
```