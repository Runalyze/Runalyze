<?php

/*
 * khoaofgod@yahoo.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://www.codehelper.io
 */

class phpfastcache_memcached extends phpFastCache implements phpfastcache_driver  {

    var $instant;

    function checkdriver() {
        if(class_exists("Memcached")) {
            return true;
        }
       return false;
    }

    function __construct($option = array()) {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }

        $this->instant = new Memcached();
    }

    function connectServer() {
        $s = $this->option['server'];
        if(count($s) < 1) {
            $s = array(
                array("127.0.0.1",11211,100),
            );
        }

        foreach($s as $server) {
            $name = isset($server[0]) ? $server[0] : "127.0.0.1";
            $port = isset($server[1]) ? $server[1] : 11211;
            $sharing = isset($server[2]) ? $server[2] : 0;
            $checked = $name."_".$port;
            if(!isset($this->checked[$checked])) {
                if($sharing >0 ) {
                    $this->instant->addServer($name,$port,$sharing);
                } else {
                    $this->instant->addServer($name,$port);
                }
                $this->checked[$checked] = 1;
            }
        }
    }

    function driver_set($keyword, $value = "", $time = 300, $option = array() ) {
        $this->connectServer();
        if(isset($option['isExisting']) && $option['isExisting'] == true) {
            return $this->instant->add($keyword, $value, time() + $time );
        } else {
            return $this->instant->set($keyword, $value, time() + $time );

        }
    }

    function driver_get($keyword, $option = array()) {
        // return null if no caching
        // return value if in caching
        $this->connectServer();
        $x = $this->instant->get($keyword);
        if($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    function driver_delete($keyword, $option = array()) {
        $this->connectServer();
        $this->instant->delete($keyword);
    }

    function driver_stats($option = array()) {
        $this->connectServer();
        $res = array(
        "info" => "",
        "size"  =>  "",
        "data"  => $this->instant->getStats(),
        );

        return $res;
    }

    function driver_clean($option = array()) {
        $this->connectServer();
        $this->instant->flush();
    }

    function driver_isExisting($keyword) {
        $this->connectServer();
        $x = $this->get($keyword);
        if($x == null) {
            return false;
        } else {
            return true;
        }
    }



}