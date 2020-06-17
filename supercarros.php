<?php

Class SuperCarRos {

    protected $site_url = 'https://www.supercarros.com/';
    protected $xpath = array (
        'page_numbers' => '/html/body/div[3]/div/div[2]/div[1]/div/div[3]/div[4]/div[1]/ul/li/a',
        'cars' => '/html/body/div[3]/div/div[2]/div[1]/div/div[3]/ul/li/a',

        'car_name' => '/html/body/div[3]/div/div[2]/div[1]/div[1]/h1',
        'car_price'=> '/html/body/div[3]/div/div[2]/div[1]/div[1]/h3',
        'car_address' => '/html/body/div[3]/div/div[2]/div[2]/ul/li[7]',
        'car_body' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[3]/td[4]',
        'car_mileage' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[4]/td[4]',
        'car_fuel' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[5]/td[2]',
        'car_engine' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[2]/td[2]',
        'car_transmission' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[6]/td[2]',
        'car_drive' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[7]/td[2]',
        'car_exterior-color' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[3]/td[2]',
        'car_interior-color' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[5]/table/tr[4]/td[2]',

        'car_photos' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[1]/ul/li/a/img',
        'car_gmap' => '/html/body/div[3]/div/div[2]/div[2]/ul/li[7]/iframe',
        'car_features' => '/html/body/div[3]/div/div[2]/div[1]/div[2]/div[2]/div[6]/ul/li'
    );
    public $dealer_pages = array ();
    public $car_pages = array ();

    function __construct ($url) {
        $this->dealer_pages[] = new Page ($url);
        $this->collectDealerPages ();
        $this->collectCarPages ();
    }

    private function collectDealerPages ($limit = false) {
        foreach ($this->dealer_pages[0]->dom->query($this->xpath['page_numbers']) as $index => $page_link) {
            if (0 === $index) continue;
            if ('»' === $page_link->nodeValue) continue;
            if (false !== $limit && $index >= $limit) continue;
            $href = $page_link->getAttribute ('href');
            $prefix = explode ('/Dealers', $this->dealer_pages[0]->url)[0];
            $url = $prefix . $href;
            $this->dealer_pages[] = new Page ($url);
        }
    }

    private function collectCarPages ($limit = false) {
        foreach ($this->dealer_pages as $page) {
            $dom = $page->dom;
            $xpath = $this->xpath['cars'];
            foreach ($dom->query($xpath) as $index => $link) {
                if (false !== $limit && $index >= $limit) continue;
                $url = $this->site_url . $link->getAttribute('href');
                $this->car_pages[] = new Page ($url);
            }
        }
    }

    function getCars () {
        $cars = array();
        foreach ($this->car_pages as $car_page) {
            $car = array ();
            $attributes = array (
                'car_name',
                'car_price',
                'car_address',
                'car_body',
                'car_mileage',
                'car_fuel',
                'car_engine',
                'car_transmission',
                'car_drive',
                'car_exterior-color',
                'car_interior-color'
            );
            foreach ($attributes as $field) $car[$field] = $this->domQuery ($car_page->dom, $field);
            $car['car_photos'] = $this->domQueryPhotos ($car_page->dom, 'car_photos');
            $car['car_gmap'] = $this->domQueryMap ($car_page->dom, 'car_gmap');
            $car['car_lat'] = trim (explode (',', $car['car_gmap'])[0]);
            $car['car_lng'] = trim (explode (',', $car['car_gmap'])[1]);
            $car['car_features'] = $this->domQueryFeatures ($car_page->dom, 'car_features');
            $cars[] = $car;
        }
        return $cars;
    }

    function domQuery ($dom, $element) {
        return $dom->query($this->xpath[$element])->item(0)->nodeValue;
    }

    function domQueryPhotos ($dom, $element) {
        $src = array ();
        foreach ($dom->query($this->xpath[$element]) as $node) {
            $src[] = $node->getAttribute('src');
        }
        return $src;
    }

    function domQueryFeatures ($dom, $element) {
        $features = array ();
        foreach ($dom->query($this->xpath[$element]) as $node) {
            $features[] = $node->nodeValue;
        }
        return $features;
    }

    function domQueryMap ($dom, $element) {
        $src = $dom->query($this->xpath[$element])->item(0)->getAttribute('src');
        $latLng = explode ('&zoom', explode ('er=', $src)[1])[0];
        return $latLng;
    }

    function test () {
        return 'anything you want from the class';
    }
}

?>