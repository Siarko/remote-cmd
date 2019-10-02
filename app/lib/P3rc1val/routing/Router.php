<?phpnamespace P3rc1val\routing;use P3rc1val\Url;/* * Use cases: * Router $router->get('preg', function($preg_params){ *      //first option -> return *      return [ Router::JSON => json_serializable_data] *      //second option -> Resolver *      Router::resolver(Router::JSON) *        ->resolve('path.to.object', data) *        ->resolve() other resolve... *        ->end() //end ends this function execution * }) * Router $router->post('preg....) => WORKS AS ->get BUT FOR POST REQUESTS * * Router::JSON -> json result * Router::RAW -> raw text (resolve() concatenates final text, path arg only) * Router::REDIRECT -> redirects to url *   Can use $GET params: *   Router::resolver(Router::REDIRECT) *        ->resolve('DESTINATION_URL') *        ->resolve(['param1' => 'value1']) *        ->resolve(['param2' => 'value2', 'param3' => 'value3']) *        ... ->end() * * * * */class Router {    const GET = 'get';    const POST = 'post';    const CONTENT = 'content';    const REDIRECT = 'redirect';    const PARAMS = 'params';    const JSON = 'json';    const RAW = 'raw';    private $getRoutes = [];    private $postRoutes = [];    private $url = null;    private $routedData = [];    public function route($url, $method = self::GET){        $this->url = $url;        $this->routedData = $this->getRoutingData($this->url, $method);    }    public function getContent($url = null, $method = 'get') {        $content = $this->getRoutingResult('content', $url, $method);        if(!$content){            return '';        }        return $content;    }    private function getDefaultRoutingActions(){        return [            Router::RAW => function($result){                echo($result); exit();            },            Router::REDIRECT => function($result){                Url::redirect($result);            },            Router::JSON => function($result){                echo(json_encode($result)); exit();            }        ];    }    /**     * Executes actions for output types     * @param $sequence array     */    public function applyRoutingResult($sequence = null){        if($sequence === null){            $sequence = $this->getDefaultRoutingActions();        }        foreach ($sequence as $name => $action) {            if($this->getRoutingResult($name) !== null){                $action($this->getRoutingResult($name));            }        }    }    public function getRoutingResult($key, $url = null, $method = 'get'){        if(!$url){            $data = $this->routedData;        }else{            $data = $this->getRoutingData($url, $method);        }        if (is_array($data)) {            if (array_key_exists($key, $data)) {                if ($key === Router::REDIRECT and array_key_exists(Router::PARAMS, $data)){                    return $data[$key].'?'.$this->parseParams($data[Router::PARAMS]);                }                return $data[$key];            }            return null;        }        return $data;    }    /*private function getRoutingData($url, $method) {        $array = ($method == self::GET?$this->getRoutes:$this->postRoutes);        foreach ($array as $route => $pageData) {            $tmp = null;            preg_match('#'.$route.'#', $url, $tmp);            if (count($tmp) != 0) {                return $pageData($tmp);            }        }        return null;    }*/    private function resolve($method, $data){        try{            $result = $method($data);        }catch (ResolverEndException $e){            $result = $e->get();        }        return $result;    }    private function getRoutingData($url, $method) {        $d = "URL: ".$url." METHOD: ".$method." \n";        file_put_contents("HTTP.log", $d, FILE_APPEND);        $array = ($method == self::GET?$this->getRoutes:$this->postRoutes);        foreach ($array as $route => $pageData) {            $tmp = null;            preg_match('#'.$route.'#', $url, $tmp);            if (count($tmp) != 0) {                return $this->resolve($pageData, $tmp);            }        }        return null;    }    public function get($route, $method) {        $this->getRoutes[$route] = $method;    }    public function post($route, $method) {        $this->postRoutes[$route] = $method;    }    /**     * @param $params array     */    private function parseParams($params){        return http_build_query($params, '', '&');    }    public static function resolver($type){        return new Resolver($type);    }}