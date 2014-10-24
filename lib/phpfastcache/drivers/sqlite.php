<?php

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */


class phpfastcache_sqlite extends phpFastCache implements phpfastcache_driver  {
    var $max_size = 10; // 10 mb

    var $instant = array();
    var $indexing = NULL;
    var $path = "";

    var $currentDB = 1;

    /*
     * INIT NEW DB
     */
    function initDB(PDO $db) {
        $db->exec('drop table if exists "caching"');
        $db->exec('CREATE TABLE "caching" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "keyword" VARCHAR UNIQUE, "object" BLOB, "exp" INTEGER)');
        $db->exec('CREATE UNIQUE INDEX "cleaup" ON "caching" ("keyword","exp")');
        $db->exec('CREATE INDEX "exp" ON "caching" ("exp")');
        $db->exec('CREATE UNIQUE INDEX "keyword" ON "caching" ("keyword")');
    }

    /*
     * INIT Indexing DB
     */
    function initIndexing(PDO $db) {

        // delete everything before reset indexing
        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file != "." && $file!=".." && $file != "indexing" && $file!="dbfastcache") {
                @unlink($this->path."/".$file);
            }
        }

        $db->exec('drop table if exists "balancing"');
        $db->exec('CREATE TABLE "balancing" ("keyword" VARCHAR PRIMARY KEY NOT NULL UNIQUE, "db" INTEGER)');
        $db->exec('CREATE INDEX "db" ON "balancing" ("db")');
        $db->exec('CREATE UNIQUE INDEX "lookup" ON "balacing" ("keyword")');

    }

    /*
     * INIT Instant DB
     * Return Database of Keyword
     */
    function indexing($keyword) {
        if($this->indexing == NULL) {
            $createTable = false;
            if(!file_exists($this->path."/indexing")) {
                $createTable = true;
            }

            $PDO = new PDO("sqlite:".$this->path."/indexing");
            $PDO->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);

            if($createTable == true) {
                $this->initIndexing($PDO);
            }
            $this->indexing = $PDO;
            unset($PDO);

            $stm = $this->indexing->prepare("SELECT MAX(`db`) as `db` FROM `balancing`");
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            if(!isset($row['db'])) {
                $db = 1;
            } elseif($row['db'] <=1 ) {
                $db = 1;
            } else {
                $db = $row['db'];
            }

            // check file size

            $size = file_exists($this->path."/db".$db) ? filesize($this->path."/db".$db) : 1;
            $size = round($size / 1024 / 1024,1);


            if($size > $this->max_size) {
                $db = $db + 1;
            }
            $this->currentDB = $db;

        }

        // look for keyword
        $stm = $this->indexing->prepare("SELECT * FROM `balancing` WHERE `keyword`=:keyword LIMIT 1");
        $stm->execute(array(
             ":keyword"  => $keyword
        ));
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        if(isset($row['db']) && $row['db'] != "") {
            $db = $row['db'];
        } else {
            /*
             * Insert new to Indexing
             */
            $db = $this->currentDB;
            $stm = $this->indexing->prepare("INSERT INTO `balancing` (`keyword`,`db`) VALUES(:keyword, :db)");
            $stm->execute(array(
                ":keyword"  => $keyword,
                ":db"       =>  $db,
            ));
        }

        return $db;
    }



    function db($keyword, $reset = false) {
        /*
         * Default is fastcache
         */
        $instant = $this->indexing($keyword);

        /*
         * init instant
         */
        if(!isset($this->instant[$instant])) {
            // check DB Files ready or not
            $createTable = false;
            if(!file_exists($this->path."/db".$instant) || $reset == true) {
                $createTable = true;
            }
            $PDO = new PDO("sqlite:".$this->path."/db".$instant);
            $PDO->setAttribute(PDO::ATTR_ERRMODE,
                               PDO::ERRMODE_EXCEPTION);

            if($createTable == true) {
                $this->initDB($PDO);
            }

            $this->instant[$instant] = $PDO;
            unset($PDO);

        }


        return $this->instant[$instant];
    }



    function checkdriver() {
        if(extension_loaded('pdo_sqlite') && is_writeable($this->getPath())) {
           return true;
        }
        return false;
    }

    /*
     * Init Main Database & Sub Database
     */
    function __construct($option = array()) {
        /*
         * init the path
         */
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        if(!file_exists($this->getPath()."/sqlite")) {
            if(!@mkdir($this->getPath()."/sqlite",0777)) {
                die("Sorry, Please CHMOD 0777 for this path: ".$this->getPath());
            }
        }
        $this->path = $this->getPath()."/sqlite";
    }


    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $skipExisting = isset($option['skipExisting']) ? $option['skipExisting'] : false;
        $toWrite = true;

        // check in cache first
        $in_cache = $this->get($keyword,$option);

        if($skipExisting == true) {
            if($in_cache == null) {
                $toWrite = true;
            } else {
                $toWrite = false;
            }
        }

        if($toWrite == true) {
            try {
                $stm = $this->db($keyword)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword"  => $keyword,
                    ":object"   =>  $this->encode($value),
                    ":exp"      => @date("U") + (Int)$time,
                ));

                return true;
            } catch(PDOException $e) {
                $stm = $this->db($keyword,true)->prepare("INSERT OR REPLACE INTO `caching` (`keyword`,`object`,`exp`) values(:keyword,:object,:exp)");
                $stm->execute(array(
                    ":keyword"  => $keyword,
                    ":object"   =>  $this->encode($value),
                    ":exp"      => @date("U") + (Int)$time,
                ));
            }


        }

        return false;

    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        try {
            $stm = $this->db($keyword)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword"  =>  $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {

            $stm = $this->db($keyword,true)->prepare("SELECT * FROM `caching` WHERE `keyword`=:keyword LIMIT 1");
            $stm->execute(array(
                ":keyword"  =>  $keyword
            ));
            $row = $stm->fetch(PDO::FETCH_ASSOC);
        }


        if($this->isExpired($row)) {
            $this->deleteRow($row);
            return null;
        }



        if(isset($row['id'])) {
            $data = $this->decode($row['object']);
            return $data;
        }


        return null;
    }

    function isExpired($row) {
        if(isset($row['exp']) && @date("U") >= $row['exp']) {
            return true;
        }

        return false;
    }

    function deleteRow($row) {
        $stm = $this->db($row['keyword'])->prepare("DELETE FROM `caching` WHERE (`id`=:id) OR (`exp` <= :U) ");
        $stm->execute(array(
            ":id"   => $row['id'],
            ":U"    =>  @date("U"),
        ));
    }

    function driver_delete($keyword, $option = array()) {
        $stm = $this->db($keyword)->prepare("DELETE FROM `caching` WHERE (`keyword`=:keyword) OR (`exp` <= :U)");
        $stm->execute(array(
            ":keyword"   => $keyword,
            ":U"    =>  @date("U"),
        ));
    }

    function driver_stats($option = array()) {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );
        $total = 0;
        $optimized = 0;

        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file!="." && $file!="..") {
                $file_path = $this->path."/".$file;
                $size = filesize($file_path);
                $total = $total + $size;

                $PDO = new PDO("sqlite:".$file_path);
                $PDO->setAttribute(PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION);

                $stm = $PDO->prepare("DELETE FROM `caching` WHERE `exp` <= :U");
                $stm->execute(array(
                    ":U"    =>  @date("U"),
                ));

                $PDO->exec("VACUUM;");
                $size = filesize($file_path);
                $optimized = $optimized + $size;

            }
        }
        $res['size'] = round($optimized/1024/1024,1);
        $res['info'] = array(
            "total" => round($total/1024/1024,1),
            "optimized" => round($optimized/1024/1024,1),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        
        // close connection
        $this->instant = array();
        $this->indexing = NULL;
    
        // delete everything before reset indexing
        $dir = opendir($this->path);
        while($file = readdir($dir)) {
            if($file != "." && $file!="..") {
                @unlink($this->path."/".$file);
            }
        }
    }

    function driver_isExisting($keyword) {
        $stm = $this->db($keyword)->prepare("SELECT COUNT(`id`) as `total` FROM `caching` WHERE `keyword`=:keyword");
        $stm->execute(array(
            ":keyword"   => $keyword
        ));
        $data = $stm->fetch(PDO::FETCH_ASSOC);
        if($data['total'] >= 1) {
            return true;
        } else {
            return false;
        }
    }


}
