<?php


namespace Chalapa13\WorldGuard;


/** This class is an utility that will be used to fix conflicts between old and new resources
 * in order to avoid crashes due to changes in resource files between updates of the plugin
 */
class ResourceUpdater
{
    /** Only 1 instance of this class will be allowed at all times */
    private static $instance = null;
    private $resouceManagerInstance = null;

    private function __construct(ResourceManager $resouceManagerInstance)
    {
        $this->resouceManagerInstance = $resouceManagerInstance;
    }

    public static function getInstance(ResourceManager $resouceManagerInstance)
    {
        if(ResourceUpdater::$instance === null)
            ResourceUpdater::$instance = new ResourceUpdater($resouceManagerInstance);

        return ResourceUpdater::$instance;
    }

    /** TO-DO: Code this function */
    public function updateResourcesIfRequired()
    {

    }
}