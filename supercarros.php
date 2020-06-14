<?php

Class SuperCarRos {

    protected $site_url = 'https://www.supercarros.com/';
    protected $xpath = array (
        'page_numbers' => '/html/body/div[3]/div/div[2]/div[1]/div/div[3]/div[4]/div[1]/ul/li/a',
        'cars' => '/html/body/div[3]/div/div[2]/div[1]/div/div[3]/ul/li/a'
    );
    public $dealer_pages = array ();
    public $car_pages = array ();

    function __construct ($url) {
        $this->dealer_pages[] = new Page ($url);
        $this->collectDealerPages (2);
        $this->collectCarPages (4);
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

    function test () {
        return json_encode (array (
            'dealer_pages' => count ($this->dealer_pages),
            'car_pages' => count ($this->car_pages)
        ));
    }
}

?>