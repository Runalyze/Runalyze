<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PluginConf
 *
 * @ORM\Table(name="plugin_conf", indexes={@ORM\Index(name="pluginid", columns={"pluginid"})})
 * @ORM\Entity
 */
class PluginConf
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="string", length=100, nullable=false)
     */
    private $config;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @var Plugin
     *
     * @ORM\ManyToOne(targetEntity="Plugin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pluginid", referencedColumnName="id", nullable=false)
     * })
     */
    private $plugin;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Plugin $plugin
     * @return $this
     */
    public function setPlugin(\Runalyze\Bundle\CoreBundle\Entity\Plugin $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}

