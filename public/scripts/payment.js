const stripe = Stripe("pk_test_51LAVnEEIp55pXkWxvHI4y96okGqXS2imtg67CrwfWHjoPjsrhP4iaBQ6E8d8UhC7MKcYPJkJsp3ws2cGRBwJA2aR00Vq1OVL9P");

const elements = stripe.element();

const card =  elements.create("card");

card.mount("#card-element");
card.on("change", function (event){
    document.querySelector("button").disabled = event.empty;
    document.querySelector("#card-error").textContent = event.error
        ?event.error.message
        : "";
});


const form =document.getElementById("payment-form");

form.addEventListener("submit",function(event){
    event.preventDefault();
    //Complete payment when the submit button is clicked
    stripe 
    .confirmationCardPayment(clientSecret, {
        payment_method: {
            card: card
        }
    })
})
    .then(function(result) {
        if (result.error) {
           console.log(result.error.message);
        }else{
            window.location.href = "{{ url('purchase_payment_success', {'id':purchase.id})}}"
        }
    })