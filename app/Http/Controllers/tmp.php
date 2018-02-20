      /* Check if is paying different than the minimum */
      // $credit = ( $loan->mindue ) - ( $this->nicecify( $loan->intval ) );

      // /* Add the credit to the duemod. This will decrease the next regular due. */
      // if ( $credit > 1000 )
      // {
      //   // $loan->duemod += ($credit * $multiplier) * -1;
      //   $CREDITS += $credit;
      // }


      










// echo '<pre>';
// print_r( $DUE );
// echo 'Line: ', __LINE__;
    die;


    /* ---------------------------------- CALC FEES ---------------------------------- */

// /* Calc the fee */
// $u = Auth::user();

// if ( $loan->intrate )
// {
//   $u->aggregate += $this->nicecify( $loan->intval ) * $u->fee;
// }
// else
// {
//   /* Fixed 20% on loans at 0% interest rate */  
//   $u->aggregate += (($substract + $extra) * $multiplier) * 0.20;
// }
// $u->save();
    /* ______________________________ END: CALC FEES ______________________________ */


    /* Substract the extra from the next PC payment */
    if ( !$custompay )
    {
      $loan->duemod += ($extra * $multiplier) * -1;
    }

    /* Check if the discount is greater than a regdue, to avoid zero-payments */
    if ( $loan->duemod*-1 >= $loan->regdue ) { $loan->duemod -= $loan->regdue; }

    /* Register the Payments */
    for ($i=0; $i < $multiplier; $i++)
    { 
      /* Update the balance */
      $loan->balance -= $substract;
      $PaymentsController->store( $type, $loan->id, $substract, $loan->balance);

      /* Update the delays */
      $loan->delays -= $loan->delays > 0 ? 1 : 0;
    }

    /* Register the Optional extra deposit */
    if ( $extra )
    {
      for ($i=0; $i < $multiplier; $i++)
      { 
        /* Update the balance */
        $loan->balance -= $extra;
        $PaymentsController->store( 'AB', $loan->id, $extra, $loan->balance);
      }
    }

    /* Close the loan if balance is zero */
    if ( $loan->balance <= 0) $loan->balance = $loan->status = 0;

    /* Next Due Timestamp */
    $ndt = strtotime( $loan->next_due );

    /* Created at Timestamp */
    $cat = strtotime( $loan->created_at );

    switch ($loan->payplan)
    {
      case 'we':
      $original_day = date('l', $cat);
      $loan->next_due = date('Y-m-d', strtotime( "next $original_day", $ndt ));
      break;

      case 'bw':
      $eom = ( date('m', $ndt) == 2 ) ? 28 : 30;
      if ( date('d', $ndt) >= $eom )
      {
        $next_ft = strtotime( date('Y-m-15', $ndt) );
        $loan->next_due = date('Y-m-d', strtotime( '+1 month', $next_ft ) );
      }
      else if ( date('d') < 15)
      {
        $loan->next_due = date('Y-m-15', $ndt);
      }
      else
      {
        /* Between the 15 and the EOM */
        $loan->next_due = date('Y-m-d', $ndt);
      }
      break;

      case 'mo':
      $original_date = date('d', $cat);
      $loan->next_due = date("Y-m-$original_date", strtotime( "+1 month", $ndt ));
      break;