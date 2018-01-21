/* ---------------------------------- HELPERS ---------------------------------- */
function toggle( element, visibility )
{
  if ( visibility )
    document.getElementById( element ).classList.remove( 'd-none' );
  else
    document.getElementById( element ).classList.add( 'd-none' );
}

function toggle_v( element, visibility )
{
if ( visibility )
{
  document.getElementById( element ).classList.remove( 'invisible' );
  
}
else
{
  // document.getElementById( element ).classList.remove( 'visible' );
  // document.getElementById( element ).classList.add( 'visible' );
  document.getElementById( element ).classList.add( 'invisible' );
}

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


var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = decodeURIComponent(window.location.search.substring(1)),
  sURLVariables = sPageURL.split('&'),
  sParameterName,
  i;
  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : sParameterName[1];
    }
  }
};
