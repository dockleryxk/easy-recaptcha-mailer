/**
 * Created by Rich on 2/16/17.
 */

var recaptchaflg = false;

// be sure to include these in your recaptcha div
// data-callback="recaptchaCallback" data-expired-callback="recaptchaExpiredCallback"
function recaptchaCallback() { recaptchaflg = true; }
function recaptchaExpiredCallback() { recaptchaflg = false; }

function submitForm(e) {
    e.preventDefault();
    if(recaptchaflg) {
        var form = document.querySelector("form");
        var request = new XMLHttpRequest();

        request.open('POST', 'http://yoursite.com/easy-recaptcha-mailer.php', true);
        request.onload = function () {
            if (request.status >= 200 && request.status < 400) {
                // Success!
                console.log(JSON.stringify(request, null, 4));
                recaptchaflg = false;
            }
            else {
                // We reached our target server, but it returned an error
            }
            recaptchaflg = false;
            grecaptcha.reset();
        };

        request.onerror = function () {
            // There was a connection error of some sort
            recaptchaflg = false;
            grecaptcha.reset();
        };

        request.send(new FormData(form));
    }

    return false;
}