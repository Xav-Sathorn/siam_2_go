  document.addEventListener('DOMContentLoaded', async () => {
  // This is your test publishable API key.
  const stripe = Stripe(
    "pk_test_51LAVnEEIp55pXkWxvHI4y96okGqXS2imtg67CrwfWHjoPjsrhP4iaBQ6E8d8UhC7MKcYPJkJsp3ws2cGRBwJA2aR00Vq1OVL9P"
  );
  

  async function getClientSecret() {
    // On récupère la clé secrète passé par le controller à TWIG, comme présenté dans la vidéo
    const { pathname } = window.location;
    if (pathname.endsWith("/")) {
      pathname = pathname.slice(0, pathname.length - 1);
    }
    const paths = pathname.split("/");
    const id = paths[paths.length - 1];     
    const apiUrl = "https://127.0.0.1:8000/purchase/pay/stripe/"+id;
    const  response = await fetch(apiUrl,{method: "GET",headers: { "Content-Type": "application/json" }});
    const json = await response.json();  
    //console.log(json)
    const data = JSON.parse(json);
    //console.log(data);
    return data.clientSecret ;
  }

  const clientSecret = await getClientSecret();
  //console.log(clientSecret);






  // Fetches a payment intent and captures the client secret
  function initialize() {


    document
    .querySelector("#payment-form")
    .addEventListener("submit", handleSubmit);

    const elements = stripe.elements({ clientSecret });

    const paymentElement = elements.create("payment");
    paymentElement.mount("#payment-element");
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);

    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        // Make sure to change this to your payment completion page
        payment_method_options:  ['card'], 
        return_url:  "{{ url('cart_show') }}",
      },
    });

    // This point will only be reached if there is an immediate error when
    // confirming the payment. Otherwise, your customer will be redirected to
    // your `return_url`. For some payment methods like iDEAL, your customer will
    // be redirected to an intermediate site first to authorize the payment, then
    // redirected to the `return_url`.
    if (error.type === "card_error" || error.type === "validation_error") {
      showMessage(error.message);
      console.log(error.message);
    } else {
      showMessage("An unexpected error occured.");
    }

    setLoading(false);
  }

  // Fetches the payment intent status after payment submission
  async function checkStatus() {
    const clientSecret = new URLSearchParams(window.location.search).get(
      "payment_intent_client_secret"
    );

    if (!clientSecret) {
      return;
    }

    const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

    switch (paymentIntent.status) {
      case "succeeded":
        showMessage("Payment succeeded!");
        break;
      case "processing":
        showMessage("Your payment is processing.");
        break;
      case "requires_payment_method":
        showMessage("Your payment was not successful, please try again.");
        break;
      default:
        showMessage("Something went wrong.");
        break;
    }
  }

  // ------- UI helpers -------

  function showMessage(messageText) {
    const messageContainer = document.querySelector("#payment-message");

    messageContainer.classList.remove("hidden");
    messageContainer.textContent = messageText;

    setTimeout(function () {
      messageContainer.classList.add("hidden");
      messageText.textContent = "";
    }, 4000);
  }

  // Show a spinner on payment submission
  function setLoading(isLoading) {
    if (isLoading) {
      // Disable the button and show a spinner
      document.querySelector("#submit").disabled = true;
      document.querySelector("#spinner").classList.remove("hidden");
      document.querySelector("#button-text").classList.add("hidden");
    } else {
      document.querySelector("#submit").disabled = false;
      document.querySelector("#spinner").classList.add("hidden");
      document.querySelector("#button-text").classList.remove("hidden");
    }
  }

  initialize();
  checkStatus();

});