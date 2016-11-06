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
     * @var integer
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
     * @var \Plugin
     *
     * @ORM\ManyToOne(targetEntity="Plugin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pluginid", referencedColumnName="id", nullable=false, onDelete="cascade")
     * })
     */
    private $plugin;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return PluginConf
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set plugin
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Plugin $account
     *
     * @return PluginConf
     */
    public function setPlugin(\Runalyze\Bundle\CoreBundle\Entity\Plugin $plugin = null)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Get plugin
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}

