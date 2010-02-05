<?php
$nzshpcrt_gateways[$num]['name'] = 'Ewire Payment';
$nzshpcrt_gateways[$num]['internalname'] = 'ewire_payment';
$nzshpcrt_gateways[$num]['admin_name'] = 'Ewire Payment';
$nzshpcrt_gateways[$num]['function'] = 'gateway_ewire_payment';
$nzshpcrt_gateways[$num]['form'] = "form_ewire_payment";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_ewire_payment";


function gateway_ewire_payment($seperator, $sessionid){
global $wpdb, $wpsc_cart;
 $purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
 $currency_code = $wpdb->get_results("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".(int)get_option('currency_type')."' LIMIT 1",ARRAY_A);
 $ewire_currency_code = $currency_code[0]['code'];
 $transact_url = get_option('transact_url');

$pageURL = 'http';
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}


$ewirePaymentBaseURL = "https://secure.ewire.dk/payment/transaction.ew";		
$ewirePaymentChangeBaseURL = "https://secure.ewire.dk/payment/transaction_change.ew";

if(get_option('EWPMEwireSubjectExt') == "1"){ 
	$EWPMSubjectString = get_option('EWPMEwireSubject').' '.$purchase_log['id'];
}else{ 
	$EWPMSubjectString = $purchase_log['id'];
}

$companyPrivateKey 		= get_option('EWPMCompanyPrivateKey');
$companyId 				= get_option('EWPMCompanyid');
$subject 				= $EWPMSubjectString;						
$lang 					= get_option('EWPMPayLang');										
$customerOrderId 		= $purchase_log['id'];								
$amount 				= number_format(wpsc_cart_total(false) * 100);								
$currency 				= $ewire_currency_code;							
$itemURL 				= 'http://www.yourshop.com/product';	
$expireDays 			= get_option('EWPMExpireDays');									
$annonymousPayerAllowed = get_option('EWPMAnonymUsers');			
$testmode				= get_option('EWPMTestmode');		
$validateResultAccept	= md5($customerOrderId . $amount . 'Success');
$validateResultDecline	= md5($customerOrderId . $amount . 'Failed');
$acceptURL 				= $transact_url.$seperator.'sessionid='.$sessionid.'&gateway=ewire_payment&ewire_payment=1&status='.$validateResultAccept.'&customerOrderId='.$customerOrderId;		
$declineURL 			= $pageURL.'&ewire_payment=0&customerOrderId='.$customerOrderId.'&status='.$validateResultDecline;			

// The string below validates all the information
$validateMD = md5($companyPrivateKey . $companyId . $subject . $customerOrderId . $amount . $currency . $expireDays . $annonymousPayerAllowed . $acceptURL . $declineURL);

?>
<h2>&nbsp;</h2>
<form action="https://secure.ewire.dk/payment/transaction.ew" method="post" name="Ewirepay" id="Ewirepay">
<input type="hidden" name="companyId" value="<?php echo $companyId ?>" />
<input type="hidden" name="lang" value="<?php echo $lang ?>" />
<input type="hidden" name="subject" value="<?php echo $subject ?>" />
<input type="hidden" name="customerOrderId" value="<?php echo $customerOrderId?>" />
<input type="hidden" name="amount" value="<?php echo$amount?>" />
<input type="hidden" name="currency" value="<?php echo $currency?>" />
<input type="hidden" name="itemURL" value="<?php echo $itemURL?>" />
<input type="hidden" name="testmode" value="<?php echo $testmode?>" />
<input type="hidden" name="expireDays" value="<?php echo $expireDays?>" />
<input type="hidden" name="annonymousPayerAllowed" value="<?php echo $annonymousPayerAllowed?>" />
<input type="hidden" name="acceptURL" value="<?php echo $acceptURL?>" />
<input type="hidden" name="declineURL" value="<?php echo $declineURL?>" />
<input type="hidden" name="validateMD" value="<?php echo $validateMD?>" />
</form>
<script language="JavaScript" type="text/javascript"> 
document.Ewirepay.submit();
</script> 
<?php
}

