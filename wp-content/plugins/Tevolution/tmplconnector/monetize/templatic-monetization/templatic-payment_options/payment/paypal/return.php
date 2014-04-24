<?php
/*
NAME : RETURN FILE AFTER PAYING FOR AN EVENT
DESCRIPTION : THIS FILE WILL BE CALLED ON SUCCESSFUL PAYMENT AFTER SUBMITTING AN EVENT.
*/
add_action('wp_head','show_background_color');
function show_background_color()
{
/* Get the background image. */
	$image = get_background_image();
	/* If there's an image, just call the normal WordPress callback. We won't do anything here. */
	if ( !empty( $image ) ) {
		_custom_background_cb();
		return;
	}
	/* Get the background color. */
	$color = get_background_color();
	/* If no background color, return. */
	if ( empty( $color ) )
		return;
	/* Use 'background' instead of 'background-color'. */
	$style = "background: #{$color};";
?>
<style type="text/css">
body.custom-background {
<?php echo trim( $style );
?>
}
</style>
<?php }
$page_title = PAYMENT_SUCCESS_TITLE;
global $page_title; ?>
<?php get_header();
apply_filters( 'wp_title', $page_title, $separator, '',11 )
 ?>
<div id="content">

<div id="hfeed">
	 <?php if ( get_option( 'ptthemes_breadcrumbs' ) == 'Yes') {  ?>
<div class="breadcrumb_in"><a href="<?php echo site_url(); ?>"><?php _e('Home'); ?></a> &raquo; <?php echo $page_title; ?></div><?php } ?>
<?php 
	if($_REQUEST['trans_id'] != "" && $_REQUEST['pid'] != ""){
	global $wpdb,$transection_db_table_name;
	$transection_db_table_name=$wpdb->prefix.'transactions';
	$trans_qry = "select * from $transection_db_table_name where trans_id='".$_REQUEST['trans_id']."' ";
	$trans_id = $wpdb->get_row($trans_qry);
	
	if($trans_id->trans_id !=""){
	$tmpdata = get_option('templatic_settings');
	$filecontent = stripslashes($tmpdata['post_payment_success_msg_content']);
	if(!$filecontent)
	{
		$filecontent = PAYMENT_SUCCESS_MSG;
	}
	
	$filesubject = __('Payment procedure has been completed',DOMAIN);
	
	$store_name = get_option('blogname');
	$order_id = $_REQUEST['pid'];
	if(get_post_type($order_id)=='event')
	{
		$post_link = get_permalink($_REQUEST['pid']);
	}else
	{
	$post_link = get_permalink($_REQUEST['pid']);	
	}
	
	
	$buyer_information = "";
	global $wpdb,$custom_post_meta_db_table_name;
	$post = get_post($_REQUEST['pid']);
	$address = stripslashes(get_post_meta($post->ID,'geo_address',true));
	$geo_latitude = get_post_meta($post->ID,'geo_latitude',true);
	$geo_longitude = get_post_meta($post->ID,'geo_longitude',true);
	$timing = get_post_meta($post->ID,'timing',true);
	$contact = stripslashes(get_post_meta($post->ID,'contact',true));
	$email = get_post_meta($post->ID,'email',true);
	$website = get_post_meta($post->ID,'website',true);
	$twitter = get_post_meta($post->ID,'twitter',true);
	$facebook = get_post_meta($post->ID,'facebook',true);

			
	$search_array = array('[#site_name#]','[#submited_information_link#]','[#submited_information#]');
	$replace_array = array($store_name,$post_link,$buyer_information);
	
	$filecontent = str_replace($search_array,$replace_array,$filecontent);
	?>
	<div class="content-title"><?php echo $page_title; ?></div>
	<?php
	if($_REQUEST['pid']!="" && $_REQUEST['trans_id']!=""){
		global $wpdb,$transection_db_table_name;

		$wpdb->query("UPDATE $transection_db_table_name SET status=1 , paypal_transection_id ='".$_REQUEST['txn_id']."' where trans_id = '".$_REQUEST['trans_id']."'");
		$wpdb->query("UPDATE $wpdb->posts SET post_status='".fetch_posts_default_paid_status()."' where ID = '".$_REQUEST['pid']."'");
		$post_default_status = $tmpdata['post_default_status'];
		if($post_default_status == 'publish')
		{
			$status = 'Approved';
		}
		else
		{
			$status = 'Pending';
		}
		update_post_meta($_REQUEST['pid'],'status',$status);
		
	}
	}else{
		$filesubject =  INVALID_TRANSACTION_TITLE;
		$filecontent = AUTHENTICATION_CONTENT;
	
	}
	//Payment success email: start
	$orderId = $_REQUEST['trans_id'];
	global $wpdb,$transection_db_table_name;
	$transection_db_table_name = $wpdb->prefix . "transactions";
	$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
	$orderinfo = $wpdb->get_row($ordersql);
	$pid = $orderinfo->post_id;
	$payment_type = $orderinfo->payment_method;
	$payment_date =  date_i18n(get_option('date_format'),strtotime($orderinfo->payment_date));
	$user_detail = get_userdata($orderinfo->user_id); // get user details 
	$user_email = $user_detail->user_email;
	$user_login = $user_detail->display_name;
	if(isset($orderinfo->status) && $orderinfo->status== 1)
		$payment_status = APPROVED_TEXT;
	elseif(isset($orderinfo->status) && $orderinfo->status== 2)
		$payment_status = ORDER_CANCEL_TEXT;
	elseif(isset($orderinfo->status) && $orderinfo->status== 0)
		$payment_status = PENDING_MONI;
		
	$to = get_site_emailId_plugin();
	$productinfosql = "select ID,post_title,guid,post_author from $wpdb->posts where ID = $pid";
	$productinfo = get_post($pid);
    $post_name = $productinfo->post_title;
	$txn_id = $_REQUEST['txn_id'];
	$tarns_id = $_REQUEST['trans_id'];
	$txn_type = $_REQUEST['txn_type'];
	$transaction_details="";
	
	if(isset($_REQUEST['upgrade']) && $_REQUEST['upgrade']=='pkg'){
		$transaction_details .= "--------------------------------------------------</br>\r\n";
		$transaction_details .= "Transaction details of upgrade subscription.</br>\r\n";
		$transaction_details .= "--------------------------------------------------</br>\r\n";	
	}else{
		$transaction_details .= "--------------------------------------------------</br>\r\n";
		$transaction_details .= __('Payment Details for',DOMAIN)." $post_name</br>\r\n";
		$transaction_details .= "--------------------------------------------------</br>\r\n";
	}

	if($txn_id)
		$transaction_details .=     __('Paypal Transaction ID',DOMAIN).": $txn_id</br>\r\n";
	if($trans_id)
		$transaction_details .=     __('Transaction ID',DOMAIN).": $tarns_id</br>\r\n";
	if($payment_status !='')
		$transaction_details .=     __('Status',DOMAIN).": $payment_status</br>\r\n";
	if($payment_type !='')
		$transaction_details .= 	__('Type',DOMAIN).": $payment_type</br>\r\n";
	$transaction_details .= 		__('Date',DOMAIN).": $payment_date</br>\r\n";
	if($txn_type !='')
		$transaction_details .=         __('Method',DOMAIN).": $txn_type</br>\r\n";
	$transaction_details .= "--------------------------------------------------\r\n";
	$transaction_details = $transaction_details;
	$subject = $tmpdata['payment_success_email_subject_to_admin'];
	if(!$subject)
	{
		$subject = __("Payment Success Confirmation Email",DOMAIN);
	}
	$content = $tmpdata['payment_success_email_content_to_admin'];
	if(!$content)
	{
		$content = __("<p>Howdy [#to_name#] ,</p><p>You have received a payment of [#payable_amt#] on [#site_name#]. Details are available below</p><p>[#transaction_details#]</p><p>Thanks,<br/>[#site_name#]</p>",DOMAIN);
	}
	$store_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
	$fromEmail = get_option('admin_email');
	$fromEmailName = stripslashes(get_option('blogname'));	
	$search_array = array('[#to_name#]','[#payable_amt#]','[#transaction_details#]','[#site_name#]');
	$replace_array = array($fromEmail,$payable_amount,$transaction_details,$store_name);
	$filecontent1 = str_replace($search_array,$replace_array,$content);
	@templ_send_email($fromEmail,$fromEmailName,$to,$user_login,$subject,$filecontent1,''); // email to admin
	// post details
	$post_link = site_url().'/?ptype=preview&alook=1&pid='.$pid;
	$post_title = '<a href="'.$post_link.'">'.stripslashes($productinfo->post_title).'</a>'; 
	$aid = $productinfo->post_author;
	$mail_post_type = $productinfo->post_type;
	$userInfo = get_userdata($aid);
	$to_name = $userInfo->user_nicename;
	$to_email = $userInfo->user_email;
	$user_email = $userInfo->user_email;
	
	$transaction_details ="";
	if(isset($_REQUEST['upgrade']) && $_REQUEST['upgrade']=='pkg'){
		$transaction_details .= "--------------------------------------------------</br>\r\n";
		$transaction_details .= "Transaction details of upgrade subscription.</br>\r\n";
		$transaction_details .= "--------------------------------------------------</br>\r\n";	
	}else{
		$transaction_details .= "--------------------------------------------------</br>\r\n";
		$transaction_details .= "Payment Details for $post_title</br>\r\n";
		$transaction_details .= "--------------------------------------------------</br>\r\n";	
	}
	if($txn_id)
		$transaction_details .=     __('Paypal Transaction ID',DOMAIN).": $txn_id</br>\r\n";
	if($trans_id)
		$transaction_details .=     __('Transaction ID',DOMAIN).": $tarns_id</br>\r\n";
	if($payment_status !='')
		$transaction_details .=     __('Status',DOMAIN).": $payment_status</br>\r\n";
	if($payment_type !='')
		$transaction_details .= 	__('Type',DOMAIN).": $payment_type</br>\r\n";
	$transaction_details .= 		__('Date',DOMAIN).": $payment_date</br>\r\n";
	if($txn_type !='')
		$transaction_details .=         __('Method',DOMAIN).": $txn_type</br>\r\n";
	$transaction_details .= "--------------------------------------------------\r\n";
	$transaction_details = $transaction_details;
	
	$subject = $tmpdata['payment_success_email_subject_to_client'];
	if(!$subject)
	{
		$subject = __("Payment Success Confirmation Email",DOMAIN);
	}
	$content = $tmpdata['payment_success_email_content_to_client'];
	if(!$content)
	{
		$content = __("<p>Hello [#to_name#]</p><p>Here's some info about your payment...</p><p>[#transaction_details#]</p><p>If you'll have any questions about this payment please send an email to [#admin_email#]</p><p>Thanks!,<br/>[#site_name#]</p>",DOMAIN);
	}
	$store_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
	$search_array = array('[#to_name#]','[#transaction_details#]','[#site_name#]','[#admin_email#]');
	$replace_array = array($to_name,$transaction_details,$store_name,get_option('admin_email'));
	$content = str_replace($search_array,$replace_array,$content);
	templ_send_email($fromEmail,$fromEmailName,$user_email,$user_login,$subject,$content,$extra='');
	//Payment success email: end	
}else{
	$filesubject = INVALID_TRANSACTION_TITLE;
	$filecontent = INVALID_TRANSACTION_CONTENT;
}
/*Add Action for change the paypal successful return content as per needed */
do_action('paypal_successfull_return_content',$_REQUEST['pid'],$filesubject,$filecontent);
?>
<?php do_action('tevolution_submition_success_post_content');?>
</div> <!-- content #end -->
</div> <!-- content #end -->
<?php if ( is_active_sidebar( 'primary-sidebar') ) : ?>
	<div id="sidebar-primary" class="sidebar">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</div>
<?php endif; ?>
<?php get_footer(); ?>