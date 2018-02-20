<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Loan;
use App\Notification;
use App\Helper;

class NotificationController extends Controller
{
  
  /* BulkSMS.com Credentials */
  
  var $username = 'nosreg216';
  var $password = '5TjOI37FjsqI';
  
  var $url = 'https://bulksms.vsms.net/eapi/submission/send_sms/2/2.0';
  
  public function send( $destination, $type, Loan $loan)
  {
    $message = $this->buildMessagge($type, $loan);
    
    $post_body = $this->enconde_seven_bit_sms( $message, $destination );
    
    $result = $this->send_message( $post_body );
  }
  
  function buildMessagge( $type, Loan $loan)
  {
    
    $notification = Notification::where('type', $type)->first();
    
    $message = $notification->message;
    
    switch ( $type )
    {
      case 'NL':
      $niceloan = number_format( $loan->loaned, 0 );
      $nicedate = Helper::date( $loan->next_due, 'd/m/Y');
      $nicebalance = number_format( $loan->balance, 0 );
      $message = str_replace( '[Saldo]', $nicebalance, $message);
      $message = str_replace( '[Monto]', $niceloan , $message);
      $message = str_replace( '[Fecha]', $nicedate, $message);
      break;
      
      case 'SP':
      $nicedate = Helper::date( $loan->next_due, 'd/m/Y');
      $nicedue = number_format( $loan->credits, 0 );
      $nicebalance = number_format( $loan->balance, 0 );
      $message = str_replace( '[Cuota]', $nicedue, $message);
      $message = str_replace( '[Saldo]', $nicebalance , $message);
      $message = str_replace( '[Fecha]', $nicedate, $message);
      break;
      
      case 'PR':
      $nicedate = Helper::date( $loan->next_due, 'd/m/Y');
      $due = ( $loan->firdue ) ? $loan->firdue : $loan->regdue;
      $nicedue = number_format( $due, 0 );

      $nicebalance = number_format( $loan->balance, 0 );
      $message = str_replace( '[Cuota]', $nicedue, $message);
      $message = str_replace( '[Saldo]', $nicebalance, $message);
      $message = str_replace( '[Fecha]', $nicedate, $message);
      break;
      
      case 'CL':
      $niceloan = number_format( $loan->loaned, 0 );
      $message = str_replace( '[Monto]', $niceloan , $message);
      break;
    }
    
    return $message;
  }
  
  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }
  
  
  private function enconde_seven_bit_sms ( $message, $destination )
  {
    $post_fields = array (
      'username' => $this->username,
      'password' => $this->password,
      'message'  => $this->character_resolve( $message ),
      'msisdn'   => '506' . str_replace(' ', '', $destination),
      'allow_concat_text_sms' => 0, # Change to 1 to enable long messages
      'concat_text_sms_max_parts' => 2
    );
    
    return $this->make_post_body($post_fields);
  }
  
  private function character_resolve($body)
  {
    $special_chrs = array(
      'Δ'=>'0xD0', 'Φ'=>'0xDE', 'Γ'=>'0xAC', 'Λ'=>'0xC2', 'Ω'=>'0xDB',
      'Π'=>'0xBA', 'Ψ'=>'0xDD', 'Σ'=>'0xCA', 'Θ'=>'0xD4', 'Ξ'=>'0xB1',
      '¡'=>'0xA1', '£'=>'0xA3', '¤'=>'0xA4', '¥'=>'0xA5', '§'=>'0xA7',
      '¿'=>'0xBF', 'Ä'=>'0xC4', 'Å'=>'0xC5', 'Æ'=>'0xC6', 'Ç'=>'0xC7',
      'É'=>'0xC9', 'Ñ'=>'0xD1', 'Ö'=>'0xD6', 'Ø'=>'0xD8', 'Ü'=>'0xDC',
      'ß'=>'0xDF', 'à'=>'0xE0', 'ä'=>'0xE4', 'å'=>'0xE5', 'æ'=>'0xE6',
      'è'=>'0xE8', 'é'=>'0xE9', 'ì'=>'0xEC', 'ñ'=>'0xF1', 'ò'=>'0xF2',
      'ö'=>'0xF6', 'ø'=>'0xF8', 'ù'=>'0xF9', 'ü'=>'0xFC',
    );
    
    $ret_msg = '';
    
    $body = ( mb_detect_encoding( $body, 'UTF-8') != 'UTF-8' ) ? utf8_encode($body) : $body;
    
    for ( $i = 0; $i < mb_strlen( $body, 'UTF-8' ); $i++ )
    {
      $c = mb_substr( $body, $i, 1, 'UTF-8' );
      
      if( isset( $special_chrs[ $c ] ) ) $ret_msg .= chr( $special_chrs[ $c ] );
      else $ret_msg .= $c;
    }
    return $ret_msg;
  }
  
  
  private function make_post_body($post_fields)
  {
    $post_body = '';
    
    foreach( $post_fields as $key => $value )
    $post_body .= urlencode( $key ).'='.urlencode( $value ).'&';
    
    $post_body = rtrim( $post_body,'&' );
    return $post_body;
  }
  
  function send_message ( $post_body )
  {
    $ch = curl_init( );
    curl_setopt ( $ch, CURLOPT_URL, $this->url );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
    // Allowing cUrl funtions 20 second to execute
    curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
    // Waiting 20 seconds while trying to connect
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 );
    
    $response_string = curl_exec( $ch );
    $curl_info = curl_getinfo( $ch );
    
    $sms_result = array();
    $sms_result['success'] = 0;
    $sms_result['details'] = '';
    $sms_result['transient_error'] = 0;
    $sms_result['http_status_code'] = $curl_info['http_code'];
    $sms_result['api_status_code'] = '';
    $sms_result['api_message'] = '';
    $sms_result['api_batch_id'] = '';
    
    if ( $response_string == FALSE )
    {
      $sms_result['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
    }
    elseif ( $curl_info[ 'http_code' ] != 200 )
    {
      $sms_result['transient_error'] = 1;
      $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
    }
    else
    {
      $sms_result['details'] .= "Response from server: $response_string\n";
      $api_result = explode( '|', $response_string );
      $status_code = $api_result[0];
      $sms_result['api_status_code'] = $status_code;
      $sms_result['api_message'] = $api_result[1];
      if ( count( $api_result ) != 3 ) {
        $sms_result['details'] .= "Error: could not parse valid return data from server.\n" . count( $api_result );
      }
      else
      {
        if ($status_code == '0') {
          $sms_result['success'] = 1;
          $sms_result['api_batch_id'] = $api_result[2];
          $sms_result['details'] .= "Message sent - batch ID $api_result[2]\n";
        }
        else if ($status_code == '1') {
          # Success: scheduled for later sending.
          $sms_result['success'] = 1;
          $sms_result['api_batch_id'] = $api_result[2];
        }
        else
        {
          $sms_result['details'] .= "Error sending: status code [$api_result[0]] description [$api_result[1]]\n";
        }
      }
    }
    curl_close( $ch );
    return $sms_result;
  }
}
