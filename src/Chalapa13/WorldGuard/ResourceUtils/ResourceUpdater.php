<?php


namespace Chalapa13\WorldGuard\ResourceUtils;


/** This class is an utility that will be used to fix conflicts between old and new resources
 * in order to avoid crashes due to changes in resource files between updates of the plugin
 */
class ResourceUpdater
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $resourceManagerInstance = null;

    private $defaultConfig = null;
    private $defaultMessages = null;
    private $defaultLanguagePack = null;

    private function __construct(ResourceManager $resourceManagerInstance)
    {
        $this->resourceManagerInstance = $resourceManagerInstance;

        $this->defaultConfig = array(
            "version" => $this->resourceManagerInstance->getPluginVersion(),
            "language" => "en",
            "debugging" => false);

        $this->defaultMessages = array (
            "version" => $this->resourceManagerInstance->getPluginVersion(),
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

        $this->defaultLanguagePack = array(
            "version" => $this->resourceManagerInstance->getPluginVersion(),
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
            "gui_creation_menu_label3" => "Now hit the Â§a'Submit'Â§r and select 2 corners of your region as you will be instructed next.",
            "gui_dropdown_select_redefine" => "Select the region you would like to redefine",
            "gui_dropdown_select_delete" => "Select the region you would like to delete",
            "gui_dropdown_select_manage" => "Select the region you would like to manage",
            "gui_manage_menu_title" => "Managing region:",
            "gui_flag_pluginbypass" => "Plugin bypass for USE and FLOW",
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
            "gui_gm_adventure" => "Adventure",
            "gui_flag_sleep" => "Allow sleeping",
            "gui_flag_send_chat" => "Allow sending chat messages",
            "gui_flag_rcv_chat" => "Allow receiving chat messages",
            "gui_flag_enderpearl" => "Allow use of ender pearls",
            "gui_flag_fly_mode" => "Fly mode",
            "gui_enabled" => "Enabled",
            "gui_disabled" => "Disabled",
            "gui_flag_eat" => "Allow eating",
            "gui_flag_hunger" => "Disable Hunger",
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
    }

    public static function getInstance(ResourceManager $resourceManagerInstance)
    {
        if(ResourceUpdater::$instance === null)
            ResourceUpdater::$instance = new ResourceUpdater($resourceManagerInstance);

        return ResourceUpdater::$instance;
    }

    /** Helper functions to check if a resource file is outdated */
    public function isConfigResourceOutdated() : bool
    {
        $ver = $this->resourceManagerInstance->getConfigVersion();

        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if($ver === null)
            return true;

        if($ver !== $this->resourceManagerInstance->getPluginVersion())
            return true;

        return false;
    }

    public function isMessagesResourceOutdated() : bool
    {
        $ver = $this->resourceManagerInstance->getMessagesVersion();

        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if($ver === null)
            return true;

        if($ver !== $this->resourceManagerInstance->getPluginVersion())
            return true;

        return false;
    }

    public function isLanguagePackResourceOutdated() : bool
    {
        $ver = $this->resourceManagerInstance->getLanguagePackVersion();

        /** Old versions do not have this field so if its not set its obviously an outdated one */
        if($ver === null)
            return true;

        if($ver !== $this->resourceManagerInstance->getPluginVersion())
            return true;

        return false;
    }
    /****************************************************************** */

    public function getDefaultConfig() { return $this->defaultConfig; }
    public function getDefaultMessages() { return $this->defaultMessages; }
    public function getDefaultLanguagePack() { return $this->defaultLanguagePack; }

    /** For each resource file check it's version and if it doesn't match have it updated */
    public function updateResourcesIfRequired($forceUpdate = false)
    {
        if($this->isConfigResourceOutdated() || $forceUpdate === true)
        {
            $oldConfig = $this->resourceManagerInstance->getConfig();

            $newConfigKeys = array_keys($this->getDefaultConfig());

            /** If a key from the new config is not present in the old config, then add it */
            foreach ($newConfigKeys as $key)
            {
                if(!isset($oldConfig[$key]))
                    $oldConfig[$key] = $this->getDefaultConfig()[$key];
            }

            /** Change the file version to match the current version */
            $oldConfig['version'] = $this->resourceManagerInstance->getPluginVersion();

            $this->resourceManagerInstance->saveConfig($oldConfig);
        }

        if($this->isMessagesResourceOutdated() || $forceUpdate === true)
        {
            $oldMessages = $this->resourceManagerInstance->getMessages();

            $newMessagesKeys = array_keys($this->getDefaultMessages());
            foreach ($newMessagesKeys as $key)
            {
                if(!isset($oldMessages[$key]))
                    $oldMessages[$key] = $this->getDefaultMessages()[$key];
            }

            $oldMessages['version'] = $this->resourceManagerInstance->getPluginVersion();

            $this->resourceManagerInstance->saveMessages($oldMessages);
        }

        if($this->isLanguagePackResourceOutdated() || $forceUpdate === true)
        {
            $oldLangPack = $this->resourceManagerInstance->getLanguagePack();

            $newLangPackKeys = array_keys($this->getDefaultLanguagePack());
            foreach ($newLangPackKeys as $key)
            {
                if(!isset($oldLangPack[$key]))
                    $oldLangPack[$key] = $this->getDefaultLanguagePack()[$key];
            }

            $oldLangPack['version'] = $this->resourceManagerInstance->getPluginVersion();

            $this->resourceManagerInstance->saveLanguagePack($oldLangPack);
        }
    }
}
