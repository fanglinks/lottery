<?php

    /**
    * mongoDB 的操作类，采用单例模式
    **/
    class LibMongo {

        //mongo操作句柄
        private static $_ins = null;
        private static $_config = '';
        private static $database = null;

        /*
        * 连接mongodb
        */
        private static function getInstance($config) {
            if (self::$_config && $config != self::$_config) {
                self::$_ins = null;
            }
            if (self::$_ins) {
                return self::$_ins;
            }
            self::$_config = $config;
            try {
                // 判断是$config 是否dsn格式的配置
                if (is_string($config)) {
                    self::$_ins = new MongoClient($config);
                // 数组格式的配置
                } else {
                    if (!empty($config['server'])) {
                        if (!empty($config['port'])) {
                            if (!empty($config['user']) && !empty($config['password'])) {
                                self::$_ins = new MongoClient("mongodb://{$config['user']}:{$config['password']}@{$config['server']}:{$config['port']}");
                            } else {
                                self::$_ins = new MongoClient("mongodb://{$config['server']}:{$config['port']}");
                            }
                        } else {
                            self::$_ins = new MongoClient("mongodb://{$config['server']}");
                        }
                    } else {
                        self::$_ins = new MongoClient();
                    }
                }
            } catch (MongoConnectionException $e) {
                Log::error('Mongo connect failed: ' . $e->getMessage());
            }
        }


        public function __construct($database, $config = array()) {
            if (empty($config)) {
                $config = Config::MONGODB_DSN;
            }

            self::getInstance($config);

            if (is_null(self::$_ins)) {
                Log::error('Mongo connect failed. ');
                // die('Mongo connect failed');
            }

            self::$database = self::$_ins->selectDB($database);

            if (is_null(self::$database)) {
                Log::error('Mongo connect failed. ');
                // die('Mongo connect failed');
            }
        }


        /**
        * 查询多行信息
        * @param: $collection Sting 文档名
        * @param: $where Array 查询条件
        * @param: $sort Array 排序
        * @param: $limit Int 读取条数
        * @param: $skip Int 跳过条数
        **/
        public function getAll($collection, $where = array(), $sort = array(), $limit = '', $skip = '') {
            try {
                $cusorData = $where ? self::$database->$collection->find($where) : self::$database->$collection->find();
                $sort && $cusorData = $cusorData->sort($sort);
                $limit && $cusorData = $cusorData->limit($limit);
                $skip && $cusorData = $cusorData->skip($skip);
                return iterator_to_array($cusorData);
            } catch (Exception $e){
                Log::error('Mongo getAll error:' . $e->getMessage());
            }

        }


        /**
        * 查询单条记录
        * @param: $collection String 文档名
        * @param: $where Array 查询条件
        **/
        public function getOne($collection, $where = array()) {
            try {
                $cusorData = $where ? self::$database->$collection->findOne($where) : self::$database->$collection->findOne();
                return $cusorData;
            } catch (Exception $e) {
                Log::error('Mongo getOne error:' . $e->getMessage());
            }
        }


        /**
        * 查询条数
        * @param: $collection String 文档名
        * @param: $where Array 查询条件
        **/
        public function getCount($collection, $where = array()) {
            try {
                $cusorData = $where ? self::$database->$collection->find($where)->count() : self::$database->$collection->find()->count();
                return $cusorData;
            } catch (Exception $e) {
                Log::error('Mongo getCount error:' . $e->getMessage());
            }
        }


        /**
        * 执行命令
        * @param：$options Array 命令参数
        **/
        public function runCommand($options) {
            return self::$database->command($options);
        }


        /**
        * 插入操作
        * @param: $collection String 文档名
        * @param: $data Array 插入的数据
        **/
        public function insert($collection, $data) {
            return self::$database->$collection->insert($data);
        }

        /**
        * 更新操作
        * @param: $collection String 文档名
        * @param: $where Array 更新的条件
        * @param: $data 更新的内容
        **/
        public function update($collection, $where, $data) {
            return self::$database->$collection->update($where, array( '$set' => $data));
        }

        /**
        * 获取distinct数据
        * @param: $collection String 文档名
        * @param: $key String 字段名
        * @param: $query Array 查询条件
        **/
        public function getDistinct($collection, $key, $query) {
            $command = array(
                'distinct' => $collection,
                'key' => $key,
                'query' => $query
                );
            $data = self::$database->command($command);
            return $data['values'];
        }

        /**
        * 地理索引查询（查询某个距离内的点）
        * @param: $collection String 文档名
        * @param: $near Array 当前位置点信息
        * @param: $query Array 点查询条件
        * @param: $is_spherical Boolean 是否是球面索引
        * @param: $maxDistance Int 最大查询距离（单位 m ）
        * @param: $num Int 返回数据条数
        **/
        public function geoNear($collection, $near, $query, $is_spherical = true, $maxDistance = 1000, $num = 100) {
            $args = array(
                'geoNear' => $collection,
                'near' => $near,
                'spherical' => $is_spherical,
                'query' => $query,
                'maxDistance' => $maxDistance,
                'num' => $num
                );
            return self::$database->command($args);
        }

        /**
        * 开放client接口
        **/
        public function getClientIns() {
            return self::$_ins;
        }


        /**
        * 开放mongo数据库的数据库接口
        **/
        public function getDBIns() {
            return self::$database;
        }


    }