function form_ewire_payment(){
if(get_option('EWPMTestmode') == "1"){ 
	$EWPMTestmodeselected = 'checked="checked"';
}else{ 
	$EWPMTestmodeselected = '';
}

if(get_option('EWPMAnonymUsers') == "1"){ 
	$EWPMAnonymUsersmodeselected = 'checked="checked"';
}else{ 
	$EWPMAnonymUsersmodeselected = '';
}

if (get_option('EWPMEwireSubjectExt') == '2')
{
	$EWPMEwireSubjectExtDisabled = 'disabled="disabled"';
}else{ 
	$EWPMEwireSubjectExtDisabled = '';
}




$output ='
<style type="text/css">
.style2 {
	text-decoration: line-through;
}
.style3 {
	color: #C0C0C0;
}
</style>
<colgroup>
<a href="http://www.ewire.dk" target="_blank"><img src="..\wp-content\plugins\wp-e-commerce\images\Ewirelogo.gif" /></a>
<br />
<br />
</colgroup>
	<tr>
		<td style="width: 198px">CompanyID </td>
		<td><input name="EWPMCompanyid" type="text" value="'.get_option('EWPMCompanyid').'"></td>
	</tr>
	<tr>
		<td style="width: 198px">EncryptionKey</td>
		<td><input name="EWPMCompanyPrivateKey" type="text" value="'.get_option('EWPMCompanyPrivateKey').'"></td>
	</tr>
	<tr>
	<td colspan="2"></td>
	</tr>	
	<tr>
		<td style="width: 198px">Ewire Testmode</td>
		<td class="style1">
		<input type="hidden" name="EWPMTestmode" value="0" /><input type="checkbox" name="EWPMTestmode" id="EWPMTestmode" value="1" '.$EWPMTestmodeselected .' />
		</td>
	</tr>
	<tr>
		<td style="width: 198px">Annonymous Users</td>
		<td class="style1">
		<input type="hidden" name="EWPMAnonymUsers" value="0" /><input type="checkbox" name="EWPMAnonymUsers" id="EWPMAnonymUsers" value="1" '.$EWPMAnonymUsersmodeselected .' />
		</td>
	</tr>
	<tr>
		<td style="width: 198px">Expire Days</td>
		<td class="style1">7 Days<input name="EWPMExpireDays" type="radio" value="7" '.(get_option('EWPMExpireDays') == "7" ? 'checked="checked"' : '').' />14 Days<input name="EWPMExpireDays" type="radio" value="14" '.(get_option('EWPMExpireDays') == "14" ? 'checked="checked"' : '').' />
		21 Days<input name="EWPMExpireDays" type="radio" value="21" '.(get_option('EWPMExpireDays') == "21" ? 'checked="checked"' : '').' />&nbsp; </td>
	</tr>
	<tr>
		<td style="width: 198px">Payment Language</td>

		<td class="style1">Danish<input name="EWPMPayLang" type="radio" value="DA" '.(get_option('EWPMPayLang') == "DA" ? 'checked="checked"' : '').' /><span class="style2">English</span><input disabled="disabled" name="EWPMPayLang" type="radio" value="EN-GB" '.(get_option('EWPMPayLang') == "EN-GB" ? 'checked="checked"' : '').' />
		</td>
	</tr>
	<tr>
	<td colspan="2"></td>
	</tr>	

	<tr>		 
		<td style="width: 198px">Order Identifier</td>
		<td><input '.$EWPMEwireSubjectExtDisabled.' name="EWPMEwireSubject" type="text" value="'.get_option('EWPMEwireSubject').'"></td>
	</tr>
	<tr>
	<td>
	<p style="">
	Extension e.g<br /> [Order: xxxx] or [xxx]</td>
	</p>
	<td>
	<p style="width: 138px;text-align:right;">
	['.get_option('EWPMEwireSubject').' 123456]<input name="EWPMEwireSubjectExt" type="radio" value="1" '.(get_option('EWPMEwireSubjectExt') == "1" ? 'checked="checked"' : '').' /><br />
	[1324565]<input name="EWPMEwireSubjectExt" type="radio" value="2" '.(get_option('EWPMEwireSubjectExt') == "2" ? 'checked="checked"' : '').' />	
	</p>
</td>
	</tr>
		<tr><td></td>
	<td>
		</td>
	</tr>
';


return $output;
 
}

function submit_ewire_payment(){
 
if($_POST['EWPMCompanyid'] != null) {
 
update_option('EWPMCompanyid',
$_POST['EWPMCompanyid']);

}
 
if($_POST['EWPMCompanyPrivateKey'] != null) {
 
update_option('EWPMCompanyPrivateKey',
$_POST['EWPMCompanyPrivateKey']);
 
}

if($_POST['EWPMTestmode'] != null) {
 
update_option('EWPMTestmode',
$_POST['EWPMTestmode']);
 
}

if($_POST['EWPMAnonymUsers'] != null) {
 
update_option('EWPMAnonymUsers',
$_POST['EWPMAnonymUsers']);
 
}

if($_POST['EWPMExpireDays'] != null) {
 
update_option('EWPMExpireDays',
$_POST['EWPMExpireDays']);
 
}

if($_POST['EWPMPayLang'] != null) {
 
update_option('EWPMPayLang',
$_POST['EWPMPayLang']);
 
}

if($_POST['EWPMEwireSubject'] != null) {
 
update_option('EWPMEwireSubject',
$_POST['EWPMEwireSubject']);
 
}

if($_POST['EWPMEwireSubjectExt'] != null) {
 
update_option('EWPMEwireSubjectExt',
$_POST['EWPMEwireSubjectExt']);
 
}

return true;
 
}

function nzsc_ewire_payment_accept() {
global $wpdb, $wpsc_cart;

$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE id = ".$_GET['customerOrderId'] ." LIMIT 1",ARRAY_A) ;

$validateResultAccept = md5($_GET['customerOrderId'] . number_format($purchase_log['totalprice'] * 100) . 'Success');

if (($_GET['ewire_payment'] == '1') && $_GET['status'] == $validateResultAccept)
	{
		$sql = "UPDATE ".WPSC_TABLE_PURCHASE_LOGS." set transactid = '" . $_GET['ewireTicket'] . "', processed = 2 where id = " . $_GET['customerOrderId'];
		$wpdb->query($sql);
		
		unset($_SESSION['WpscGatewayErrorMessage']);
	}
if (($_GET['ewire_payment'] == '0') && $_GET['status'] != $validateResultAccept)
	{
		$errormsg1 = 'Customer Error 441';
		$sql = "UPDATE ".WPSC_TABLE_PURCHASE_LOGS." set transactid = 0, processed = 5 where id = " . $_GET['customerOrderId'];
		$wpdb->query($sql);
		
		$sql = "UPDATE ".WPSC_TABLE_PURCHASE_LOGS." set notes = 'Payment declined, following message was returned by Ewire:".$_GET['errorMessage'].".' where id = " . $_GET['customerOrderId'];
		$wpdb->query($sql);	

		$_SESSION['WpscGatewayErrorMessage'] = __('The payment was declined. <br />Please, try again or contact shop owner<br/>with following error message: <p style="color:red;text-decoration:underline;">'.$_GET['errorMessage'].'</p>');
	}

	
	

}
add_action('init', 'nzsc_ewire_payment_accept');

?>
