<?php
// webba booking email notifications class
class WBK_Email_Notifications {
 
	public function __construct( $service_id, $appointment_id ) {
		$this->admin_book_status = get_option( 'wbk_email_admin_book_status', '' );
		$this->admin_email_message = get_option( 'wbk_email_admin_book_message', '' );
		$this->admin_email_subject = get_option( 'wbk_email_admin_book_subject', '' );
		$this->from_email = get_option( 'wbk_from_email' );
		$this->from_name = get_option( 'wbk_from_name' );
		$this->service_id = $service_id;
		$this->appointment_id = $appointment_id;
 	}
 	public function set_email_content_type() {
 		return 'text/html';
 	}
 	public function send( $event ) {
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		switch ( $event ) {
		    case 'book':
				$appointment = new WBK_Appointment();
				if ( !$appointment->setId( $this->appointment_id ) ) {
					return;
				}
				if ( !$appointment->load() ) {
					return;
				}
				$service = new WBK_Service();
				if ( !$service->setId( $this->service_id ) ) {
					return;
				}
				if ( !$service->load() ) {
					return;
				}
		     
		    	// email to admin
		    	if( $this->admin_book_status != '' ) {
			    	//	validation
			    	if ( !WBK_Validator::checkStringSize( $this->admin_email_message, 1, 50000 ) ||
			    		 !WBK_Validator::checkStringSize( $this->admin_email_subject, 1, 100 ) ||
			    		 !WBK_Validator::checkEmail( $this->from_email ) ||
			    		 !WBK_Validator::checkStringSize( $this->from_name, 1, 100 )
			    	   ) {
			    	   return;
			        }
				    $message = $this->message_placeholder_processing( $this->admin_email_message, $appointment, $service );	
					$headers = 'From: ' . $this->from_name . ' <' . $this->from_email .'>' . "\r\n";
					$subject = 	$this->subject_placeholder_processing( $this->admin_email_subject, $appointment, $service );
					 
					add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			    	wp_mail( $service->getEmail(), $this->admin_email_subject, $message, $headers );
 		 			if ( WBK_Validator::checkEmail( $this->super_admin_email )  ) {
 						wp_mail(  $this->super_admin_email, $this->admin_email_subject, $message, $headers );
 					}
 					remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
			    }
		    break;
		 
		}
 	}
	public function sendToSecondary( $data ) {
		return;
	}
	public function sendOnApprove(){
	}
	public function prepareOnCancel(){
		return;
	}
	public function sendOnCancel(){
		return;
 	}
	protected  function get_string_between( $string, $start, $end ){
		return;
	}
	protected function subject_placeholder_processing( $message, $appointment, $service ){
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat(); 
	 	$message = str_replace( '#service_name', $service->getName(), $message );
		$message = str_replace( '#appointment_day', date_i18n( $date_format, $appointment->getDay() ), $message );
		$message = str_replace( '#appointment_time', date_i18n( $time_format, $appointment->getTime() ), $message );
		return $message;					 
	}
	protected function message_placeholder_processing( $message, $appointment, $service ){
		$date_format = WBK_Date_Time_Utils::getDateFormat();
		$time_format = WBK_Date_Time_Utils::getTimeFormat();
		// begin landing for payment and cancelation
		$payment_link = '';
		$payment_link_text = get_option( 'wbk_email_landing_text', __( 'Click here to pay for your booking.', 'wbk' ) );
		$payment_link_url = get_option( 'wbk_email_landing', '' );
		$cancel_link_text = get_option( 'wbk_email_landing_text_cancel',  __( 'Click here to cancel your booking.', 'wbk' ) );
		
		$payment_link = '';
		$cancel_link = '';
		
		if( $payment_link_url != '' ){
			$token = WBK_Db_Utils::getTokenByAppointmentId( $appointment->getId() );
			if( $token != false ){
				$payment_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?order_payment=' . $token . '">' . trim( $payment_link_text ) . '</a>';
			    $cancel_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?cancelation=' . $token . '">' . trim( $cancel_link_text ) . '</a>';
			 }
		}   
		// end landing for payment

		// begin total amount
		$total_price = '';
		$payment_methods = explode( ';', $service->getPayementMethods() );
		if( count( $payment_methods )  > 0 ){
			$total = $appointment->getQuantity() * $service->getPrice();
			$price_format = get_option( 'wbk_payment_price_format', '$#price' );
			$tax = get_option( 'wbk_paypal_tax', 0 );
 			if( is_numeric( $tax ) && $tax > 0 ){
				$tax_amount = ( ( $total ) / 100 ) * $tax;
			    	$total = $total + $tax_amount;
				} 
			$total_price =  str_replace( '#price', number_format( $total, 2 ), $price_format );
		}
		// end total amount

		// beging extra data
		$extra_data = $appointment->getExtra();
		$extra_data_html = str_replace( '###', '<br />', $extra_data);

		$extra_data_ids = explode( '###', $appointment->getExtraWithFieldIds() );
		foreach( $extra_data_ids as $extra_id ){
			if( trim( $extra_id ) == '' ){
				continue;
			}
			$value_pair = explode(':', $extra_id );
			if( count( $value_pair ) != 2 ){
				continue;
			}		
			$field_id = trim( $value_pair[0] );
			$matches = array();
			preg_match( "/\[[^\]]*\]/", $field_id, $matches);
			$field_id = trim( $matches[0], '[]' );
			$mask = '#field_' . $field_id;		 
			$message = str_replace( $mask, $value_pair[1], $message );		        	        	
		}
		// end extra data
		
		$message = str_replace( '#cancel_link', $cancel_link, $message );		        	        
		$message = str_replace( '#payment_link', $payment_link, $message );		        
		$message = str_replace( '#total_amount', $total_price, $message );		        
		$message = str_replace( '#service_name', $service->getName(), $message );
		$message = str_replace( '#customer_name', $appointment->getName(), $message );
		$message = str_replace( '#appointment_day', date_i18n( $date_format, $appointment->getDay() ), $message );
		$message = str_replace( '#appointment_time', date_i18n( $time_format, $appointment->getTime() ), $message );
		$message = str_replace( '#customer_phone', $appointment->getPhone(), $message );
		$message = str_replace( '#customer_email', $appointment->getEmail(), $message );
		$message = str_replace( '#customer_comment', $appointment->getDescription(), $message );
		$message = str_replace( '#items_count', $appointment->getQuantity(), $message );
		$message = str_replace( '#appointment_id', $appointment->getId(), $message );
		$message = str_replace( '#customer_custom', $extra_data_html, $message );

		return $message;					 
	}
	public function sendMultipleCustomerNotification( $appointment_ids ){
		return;
	}

}
?>