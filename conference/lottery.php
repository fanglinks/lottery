<?php
	ini_set('always_populate_raw_post_data','-1');
	$dir = "image/";
	$i = 0;
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != "." && $file != ".." && $file != ".DS_Store") {
					$data[$i] = $file;
					$i++;
				}
			} closedir($dh);
		};
	};
	// print_r($data);
	echo json_encode($data);
?>
