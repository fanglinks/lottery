  <?php
class WeicheApi{

	public static $api = 'http://rest.martin.buding.cn';
	public static $memtime = 18000;	// 5 hours

	/*
	//旧API
	public static function getVehicleBrand(){
		$mkey = __CLASS__ . __FUNCTION__;
		$data = Mem::get($mkey);
		if(!$data){
			$url = self::$api . '/v2/vehicle_brands?bitauto=1';
			$data = CURL::get($url);
			if($data){
				$data = json_decode($data,true);
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}
	*/

	/*
	@function: 获取所有车的品牌信息
	*/
	public static function getVehicleBrand() {
		$mkey = __CLASS__ . __FUNCTION__;
		$res = Mem::get($mkey);
		if(!$res) {
			$data = DB::getAll('SELECT `car_brand`.`id` AS `brand_id`,`pic_url` AS `image_url` ,`brand` AS `name`,`brand_pinyin` AS `name_pinyin`, `sub_brand` , `car_sub_brand`.`id` AS `sub_brand_id` FROM `car_brand` LEFT JOIN `car_sub_brand` ON `car_brand`.`id` = `car_sub_brand`.`brand_id` WHERE `bitauto_master_id` > 0 ORDER BY `brand_id`,`sub_brand_id`' , array());
			$res = array();
			$i = -1;
			if($data){
				foreach ($data as $key => $value) {
					if(in_array( $value['brand_id'], array_column($res,'brand_id') )) {
						$res[$i]['vehicle_sub_brands'][] = array(
							'name' => $value['sub_brand'],
							'sub_brand_id' => $value['sub_brand_id']
							);
					}else{
						$i++;
						$res[$i]['brand_id'] = $value['brand_id'];
						$res[$i]['image_url'] = $value['image_url'];
						$res[$i]['name'] = $value['name'];
						$res[$i]['name_pinyin'] = $value['name_pinyin'];
						if($value['sub_brand'] || $value['sub_brand_id']){
							$res[$i]['vehicle_sub_brands'][] = array(
							'name' => $value['sub_brand'],
							'sub_brand_id' => $value['sub_brand_id']
							);
						}else{
							$res[$i]['vehicle_sub_brands'] = array();
						}
					}
				}
			}
			Mem::set($mkey,$res,self::$memtime);
		}
		return $res;
	}



	/*
	//旧API
	public static function getVehicleTypes($brand_id){
		$mkey = __CLASS__ . __FUNCTION__ . $brand_id;
		$data = Mem::get($mkey);
		if(!$data){
			$url = self::$api . '/v2/vehicle_brands/'.$brand_id.'/vehicle_types?bitauto=1';
			$data = CURL::get($url);
			if($data){
				$data = json_decode($data,true);
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}*/

	/*
	@function: 根据品牌id获取车型
	@param: $brand_id(numberic 品牌id)
	*/
	public static function getVehicleTypes($brand_id) {
		$mkey = __CLASS__ . __FUNCTION__ . $brand_id;
		$data = Mem::get($mkey);
		if(!$data){
			$data = DB::getAll('SELECT `pic_url` as `image_url`, `type` AS `name`, `sub_brand_id`, `sub_brand` AS `sub_brand_name`, `id` AS `vehicle_type_id` FROM `car_type` WHERE `brand_id` = ? AND `bitauto_master_id` > 0 ORDER BY `vehicle_type_id` DESC',array($brand_id));
			if($data){
				Mem::set($mkey, $data, self::$memtime);
			}
		}
		return $data;
	}



	/*
	//旧api
	public static function getVehicleSubTypes($brand_id,$vehicle_type_id){
		$mkey = __CLASS__ . __FUNCTION__ . $brand_id . $vehicle_type_id;
		$data = Mem::get($mkey);
		if(!$data){
			$url = self::$api . '/v2/vehicle_brands/'.$brand_id.'/vehicle_types/'.$vehicle_type_id.'/sub_types?bitauto=1';
			$data = CURL::get($url);
			if($data){
				$data = json_decode($data,true);
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}*/

