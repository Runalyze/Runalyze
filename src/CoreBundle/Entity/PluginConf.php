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
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     *   @ORM\JoinColumn(name="pluginid", referencedColumnName="id")
     * })
     */
    private $pluginid;


}

