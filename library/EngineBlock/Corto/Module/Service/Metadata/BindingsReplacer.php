<?php
/**
 * Replaces bindings like SingleSignOn and SingleLogout with bindings of EngineBlock
  */

use EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception as Exception;

class EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer
{
    private $serviceName;
    
    /**
     * @var array
     */
    private $knownBindings = array(
        EngineBlock_Corto_Module_Services::BINDING_TYPE_HTTP_REDIRECT,
        EngineBlock_Corto_Module_Services::BINDING_TYPE_HTTP_POST
    );

    /**
     * @var array
     */
    private $supportedBindings;

    /**
     * @param array $proxyEntity
     * @param string $serviceName
     */
    public function __construct(array $proxyEntity, $serviceName)
    {
        $this->serviceName = $serviceName;
        $this->supportedBindings = $this->getSupportedBindingsFromProxy($proxyEntity, $serviceName);
    }

    /**
     * @param array &$entity
     * @param string $location
     */
    public function replace(array &$entity, $location)
    {
        $entity[$this->serviceName] = array();
        foreach($this->supportedBindings as $binding) {
            $entity[$this->serviceName][] = array(
                'Location'=> $location,
                'Binding' => $binding
            );
        }
    }

    /**
     * Builds a list of bindings supported by the proxy
     *
     * @param array $proxyEntity
     * @return array
     * @throws Exception
     */
    private function getSupportedBindingsFromProxy(array $proxyEntity)
    {
        if (!isset($proxyEntity[$this->serviceName])) {
            throw new Exception("'No service '$this->serviceName' is configured in EngineBlock metadata");
        }

        $services = $proxyEntity[$this->serviceName];
        if (!is_array($services)) {
            throw new Exception("Service '$this->serviceName' in EngineBlock metadata is not an array");
        }

        $supportedBindings = array();
        foreach($services as $serviceInfo) {
            if (!isset($serviceInfo['Binding'])) {
                throw new Exception("Service '$this->serviceName' configured without a Binding in EngineBlock metadata");
            }

            $binding = $serviceInfo['Binding'];
            if (!in_array($binding, $this->knownBindings)) {
                throw new Exception("Service '$this->serviceName' has an invalid binding '$binding' configured in EngineBlock metadata");
            }

            $supportedBindings[] = $binding;
        }

        if (count($supportedBindings) === 0) {
            throw new Exception("No '$this->serviceName' bindings configured in EngineBlock metadata");
        }

        return $supportedBindings;
    }
}