	/*
	@function: 根据品牌id和车型id获取所有的子车型
	@param: $brand_id 品牌id  $vehicle_type_id 车型id
	*/
	public static function getVehicleSubTypes($brand_id,$vehicle_type_id) {
		$mkey = __CLASS__ . __FUNCTION__ . $brand_id . $vehicle_type_id;
		$data = Mem::get($mkey);
		if(!$data){
			$data = DB::getAll('SELECT `bitauto_detail_type_id` , `bitauto_master_id` , `bitauto_type_id` , `detail_type` , `engine_type` ,  `fuel_tank_size` , `gearbox` , `onsale` , `power` , `price` , `id` AS `vehicle_sub_type_id` , `year` FROM `car_sub_type` WHERE `type_id` = ? AND `brand_id` = ? ORDER BY `vehicle_sub_type_id`', array($vehicle_type_id , $brand_id));
			if($data){
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}



	/*
	//旧API
	public static function getVehicleTypesById($vehicle_sub_type_id){
		$mkey = __CLASS__ . __FUNCTION__ . $vehicle_sub_type_id;
		$data = Mem::get($mkey);
		if(!$data){
			$url = self::$api . '/v2/vehicle_sub_types/' .$vehicle_sub_type_id. '?bitauto=1';
			$data = CURL::get($url);
			if($data){
				$data = json_decode($data,true);
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}*/
	/*
	@function: 根据子车型id获取车型详细信息
	@param: $vehicle_sub_type_id 子车型id（也就是car_id）
	*/
	public static function getVehicleTypesById($vehicle_sub_type_id) {
		$mkey = __CLASS__ . __FUNCTION__ . $vehicle_sub_type_id;
		$data = Mem::get($mkey);
		if(!$data){
			$data = DB::getOne('SELECT `bitauto_detail_type_id` , `bitauto_master_id` , `bitauto_type_id` , `brand_id` , `detail_type` , `engine_type` ,  `fuel_tank_size` , `gearbox` , `onsale` , `power` , `price`, `sub_brand_id` , `id` AS `vehicle_sub_type_id` , `type_id` AS `vehicle_type_id` , `year` FROM `car_sub_type` WHERE `id` = ? ',array($vehicle_sub_type_id));
			if($data){
				Mem::set($mkey,$data,self::$memtime);
			}
		}
		return $data;
	}

	/*
	@function: 根据car_id获取车型
	@param: $car_id 车id
	*/
	public static function getVehicleByCarId($car_id) {
		$sql = 'SELECT `brand`.`brand` AS `brand_name`,`sub_brand`.`sub_brand` AS `sub_brand_name`, `car_type`.`type` AS type ,`detail_type` FROM `car_sub_type` AS `car`  JOIN `car_brand` AS `brand` ON `brand`.`id` = `car`.`brand_id`  JOIN `car_sub_brand` AS `sub_brand` ON `car`.`sub_brand_id` = `sub_brand`.`id`  JOIN `car_type` ON  `car_type`.`id` = `car`.`type_id` WHERE `car`.`id` = ?';
		$mkey = __CLASS__ . __FUNCTION__ . $car_id;
		$data = Mem::get($mkey);
		if(!$data) {
			$data = DB::getOne($sql, array($car_id));
			if($data) {
				$data = $data['brand_name'] . ' ' . $data['sub_brand_name'] . ' ' . $data['type'] . ' ' . $data['detail_type'];
				Mem::set($mkey, $data, self::$memtime);
			}
		}
		return $data;
	}

	/**
	* 获取当前所有支持违章代缴的城市列表
	*@return:
	*/
	public static function getAllViolationCity() {
		$mkey = __CLASS__ . __FUNCTION__;
		$data = Mem::get($mkey);
		if (!$data) {
			$sql = 'SELECT `city_id`, `city_name` FROM `city_license_prefix` AS p JOIN `violation_payment_config` AS c ON p.`license_prefix` = c.`license_plate_num_prefix` WHERE c.`online` = 1';
			$data = DB::getAll($sql);
			if ($data) {
				Mem::set($mkey, $data, self::$memtime);
			}
		}
		return $data;
	}

	public static function getViolationPaymentCities(){
		$sql = 'select license_plate_num_prefix from violation_payment_config where online = 1';
		$mkey = md5($sql);
		$data = Mem::get($mkey);
		if(!$data){
			DB::setHarpoon('martin');
			$rt = DB::getAll($sql);
			$data = array();
			foreach ($rt as $val) {
				$data[] = $val['license_plate_num_prefix'];
			}
			DB::setHarpoon();
			Mem::set($mkey, $data , self::$memtime);
		}
		return $data;
	}

	public static function getTailLimitCities(){
		$sql = 'select city_id,city_name,province_name from city_online where tail_limit_on = 1';
		$mkey = md5($sql);
		$data = Mem::get($mkey);
		if(!$data){
			DB::setHarpoon('martin');
			$data = DB::getAll($sql);
			DB::setHarpoon();
			Mem::set($mkey, $data , self::$memtime);
		}
		return $data;
	}

}
