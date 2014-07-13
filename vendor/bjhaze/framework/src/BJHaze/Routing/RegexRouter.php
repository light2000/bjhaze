<?php
/**
 *
 * Regex router class
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;

use Closure, ReflectionFunction, ReflectionMethod;
use BJHaze\Foundation\Container;

class RegexRouter extends Router
{

    const REGEX_DELIMITER = '#';

    /**
     * the URL rules array ( 'path' => 'newpath', 'path2' => 'newpath2').
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
     * @return void
     */
    public function __construct(array $rules, array $patterns = null, Container $container, $separator = '/', $defaultController = 'home', $defaultAction = 'index')
    {
        $this->rules = $rules;
        $this->variablePatterns = $patterns;
        parent::__construct($container, $separator, $defaultController, $defaultAction);
    }

    /**
     * Add route rule
     *
     * @param string $path            
     * @param string $newpath            
     * @return void
     */
    public function addRule($path, $newPath)
    {
        $this->rules[$path] = $newPath;
    }

    /**
     * Internal redirect
     *
     * @param string $requestPath            
     * @param string $baseUrl            
     * @return mixed
     */
    public function forward($requestPath, $baseUrl = '')
    {
        foreach ((array) $this->rules as $pattern => $newPath) {
            if (0 === strpos($pattern, 'http') && ! empty($baseUrl))
                $params = $this->matches($pattern, $baseUrl . $requestPath);
            else
                $params = $this->matches($pattern, $requestPath);
            if (! empty($params)) {
                if (false !== strpos($newPath, '{'))
                    $newPath = self::complie($newPath, $params);
                return parent::forward($newPath, $baseUrl);
            }
        }
        
        return parent::forward($requestPath, $baseUrl);
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
     * @param string $path            
     * @param array $params            
     * @return string
     */
    public function buildUrl($path, array $params = null)
    {
        $realPath = parent::buildUrl($path, $params);
        foreach ($this->rules as $pattern => $newPath)
            if ($realPath == $newPath && false === strpos($pattern, '{'))
                return $pattern; // rewrite rule with no variable pattern
            elseif (false !== strpos($newPath, '{') && false != ($params = $this->matches($newPath, $realPath)))
                return self::complie($pattern, $params);
        return $realPath;
    }
}