<?php 
return 1;
$controller = new NotificationController();
$controller->test();

$today = date('Y-m-d');

DB::beginTransaction();

$toUpdate = Loan::where('status', 1)
->where('next_due', '<=', $today)
->where('payplan', 'bw')
->get();

foreach ($toUpdate as $loan )
{
 $day_lp = $this->calcDelays($loan);
 $this->addPendingOrders( $loan );
 if( $loan->payplan === 'we')
 {
   $loan->next_due = $this->getNextPeriod('we', $day_lp);
 }
 else
 {
 }

 if(  $loan->next_due == $today )
 {

  $loan->delays++;
  $loan->next_due = $this->getNextPeriod($loan->payplan);
  $loan->collect_date = $loan->next_due;

  $po = new PayOrder;
  $po->loan_id = $loan->id;
  $po->date = date('Y-m-d');
  $po->amount = $loan->mindue;
  $po->balance = $po->amount;
  $po->save();

  echo $loan->client_id, " | ", $loan->payplan, " | ",$loan->next_due, " | ", $loan->delays;
  $this->hr();

  $loan->save();
}
}

DB::rollback();
DB::commit();


/* ---------------------------------- TO UPDATE ---------------------------------- */

$ID = 409;

/* ______________________________ END: TO UPDATE ______________________________ */

$loan = Loan::find($ID);

$this->calcDelays($loan);
$this->addPendingOrders( $loan );




foreach ($loans_today as $loan)
{
 echo $loan , "<hr>";

 $NEXT_DUE = $loan->next_due;

 if ( $loan->delays > 0 )
 {
   $NEXT_DUE = $this->getNextPeriod( $loan->payplan, $loan->next_due, $loan->delays );
 }

 echo $loan;
 echo $this->br();
 echo $NEXT_DUE;
 echo $this->hr();

 if ( $NEXT_DUE === $today )
 {
   $po = new PayOrder;
   $po->loan_id = $loan->id;
   $po->date = $today;
   $po->amount = $loan->mindue;
   $po->balance = $po->amount;
   $po->save();

   /* Update the loan */
   $loan->delays++;
   $loan->save();
 }
}

$toUpdate = Loan::where('status', 1)
->where('intrate', 0)
->get();
echo '<pre>';
DB::commit();
$this->updateDaily();
$controller = new NotificationController();
$controller->test();