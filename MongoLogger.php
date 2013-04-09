<?php
/*  Copyright 2013 Michael Moore <stuporglue@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */



class MongoLogger{
    /**
     * @class MongoLogger
     *
     * @brief A debugging shim between your code and MongoDB. Should be disbaled for production
     *
     * Usage:
     * $mongodb = new Mongo("mongodb://un:pw@host/db"); // Connect to Mongo
     * $mongo = $mongodb->db;                           // pick your database
     * $mongo = new MongoLogger($mongo);                // and all calls made to the original $mongo just keep working...
     *
     * YAY MAGIC METHODS! http://php.net/manual/en/language.oop5.magic.php
     * Makes use of magic methods __construct, __call and __get and the almost magic method call_user_func_array.
     */

    var $collections = Array();
    var $real_mongo;

    /**
      @brief Make a new MongoLogger
      @param $mongo A mongo connection object
    */
    function __construct($mongo){
        $this->real_mongo = $mongo;
    }

    public function __call($name,$args){
        error_log("Mongo->$name called with " . print_r($args,TRUE));

        try {
            $res = call_user_func_array(Array($this->real_mongo,$name),$args);
            return $res;
        } catch (Exception $e){
            debug_print_backtrace();
            error_log(print_r($e,TRUE));
            throw $e;
        }
    }

    /**
     * @brief Return a MongoLoggerCollection for the requested collection
     * This is the main function that gets called in this class. We just pretend that
     * we've got whatever we're asked for (just like Mongo does)
     *
     */
    public function __get($name){
        if(!array_key_exists($name,$this->collections)){ 
            $this->collections[$name] = new MongoLoggerCollection($this->real_mongo,$name); 
        }
        return $this->collections[$name];
    }
}

class MongoLoggerCollection {
    /**
     * @class MongoLoggerCollection
     *
     * @brief Represents a collection in Mongo. Most of your calls will come through here.
     */
    var $collection;

    function __construct(&$real_mongo,$name){
        $this->real_mongo = $real_mongo;
        $this->collection = $name;
    }

    public function __call($name,$args){
        // Logging!
        $debug = debug_backtrace();
        $caller = $debug[1];
        error_log($caller['file'] . ':' . $caller['line']);
        error_log("\$mongo->{$this->collection}->$name(");
        foreach($args as $arg){ error_log(print_r($arg,TRUE)); }
        error_log(")");

        try {
            $res = call_user_func_array(Array($this->real_mongo->{$this->collection},$name),$args);
            return $res;
        } catch (Exception $e){
            debug_print_backtrace();
            error_log(print_r($e,TRUE));
            throw $e;
        }
    }
}
