// Get API Key
let STRIPE_PUBLISHABLE_KEY = document.currentScript.getAttribute('STRIPE_PUBLISHABLE_KEY');

// Create an instance of the Stripe object and set your publishable API key
const stripe = Stripe(STRIPE_PUBLISHABLE_KEY);

// Select subscription form element
const subscrFrm = document.querySelector("#subscrFrm");

// Attach an event handler to subscription form
subscrFrm.addEventListener("submit", handleSubscrSubmit);

let elements = stripe.elements();
var style = {
    base: {
        lineHeight: "30px",
        fontSize: "16px",
        border: "1px solid #ced4da",
    }
};
let cardElement = elements.create('card', { style: style });
cardElement.mount('#card-element');

cardElement.on('change', function (event) {
    displayError(event);
});

function displayError(event) {
    if (event.error) {
        showMessage(event.error.message);
    }
}

async function handleSubscrSubmit(e) {
    e.preventDefault();
    setLoading(true);
	
    let subscr_plan_id = document.getElementById("subscr_plan").value;
    let customer_name = document.getElementById("name").value;
    let customer_email = document.getElementById("email").value;
	
    // Post the subscription info to the server-side script
    fetch("payment_init.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ request_type:'create_customer_subscription', subscr_plan_id: subscr_plan_id, name: customer_name, email: customer_email }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.subscriptionId && data.clientSecret) {
            paymentProcess(data.subscriptionId, data.clientSecret, data.customerId);
        } else {
            showMessage(data.error);
        }
		
        setLoading(false);
    })
    .catch(console.error);
}

function paymentProcess(subscriptionId, clientSecret, customerId){
    setProcessing(true);
	
    let subscr_plan_id = document.getElementById("subscr_plan").value;
    let customer_name = document.getElementById("name").value;
	
    // Create payment method and confirm payment intent.
    stripe.confirmCardPayment(clientSecret, {
        payment_method: {
            card: cardElement,
            billing_details: {
                name: customer_name,
            },
        }
    }).then((result) => {
        if(result.error) {
            showMessage(result.error.message);
			
            setProcessing(false);
            setLoading(false);
        } else {
            // Successful subscription payment
            //console.log(result);
            // Post the transaction info to the server-side script and redirect to the payment status page
            fetch("payment_init.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ request_type:'payment_insert', subscription_id: subscriptionId, customer_id: customerId, subscr_plan_id: subscr_plan_id,payment_intent: result.paymentIntent }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.payment_id) {
                    window.location.href = 'payment-status.php?sid='+data.payment_id;
                } else {
                    showMessage(data.error);
					
                    setProcessing(false);
                    setLoading(false);
                }
            })
            .catch(console.error);
        }
    });
}

// Display message
function showMessage(messageText) {
    const messageContainer = document.querySelector("#paymentResponse");
	
    messageContainer.classList.remove("hidden");
    messageContainer.textContent = messageText;
	
    setTimeout(function () {
        messageContainer.classList.add("hidden");
        messageText.textContent = "";
    }, 5000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
    if (isLoading) {
        // Disable the button and show a spinner
        document.querySelector("#submitBtn").disabled = true;
        document.querySelector("#spinner").classList.remove("hidden");
        document.querySelector("#buttonText").classList.add("hidden");
    } else {
        // Enable the button and hide spinner
        document.querySelector("#submitBtn").disabled = false;
        document.querySelector("#spinner").classList.add("hidden");
        document.querySelector("#buttonText").classList.remove("hidden");
    }
}

// Show a spinner on payment form processing
function setProcessing(isProcessing) {
    if (isProcessing) {
        subscrFrm.classList.add("hidden");
        document.querySelector("#frmProcess").classList.remove("hidden");
    } else {
        subscrFrm.classList.remove("hidden");
        document.querySelector("#frmProcess").classList.add("hidden");
    }
}