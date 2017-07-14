<?php
    // check if accessed directly
    if ( ! defined( 'ABSPATH' ) ) exit;
 	if( isset( $_GET['paypal_status'] ) ){
?>
		<div class="wbk-outer-container">
			<div class="wbk-inner-container">
				<div class="wbk-frontend-row">
					<div class="wbk-col-12-12"> 
						<div class="wbk-details-sub-title"><?php echo get_option( 'wbk_payment_result_title', __( 'Payment status', 'wbk' ) ); ?></div>
					</div>
					<div class="wbk-col-12-12"> 
						<?php
							if( $_GET['paypal_status'] == 1 ){
							?>
								<div class="wbk-input-label"><?php echo get_option( 'wbk_payment_success_message', __( 'Payment completed.') ); ?></div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 5 ){
							?>
								<div class="wbk-input-label"><?php echo get_option( 'wbk_payment_cancel_message', __( 'Payment canceled.') ); ?></div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 2 ){
							?>
								<div class="wbk-input-label">Error 102</div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 3 ){
							?>
								<div class="wbk-input-label">Error 103</div>
						<?php
						    }
						?>
						<?php
							if( $_GET['paypal_status'] == 4 ){
							?>
								<div class="wbk-input-label">Error 104</div>
						<?php
						    }
						?>
					</div>
				</div>
			</div>
		</div>

<?php
		return;
	}
?>
<?php
 	if( isset( $_GET['order_payment'] ) ){
 		$order_payment =  $_GET['order_payment'];

		$order_payment = str_replace('"', '', $order_payment );
		$order_payment = str_replace('<', '', $order_payment );
		$order_payment = str_replace('\'', '', $order_payment );
		$order_payment = str_replace('>', '', $order_payment );
		$order_payment = str_replace('/', '', $order_payment );
		$order_payment = str_replace('\\',  '', $order_payment );

 		$appointment_id = WBK_Db_Utils::getAppointmentIdByToken( $order_payment );
 		if( $appointment_id === false ){
 			$valid = false; 
				?>
				<div class="wbk-outer-container">
					<div class="wbk-inner-container">
						<div class="wbk-frontend-row">
							<div class="wbk-col-12-12">
								<div class="wbk-input-label">
									<?php echo get_option( 'wbk_email_landing_text_invalid_token', __( 'Appointment booking doesn\'t exist.', 'wbk' ) ); ?>
								</div>			
							</div>
						</div>
					</div>
				</div>				 
				<?php	
				exit;			
 		} else {
 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
 				$valid = true;
 				$appointment = new WBK_Appointment();
				if ( !$appointment->setId( $appointment_id ) ) {				
					$valid = false;
				}
				if ( !$appointment->load() ) {
					$valid = false;
				}
				$service = new WBK_Service();
				if ( !$service->setId( $service_id ) ) {
					$valid = false;
				}
				if ( !$service->load() ) {
					$valid = false;
				}
				$appointment_status = WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
				if(  $appointment_status != 'paid' && $appointment_status != 'paid_approved' ){			
					$title = get_option( 'wbk_appointment_information', __( 'Appointment on #dt', 'wbk' ) );
					$time_format = WBK_Date_Time_Utils::getTimeFormat();
					$date_format = WBK_Date_Time_Utils::getDateFormat();
					$time = $appointment->getTime();			
						
					$title = str_replace( '#name', $appointment->getName(), $title );
					$title = str_replace( '#service', $service->getName(), $title );
					$title = str_replace( '#date', date_i18n( $date_format, $time ), $title );
					$title = str_replace( '#time', date_i18n( $time_format, $time ), $title );
					$title = str_replace( '#dt', date_i18n( $date_format, $time ) . ' ' .  date_i18n( $time_format, $time ), $title );
	 
					$title .= WBK_PayPal::renderPaymentMethods( $service_id, array( $appointment_id ) );
				} else {
					$title = get_option( 'wbk_nothing_to_pay_message', __( 'There are no bookings available for payment.', 'wbk' ) );
				}
 				if( $valid == true ){
			?>
					<div class="wbk-outer-container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php echo $title; ?>
									</div>	
								</div>
							</div>
							<div class="wbk-frontend-row" id="wbk-payment">
							</div>
						</div>
					</div> 
					<?php
					return;
			}
 		}
?>
<?php
	}					
