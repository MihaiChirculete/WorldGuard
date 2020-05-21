<?php


namespace Chalapa13\WorldGuard\ResourceUtils;


class AdManager
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $adText = null;

    private function __construct()
    {
        $this->adText = $this->grabAd();
    }

    public static function getInstance()
    {
        if(self::$instance === null)
            self::$instance = new AdManager();

        return self::$instance;
    }

    private function grabAd($url = 'https://raw.githubusercontent.com/Chalapa13/WorldGuard/master/resources/sponsor.txt')
    {
        $ad = "\n";

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        $ad = $ad . file_get_contents($url, false, stream_context_create($arrContextOptions));


        return $ad;
    }

    public function getAdText() { return $this->adText; }
}