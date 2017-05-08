<?php


	/**
	* 二维码，生成二维码
	*/
	class Qrcode{
		//二维码生成API接口
		public $apiUrl 	=  'http://qr.liantu.com/api.php?text=';
		//二维码生成的内容
		public $text 	= '';
		//生成二维码的地址
		public $qrUrl 	= '';

		//logo 图片
		private $logo = 'https://buding-img.b0.upaiyun.com/weiche/2015/11/04/e8e6dd7521359118584151682acb3ba0.jpg';
	    //纠错等级
	    private $errorLevel = 'h';
	    //背景颜色
	    private $bgColor = 'ffffff';
	    //前景颜色
	    private $fgColor = '000000';
	    //渐变颜色
	    private $fadeColor = '000000';
	    //生成二维码宽度
	    private $width = '200';
	    //二维码外边距
	    private $padding = '10';
	    //定位点外框颜色
	    private $locateBorderColor = '000000';
	    //定位点颜色
	    private $locateColor = '000000';

		/**
		* 处理颜色值
		*@param: $color ffffff 或者 #ffffff 或者 255,255,255
		*@param: $is_rgb 是否是rgb形式
		*@return: 处理后的色值
		*/
		private function doColorVlaue($color, $is_rgb) {
			if ($is_rgb) {
				$rgb = explode(',', $color);
				if ($rgb) {
					$color = implode('',array_map('dechex', $rgb));
				} else {
					return false;
				}
			} else {
				$color = str_replace('#', '', $color);
			}
			return (string)$color;
		}

		/**
		* 设置背景颜色
		*@param: $color ffffff 或者 #ffffff 或者 255,255,255
		*@param: $is_rgb 是否是rgb形式
		*/
		public function setBgColor($color, $is_rgb = false) {
			$color = $this->doColorVlaue($color, $is_rgb);
			if ($color) {
				$this->bgColor = $color;
			}
		}

		/**
		* 设置前景色
		*@param: $color String
		*@param: $is_rgb String
		*/
		public function setFgColor($color, $is_rgb = false) {
			$color = $this->doColorVlaue($color, $is_rgb);
			if ($color) {
				$this->fgColor = $color;
			}
		}

		/**
		* 设置渐变色
		*@param: $color String
		*@param: $is_rgb String
		*/
		public function setFadeColor($color, $is_rgb = false) {
			$color = $this->doColorVlaue($color, $is_rgb);
			if ($color) {
				$this->fadeColor = $color;
			}
		}

		/**
		* 设置纠错级别
		*@param: $level String (h高\q中\m低\l很低)
		*/
		public function setErrorLevel($level) {
			if (array_search($level, array('h', 'q', 'm', 'l')) !== false) {
				$this->errorLevel = $level;
			}
		}

		/**
		* 设置二维码宽度
		*@param: $width
		*/
		public function setWidth($width) {
			$this->width = $width;
		}

		/**
		* 设置二维码外边距
		*@param: $padding
		*/
		public function setPadding($padding) {
			$this->padding = $padding;
		}

		/**
		* 设置定位点外框颜色
		*@param: $color String
		*@param: $is_rgb String
		*/
		public function setLocateBorderColor($color, $is_rgb = false) {
			$color = $this->doColorVlaue($color, $is_rgb);
			if ($color) {
				$this->locateBorderColor = $color;
			}
		}

		/**
		* 设置定位点的颜色
		*@param: $color String
		*@param: $is_rgb String
		*/
		public function setLocateColor($color, $is_rgb = false) {
			$color = $this->doColorVlaue($color, $is_rgb);
			if ($color) {
				$this->locateColor = $color;
			}
		}

		/**
		* 设置中心图片logo
		*@param: $imgUrl string 图片地址
		*/
		public function setLogo($logo) {
			$this->logo = $logo;
		}

		/**
		* 生成二维码
		*@param: $text String 需要生成二维码的链接或者文本
		*@param: $args Array 配置的参数
		*@return: 引用链接
		*/
		public function createQrcode($text, $args = array()) {
			//替换特殊字符
			$this->text = str_replace(array('&', "\n"), array('%26', '%0A'), $text);
			$args_default = array(
					'logo' => $this->logo,
				    'el' => $this->errorLevel,
				    'bg' => $this->bgColor,
				    'fg' => $this->fgColor,
				    'gc' => $this->fadeColor,
				    'w' => $this->width,
				    'm' => $this->padding,
				    'pt' => $this->locateBorderColor,
				    'inpt' => $this->locateColor
				);
			$args_default = array_merge($args_default, $args);
			$this->qrUrl = $this->apiUrl . $this->text . '&' . http_build_query($args_default);
			Log::info($args_default, 'qrcode_args');
			return $this->downloadAndUploadCDN($this->qrUrl);
		}

		/**
		* 上传图片到cdn,返回正式链接
		*@param: $imgUrl String
		*@return: $url String
		*/
		private function downloadAndUploadCDN($imgUrl) {
			$curl = new Libcurl('','', 60);
			$curl->doGET($imgUrl);
			$res = $curl->getBody();
			if ( $res ) {
				file_put_contents( '/tmp/qr_tmp_file', $res);
			} else {
				Log::error( 'download image error, URL:' . $imgUrl . '----errorInfo:' . $curl->getError() . '----status:' . $curl->getStatus(), 'qrcodeImg_download' );
				return false;
			}
			if (is_file( '/tmp/qr_tmp_file')) {
				//获取文件类型，并重名名
				$info = getimagesize( '/tmp/qr_tmp_file');
				$name =  '/tmp/qr_tmp_file.' . explode('/', $info['mime'])[1];
				rename( '/tmp/qr_tmp_file', $name);

				//上传图片
				$img = curl_file_create($name,$info['mime'],'fileToUpload');
		        $url = 'http://upload.buding.cn/ajaxupload.php';
		        $data = array('fileToUpload' => $img, 'product'=>'weiche');
		        echo 'success';
		        // $ch = curl_init();
		        // curl_setopt($ch,CURLOPT_URL,$url);
		        // curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		        // curl_setopt($ch,CURLOPT_POST,true);
		        // curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		        // $result = curl_exec($ch);
		        // if ($result === false) {
		        // 	Log::error(curl_error($ch), 'qrcodeImg_download');
		        // 	return false;
		        // }
		        // curl_close($ch);
		        // @unlink($name);
		        // return $result;
			}
		}

		/**
		* 获取配置信息
		*@param: void
		*@return: $args array
		*/
		public function getConfig() {
			$args_default = array(
					'logo' => $this->logo,
				    'el' => $this->errorLevel,
				    'bg' => $this->bgColor,
				    'fg' => $this->fgColor,
				    'gc' => $this->fadeColor,
				    'w' => $this->width,
				    'm' => $this->padding,
				    'pt' => $this->locateBorderColor,
				    'inpt' => $this->locateColor
				);
			return $args_default;
		}
	}

 ?>
