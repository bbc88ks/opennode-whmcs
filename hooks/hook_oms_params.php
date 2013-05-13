<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

include_once (dirname(__FILE__) . '/inc/oms_utils.php');

function addParams($vars) {
	global $oms_img, $oms_hostname, $oms_pass;

	$oms_iframe_logout_src = $oms_hostname . 'logout';
	$oms_iframe_logout = '<iframe name="oms_iframe" src="' . $oms_iframe_logout_src . '" style="display:none"></iframe>';

	$arr = array("oms_img" => $oms_img, "oms_link" => $oms_hostname, "oms_iframe_logout" => $oms_iframe_logout);

	$userId = $_SESSION['uid'];
	if ($userId) {
		$omsArr = getOmsUsageForUserId($userId);
		$arr['omsdata'] = $omsArr;
	}
	return $arr;
}

/**
 * Function that gets OMC VM data to display on template
 */
function getOmsUsageForUserId($userId) {
	global $oms_usage_db;

	//Get products prices
	$p_core = getProductPriceByName("1 Core");
	$p_disk = getProductPriceByName("1GB Storage");
	$p_memory = getProductPriceByName("1GB RAM");
	//logActivity("Using product prices for calculations: Cores:" . $p_core . ". Disk:" . $p_disk . ".Memory:" . $p_memory);

	if (!$p_core || !$p_disk || !$p_memory) {
		logActivity("Error: Product prices not set.");
		return;
	}

	$username = get_username($userId);

	$table = $oms_usage_db . ".CONF_CHANGES";

	$sql = "select * from " . $table . " WHERE username='" . $username . "' ORDER BY timestamp DESC LIMIT 1";

	$result = mysql_query($sql);

	if ($result) {
		$data = mysql_fetch_array($result);
		if ($data) {
			$id = $data['id'];
			$amount = $data['cores'] * $p_core + $data['disk'] * $p_disk + $data['memory'] * $p_memory;
			$data['vm_cost'] = $amount;
		}
	}
	$data['currentcredit'] = getCreditForUserId($userId);
	return $data;
}

function addIframe($vars) {
	global $oms_img, $oms_hostname, $oms_pass;
	if ($_COOKIE[p]) {
		$pass = $_COOKIE[p];
		setcookie('p', '');
		unset($_COOKIE[p]);

		$userid = $_SESSION[uid];
		$usernameOms = get_username($userid);
		if ($userid && $usernameOms) {
			$oms_iframe_src = $oms_hostname . 'basicauth?username=' . $usernameOms . '&password=' . $pass;
			return '<iframe name="oms_iframe" src="' . $oms_iframe_src . '" style="display:none"></iframe>';
		}

	}
}

add_hook("ClientAreaPage", 1, "addParams");
add_hook("ClientAreaHeaderOutput", 1, "addIframe");
?>
