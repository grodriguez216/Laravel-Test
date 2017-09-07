/* ---------------------------------- HELPERS ---------------------------------- */
function toggle( element, visibility )
{
  if ( visibility )
  document.getElementById( element ).classList.remove( 'd-none' );
  else
  document.getElementById( element ).classList.add( 'd-none' );
}

function redirect( url ) { window.location.href=url }
function log( object ) { console.log( object ); }

/* JQuery UI */
function modal(modal) { $(modal).modal('show'); }

function onRadioLabelClick( radio , callback, args)
{
  document.getElementById( radio ).checked = true;
  window[callback]( args );
}

function valueOf( id )
{
  var element = document.getElementById( id );
  return element.value ? element.value : element.innerHTML;
}

function get( id )
{
  return document.getElementById( id );
}

String.prototype.replaceAll = function(search, replacement) {
  var target = this;
  return target.replace(new RegExp(search, 'g'), replacement);
};


$(document).on('keyup', '.amount-control', function()
{
  var x = $(this).val();
  $(this).val(x.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
});

function nicecify( value )
{
  return value.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
