<?phpnamespace P3rc1val\routing;use P3rc1val\logger\Logger;use P3rc1val\logger\LoggerInterface;use P3rc1val\routing\matcher\MatcherInterface;use P3rc1val\routing\matcher\ParametricMatcher;use P3rc1val\Url;/* * Use cases: * Router $router->get('preg', function($preg_params){ *      //first option -> return *      return [ Router::JSON => json_serializable_data] *      //second option -> Resolver *      Router::resolver(Router::JSON) *        ->resolve('path.to.object', data) *        ->resolve() other resolve... *        ->end() //end ends this function execution * }) * Router $router->post('preg....) => WORKS AS ->get BUT FOR POST REQUESTS * * Router::JSON -> json result * Router::RAW -> raw text (resolve() concatenates final text, path arg only) * Router::REDIRECT -> redirects to url *   Can use $GET params: *   Router::resolver(Router::REDIRECT) *        ->resolve('DESTINATION_URL') *        ->resolve(['param1' => 'value1']) *        ->resolve(['param2' => 'value2', 'param3' => 'value3']) *        ... ->end() * * * * */class Router {    const GET = 'get';    const POST = 'post';    const CONTENT = 'content';    const REDIRECT = 'redirect';    const PARAMS = 'params';    const JSON = 'json';    const RAW = 'raw';    /* @var MatcherInterface */    protected $matcher;    const LOGGER_ID = 'ROUTER';    protected $logger;    private $getRoutes = [];    private $postRoutes = [];    private $url = null;    private $routedData = [];    public function __construct() {        $this->matcher = new ParametricMatcher();        $this->logger = Logger::get();        $this->logger->setLoggerName(self::LOGGER_ID);        $this->logger->setFileName('HTTP.log');    }    public function setMatcher(MatcherInterface $matcher){        $this->matcher = $matcher;    }    public function route($url, $method = self::GET){        $this->url = $url;        $this->routedData = $this->getRoutingData($this->url, new RequestMethod($method));    }    public function getContent($url = null, $method = 'get') {        $content = $this->getRoutingResult('content', $url, $method);        if(!$content){            return '';        }        return $content;    }    private function getDefaultRoutingActions(){        return [            Router::RAW => function($result){                echo($result); exit();            },            Router::REDIRECT => function($result){                Url::redirect($result);            },            Router::JSON => function($result){                echo(json_encode($result)); exit();            }        ];    }    /**     * Executes actions for output types     * @param $sequence array     */    public function applyRoutingResult($sequence = null){        if($sequence === null){            $sequence = $this->getDefaultRoutingActions();        }        foreach ($sequence as $name => $action) {            if($this->getRoutingResult($name) !== null){                $action($this->getRoutingResult($name));            }        }    }    public function getRoutingResult($key, $url = null, $method = 'get'){        if(!$url){            $data = $this->routedData;        }else{            $data = $this->getRoutingData($url, $method);        }        if (is_array($data)) {            if (array_key_exists($key, $data)) {                if ($key === Router::REDIRECT and array_key_exists(Router::PARAMS, $data)){                    return $data[$key].'?'.$this->parseParams($data[Router::PARAMS]);                }                return $data[$key];            }            return null;        }        return $data;    }    private function resolve(callable $resolver, array $data, RequestMethod $requestMethod){        try{            $result = $resolver($data, $requestMethod);        }catch (ResolverEndException $e){            $result = $e->get();        }        return $result;    }    private function getRoutingData(string $url, RequestMethod $method) {        $this->logger->log('URL: '.$url);        $this->logger->log('METHOD: '.$method);        $array = ($method->isGet() ? $this->getRoutes : $this->postRoutes);        foreach ($array as $route => $pageData) {            $matchResult = $this->matcher->_match($url, $route);            if (count($matchResult) != 0) {                $this->logger->log("RESULT");                $this->logger->log("> MATCHER: ".get_class($this->matcher));                $this->logger->log("> DATA: ".print_r($matchResult, true));                $this->logger->endLog();                return $this->resolve($pageData, $matchResult, $method);            }        }        $this->logger->log("RESULT: NOT FOUND", LoggerInterface::LOG_WARN);        $this->logger->endLog();        return null;    }    public function get($route, callable $method) {        $this->getRoutes[$route] = $method;    }    public function post($route, $method) {        $this->postRoutes[$route] = $method;    }    /**     * @param $params array     */    private function parseParams($params){        return http_build_query($params, '', '&');    }    public static function resolver($type){        return new Resolver($type);    }}