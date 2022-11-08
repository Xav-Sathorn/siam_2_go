

export class Payments {
    elements
    stripe = Stripe(
        "pk_test_51LAVnEEIp55pXkWxvHI4y96okGqXS2imtg67CrwfWHjoPjsrhP4iaBQ6E8d8UhC7MKcYPJkJsp3ws2cGRBwJA2aR00Vq1OVL9P"
      );
    clientSecret
    paymentElement

    constructor (){
        this.initialize().then(()=>{
            document
            .querySelector("#payment-form")
            .addEventListener("submit", this.handleSubmit);
            let clientSecret = this.clientSecret
            this.elements = this.stripe.elements({ clientSecret });
            const style = {
                base: {
                  color: "#32325d",
                  fontFamily: 'Arial, sans-serif',
                  fontSmoothing: "antialiased",
                  fontSize: "16px",
                  "::placeholder": {
                    color: "#32325d"
                  }
                },
                invalid: {
                  fontFamily: 'Arial, sans-serif',
                  color: "#fa755a",
                  iconColor: "#fa755a"
                }
              };
            
            this.paymentElement = this.elements.create("card", {style: style});
            this.paymentElement.mount("#payment-element");
            console.log('stripe payment loaded');
        })
    }

    initialize = async () => {
       this.clientSecret = await this.getClientSecret()
    }

    getClientSecret = async() => {
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
    // Fetches a payment intent and captures the client secret


    handleSubmit = async(e) => {
        e.preventDefault();
        // setLoading(true);
        console.log(this.paymentElement);
        let cardElement = this.paymentElement
        try {
            const comfirmPayment = await this.stripe.confirmCardPayment(this.clientSecret, {
                payment_method: {
                    card: cardElement
                    
                }
            })

        } catch (error) {
            console.log('error');            
            console.log(error);            
        }

        // setLoading(false);
        window.location.href ='/purchase/validation/160'
      }
}
