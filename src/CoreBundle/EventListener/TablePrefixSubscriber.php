<?php
namespace Runalyze\Bundle\CoreBundle\EventListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
/**
 * Description of TablePrefixSubscriber
 *
 * @author avanzu
 */
class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber {
    protected $prefix = '';
    public function __construct($prefix = '') {
        $this->prefix = (string) $prefix;
    }
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
        $classMetadata = $eventArgs->getClassMetadata();
        if(strlen($this->prefix)) {
            if(0 !== strpos($classMetadata->getTableName(), $this->prefix)) {
                $classMetadata->setTableName($this->prefix . $classMetadata->getTableName());
            }
        }
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                if(!isset($classMetadata->associationMappings[$fieldName]['joinTable'])) { continue; }
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                if(0 !== strpos($mappedTableName, $this->prefix)) {
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
                }
            }
        }
    }
    public function getSubscribedEvents() {
        return array('loadClassMetadata');
    }
}