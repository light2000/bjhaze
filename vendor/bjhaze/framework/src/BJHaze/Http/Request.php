<?php
/**
 *
 * Http request.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Http;

/**
 *
 * @property string $hostInfo Schema and hostname part (with port number if needed)
 * @property string $baseUrl The relative URL for the application.
 * @property string $scriptUrl The relative URL of the entry script.
 * @property string $pathInfo Part of the request URL that is after the entry script and before the question mark.
 * @property string $requestUri The request URI portion for the currently requested URL.
 * @property string $requestType Request type, such as GET, POST, HEAD, PUT, DELETE.
 * @property boolean $isSecure If the request is sent via secure channel (https).
 * @property boolean $isPost Whether this is a POST request.
 * @property boolean $isDelete Whether this is a DELETE request.
 * @property boolean $isPut Whether this is a PUT request.
 * @property boolean $isAjax Whether this is an AJAX (XMLHttpRequest) request.
 * @property boolean $isFlash Whether this is an Adobe Flash or Adobe Flex request.
 * @property string $urlReferrer URL referrer, null if not present.
 * @property string $userAgent User agent, null if not present.
 * @property string $userHostAddress User IP address.
 * @property array $browser User browser capabilities.
 * @property string $acceptTypes User browser accept types, null if not present.
 * @property integer $port Port number for insecure requests.
 * @property boolean $showScriptName whether use url rewrite hide entry script name
 */
class Request
{

    /**
     * The request uri
     *
     * @var string
     */
    private $_requestUri;

    /**
     * The uri path part
     *
     * @var string
     */
    private $_pathInfo;

    /**
     * The request base url
     *
     * @var string
     */
    private $_baseUrl;

    /**
     * Get the rest parameters
     *
     * @var string
     */
    private $_restParams;

    /**
     * Request script file name
     *
     * @var string
     */
    private $_scriptUrl;

    /**
     * Request host name
     *
     * @var string
     */
    private $_hostInfo;

    /**
     * Whether use url rewrite hide entry script name
     *
     * @var boolean
     */
    private $_showScriptName;

    /**
     * Whether request from a mobile device.
     *
     * @var boolean
     */
    private $_isMobileDevice;

    /**
     * Returns the GET or POST or other method parameter value.
     *
     * @return mixed
     */
    public function getParams()
    {
        if ($this->getIsGet())
            return $_GET;
        elseif ($this->getIsPost())
            return $_GET + $_POST;
        elseif ($this->getIsPut() || $this->getIsDelete() || $this->getIsPatch())
            return $this->getRestParams();
    }

