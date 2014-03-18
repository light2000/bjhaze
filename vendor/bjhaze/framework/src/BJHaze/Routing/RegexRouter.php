<?php
/**
 *
 * Router class
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;

use Closure, ReflectionFunction, ReflectionMethod;
use BJHaze\Http\RequestInterface;

class RegexRouter implements RouterInterface
{

    const REGEX_DELIMITER = '#';

    const ANY = 0b1111111111;

    const PUT = 0b1000010000;

    const PATCH = 0b0100001000;

    const POST = 0b0010000100;

    const GET = 0b0001000010;

    const DELETE = 0b0000100001;

    const MOBILE = 0b1111100000;

    const PC = 0b0000011111;

    /**
     * Url separator
     *
     * @var string
     */
    protected $separator;

    /**
     * the URL rules ( 'path' =>, 'baseUrl' =>, 'action' => , 'from' => ).
     *
     * @var array
     */
    private $rules;

    /**
     * Share variable patterns (regular expression) eg['id' => '0-9', 'name' => '\w']
     *
     * @var array
     */
    private $variablePatterns;

    /**
     * Constructor
     *
     * @param array $rules            
     * @param array $patterns            
     * @param string $separator            
     */
    public function __construct(array $rules, array $patterns = null, $separator = '/')
    {
        $this->rules = $rules;
        $this->variablePatterns = $patterns;
        $this->separator = $separator;
    }

    /**
     * Add route rule
     *
     * @param string $path            
     * @param mixed $action            
     * @param mixed $filter            
     * @return void
     */
    public function addRule($path, $action, $filter = null)
    {
        $this->rules[] = compact('path', 'baseUrl', 'action', 'filter');
    }

    /**
     * Get current request from code
     *
     * @param RequestInterface $request            
     * @return int
     */
    public function getFrom(RequestInterface $request)
    {
        $device = self::isMobile($request->getUserAgent()) ? self::MOBILE : self::PC;
        $method = $request->getRequestType();
        if (0 == strcasecmp('GET', $method))
            return $device & self::GET;
        elseif (0 == strcasecmp('POST', $method))
            return $device & self::POST;
        elseif (0 == strcasecmp('DELETE', $method))
            return $device & self::DELETE;
        elseif (0 == strcasecmp('PUT', $method))
            return $device & self::PUT;
        elseif (0 == strcasecmp('PATCH', $method))
            return $device & self::PATCH;
    }

    /**
     * Check whether from a mobile device
     *
     * @param string $userAgent            
     *
     * @return boolean
     */
    public static function isMobile($userAgent)
    {
        static $mobiles = array(
            'iphone',
            'android',
            'phone',
            'mobile',
            'wap',
            'netfront',
            'java',
            'opera mobi',
            'opera mini',
            'ucweb',
            'windows ce',
            'symbian',
            'series',
            'webos',
            'sony',
            'blackberry',
            'dopod',
            'nokia',
            'samsung',
            'palmsource',
            'xda',
            'pieplus',
            'meizu',
            'midp',
            'cldc',
            'motorola',
            'foma',
            'docomo',
            'up.browser',
            'up.link',
            'blazer',
            'helio',
            'hosin',
            'huawei',
            'novarra',
            'coolpad',
            'webos',
            'techfaith',
            'palmsource',
            'alcatel',
            'amoi',
            'ktouch',
            'nexian',
            'ericsson',
            'philips',
            'sagem',
            'wellcom',
            'bunjalloo',
            'maui',
            'smartphone',
            'iemobile',
            'spice',
            'bird',
            'zte-',
            'longcos',
            'pantech',
            'gionee',
            'portalmmm',
            'jig browser',
            'hiptop',
            'benq',
            'haier',
            '^lct',
            '320x320',
            '240x320',
            '176x220',
            'w3c ',
            'acs-',
            'alav',
            'alca',
            'amoi',
            'audi',
            'avan',
            'benq',
            'bird',
            'blac',
            'blaz',
            'brew',
            'cell',
            'cldc',
            'cmd-',
            'dang',
            'doco',
            'eric',
            'hipt',
            'inno',
            'ipaq',
            'java',
            'jigs',
            'kddi',
            'keji',
            'leno',
            'lg-c',
            'lg-d',
            'lg-g',
            'lge-',
            'maui',
            'maxo',
            'midp',
            'mits',
            'mmef',
            'mobi',
            'mot-',
            'moto',
            'mwbp',
            'nec-',
            'newt',
            'noki',
            'oper',
            'palm',
            'pana',
            'pant',
            'phil',
            'play',
            'port',
            'prox',
            'qwap',
            'sage',
            'sams',
            'sany',
            'sch-',
            'sec-',
            'send',
            'seri',
            'sgh-',
            'shar',
            'sie-',
            'siem',
            'smal',
            'smar',
            'sony',
            'sph-',
            'symb',
            't-mo',
            'teli',
            'tim-',
            'tosh',
            'tsm-',
            'upg1',
            'upsi',
            'vk-v',
            'voda',
            'wap-',
            'wapa',
            'wapi',
            'wapp',
            'wapr',
            'webc',
            'winw',
            'winw',
            'xda',
            'xda-',
            'Googlebot-Mobile'
        );
        foreach ($mobiles as $mobile)
            if (false != strpos($userAgent, $mobile))
                return true;
        
        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \BJHaze\Routing\RouterInterface::forward()
     */
    public function dispatch(RequestInterface $request)
    {
        return $this->forward($request->getPathInfo(), $request->getBaseUrl(true), $this->getFrom($request)) ?  : $this->missingAction();
    }

    /**
     * Internal redirect
     *
     * @param string $requestPath            
     * @param string $baseUrl            
     * @param string $from            
     * @return mixed
     */
    public function forward($requestPath, $baseUrl = null, $from = null)
    {
        foreach ((array) $this->rules as $pattern => $rule) {
            if ($from !== null && ! empty($rule['from']) && ! ($rule['from'] & $from))
                continue;
            if (! empty($rule['baseUrl']) && ! empty($baseUrl))
                $params = $this->matches($rule['baseUrl'] . $rule['path'], $baseUrl . $requestPath);
            else
                $params = $this->matches($rule['path'], $requestPath);
            if (! empty($params)) {
                $action = $rule['action'];
                if (is_string($action) && false !== strpos($action, '{'))
                    $action = self::complie($action, $params);
                return array(
                    $action,
                    $params
                );
            }
        }
    }

    /**
     * Rule match
     *
     * @param string $pattern            
     * @param string $url            
     * @return mixed
     */
    protected function matches($pattern, $url)
    {
        $pattern = self::REGEX_DELIMITER . '^' . self::complie($pattern, (array) $this->variablePatterns, '[^' . preg_quote($this->separator) . ']++', "(?P<%s>%s)") . '$' . self::REGEX_DELIMITER . 'isU';
        
        if (preg_match($pattern, $url, $matches))
            return $matches;
    }

    /**
     * Fix pattern, replace {variable} to real parameter
     *
     * @param string $pattern            
     * @param array $params            
     * @param string $def            
     * @param string $format            
     * @return string
     */
    protected static function complie($pattern, array $params, $def = '', $format = null)
    {
        return preg_replace_callback(self::REGEX_DELIMITER . '{(\w+)}' . self::REGEX_DELIMITER, function ($matches) use($params, $def, $format)
        {
            $var = isset($params[$matches[1]]) ? $params[$matches[1]] : $def;
            return $format ? sprintf($format, $matches[1], $var) : $var;
        }, $pattern);
    }

    /**
     * Build url from rules
     *
     * @param string $action            
     * @param array $params            
     * @return string
     */
    public function buildUrl($action, array $params = null)
    {
        foreach ($this->rules as $rule)
            if ($action == $rule['action'])
                return $params ? self::complie(! empty($rule['baseUrl']) ? $rule['baseUrl'] . $rule['path'] : $rule['path'], $params) : $rule['path'];
    }

    /**
     * (non-PHPdoc)
     *
     * @see \BJHaze\Routing\RouterInterface::missingAction()
     */
    public function missingAction()
    {
        throw new \RuntimeException('No matched action found', 404);
    }
}