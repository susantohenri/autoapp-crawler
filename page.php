<?php

Class Page {
    public $url;
    public $dom;

    function __construct ($url) {
        $this->url = $url;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Cookie: __cfduid=d19e8c8a1677ad2d060c3143db6a40bda1592099097"
          ),
        ));
        
        $response = curl_exec($curl);
        $file = plugin_dir_path( __FILE__ ) . 'page.html';
        file_put_contents($file, $response);
        curl_close($curl);
    
        $dom = new DOMDocument();
        $dom->loadHTML($response);
        $domXpath = new DomXpath($dom);
    
        $this->dom = $domXpath;
    }

}



?>