?>
<?php
 	if( isset( $_GET['cancelation'] ) ){	 	

 	 		$cancelation =  $_GET['cancelation'];
			$cancelation = str_replace('"', '', $cancelation );
			$cancelation = str_replace('<', '', $cancelation );
			$cancelation = str_replace('\'', '', $cancelation );
			$cancelation = str_replace('>', '', $cancelation );
			$cancelation = str_replace('/', '', $cancelation );
			$cancelation = str_replace('\\',  '', $cancelation );

			$appointment_id = WBK_Db_Utils::getAppointmentIdByToken( $cancelation );
	 		if( $appointment_id === false ){
				$valid = false; 
				?>
				<div class="wbk-outer-container">
					<div class="wbk-inner-container">
						<div class="wbk-frontend-row">
							<div class="wbk-col-12-12">
								<div class="wbk-input-label">
									<?php  echo get_option( 'wbk_email_landing_text_invalid_token', __( 'Appointment booking doesn\'t exist.', 'wbk' ) ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>				 
				<?php	
				exit;				
	 		} else {
 				$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
 				$valid = true;
 				$appointment = new WBK_Appointment();
				if ( !$appointment->setId( $appointment_id ) ) {
					$valid = false;
				}
				if ( !$appointment->load() ) {
					$valid = false;
				}
				// check buffer
				$buffer = get_option( 'wbk_cancellation_buffer', '' );
				if( $buffer != '' ){
					if( intval( $buffer ) > 0 ){
						$buffer_point = ( intval( $appointment->getTime() - intval( $buffer ) * 86400 ) );
						if( time() >  $buffer_point ){
							?>
								<div class="wbk-outer-container">
									<div class="wbk-inner-container">
										<div class="wbk-frontend-row">
											<div class="wbk-col-12-12">
												<div class="wbk-input-label">
 													<?php echo get_option( 'wbk_booking_couldnt_be_canceled2', __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'wbk' ) ); ?>
												</div>			
											</div>
										</div>
									</div>
								</div>	
							<?php
							exit;
						}
					}
				}
				// end check buffer

				$service = new WBK_Service();
				if ( !$service->setId( $service_id ) ) {
					$valid = false;
				}
				if ( !$service->load() ) {
					$valid = false;
				}
				 			
				$title = get_option( 'wbk_appointment_information', __( 'Appointment on #dt', 'wbk' ) );
				$time_format = WBK_Date_Time_Utils::getTimeFormat();
				$date_format = WBK_Date_Time_Utils::getDateFormat();
				$time = $appointment->getTime();			
						
				$title = str_replace( '#name', $appointment->getName(), $title );
				$title = str_replace( '#service', $service->getName(), $title );
				$title = str_replace( '#date', date_i18n( $date_format, $time ), $title );
				$title = str_replace( '#time', date_i18n( $time_format, $time ), $title );
				$title = str_replace( '#dt', date_i18n( $date_format, $time ) . ' ' .  date_i18n( $time_format, $time ), $title );
	 			
	 			$appointment_status = WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );

				if( $appointment_status == 'paid' || $appointment_status == 'paid_approved' ){	
					$title .= '<p>' . get_option( 'wbk_booking_couldnt_be_canceled', __( 'Paid booking couldn\'t be canceled.', 'wbk' ) ) . '</p>';
					$content = '';
				} else {
					 
					$content = '<label class="wbk-input-label" for="wbk-customer_email">'. get_option( 'wbk_booking_cancel_email_label', __( 'Please, enter your email to confirm cancelation', 'wbk' ) ).'</label>';	
					$content .= '<input name="wbk-email" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-customer_email" type="text">';
					$content .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10" id="wbk-cancel_booked_appointment" data-appointment="'. $cancelation .'" value="' . get_option( 'wbk_cancel_button_text', __( 'Cancel booking', 'wbk' ) ) . '" type="button">';
				}			
			}  
 				if( $valid == true ){
			?>
					<div class="wbk-outer-container">
						<div class="wbk-inner-container">
							<div class="wbk-frontend-row">
								<div class="wbk-col-12-12">
									<div class="wbk-input-label">
									 	<?php echo $title . $content; ?>
									</div>
								</div>
							</div>
							<div class="wbk-frontend-row" id="wbk-cancel-result">
							</div>
						</div>
					</div> 
					<?php
					return;
				}
 	}
?>

 