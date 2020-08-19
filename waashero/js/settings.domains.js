/**
 * Create and show a dismissible admin notice
 */
function myAdminNotice(msg,level) {
 
    /* create notice div */
    //level e.g notice, warning, error
     
    var div = document.createElement( 'div' );
    div.classList.add( 'notice', 'notice-'+level, 'is-dismissible' );
     
    /* create paragraph element to hold message */
     
    var p = document.createElement( 'p' );
     
    /* Add message text */
     
    p.appendChild( document.createTextNode( msg ) );
 
    // Optionally add a link here
 
    /* Add the whole message to notice div */
 
    div.appendChild( p );
 
    /* Create Dismiss icon */
     
    var b = document.createElement( 'button' );
    b.setAttribute( 'type', 'button' );
    b.classList.add( 'notice-dismiss' );
 
    /* Add screen reader text to Dismiss icon */
 
    var bSpan = document.createElement( 'span' );
    bSpan.classList.add( 'screen-reader-text' );
    bSpan.appendChild( document.createTextNode( 'Dismiss this notice' ) );
    b.appendChild( bSpan );
 
    /* Add Dismiss icon to notice */
 
    div.appendChild( b );
 
    /* Insert notice after the first h1 */
     
    var h1 = document.getElementsByTagName( 'h1' )[0];
    h1.parentNode.insertBefore( div, h1.nextSibling);
 
 
    /* Make the notice dismissable when the Dismiss icon is clicked */
 
    b.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });
 
     
}


jQuery( document ).ready(function ($) {

    

    $( document ).on( "click", "#domain-alias-submit", function () {

        var submitBtn = $(this);
        submitBtn.hide();
        $("<img class='waashero-loader' src='/wp-admin/images/loading.gif'>").insertAfter(submitBtn);
    
      
        var data = {
            'action': 'waashero_add_domain_alias',
            'domain': $("#domain-alias-hostname").val()
        };

        jQuery.post( ajaxurl, data, function ( response ) {
           
            if ( response.success ) {
                location.reload();
            } else {
            
                myAdminNotice( response.error, "error" );
                $('.waashero-loader').remove();
                submitBtn.show();
            }

        });
    });

  
});