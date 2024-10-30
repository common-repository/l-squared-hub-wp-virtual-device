<?php
/*
Plugin Name: L Squared Hub WP - Virtual Device Plugin
Plugin URI: https://www.lsquared.com/
Description: This wordpress plugin is developed to use L Squared Hub Virtual Device on wordpress website and control the banners/sliders through L Squared Hub.
Version: 1.0
Author: L Squared Support
Author URI: https://www.lsquared.com/
License: GPLv2+
*/
// include 'functions.php';
include( plugin_dir_path( __FILE__ ) . 'functions.php');

class LsnCarousel{

	// Constructor
	function lsnCarousel_init() {
		add_action( 'admin_menu', array( $this, 'lsnCarousel_add_menu' ));
		register_activation_hook( __FILE__, array( $this, 'lsnCarousel_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'lsnCarousel_uninstall' ) );

		wp_enqueue_script("jquery");
		wp_enqueue_script("lsnCarousel_bootstrap_js", plugins_url('js/bootstrap.min.js', __FILE__));
		wp_enqueue_style("lsnCarousel_custom_css", plugins_url('css/custom.css', __FILE__), array('lsnCarousel_bootstrap_css'), false);
	}

    // Actions perform at loading of admin menu
    function lsnCarousel_add_menu() {
		$pluginMenu = 'L Squared Hub Slider';
		$pluginSubMenuName1 = 'Manage Slider Captions';

		$pluginMenuId = 'lsnCarousel_slider';
		// $pluginSubMenuId1 = 'lsnCarousel_caption';

		//create new top-level menu
        add_menu_page($pluginMenu, $pluginMenu, 'manage_options', $pluginMenuId, array(
				__CLASS__,
				'lsnCarousel_manage_slider'
			),
			plugins_url('images/logo.png', __FILE__)
		);
	}

	// Actions perform on loading of menu pages
    function lsnCarousel_manage_slider() {
		global $wpdb;

		wp_enqueue_style("lsnCarousel_bootstrap_css", plugins_url('css/bootstrap.min.css', __FILE__));
		wp_enqueue_script("lsnCarousel_bootstrap_js", plugins_url('js/bootstrap.min.js', __FILE__));

		?>
		<div class="wrap">

			<?php
			$isServerExist = getHubServer();	// check server is already existing for current domain

			$table_name = $wpdb->prefix . 'lsnCarousel'; // main slider table
			$table_name_caption = $wpdb->prefix . 'lsnCarousel_caption';

			// request for manage slider
			$isEdit = false;
			if(!empty($_GET['id'])){
				$isEdit = true;
				$id = intval($_GET['id']);
				$getRow = $wpdb->get_row("SELECT * FROM $table_name where id = $id", ARRAY_A);
			}
			?>

			<h1 class="wp-heading-inline custom-plugin-heading-inline">L Squared Hub Slider</h1>
			<ul class="nav nav-tabs">
				<li class="<?php if(isset($_GET['s']) OR (!isset($_GET['a']) && !isset($_GET['m']) && !isset($_GET['u']))){ ?>active<?php } ?>"><a href="<?php echo admin_url("admin.php?page=lsnCarousel_slider&s=1"); ?>">Server</a></li>
				<?php //if(!empty($isServerExist)){ ?>
						<li class="<?php if(empty($isServerExist)){ ?>disabled disabledtab<?php } ?><?php if(!isset($_GET['s']) && (isset($_GET['a']) OR isset($_GET['u']) OR isset($_GET['m']))){ ?>active<?php } ?>"><a href="<?php echo admin_url("admin.php?page=lsnCarousel_slider&m=1"); ?>">Manage Slider</a></li>

				<?php //} ?>
			</ul>
			<div class="tab-content">
				<?php
					if(!empty($_GET['timestamp']) && !empty($_GET['status_type'])){
						$timestamp10 = strtotime("+10 seconds", $_GET['timestamp']);
						if($timestamp10 >= time()){
							if($_GET['status_type'] == 'add'){ $msg = 'Slider added successfully.'; }
							else if($_GET['status_type'] == 'edit'){ $msg = 'Slider edited successfully.'; }
							else if($_GET['status_type'] == 'delete'){ $msg = 'Slider deleted successfully.'; }
							else if($_GET['status_type'] == 'addServer'){ $msg = 'Server added successfully.'; }
							else if($_GET['code'] == 8008){ $msg = 'This Server Name already in use.'; }
							else if($_GET['code'] == 12020){ $msg = 'This Device Id already in use.'; }
							else if($_GET['code'] == 12021){ $msg = 'Please enter required filed.'; }
							if(!empty($msg) && $_GET['status_type'] == 'error'){
								lsnCarousel_showAdminErrorMessage($msg, "error error-msg");
							}
							else{
								lsnCarousel_showAdminErrorMessage($msg);
							}
						}
					}
				?>

				<!-- Add Server -->
				<?php if(isset($_GET['s']) OR (!isset($_GET['a']) && !isset($_GET['m']) && !isset($_GET['u']))){ ?>
					<div id="server">
						<div class="row">
							<div class="col-xs-12">
								<?php if($isServerExist){	// if server already exsting for this current on hub then will display detail only ?>
									<div class="form-group">
										<label>Server Name: <?php echo $isServerExist['serverName']; ?></label>
									</div>
									<div class="form-group">
										<label>Email: <?php echo $isServerExist['email']; ?></label>
									</div>

									<div class="form-group">
										<label>License: <?php echo $isServerExist['licenseQuantity']; ?></label>
									</div>
								<?php }
								else{ // if server doesn't existing for this domain on hub then will display form to add server?>
									<form method="POST" id="serverFrm" action="admin-post.php" enctype="multipart/form-data">
										<input type="hidden" name="action" value="lsnServer_save" />

										<div class="form-group">
											<label>Server Name <span class="description">(required)</span></label>
											<input required type="text" class="form-control" name="server_name" value="" style = "width:21%"/>
										</div>
										<div class="col-xs-6 primary-contact">
											<div>
												<div class="heading"><label>Primary Contact</label></div>
												<div class="col-xs-6">
													<div class="form-group">
													    <label>Email Address <span class="description">(required)</span></label>
													    <input type="email" class="form-control validate" name="email" id="email" tabindex="3" maxlength="100" autocomplete="off" data-validate="email" required>
													</div>
													<div class="form-group">
													    <label>First Name <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate" name="fname" id="fname" tabindex="5" maxlength="100" autocomplete="off" data-validate="no-blank" required>
													</div>
													<div class="form-group">
													    <label>Company Name <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate" id="companyName" name="companyName" tabindex="7" maxlength="100" autocomplete="off" data-validate="no-blank" required>
													</div>
												</div>
												<div class="col-xs-6">
													<div class="form-group">
													    <label>Language</label>
													    <select name="lang" class="form-control" tabindex="4">
													        <option value="en_US">English / USA</option>
													        <option value="fr_FR">French / France</option>
													    </select>
													</div>
													<div class="form-group">
													    <label>Last Name <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate" name="lname" id="lname" tabindex="6" maxlength="100" autocomplete="off" data-validate="no-blank" required>
													</div>
													<div class="form-group">
													    <label>Phone Number <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate" name="phone" id="phone" maxlength="18" tabindex="8" autocomplete="off" data-validate="no-blank">
													</div>
												</div>
											</div>
										</div>
										<div class="col-xs-6 billing-address">
											<div>
												<div class="heading"><label>Billing Address</label></div>
												<div class="col-xs-6">
													<div class="form-group">
													    <label>Address Line 1 <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate" name="houseNo" id="houseNo" tabindex="9" placeholder="Enter a location" maxlength="100" autocomplete="off" data-validate="no-blank" required>
													</div>
													<div class="form-group input-group">
													    <label>Postal/Zip Code <span class="description">(required)</span></label>
													    <div class="input-group"> <input type="text" class="form-control validate postal_code" name="postalCode" id="postalCode" tabindex="11" maxlength="100" autocomplete="off"><span class="input-group-btn"> <button class="btn btn-primary hide" id="getAddress" type="button">Get City</button></span></div>
													</div>
													<div class="form-group">
													    <label>Province <span class="description">(required)</span></label>
													    <input type="text" class="form-control administrative_area_level_1" name="province" id="province" tabindex="13" data-validate="no-blank" required>
													</div>
												</div>
												<div class="col-xs-6">
													<div class="form-group">
													    <label>Address Line 2</label>
													    <input type="text" class="form-control validate" name="addressStreet" id="" tabindex="10" maxlength="100" autocomplete="off">
													</div>
													<div class="form-group">
													    <label>City <span class="description">(required)</span></label>
													    <input type="text" class="form-control validate locality" name="city" id="city" tabindex="12" maxlength="100" autocomplete="off" data-validate="no-blank" required>
													</div>
													<div class="form-group">
													    <label>Country <span class="description">(required)</span></label>
													    <input type="text" class="form-control country" name="countryCode" id="countryCode" data-country="CA" tabindex="14" required>
													</div>
												</div>
											</div>
										</div>

										<div class="form-group">
											<button type="Submit" class="button button-primary button-large server-submit-btn">Submit</button>
										</div>
									 </form>
								<?php } ?>

							</div>
						</div>
					</div>
				<?php } ?>

				<?php if(!empty($isServerExist)){ ?>
				  <!-- Add new slider -->
				  <?php if((isset($_GET['a']) OR isset($_GET['u'])) && !isset($_GET['s'])){ ?>
					<div id="add">
						<h4> </h4>
						<div class="row">
							<div class="col-xs-4">
								<?php
								$sliders = countSliders($table_name, $wpdb);	// count number of sliders

								// user will able to add slider on basis of license
								if($isServerExist['licenseQuantity'] == 1 && $sliders > 0 && !isset($_GET['id'])){
									$sliderManagePage = admin_url( "options-general.php?page=lsnCarousel_slider&m=1");
									echo("<script>location.href = '".$sliderManagePage."';</script>");

								}else{ ?>
									<form method="POST" action="admin-post.php" enctype="multipart/form-data">
										<input type="hidden" name="action" value="lsnCarousel_save" />
										<input type="hidden" name="row_id" value="<?php echo ($isEdit) ? $getRow['id'] : ''; ?>" />
										<input type="hidden" name="serverId" value="<?php echo $isServerExist['serverId']?>" />

										<div class="form-group">
											<label>Name <span class="description">(required)</span></label>
											<input required type="text" class="form-control" name="slider_name" value="<?php echo ($isEdit) ? $getRow['name'] : ''; ?>" />
										</div>

										<div class="form-group">
											<label>Slider Id <span class="description">(required)</span></label>
											<input required type="text" class="form-control" name="slider_id" value="<?php echo ($isEdit) ? $getRow['slider_id'] : ''; ?>" />
										</div>

										<div class="form-group">
											<button type="Submit" class="button button-primary button-large">Submit</button>

											<a href="<?php echo admin_url( "options-general.php?page=lsnCarousel_slider&m=1"); ?>" class="button button-link button-large">Cancel</a>
										</div>
									 </form>
								<?php } ?>
							</div>
						</div>
					</div>
				  <?php }elseif(isset($_GET['m']) && !isset($_GET['s'])){ ?>
				  	<!-- Get list of sliders and Edit-->
					<div id="showdata">
						<h4> </h4>
						<?php
						// $getResults = $wpdb->get_results("SELECT * FROM $table_name ORDER BY name", ARRAY_A);
						$getResults = getSlidersList("*", $table_name, $wpdb);	// func('col name', 'table_name')
						$sliderCount = count($getResults);
						if($isServerExist['licenseQuantity'] == 1 && $sliderCount > 0){ ?>
							<h4 class="alert-warning">Contact support@lsquared.com to add more licenses to your account.</h4>
						<?php }else{ ?>
							<a class="btn btn-primary" href="<?php echo admin_url("admin.php?page=lsnCarousel_slider&a=1"); ?>">Add New Slider</a>
						<?php } ?>

						<!-- hide table if doesn't have any slider  -->
						<?php if($sliderCount > 0){ ?>
							<table class="wp-list-table widefat fixed striped posts">
								<thead>
									<tr>
										<th>Name</th>
										<th>Id</th>
										<th>Short Code</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($getResults as $fetchResult){
											$sliderId = $fetchResult['slider_id'];
										?>
										<tr>
											<td><?php echo $fetchResult['name']; ?></td>
											<td><?php echo $sliderId; ?></td>
											<td><?php echo '[LSquaredHubSlider id="'.$sliderId.'"]'; ?></td>
											<td>
												<a href="<?php echo admin_url( "options-general.php?page=lsnCarousel_slider&u=1&id=".$fetchResult['id'] ); ?>">Edit</a>	/
												<a onclick="return confirm('Are you sure you want to delete this Slider?');" href="<?php echo admin_url( "admin-post.php?page=lsnCarousel_slider&action=lsnCarousel_delete&rowId=".$fetchResult['id'] ); ?>">Delete</a>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						<?php } ?>
					</div>
				  <?php } ?>
				<?php }elseif(isset($_GET['a']) OR isset($_GET['m']) OR isset($_GET['u'])){
					$sliderManagePage = admin_url( "options-general.php?page=lsnCarousel_slider&s=1");
					echo("<script>location.href = '".$sliderManagePage."';</script>");
				} ?>

			</div>
		</div>
		<?php
	}


    // Actions perform on activation of plugin
    function lsnCarousel_install() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_slider = $wpdb->prefix . 'lsnCarousel';
		// $table_caption = $wpdb->prefix . 'lsnCarousel_caption';

		$sql1 = "CREATE TABLE IF NOT EXISTS $table_slider (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			slider_id varchar(255) NOT NULL,
			width int(5) NOT NULL,
			height int(5) NOT NULL,
			PRIMARY KEY id (id)
		) $charset_collate;";
		$wpdb->query($sql1);
	}

    // Actions perform on de-activation of plugin
    function lsnCarousel_uninstall() {
		global $wpdb;
		$table_slider = $wpdb->prefix . 'lsnCarousel';

		$sql1 = "DROP TABLE IF EXISTS $table_slider;";
		$wpdb->query($sql1);
    }
}

$lsnCarousel = new LsnCarousel();
$lsnCarousel->lsnCarousel_init();

// manage slider function start
function lsnCarousel_save(){
	global $wpdb;

	if(empty($_POST['slider_name']) OR empty($_POST['slider_id'])){
		if(!empty($_POST['row_id'])){
			$param = '&u=1&id='.$_POST['row_id'];
		}
		elseif(empty($_POST['row_id'])){
			$param = '&a=1';
		}
		wp_redirect( admin_url( "options-general.php?page=lsnCarousel_slider&code=12021&status_type=error"."&timestamp=".time().$param) );
		die();
	}

	$table_name = $wpdb->prefix . 'lsnCarousel';

	$name   = sanitize_text_field( $_POST['slider_name'] );
	// $sid    = sanitize_text_field( $_POST['slider_id'] );
	$deviceId    = $_POST['slider_id'];
	$serverId = $_POST['serverId'];

	// case for check device id already exists or not
	$isDeviceExist = isDeviceIdExist($deviceId, $serverId);
	// echo '<pre>'; print_r($isDeviceExist); die;
	if(isset($isDeviceExist['code']) && $isDeviceExist['code'] == 12020){
		if(!empty($_POST['row_id'])){
			$param = '&u=1&id='.$_POST['row_id'];
		}
		else{
			$param = '&a=1';
		}
		$queryParam = '&status_type=error&code='.$isDeviceExist['code'].'&timestamp='.time().$param;
	}
	else{
		if(!empty($_POST['row_id'])){
			$id = intval($_POST['row_id']);
			$updatedRow = 0;
			if(!empty($id)){

				$updatedRow = $wpdb->update($table_name, array(
						'name' => $name,
						'slider_id' => $deviceId,
					),
					array('id' => $id),
					array(
						'%s',
						'%s'
					),
					array('%d')
				);
				$qParams = ($updatedRow == 1 OR $updatedRow == 0) ? '&status_type=edit&timestamp='.time() : '';
				$queryParam = $qParams.'&m=1';
			}
		}
		else{
			/*$insertedRow = $wpdb->insert($table_name, array(
					'name' => $name,
					'slider_id' => $deviceId
				),
				array(
					'%s',
					'%s'
				)
			);*/

			$insertedRow = $wpdb->query( $wpdb->prepare(
				"INSERT INTO $table_name( name, slider_id ) VALUES ( %s, %s )",
			    array(
			    	$name,
			    	$deviceId
			    )
			) );

			$qParams = ($insertedRow == 1) ? '&status_type=add&timestamp='.time() : '';
			$queryParam = $qParams.'&m=1';
		}
	}
	wp_redirect( admin_url( "options-general.php?page=lsnCarousel_slider".$queryParam) );
	die();
}
add_action( 'admin_post_lsnCarousel_save', 'lsnCarousel_save' );

function lsnCarousel_delete(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'lsnCarousel';
	$id = intval($_GET['rowId']);

	$deletedRow = 0;
	if(!empty($id)){
		$deletedRow = $wpdb->query( "DELETE FROM $table_name WHERE id = $id" );
	}
	$timestamp = ($deletedRow == 1) ? '&status_type=delete&timestamp='.time() : '';
	wp_redirect( admin_url( "options-general.php?page=lsnCarousel_slider".$timestamp."&m=1") );
}
add_action( 'admin_post_lsnCarousel_delete', 'lsnCarousel_delete' );
// manage slider function end --------------------------------------

// create server on LSquared Hub
function lsnServer_save(){
	global $wpdb;

	// validation
	/*$errors = new WP_Error();
	$fields = array(
					'email',
					'fname',
					'lname',
					'companyName'
				);

	foreach ($fields as $field) {
		if (isset($_POST[$field])) $posted[$field] = stripslashes(trim($_POST[$field])); else $posted[$field] = '';
	}

	//  Validattion Check start here
	if ($posted['email'] == null )
		$errors->add('empty_title', __('<strong>Notice</strong>: Please enter your Email.'));

	if ($posted['fname'] == null )
		$errors->add('empty_contact_no', __('<strong>Notice</strong>: Please enter your First Name.'));

	if ($posted['lname'] == null )
		$errors->add('empty_email', __('<strong>Notice</strong>: strong>Notice</strong>: Please enter your Last Name.'));

	if ($posted['companyName'] == null )
		$errors->add('empty_msg', __('<strong>Notice</strong>: Please enter your companyName.'));
	wp_redirect( admin_url( "admin.php?page=lsnCarousel_slider&s=1") );
	die();*/
	//validation end

	/*if(empty($_POST['server_name']) OR empty($_POST['email'])){
		wp_redirect( admin_url( "options-general.php?page=slider-dashboard") );
		die();
	}*/

	if(empty($_POST['server_name']) OR empty($_POST['email']) OR empty($_POST['fname']) OR empty($_POST['lname']) OR empty($_POST['companyName']) OR empty($_POST['houseNo']) OR empty($_POST['postalCode']) OR empty($_POST['city']) OR empty($_POST['province']) OR empty($_POST['countryCode'])){
		$queryParam = '&status_type=error&code=12021&timestamp='.time()."&s=1";
		wp_redirect( admin_url( "options-general.php?page=lsnCarousel_slider".$queryParam) );
		die();
	}


	$serverData = array();
	$serverData = $_POST;

	$domain = $_SERVER['HTTP_HOST'];

	if($domain == 'localhost'){
		$serverData['domain'] = $domain;
	}
	else{	// get domain instead of including subdomain (subdomain.domain.com)
		preg_match('/(www.)?([^.]+\.[^.]+)$/', $domain, $matches);
   		$serverData['domain'] =  $matches[0];
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://dev.lsquared.com/hub/api/v1/cpanel/server",
	  CURLOPT_RETURNTRANSFER => true,
	  // CURLOPT_ENCODING => "",
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => json_encode($serverData),
	  CURLOPT_HTTPHEADER => array(
	    "Content-Type: application/json"
	  ),
	));

