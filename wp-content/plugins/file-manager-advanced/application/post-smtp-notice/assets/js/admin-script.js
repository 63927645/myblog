// Returns: 'agree' | 'skip' | 'cancel'
function showConsentModal() {
    return new Promise( function( resolve ) {
        var overlay = document.getElementById( 'fma-consent-overlay' );
        if ( ! overlay ) {
            resolve( 'cancel' );
            return;
        }
        overlay.classList.add( 'fma-consent-visible' );

        var agreeBtn = document.getElementById( 'fma-consent-agree' );
        var skipBtn  = document.getElementById( 'fma-consent-skip' );
        var closeBtn = document.getElementById( 'fma-consent-close' );

        function cleanup() {
            overlay.classList.remove( 'fma-consent-visible' );
            agreeBtn.removeEventListener( 'click', onAgree );
            skipBtn.removeEventListener( 'click', onSkip );
            closeBtn.removeEventListener( 'click', onClose );
        }
        function onAgree()  { cleanup(); resolve( 'agree' ); }
        function onSkip()   { cleanup(); resolve( 'skip' ); }
        function onClose()  { cleanup(); resolve( 'cancel' ); }

        agreeBtn.addEventListener( 'click', onAgree );
        skipBtn.addEventListener( 'click', onSkip );
        closeBtn.addEventListener( 'click', onClose );
    } );
}

( async function() {
    const buttons = document.querySelectorAll( '.post-smtp-notice-install' );

    buttons.forEach( function( button ) {
        const action = button.getAttribute( 'data-action' );

        button.addEventListener( 'click', async function( e ) {
            e.preventDefault();

            var consentGiven = true;
            if ( ! recommendPostSMTP.hasConsent ) {
                var choice = await showConsentModal();
                if ( choice === 'cancel' ) {
                    return;
                }
                consentGiven = ( choice === 'agree' );
            }

            button.setAttribute( 'disabled', 'disabled' );
            
            if( action === 'install-plugin_post-smtp' ) {
                button.innerHTML = 'Installing...';

                try {
                    await sendPostSMTPRequest( 'installed', consentGiven );
                    
                    const response = await wp.updates.installPlugin( {
                        slug: 'post-smtp'
                    } );
                    
                    if( response.activateUrl !== undefined && response.install === 'plugin' ) {
                        await activatePostSMTP( button, consentGiven );
                    }
                } catch ( error ) {
                    console.error( error );
                    button.innerHTML = 'Error!';
                }
            }
            if( action === 'activate-plugin_post-smtp' ) {
                await sendPostSMTPRequest( 'activated', consentGiven );
                await activatePostSMTP( button, consentGiven );
            }
        } );
    } );
} )();

const sendPostSMTPRequest = async function( status, consentGiven ) {
    try {
        const params = {
            action: 'post_smtp_request',
            status: status,
            nonce: recommendPostSMTP.ajaxNonce
        };

        if ( consentGiven ) {
            params.grant_consent = '1';
        }

        const formData = new URLSearchParams( params );
        
        const response = await fetch( recommendPostSMTP.ajaxURL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });
        
        if( !response.ok ) {
            throw new Error( 'HTTP ' + response.status );
        }
        
        const data = await response.json();
        
        if ( data.success && consentGiven ) {
            recommendPostSMTP.hasConsent = true;
        }

        return data.success || false;
    } catch ( error ) {
        console.error( 'Post SMTP request error:', error );
    }
}

const activatePostSMTP = async function( button, consentGiven ) {
    button.innerHTML = 'Activating...';

    const activateResponse = await wp.updates.activatePlugin( {
        slug: 'post-smtp',
        name: 'Post SMTP',
        plugin: 'post-smtp/postman-smtp.php'
    } );

    if( activateResponse ) {
        await sendPostSMTPRequest( 'activated', consentGiven );
        
        button.innerHTML = 'Activated!';
        setTimeout(() => {
            window.location.href = recommendPostSMTP.postSMTPURL;
        }, 1000);
    }
    else {
        button.innerHTML = 'Error!';
    }
}

// Notice dismiss functionality removed since admin notice is disabled