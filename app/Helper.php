<?php

namespace App;

class Helper
{
  public static function date( $fulldate, $format = 'd-M-Y' )
  {
    return date( $format, strtotime( $fulldate ) );
  }  
}
