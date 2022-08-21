<?php include 'header.php'; ?>
<div class="bg">

</div>

<div class="wrapper">

    <!--<div class="header">-->

    <!--    <div class="lable"><?php _e("Make a payment") ?></div>-->
    <!--    <div class="title"><?php _e(sprintf(__("%s Plan"), $result->name)) ?></div>-->
    <!--    <div class="desc"><?php _e($result->description) ?></div>-->

    <!--</div>-->

    <div class="payment-info container" style="padding: 0px;">
        <div class="row">
            <div class="col-8" style="padding: 33px;">
                <form id="msform8" action="#">
                    <h4><?php _e(sprintf(__("%s Plan"), $result->name)) ?></h4>
                    <p><?php _e($result->description) ?></p>
                    
                    <div class="form-group">
                        <label style="font-weight: bold;">Card Holders Name</label>
                        <input type="text" class="form-control" placeholder="Card Holders Name" name="name">
                    </div>
    
                    <label style="font-weight: bold;">Card Information</label>
                    <div id="card-element">
                        <!-- Elements will create input elements here -->
                    </div>
        
                    <!-- We'll put the error messages in this element -->
                    <div id="card-errors" role="alert" style="color: red;"></div>
                    
                    <div>
                        Payment information is the data that is required for customers to make a purchase online.
                    </div>
        
                    <button id="submit" class="btn btn-primary btn-lg btn-block mt-3">Pay $<?php _e($result->amount) ?> now</button>
                </form>
                        <p class="mb-0">Powered by <strong>stripe</strong></p>
            </div>
            <div class="col-4" style="background-color: #f5f9fc; padding-top: 15px; padding-bottom: 110px; text-align: left;">
                <h4>Information</h4>
                <br>
                <div>
                    <div style="font-weight: bold;">Package Name</div>
                    <div style="font-size: 20px;"><?php _e(sprintf(__("%s Plan"), $result->name)) ?></div>
                </div>
                <br>
                <div>
                    <div style="font-weight: bold;">Package Price</div>
                    <h4>$<?php _e($result->amount) ?></h4>
                </div>
                <br>
                <div>
                    <div style="font-weight: bold;">Package Trial</div>
                    <div style="font-size: 20px;"><?php echo $result->trial_day; ?> days free trial</div>
                </div>
                <br>
            </div>
        </div>
    </div>

    <?php _e($counpon_view, false) ?>

    <div class="payment-method">

    </div>

</div>
<script src="https://js.stripe.com/v3/"></script>

<script>
    var stripe = Stripe('<?php echo "pk_test_51LBb1vL6FTQDDSi6EsiUx8iZjLlXeJewYsNsyskRkwHqwPrapteZe8MooKbPsK0vrZGS6zuGY9LqSoKuhwcyfKPu00BFLPoBEl"; ?>');
    var elements = stripe.elements();
    
    var successUrl = "<?php echo get_url('payment/success'); ?>";
    var unsuccessUrl = "<?php echo get_url('payment/unsuccess'); ?>";

    var style = {
        base: {
            fontWeight: 400,
            fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
            fontSize: '16px',
            lineHeight: '1.4',
            color: '#555',
            backgroundColor: '#fff',
            '::placeholder': {
                color: '#888',
            },
        },
        invalid: {
            color: '#eb1c26',
        }
    };

    var card = elements.create("card", {
        style: style
    });
    card.mount("#card-element");

    card.on('change', ({
        error
    }) => {
        let displayError = document.getElementById('card-errors');
        if (error) {
            displayError.textContent = error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Get payment form element
    var form = document.getElementById('msform8');

    // Create a token when the form is submitted.
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createToken();
    });

    // Create single-use token to charge the user
    function createToken() {
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                resultContainer.innerHTML = '<p>' + result.error.message + '</p>';
            } else {
                // Send the token to your server
                stripeTokenHandler(result.token);
                console.log(result.token);
            }
        });
    }

    // Callback to handle the response from stripe



    function stripeTokenHandler(token1) {

        // Submit the form
        var formData = new FormData(document.getElementById('msform8'));
        
        // Insert the token ID into the form so it gets submitted to the server
        formData.append('stripeToken', token1.id);
        
        formData.append('token', token);
        let packageId = "<?php echo $result->ids ?>";
        formData.append('ids', packageId);
        var ajaxurl = "/wimax/payment_submit";
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            //async: false,
            beforeSend: function() {
                $(".spinner-border").show();
            },
            success: function(response) {
                if (response == 1) {
                    // $('#respon').show();

                    // setTimeout(function() {
                    //     location.reload();
                    // }, 5000);
                    location.href = successUrl;

                } else {
                    location.href = unsuccessUrl;

                }
            },
            complete: function() {
                $(".spinner-border").hide();
            },

        });



    }
</script>

<?php include 'footer.php'; ?>