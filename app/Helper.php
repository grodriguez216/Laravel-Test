<?php

namespace App;

class Helper
{
  public static function date( $fulldate, $format = 'd-m-Y' )
  {
    return date( $format, strtotime( $fulldate ) );
  }  
}
