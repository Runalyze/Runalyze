<?php
/**
 * This file contains class::ChosenInserter
 * @package Runalyze\Model\Tag
 */

namespace Runalyze\Model\Tag;

use Runalyze\Model;

/**
 * Insert new tags from chosen to database
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Model\Tag
 */
class ChosenInserter {
	/**
	 * PDO
	 * @var \PDO
	 */
	protected $PDO;
        
	/**
	 * Array TagArrayFromChosen
	 * @var array TagArrayFromChosen
	 */
	protected $TagArrayFromChosen;
        
	/**
	 * Object
	 * @var \Runalyze\Model\Tag\Object
	 */
	protected $TagObject;
        
	/**
	 * Construct chosen inserter
	 * @param \PDO $connection
	 * @param array $TagArrayFromChosen
	 */
	public function __construct(\PDO $connection, array $TagArrayFromChosen) {
		$this->PDO = $connection;
                $this->TagArrayFromChosen = $TagArrayFromChosen;
	}
        
        /*
         * Insert Tags from Array from Chosen
         */
        public function insertTags() {
            $this->checkForNewTags();
        }
        
        /*
         * Returns TagArray for Chosen
         * @return array
         */
        public function getNewTagIDs() {
            return $this->TagArrayFromChosen;
        }
        
        /*
         * Check ChosenTagArray for new Tags
         */
        private function checkForNewTags() {
            $Factory = new Model\Factory(\SessionAccountHandler::getId());
            $allTagIDs = array_map(function ($tag) { return $tag->id(); }, $Factory->allTags());
            
            foreach($this->TagArrayFromChosen as $key => $Tag) {
                if(!in_array($Tag, $allTagIDs)) {
                    $this->setObjectwithTag($Tag);
                    $this->TagArrayFromChosen[$key] = $this->insertNewTag();

                }
            }
            $Factory->clearCache('tag');
        }
        
        /*
         * Insert a new tag
         * @return int
         */
        private function insertNewTag() {
            $InsertTag = new Model\Tag\Inserter($this->PDO, $this->TagObject);
            $InsertTag->setAccountID(\SessionAccountHandler::getId());
            $InsertTag->insert();
            return $InsertTag->insertedID();
        }
        
        /*
         * Set a new Tag object
         * @param string Tagname
         */
        private function setObjectwithTag($Tag) {
                $newTag = new Model\Tag\Entity;
                $newTag->set(Model\Tag\Entity::TAG, $Tag);
                $this->TagObject = $newTag;
        }
        
        
}