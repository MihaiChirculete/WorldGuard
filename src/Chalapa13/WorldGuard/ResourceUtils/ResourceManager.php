<?php


namespace Chalapa13\WorldGuard\ResourceUtils;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use Chalapa13\WorldGuard\WorldGuard;

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
    private $langList = [];


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

    public function getLangResource()
    {
        $result = [];

        foreach($this->plugin->getResources() as $resource)
        {
            if(mb_strpos($resource, "lang_") !== false)
                $result[] = substr($resource, 5, 0);
        }

        $this->langList = $result;

    }

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
        $this->loadLanguageFolder($path);
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

    public function loadLanguagePack($path)
    {
        /**
         * load language file
         */
        if (is_file($path . "lang_" . $this->config["language"] . ".yml")) {
            $this->lang = yaml_parse_file($path . "lang_" . $this->config["language"] . ".yml");
        } else {
            
            $langfile = $this->getFile()->getLangResource . $this->config["language"]. ".yml";

            if (!$this->config["language"] = "en" && $langfile = "lang_" . $this->config["language"]) {
                // load all lang files from ressource
                
            } else {

            // if the file does not exist, generate a default english one and use that file
            $this->config["language"] = "en";
            yaml_emit_file($path.'config.yml', $this->config);

            $this->lang = $this->resUpdaterInstance->getDefaultLanguagePack();

            yaml_emit_file($path.'lang_en.yml', $this->lang);
            }
        }
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
