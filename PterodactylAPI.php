<?php

class PterodactylAPI
{
    private $url = '';
    private $publicKey = '';
    
    
    private $methods = array(
      //End User Functions
        'listServers'   => array(),
        'singleServer'  => array('uuid'),
        'powerToggles'  => array('uuid','action'),
        'sendCommand'   => array('uuid','command')
    );
    
    //Tout ce qui concerne les params et method http de l'api
    private $methodsHttp = array(
        'singleServer'  => array(
                                'method'    => 'GET',
                                'url'       => '/api/client/servers/<uuid>'
                            ),
        'powerToggles'  => array(
                                'method'    => 'POST',
                                'url'       => '/api/client/servers/<uuid>/power'
                            ),
        'sendCommand'   => array(
                                'method'    => 'POST',
                                'url'       => '/api/client/servers/<uuid>/command'
                            ),
    );
    
    
    public function __construct($url, $publicKey) {
        $this->url = $url;
        $this->publicKey = $publicKey;
        
    }
    public function __call($function, $args) {
        $argnames = @$this->methods[$function];
        if (!is_array($argnames)){
            return array('success'=>false, 'errors'=>array('Unknown API method "'.$function.'()"'), 'data'=>array());
        }
        $callargs = array();
        $name = ''; 
        $value = '';
        for ($i = 0; $i < count($argnames); $i++)
        {
            //Surement a supp la condition
            if (is_array($argnames[$i])){
                $name = $argnames[$i]['name'];
            }
            else{
                $name = $argnames[$i];
            }
            //A voir pour supp la condition
            if ($i < count($args)){
                $value = $args[$i];
            }
            else if (is_array($argnames[$i]) && isset($argnames[$i]['default'])){
                if ($i >= count($args)){
                    $value = $argnames[$i]['default'];
                }
                else{
                    $value = $args[$i];
                }
            }
            else{
                return array('success'=>false, 'errors'=>array('"'.$function.'()": Not enough arguments ('.count($args).')'), 'data'=>array());
            }
            $callargs[$name] = $value;
            
        }
        return $this->call($function, $callargs);
    }
    
    public function call($method, $params = array()){
        if (!$this->url){
            return array('success'=>false, 'errors'=>array('Invalid target URL'));  
        }
            
        if (!$this->publicKey){
            return array('success'=>false, 'errors'=>array('Invalid API keys'));
        }
        //Define url
        $url = $this->url . $this->methodsHttp[$method]['url'];
        //If <uuid> in this url so we replace it
        if($params['uuid']){
          $url = str_replace('<uuid>', $params['uuid'], $url);
        }
        
        $query = '';
        $str = '';
        if (!is_array($params)){
            $params = array($params=>$params);
        }
        
        foreach ($params as $k=>$v)
        {
            if($k != 'uuid'){
                $str .= $k.$v;
                $query .= $v;
            }
        }
        $ret = $this->send($url, $query, $this->publicKey, $this->methodsHttp[$method]['method']);
        return $ret;
    }
    
    public function send($url, $query, $key, $httpMethod)
    {
        //Just for debug
        //echo PHP_EOL.'Url : '.$url.PHP_EOL;
        //echo 'Clef : '. $key.PHP_EOL;
        //echo 'Method : '.$httpMethod.PHP_EOL;
        
        $response = '';
        $error = '';
        if (function_exists('curl_init'))
        {
            $curl = curl_init(); 
            curl_setopt_array($curl, array(
               CURLOPT_URL              => $url,
               CURLOPT_RETURNTRANSFER   => true,
               CURLOPT_ENCODING         => "",
               CURLOPT_MAXREDIRS        => 10,
               CURLOPT_TIMEOUT          => 30,
               CURLOPT_HTTP_VERSION     => CURL_HTTP_VERSION_1_1,
               CURLOPT_SSL_VERIFYPEER   => false,
               CURLOPT_CUSTOMREQUEST    => $httpMethod,
               //CURLOPT_POSTFIELDS      => $query,
               CURLOPT_HTTPHEADER       => array(
                 "authorization: Bearer ".$key,
                 "content-type: application/json",
                 "Accept: application/vnd.pterodactyl.v1+json"
               ), 
            ));
            if($httpMethod == 'POST'){
                curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
            }
            $response = curl_exec($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl); 
        }
        return $response;
    }
}
