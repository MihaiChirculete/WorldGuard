<?php


namespace Chalapa13\WorldGuard\ResourceUtils;


class AdManager
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $adConsoleText = null;
    private $adGUI = null;

    private function __construct()
    {
        $this->adConsoleText = $this->grabConsoleAd();
        $this->adGUI = $this->getGuiAdText();
    }

    public static function getInstance()
    {
        if(self::$instance === null)
            self::$instance = new AdManager();

        return self::$instance;
    }

    private function grabConsoleAd($url = 'https://raw.githubusercontent.com/Chalapa13/WorldGuard/master/resources/sponsor_console.txt')
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

    private function grabGuiAd($url = 'https://raw.githubusercontent.com/Chalapa13/WorldGuard/master/resources/sponsor_gui.txt')
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

    public function getConsoleAdText() { return $this->adConsoleText; }
    public function getGuiAdText() { return $this->adGUI; }
}