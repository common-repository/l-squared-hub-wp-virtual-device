<?php

// display message in admin section
function lsnCarousel_showAdminErrorMessage($msg, $class="notice-success"){
	echo '<div class="notice '.$class.' is-dismissible">
		<p>' . $msg. '</p>
	</div>';
}

// display message in user section
function lsnCarousel_showErrorMessage($msg){
	echo '<div class="alert alert-danger" style="position: relative;z-index: 100;">
		<strong>Error!</strong> '.$msg.'
	</div>';
}

function getContentThumb($itemArr, $bucketPath){
	if($itemArr['type'] == 'image'){
		$src = $bucketPath.$itemArr['src'];
	}
	else if($itemArr['type'] == 'video'){
		$fileNameArr = explode('.', $itemArr['fileName']);
		$count = count($fileNameArr);
		$fileNameArr[ $count - 1 ] = 'jpg';
		$fileName = implode('.', $fileNameArr);
		$src = $bucketPath.'cl/videos/thumbs/'.$fileName;
	}
	else if($itemArr['type'] == 'youtube'){
		$src = 'https://img.youtube.com/vi/'.$itemArr['src'].'/0.jpg';
	}

	if(!empty($src)){
		echo '<img src="'.$src.'" width="150" />';
	}
	else if($itemArr['type'] == 'audio'){
		echo '<span class="glyphicon glyphicon-bullhorn lsnCarouselAudioIcon"></span>';
	}
	else{
		echo '';
	}
}

// s3 bucket path
function getS3BucketPath(){
	return 'http://s3-us-west-2.amazonaws.com/dev-lsquared-hub/';
}

// get list of all created sliders
function getSlidersList($col, $table_name, $wpdb){
	return $wpdb->get_results("SELECT $col FROM $table_name ORDER BY name", ARRAY_A);
}

// count sliders
function countSliders($table_name, $wpdb){
	$rows= $wpdb->get_results("SELECT COUNT(id) as sliderCount FROM $table_name", ARRAY_A);
	$sliderCount = $rows[0]['sliderCount'];
	return $sliderCount;
}

// check server is exsting or no for this domain
function getHubServer(){
	$domain = $_SERVER['HTTP_HOST'];
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://dev.lsquared.com/hub/api/v1/cpanel/server/$domain",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return json_decode($response, true);
	}
}

function lsnCarousel_isFeedExists($url){
	$response = wp_remote_head( $url );
	// echo '<pre>'; print_r($response);
	$response_code =  wp_remote_retrieve_response_code( $response );
	if(!empty($response_code) && $response_code == 200){
		return true;
	}
	else{
		return false;
	}
}

function lsnCarousel_getContentTag($array, $item, $frame, $captionArr){
	$width = $frame['width'];
	$height = $frame['height'];

	$slideItem = $array[0];
	$slideNav = $array[1];
	$isStart = $array[2];
	$S3BucketPath = $array[3];
	$keyCheck = $array[4];
	$rand = rand(1000, 99999999);

	$uniqueId = $isStart.$rand;
	$uniqueItem = ' id="itemId'.$uniqueId.'" ';
	$uniqueIdItemChild = ' id="inneritemId'.$uniqueId.'" ';

	$classActive = (!$isStart) ? 'active' : '';
	$duration = $item['duration'] * 1000;

	// for caption
	$htmlCaption = '';

	if(!empty($item['type']) && !empty($captionArr)){
		if(!empty($captionArr[ $keyCheck ])){
			$htmlCaption = stripslashes( $captionArr[ $keyCheck ] );
			$htmlCaption = '<div class="lsnCarouselCaptionText">'. $htmlCaption .'</div>';
		}
	}

	if($item['type'] == 'image'){
		$src = $S3BucketPath.$item['src'];

		$slideItem .= '<div '.$uniqueItem.' class="item '.$classActive.'" data-interval="'.$duration.'">';
		$slideItem .= '<img src="'.$src.'" >';//width="'.$width.'" height="'.$height.'"
		$slideItem .= $htmlCaption;
		$slideItem .= '</div>';
	}
	else if($item['type'] == 'video'){
		// controls
		$slideItem .= '<div '.$uniqueItem.' class="item '.$classActive.'" data-interval="'.$duration.'">';
		$slideItem .= '<video '.$uniqueIdItemChild.'  loop >
			<source src="'.$S3BucketPath.$item['src'].'" type="video/mp4">
		</video>';//width="'.$width.'" height="'.$height.'" //width="'.$width.'" height="'.$height.'"
		$slideItem .= $htmlCaption;
		$slideItem .= '</div>';
	}
	else if($item['type'] == 'audio'){
		// controls
		$slideItem .= '<div '.$uniqueItem.' class="item '.$classActive.'" data-interval="'.$duration.'"><span class="glyphicon glyphicon-bullhorn audioIcon"></span>';//style="height:'.$height.'px;"
		$slideItem .= '<audio '.$uniqueIdItemChild.' loop >
			<source src="'.$S3BucketPath.$item['src'].'" type="audio/mpeg">
		</audio>';
		$slideItem .= $htmlCaption;
		$slideItem .= '</div>';
	}
	else if($item['type'] == 'youtube'){
		$uTubeSrc = '//youtube.com/embed/'.$item['src'];
		$uTubeSrc = (!empty($item['extraParams'])) ? $uTubeSrc.'?'.substr($item['extraParams'], 1).'&' : $uTubeSrc.'?';
		$uTubeSrc = $uTubeSrc.'enablejsapi=1&version=3&playerapiid=ytplayer&controls=0';

		$slideItem .= '<div '.$uniqueItem.' class="item '.$classActive.'" data-interval="'.$duration.'">';
		$slideItem .= '<iframe '.$uniqueIdItemChild.' src="'.$uTubeSrc.'" frameborder="0"></iframe>'; //width="'.$width.'" height="'.$height.'"
		$slideItem .= $htmlCaption;
		$slideItem .= '</div>';
	}
	else{
		return array($slideItem, $slideNav, $isStart);
	}

	$isStart++;
	return array($slideItem, $slideNav, $isStart);
}

// case for check device id already exists or not
function isDeviceIdExist($deviceId, $serverId){
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://dev.lsquared.com/hub/api/v1/cpanel/$deviceId/device/$serverId",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return json_decode($response, true);
	}
}

?>