	$response = curl_exec($curl);

	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
		$responseArr = json_decode($response, true);
		if(isset($responseArr['code']) && $responseArr['code'] == 8008){
			$params = '&status_type=error&code='.$responseArr['code'].'&timestamp='.time();
		}
		else{
			$params = ($response) ? '&status_type=addServer&timestamp='.time() : '';
		}
		wp_redirect( admin_url( "options-general.php?page=lsnCarousel_slider".$params) );
		die();
	}
}
add_action( 'admin_post_lsnServer_save', 'lsnServer_save' );

// Add Shortcode
function lsnCarousel_slider_shortcode($atts){
	global $wpdb;

	wp_enqueue_style("lsnCarousel_bootstrap_css", plugins_url('css/bootstrap.min.css', __FILE__));

	$countLayout = 0;

	if(!empty($atts['id'])){
		$sliderId = $atts['id'];
		$table_name = $wpdb->prefix . 'lsnCarousel';
		$getData = $wpdb->get_row("SELECT * FROM $table_name where slider_id = '".$sliderId."'", ARRAY_A);

		if(!empty($getData)){
			$S3BucketPath = getS3BucketPath();
			$url = $S3BucketPath."feed/xml/$sliderId.xml";
			if(lsnCarousel_isFeedExists($url)){
				$xml = simplexml_load_file($url);
				// echo '<pre>'; print_r($xml);

				// if(!empty($xml->device['type']) && $xml->device['type'] == 'v'){
				if(!empty($xml->device['type'])){

					$result = $xml->xpath("//layout");
					foreach($result as $r){
						$countLayout = count($r->children());
						break;
					}

					if($countLayout > 0){


						$isStart = 0;
						// $slideHtml = $slideNav = $slideItem = '';

						$captionArr = $toChkRepeatItem = array();

						// before loop fetch all captions and create an array
						$tableNameCaption = $wpdb->prefix . 'lsnCarousel_caption';
						$getCaptions = $wpdb->get_results("SELECT * FROM $tableNameCaption WHERE slider_id = '".$getData['id']."'", ARRAY_A);
						foreach($getCaptions as $getCaption){
							$key = $getCaption['item_id'].'-'.$getCaption['serial'];
							$captionArr[ $key ] = $getCaption['html'];
						}

						// get layout attributes
						$layoutWidth = $result[0]->attributes()->w.'px';	// layout width
						$layoutHeight = $result[0]->attributes()->h.'px';	// layout height
						$layoutBG = $result[0]->attributes()->bg;	// layout background color
						// end

						$slideHtml = '<div class="layout" style="width:'.$layoutWidth.'; height:'.$layoutHeight.'; position:absolute; background-color:'.$layoutBG.'; ">';

						// echo 'framescount--'.count($xml->layout->frame);
						// $frameCount = 0;
						foreach($xml->layout->frame as $frame){

							// get frames attributes
							$frameWidth = $frame->attributes()->w.'px';	// frame width
							$frameHeight = $frame->attributes()->h.'px'; // frame height
							$leftPos = $frame->attributes()->x.'px';	// left
							$topPos = $frame->attributes()->y.'px';	// top
							$zIndexPos = $frame->attributes()->z;	// z-index
							$frameStartTime = $frame->attributes()->st;	// frame start time
							$frameEndTime = $frame->attributes()->et;	// frame end time
							$currentTime = date('Y-m-d H:i:s');	// current time

							$st = strtotime($frameStartTime);
							$et = strtotime($frameEndTime);
							$ct = strtotime($currentTime);
							// end

							if(count($frame->children()) > 0){
								$array = array();
								// $slideHtml = $slideItem = $slideNav = $isStart = $sliderJsId = '';
								$slideItem = $slideNav = $isStart = $sliderJsId = '';
								foreach($frame->item as $key => $item){

									// for caption order, mainly for same content and different captions
									$itemIdArr = explode('-', $item['itemid']);
									$itemId = $itemIdArr[ count($itemIdArr) - 1 ];

									if(array_key_exists($itemId, $toChkRepeatItem)){
										$toChkRepeatItem[ $itemId ] += 1;
										$keyCount = $toChkRepeatItem[ $itemId ];
									}
									else{
										$toChkRepeatItem[ $itemId ] = 0;
										$keyCount = 0;
									}
									$keyCheck = $itemId.'-'.$keyCount;

									$array = lsnCarousel_getContentTag(array($slideItem, $slideNav, $isStart, $S3BucketPath, $keyCheck), $item, $frame, $captionArr);
									$slideItem = $array[0];
									$slideNav = $array[1];
									$isStart = $array[2];
								}
								// break;
								$sliderJsId = 'lsnCarousel'.rand(10000, 99999);

								if($ct > $st && $ct < $et){
									$slideHtml .= '<div id="'.$sliderJsId.'" class="lsnCarousel carousel slide" style="z-index:'.$zIndexPos.'; width:'.$frameWidth.'; height:'.$frameHeight.'; left:'.$leftPos.'; top:'.$topPos.'; position:absolute;">
										<div class="carousel-inner">'.$slideItem.'</div>'.
										/*<a class="left carousel-control" href="#'.$sliderJsId.'" role="button" data-slide="prev">
											<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
											<span class="sr-only">Previous</span>
										</a>
										<a class="right carousel-control" href="#'.$sliderJsId.'" role="button" data-slide="next">
											<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
											<span class="sr-only">Next</span>
										</a>*/
									'</div>';
								}

								// print all html
								// echo $slideHtml;

							}
							// $frameCount++;
						}
						$slideHtml .= '</div>';
						echo $slideHtml;

						if(!empty($slideItem)){
							wp_enqueue_script("lsnCarousel_custom_js", plugins_url('js/custom.js', __FILE__), array('jquery'), false, true);
						}
						else{
							lsnCarousel_showErrorMessage('Slider data not found.');
						}
					}
					else{
						lsnCarousel_showErrorMessage('Slider data not found.');
					}
				}
				else{
					lsnCarousel_showErrorMessage('Type of slider Id is invalid.');
				}
			}
			else{
				lsnCarousel_showErrorMessage('Slider feed not found.');
			}
		}
		else{
			lsnCarousel_showErrorMessage('Invalid Slider Id.');
		}
	}
	else{
		lsnCarousel_showErrorMessage('Slider id not found.');
	}
}

add_shortcode( 'LSquaredHubSlider', 'lsnCarousel_slider_shortcode' );
?>