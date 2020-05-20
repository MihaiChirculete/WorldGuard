<?php


namespace Chalapa13\WorldGuard;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class ResourceManager
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $pluginInstance = null;
    private $serverInstance = null;
    private $pluginVersion = "1.1.0.1";
    private $messages = [];
    private $lang = [];
    private $config = [];
    private $regions = [];


    private function __construct(WorldGuard $plugin, Server $sv)
    {
        $this->pluginInstance = $plugin;
        $this->serverInstance = $sv;
    }

    public static function getInstance(WorldGuard $plugin, Server $sv)
    {
        if(ResourceManager::$instance === null)
            ResourceManager::$instance = new ResourceManager($plugin, $sv);

        return ResourceManager::$instance;
    }

    public function getConfig() { return $this->config; }
    public function getLanguagePack() { return $this->lang; }
    public function getMessages() { return $this->messages; }
    public function getRegions() : array { return $this->regions; }

    /** Helper functions to check if a resource file is outdated */
    public function isConfigResourceOutdated() : bool
    {
        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if(!isset($this->config['version']))
            return true;

        if($this->config['version'] !== $this->pluginVersion)
            return true;

        return false;
    }

    public function isMessagesResourceOutdated() : bool
    {
        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if(!isset($this->messages['version']))
            return true;

        if($this->messages['version'] !== $this->pluginVersion)
            return true;

        return false;
    }

    public function isLanguagePackResourceOutdated() : bool
    {
        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if(!isset($this->lang['version']))
            return true;

        if($this->lang['version'] !== $this->pluginVersion)
            return true;

        return false;
    }
    /****************************************************************** */

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
            $this->config = array(
                "version" => $this->pluginVersion,
                "language" => "en",
                "debugging" => false
            );

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
            // if the file does not exist, generate a default english one and use that file
            $this->config["language"] = "en";
            yaml_emit_file($path.'config.yml', $this->config);

            $this->lang = array(
                "version" => $this->pluginVersion,
                "author_name" => "Chalapa",
                "gui_wg_menu_title" => "World Guard Menu",
                "gui_label_choose_option" => "Choose an option",
                "gui_btn_rg_management" => "Region Management",
                "gui_btn_help" => "Help",
                "gui_btn_manage_existing" => "Manage existing region",
                "gui_btn_create_region" => "Create a new region",
                "gui_btn_redefine_region" => "Redefine a region",
                "gui_btn_delete_region" => "Delete a region",
                "gui_creation_menu_title" => "Region Creation",
                "gui_creation_menu_label1" => "Let's help you create a region.",
                "gui_creation_menu_rg_name_box" => "First you will have to enter a name for your region.",
                "gui_creation_menu_label2" => "If you want your region to expand infinitely upwards and downards check the following option.",
                "gui_creation_menu_toggle_expand" => "Expand vertically",
                "gui_creation_menu_label3" => "Now hit the §a'Submit'§r and select 2 corners of your region as you will be instructed next.",
                "gui_dropdown_select_redefine" => "Select the region you would like to redefine",
                "gui_dropdown_select_delete" => "Select the region you would like to delete",
                "gui_dropdown_select_manage" => "Select the region you would like to manage",
                "gui_manage_menu_title" => "Managing region:",
                "gui_flag_pvp" => "PvP",
                "gui_flag_xp_drops" => "Experience drops",
                "gui_flag_invincible" => "Invincible",
                "gui_flag_fall_dmg" => "Fall damage",
                "gui_flag_usage" => "Use",
                "gui_flag_item_drop" => "Item drop",
                "gui_flag_explosions" => "Explosions",
                "gui_flag_notify_enter" => "Notify enter",
                "gui_flag_notify_leave" => "Notify leave",
                "gui_flag_potions" => "Allow potions",
                "gui_flag_allowed_enter" => "Allowed enter",
                "gui_flag_allowed_leave" => "Allowed leave",
                "gui_flag_gm" => "Gamemode",
                "gui_gm_survival" => "Survival",
                "gui_gm_creative" => "Creative",
                "gui_flag_sleep" => "Allow sleeping",
                "gui_flag_send_chat" => "Allow sending chat messages",
                "gui_flag_rcv_chat" => "Allow receiving chat messages",
                "gui_flag_enderpearl" => "Allow use of ender pearls",
                "gui_flag_fly_mode" => "Fly mode",
                "gui_enabled" => "Enabled",
                "gui_disabled" => "Disabled",
                "gui_flag_eat" => "Allow eating",
                "gui_flag_dmg_animals" => "Allow damaging of animals",
                "gui_flag_dmg_monsters" => "Allow damaging of monsters",
                "gui_flag_leaf_decay" => "Allow leaf decay",
                "gui_flag_plant_growth" => "Allow plant growth",
                "gui_flag_spread" => "Allow spreading",
                "gui_flag_block_burn" => "Allow block burn",
                "gui_flag_priority" => "Region priority",
                "gui_help_menu_label1" => "If you need help setting up world guard check out the tutorial we made for you:",
                "gui_help_menu_label2" => "http://worldguard.ddns.net/tutorial"
            );

            yaml_emit_file($path.'lang_en.yml', $this->lang);
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
            $this->messages = array (
                "version" => $this->pluginVersion,
                "denied-enter" => "You cannot enter this area.",
                "denied-leave" => "You cannot leave this area.",
                "no-permission-for-command" => "You do not have permission to use this command.",
                "denied-eat" => "You cannot eat in this area.",
                "denied-ender-pearls" => "You cannot use ender pearls in this area.",
                "denied-chat" => "You cannot chat in this region.",
                "denied-item-drop" => "You cannot drop items in this region.",
                "denied-pvp" => "You cannot hurt players of this region.",
                "denied-block-break" => "You cannot break blocks in this region.",
                "denied-block-place" => "You cannot place blocks in this region.",
                "denied-hurt-animal" => "You cannot hurt animals of this region.",
                "denied-hurt-monster" => "You cannot hurt monsters of this region."
            );

            yaml_emit_file($path.'messages.yml', $this->messages);
        }
    }

}