    /**
     * Returns the named GET or POST parameter value.
     *
     * @param string $name            
     * @param mixed $default            
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
    }

    /**
     * Returns the named GET parameter value.
     *
     * @param string $name            
     * @param mixed $default            
     * @return mixed
     */
    public function getQuery($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * Returns the named POST parameter value.
     *
     * @param string $name            
     * @param mixed $default            
     * @return mixed
     */
    public function getPost($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * Set the post data
     *
     * @param array $data            
     * @return void
     */
    public function setPost(array $data)
    {
        $_POST = $data;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    /**
     * Returns the named DELETE parameter value.
     *
     * @param string $name            
     * @param mixed $default            
     * @return mixed
     */
    public function getDelete($name, $default = null)
    {
        if ($this->getIsDeleteRequest()) {
            $restParams = $this->getRestParams();
            return isset($restParams[$name]) ? $restParams[$name] : $default;
        } else
            return $default;
    }

    /**
     * Returns the named PUT parameter value.
     *
     * @param string $name            
     * @param mixed $defaultValue            
     * @return mixed the PUT parameter value
     */
    public function getPut($name, $default = null)
    {
        if ($this->getIsPut()) {
            $restParams = $this->getRestParams();
            return isset($restParams[$name]) ? $restParams[$name] : $default;
        } else
            return $default;
    }

    /**
     * Returns request parameters.
     * Typically PUT or DELETE.
     *
     * @return array
     */
    public function getRestParams()
    {
        if ($this->_restParams === null) {
            $result = array();
            if (function_exists('mb_parse_str'))
                mb_parse_str($this->getRawBody(), $result);
            else
                parse_str($this->getRawBody(), $result);
            $this->_restParams = $result;
        }
        return $this->_restParams;
    }

    /**
     * Returns the raw HTTP request body.
     *
     * @return string
     */
    public function getRawBody()
    {
        static $rawBody;
        if ($rawBody === null)
            $rawBody = file_get_contents('php://input');
        return $rawBody;
    }

    /**
     * Set the request uri.
     *
     * @param string $uri            
     * @return void
     */
    public function setRequestUri($uri)
    {
        $this->_requestUri = $uri;
    }

    /**
     * Returns the request URI portion for the currently requested URL.
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getRequestUri()
    {
        if ($this->_requestUri === null) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
                $this->_requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->_requestUri = $_SERVER['REQUEST_URI'];
                if (! empty($_SERVER['HTTP_HOST'])) {
                    if (strpos($this->_requestUri, $_SERVER['HTTP_HOST']) !== false)
                        $this->_requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', $this->_requestUri);
                } else
                    $this->_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $this->_requestUri);
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
                $this->_requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (! empty($_SERVER['QUERY_STRING']))
                    $this->_requestUri .= '?' . $_SERVER['QUERY_STRING'];
            } else
                throw new \RuntimeException('Unable to determine the request URI.');
        }
        return $this->_requestUri;
    }

    /**
     * Sets the relative URL for the application.
     *
     * @param string $value            
     * @return void
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl = $value;
    }

    /**
     * Returns the relative URL for the application.
     *
     * @param boolean $absolute
     *            whether to return an absolute URL.
     * @return string
     */
    public function getBaseUrl($absolute = false)
    {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        }
        return $absolute ? $this->getHostInfo() . $this->_baseUrl : $this->_baseUrl;
    }

    /**
     * Sets the schema and host part of the application URL.
     *
     * @param string $value            
     * @return void
     */
    public function setHostInfo($value)
    {
        $this->_hostInfo = rtrim($value, '/');
    }

    /**
     * Returns the schema and host part of the application URL.
     *
     * @return string
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            if ($this->getIsSecure())
                $http = 'https';
            else
                $http = 'http';
            if (isset($_SERVER['HTTP_HOST']))
                $this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            else {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $this->getPort();
                if (('http' == $http && $port != 80) || ('https' == $http && $port != 443))
                    $this->_hostInfo .= ':' . $port;
            }
        }
        return $this->_hostInfo;
    }

    /**
     * Return if the request is sent via secure channel (https).
     *
     * @return boolean if the request is sent via secure channel (https)
     */
    public function getIsSecure()
    {
        return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
    }

    /**
     * Returns the path info of the currently requested URL.
     *
     * @return string part of the request URL that is after the entry script and
     *         before the question mark.
     * @throws Exception if the request URI cannot be determined due to improper
     *         server configuration
     */
    public function getPathInfo()
    {
        if ($this->_pathInfo === null) {
            $pathInfo = $this->getScriptUrl();
            if (! $this->getShowScriptName())
                $pathInfo = $this->getBaseUrl();
            if (null !== $pathInfo && false === $pathInfo = substr($this->getRequestUri(), strlen($pathInfo))) {
                // If substr() returns false then PATH_INFO is set to an empty string
                return $this->_pathInfo = '/';
            }
            if (true == ($pos = strpos($pathInfo, '?'))) {
                $pathInfo = substr($pathInfo, 0, $pos);
            }
            $this->_pathInfo = $pathInfo;
        }
        return $this->_pathInfo;
    }

    /**
     * Sets the relative URL for the application entry script.
     *
     * @param string $value
     *            the relative URL for the application entry script.
     */
    public function setScriptUrl($value)
    {
        $this->_scriptUrl = '/' . trim($value, '/');
    }

    /**
     * Returns the relative URL of the entry script.
     *
     * @throws RuntimeException when it is unable to determine the entry script URL.
     * @return string the relative URL of the entry script.
     */
    public function getScriptUrl()
    {
        if ($this->_scriptUrl === null) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (false !== ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName))) {
                $this->_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . "/" . $scriptName;
            } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            } else {
                throw new \RuntimeException('Unable to determine the entry script URL.');
            }
        }
        return $this->_scriptUrl;
    }

    /**
     * Returns information about the capabilities of user browser.
     *
     * @param string $userAgent
     *            the user agent to be analyzed. Defaults to null, meaning using the
     *            current User-Agent HTTP header information.
     * @return array user browser capabilities.
     * @see http://www.php.net/manual/en/function.get-browser.php
     */
    public function getBrowser($userAgent = null)
    {
        return get_browser($userAgent, true);
    }

    /**
     * Get Wether a Mobile Device send the request.
     *
     * @return boolean
     */
    public function getIsMobileDevice()
    {
        if (null === $this->_isMobileDevice) {
            $this->_isMobileDevice = (boolean) $this->getBrowser()['ismobiledevice'];
        }
        
        return $this->_isMobileDevice;
    }

    /**
     * Returns the request type, such as GET, POST, HEAD, PUT, DELETE.
     *
     * @return string request type, such as GET, POST, HEAD, PUT, DELETE.
     */
    public function getRequestType()
    {
        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

    /**
     * Returns whether this is a GET request.
     *
     * @return boolean
     */
    public function getIsGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'GET');
    }

    /**
     * Returns whether this is a POST request.
     *
     * @return boolean
     */
    public function getIsPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'POST');
    }

    /**
     * Returns whether this is a DELETE request.
     *
     * @return boolean
     */
    public function getIsDelete()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'DELETE');
    }

    /**
     * Returns whether this is a PUT request.
     *
     * @return boolean
     */
    public function getIsPut()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'PUT');
    }

    /**
     * Returns whether this is a PATCH request.
     *
     * @return boolean
     */
    public function getIsPatch()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'PATCH');
    }

    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     *
     * @return boolean
     */
    public function getIsAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Returns whether this is an Adobe Flash or Adobe Flex request.
     *
     * @return boolean
     */
    public function getIsFlash()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) && (stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
    }

    /**
     * Returns the URL referrer, null if not present
     *
     * @return string
     */
    public function getUrlReferrer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * Returns the user agent, null if not present.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * Returns the user IP address.
     *
     * @return string
     */
    public function getUserHostAddress()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * Returns the port to use for requests.
     *
     * @return int
     */
    public function getPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : ($this->getIsSecure() ? 443 : 80);
    }

    /**
     * Returns user browser accept types, null if not present.
     *
     * @return string
     */
    public function getAcceptTypes()
    {
        return isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
    }

    /**
     * Returns whether use url rewrite hide entry script name
     *
     * @return boolean
     */
    public function getShowScriptName()
    {
        if (null === $this->_showScriptName)
            $this->_showScriptName = 0 === strpos($this->getRequestUri(), $this->getScriptUrl());
        
        return $this->_showScriptName;
    }

    /**
     * Redirects the browser to the specified URL.
     *
     * @param string $url            
     * @param boolean $terminate            
     * @param integer $statusCode            
     * @return void
     */
    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        if ($url[0] == '/')
            $url = $this->getBaseUrl() . $url;
        header('Location: ' . $url, true, $statusCode);
        if ($terminate)
            exit();
    }

    /**
     * Set magic method
     *
     * @param string $name            
     * @param mixed $value            
     * @throws \UnexpectedValueException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);
        elseif (method_exists($this, 'get' . $name))
            throw new \UnexpectedValueException(sprintf('Property %s is read only', $name));
        else
            throw new \UnexpectedValueException(sprintf('Property %s is not defined', $name));
    }

    /**
     * Get magic method
     *
     * @param string $name            
     * @throws \UnexpectedValueException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter($name);
        else
            throw new \UnexpectedValueException(sprintf('Property %s is not defined', $name));
    }
}
