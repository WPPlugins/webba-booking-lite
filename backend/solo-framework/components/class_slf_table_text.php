<?php
// Solo Framework table text component
if ( ! defined( 'ABSPATH' ) ) exit;

class SLFTableText extends SLFTableComponent {


	public function __construct( $title, $name, $value, $data_source ) {
		parent::__construct( $title, $name, $value, $data_source );
	}
	
    public function renderCell(){
    	if ( $this->name == 'status' &&   $this->value == 'pending' ){
    		return __( 'Booked (not paid)', 'wbk' );
    	} 
        if( $this->title == __( 'Customer name','wbk' ) ){
            return  WBK_Db_Utils::backend_customer_name_processing( $this->data_source[1], $this->value );
        }
		return $this->value;    	
    }
    public function renderControl(){
    	$html = '<label class="slf_table_component_label" >' . $this->title . '</label>';
		$html .= '<input type="text" class="slf_table_component_input slf_table_component_text" name="' . $this->name . '"   value="' . $this->value . '"  />';
		return $html;
    }


}
