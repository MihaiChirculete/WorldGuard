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
        $f = fopen($url, 'r');

        while(! feof($f)) {
            $line = fgets($f);
            $ad = $ad . $line;
        }

        fclose($f);

        return $ad;
    }

    public function getAdText() { return $this->adText; }
}