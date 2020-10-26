<?php

namespace MihaiChirculete\WorldGuard\ResourceUtils;

use pocketmine\Server;
use MihaiChirculete\WorldGuard\WorldGuard;


class ResourceManager
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $resUpdaterInstance = null;
    private $pluginInstance = null;
    private $serverInstance = null;
    private $pluginVersion = null;
    private $messages = [];
    private $lang = [];
    private $config = [];
    private $regions = [];
    private function __construct(WorldGuard $plugin, Server $sv)
    {
        $this->pluginInstance = $plugin;
        $this->serverInstance = $sv;
        $this->resUpdaterInstance = ResourceUpdater::getInstance($this);

        $this->pluginVersion = $this->pluginInstance->getDescription()->getVersion();
    }
    public static function getInstance(WorldGuard $plugin, Server $sv)
    {
        if(ResourceManager::$instance === null)
            ResourceManager::$instance = new ResourceManager($plugin, $sv);

        return ResourceManager::$instance;
    }

    //Base Lang from Client -> looked into MyPlot  files 
    /*public function getLanguage() : BaseLang {
        return $this->baseLang;
    }
    
    public function getFallBackLang() : BaseLang {
        return new BaseLang(BaseLang::FALLBACK_LANGUAGE, $this->getFile() . "resources/");
    }
    public function onLoad() : void {
    $this->getLogger()->debug(TF::BOLD . "Loading Languages");
    // Loading Languages
    // @var string $lang 
    $lang = $this->getConfig()->get("Language", BaseLang::FALLBACK_LANGUAGE);
    if($this->getConfig()->get("Custom Messages", false)) {
        if(!file_exists($this->getDataFolder()."lang.ini")) {
            // @var string|resource $resource 
            $resource = $this->getResource($lang.".ini") ?? file_get_contents($this->getFile()."resources/".BaseLang::FALLBACK_LANGUAGE.".ini");
            file_put_contents($this->getDataFolder()."lang.ini", $resource);
            if(!is_string($resource)) {
                // @var resource $resource 
                fclose($resource);
            }
            $this->saveResource(BaseLang::FALLBACK_LANGUAGE.".ini", true);
            $this->getLogger()->debug("Custom Language ini created");
        }
        $this->baseLang = new BaseLang("lang", $this->getDataFolder());
    }else{
        if(file_exists($this->getDataFolder()."lang.ini")) {
            unlink($this->getDataFolder()."lang.ini");
            unlink($this->getDataFolder().BaseLang::FALLBACK_LANGUAGE.".ini");
            $this->getLogger()->debug("Custom Language ini deleted");
        }
        $this->baseLang = new BaseLang($lang, $this->getFile() . "resources/");
    }
    }*/
    public function getConfig() { return $this->config; }
    public function getLanguagePack() { return $this->lang; }
    public function getMessages() { return $this->messages; }
    public function getRegions() : array { return $this->regions; }
    public function getPluginVersion() { return $this->pluginVersion; }

    public function getConfigVersion()
    {
        if(isset($this->config['version']))
            return $this->config['version'];

        return null;
    }

    public function getLanguagePackVersion()
    {
        if(isset($this->lang['version']))
            return $this->lang['version'];

        return null;
    }

    public function getMessagesVersion()
    {
        if(isset($this->messages['version']))
            return $this->messages['version'];

        return null;
    }


    public function loadResources()
    {
        if (!is_dir($path = $this->pluginInstance->getDataFolder())) {
            mkdir($path);
        }
        $this->loadConfig($path);
        $this->loadLanguagePack($path);
        $this->loadMessages($path);
        $this->loadRegions($path);
    }

    public function loadRegions($path)
    {
        /**
         * load regions if file exists and if not create a file
         */
        if (is_file($path.'regions.yml')) {
            $this->regions = yaml_parse_file($path.'regions.yml');
        } else {
            yaml_emit_file($path.'regions.yml', []);
        }
    }

    public function saveRegions($regions){
        $this->regions = $regions;

        $data = [];
        foreach ($regions as $name => $region) {
            $data[$name] = $region->toArray();
        }
        yaml_emit_file($this->pluginInstance->getDataFolder().'regions.yml', $data);
        return true;
    }

    public function loadConfig($path)
    {
        /**
         * load config if file exists and if not create a file
         */
        if (is_file($path.'config.yml')) {
            $this->config = yaml_parse_file($path.'config.yml');
        } else {
            $this->config = $this->resUpdaterInstance->getDefaultConfig();

            yaml_emit_file($path.'config.yml', $this->config);
        }
    }
    

    public function loadLanguagePack()
    {
        $path = $this->pluginInstance->getDataFolder();
        $langFile = "lang_" . $this->config["language"] . ".yml";
        //if not in phar ressource ord plugin_folder
        if (!is_file($path . $langFile) && !$this->pluginInstance->saveResource($langFile)) {
            //use en lang file
            $langFile = "lang_en.yml";
            $this->config["language"] = "en";
            yaml_emit_file($path.'config.yml', $this->config);
            //create default en lang file
            if (!is_file($path . $langFile) && !$this->pluginInstance->saveResource($langFile)) {
                $this->lang = $this->resUpdaterInstance->getDefaultLanguagePack();
                yaml_emit_file($path.'lang_en.yml', $this->lang);
                return;
            }
        }
        // load language
        $this->lang =  yaml_parse_file($path . $langFile);
    }

    public function loadMessages($path)
    {
        /**
         * load messages if file exists and if not write the default ones
         */
        if(is_file($path.'messages.yml'))
        {
            $this->messages = yaml_parse_file($path.'messages.yml');
        }
        else{
            $this->messages = $this->resUpdaterInstance->getDefaultMessages();

            yaml_emit_file($path.'messages.yml', $this->messages);
        }
    }

    public function saveConfig($config)
    {
        $this->config = $config;

        $path = $this->pluginInstance->getDataFolder();
        yaml_emit_file($path.'config.yml', $this->config);
    }

    public function saveMessages($messages)
    {
        $this->messages = $messages;

        $path = $this->pluginInstance->getDataFolder();
        yaml_emit_file($path.'messages.yml', $this->messages);
    }

    public function saveLanguagePack($langPack)
    {
        $this->lang = $langPack;

        $path = $this->pluginInstance->getDataFolder();
        yaml_emit_file($path . "lang_" . $this->config["language"] . ".yml", $this->lang);
    }

}
