<template>

   <div id="NetsEasyPay" :class="btnStyleOldCeres" v-if="DefaultPlaceOrderButton == 'hide'">
        <button id="NetsEasyPayBtn" @click="NetsEasyCheckout" :class="'btn btn-block  btn-appearance '+ btnStyleNewCeres">
            <i id="NetsEasyPayBtnArrow" class="fa fa-arrow-right" aria-hidden="true"></i>
           {{ $translate("Ceres::Template.checkoutBuyNow")  }} 
        </button>
        <div id="netseasy-container-div"></div>
    </div>
  
</template>

<script>

import ApiService from "Ceres/app/services/ApiService";
import { navigateTo } from "Ceres/app/services/UrlService";

export default {
  props: ['isShopbuilder','MaxValue','checkoutKey','MethodId','Icons','MethodName'],

  data() {
    return {
      BasketOverviewRightSide : [
           '.widget-inner.col-xl-5.widget-prop-xl-auto.col-lg-5.widget-prop-lg-auto.col-md-5.widget-prop-md-auto.col-sm-12.widget-prop-sm-3-1.col-12.widget-prop-3-1', //old Ceres,
           '.checkout-rightside' //new ceres
      ],

      PlaceOrderButtons:[
         '.widget-place-order', // old ceres
         '.checkout-rightside .sticky-element .btn-primary' // new ceres
      ],

      isNetsEasyCheckoutVisiable : false,
      btnStyle : 'btn btn-block btn-success btn-appearance',
      btnStyleOldCeres : null,
      btnStyleNewCeres : null,

    }
  },
  computed:{
    ...mapState({
            methodOfPaymentId: state => state.checkout.payment.methodOfPaymentId,
            CheckoutbillingAddress : state => state.address.billingAddress,
            CheckoutDeliveryAddress : state => state.address.deliveryAddress,
            checkoutValidation: state => state.checkout.validation,
            contactWish: state => state.checkout.contactWish,
            customerSign: state => state.checkout.customerSign,
            isBasketLoading: state => state.basket.isBasketLoading,
            basketAmount :state => state.basket.data.basketAmount,
            basketItemQuantity: state => state.basket.data.itemQuantity,
            isBasketInitiallyLoaded: state => state.basket.isBasketInitiallyLoaded,
            shippingPrivacyHintAccepted: state => state.checkout.shippingPrivacyHintAccepted,
            newsletterSubscription: state => state.checkout.newsletterSubscription,
            shippingProfileId: state => state.basket.data.shippingProfileId,
        }),
        DefaultPlaceOrderButton(){
          return  this.methodOfPaymentId == this.MethodId ? 'hide' : 'show'
        },

  },
  watch: {
    DefaultPlaceOrderButton:function(value){
          
          this.isNetsEasyCheckoutVisiable = false;

          this.ShowBasketDetails()

      },
      CheckoutbillingAddress:function(value){

        if(this.isNetsEasyCheckoutVisiable){

            this.ShowBasketDetails()
            this.HideNetsEasyChekout()

        }
        this.alterNetsDetailsContainer()
      },
      CheckoutDeliveryAddress:function(value){

        if(this.isNCheckoutVisiable){

            this.ShowBasketDetails()
            this.HideNChekout()

        }

        this.alterNetsDetailsContainer()
      },
      basketAmount : function(value){

        if(this.isNetsEasyCheckoutVisiable){

            this.ShowBasketDetails()
            this.HideNetsEasyChekout()
            
        }
        
      },
      shippingProfileId : function(value){
      if(this.isNetsEasyCheckoutVisiable){
          
          this.ShowBasketDetails()
          this.HideNetsEasyChekout()
        
      }
      }
  },
  mounted: function () {
    this.$nextTick(function () {
       
       let Classes = null

       for (let i = 0; i < this.PlaceOrderButtons.length; i++) {
            let selector = this.PlaceOrderButtons[i]
            if($(selector).length > 0 ){
                  Classes = $(selector).attr('class');
                  break;
             }
       }


       let selector = '.widget-place-order'

       if($(selector).length > 0 ){
          this.btnStyleOldCeres = Classes
       }else{
          this.btnStyleNewCeres = Classes
       }
       



       if(this.DefaultPlaceOrderButton == 'hide'){
          
           this.PlaceOrderButtons.forEach(selector => {
                  $(selector).hide()
            });

       }

      
       if(this.Icons.length > 0){
          let element = $(`li.method-list-item[data-id='${this.MethodId}']`); 
          let content =  element.find('div.content');
          let small = element.find('small');
          let details = small[0]
          let description = '<div>' + small[1].outerHTML + '</div>';
          let newContent = `<span style="margin-right:15px" >${this.MethodName}</span>`;
          
          this.Icons.forEach(icon => {
               var [path,width] = icon.split(',')
              newContent += `<img src="${path}" style="margin-right:10px"  width= "${width}" class="logo-images">`
          });

          content.html(newContent).append(details).append(description);

       }
       


      

       
       
    })
  },
  methods: {

      async NetsEasyCheckout(){
          
            
          if (this.validateCheckout()){

                  this.showSpin()
                  
                  const url = "/rest/io/checkout/payment";

                  let payment = ApiService.post(url);

                  payment.done(response =>{
                
                      const paymentType = response.type || "errorCode";
                      const paymentValue = response.value;
                      
                      if(paymentType == "continue" ){
                              
                              let paymentId = paymentValue
                              
                              if(!this.checkoutKey){
                                  window.CeresNotification.error('Wrong Credentials');
                                  this.hideSpin()
                                  return false
                              }
                              const checkoutOptions = {
                                      checkoutKey: this.checkoutKey, 
                                      paymentId: paymentId,
                                      containerId: "netseasy-container-div",
                              };

                              this.ShowNetsEasyChekout()

                              const checkout = new Dibs.Checkout(checkoutOptions);
                            
                            
                              window.scrollTo(0, 0);

                    
                              checkout.on('payment-completed',( response ) => {
                                  this.PlaceOrder()

                              })
                      }
                      
                      if(paymentType == "errorCode"){
                        window.CeresNotification.error(paymentValue);
                        this.hideSpin()
                      }

                      

                  }).fail(error =>{

                      this.hideSpin()

                  })


          }

      },
      validateCheckout(){
              let isValid = true;
            
              for (const index in this.checkoutValidation)
              {
                  if (this.checkoutValidation[index].validate)
                  {
                      this.checkoutValidation[index].validate();

                      if (this.checkoutValidation[index].showError)
                      {
                          isValid = !this.checkoutValidation[index].showError;
                      }
                  }
              }

              return isValid;
      },
      PlaceOrder(){
                const url = "/rest/io/order/additional_information";

                const params = {
                      orderContactWish: this.contactWish,
                      orderCustomerSign: this.customerSign,
                      shippingPrivacyHintAccepted: this.shippingPrivacyHintAccepted,
                      newsletterSubscriptions: this.activeNewsletterSubscriptions
                  };
                  const options = { supressNotifications: true };
                  
                  //call the execute payment event in plentymakets
                  ApiService.post(url, params, options)
                  .done(response =>{
                      console.log('response execute payment') 
                      console.log(response) 
                  })
                  .fail(error =>{
                      
                      window.CeresNotification.error(error);

                  })
                  .always(() =>{

                      navigateTo('/place-order/');

                  });


      },
      ShowNetsEasyChekout(){
          
          this.isNetsEasyCheckoutVisiable = true;

          $('#netseasy-container-div').html('')
          $('#NetsEasyPayBtn').hide() 
          
          this.hideSpin()

          this.BasketOverviewRightSide.forEach(selector => {
                                  
              let element = $(selector);

              if(element.length > 0 ){
                //hide all element inside the right side of the checkout page
                element.children().each(function (index) {
                    $(this).hide()
                });                  
                //show the netseasy checkout
                $("#NetsEasyPay").appendTo(selector);
              }

          });

      },
      HideNetsEasyChekout(){
          
          this.isNetsEasyCheckoutVisiable = false;

          $('#netseasy-container-div').html('')
          $('#NetsEasyPayBtn').show() 
          
          
          this.PlaceOrderButtons.forEach(selector => {
                    $(selector).hide()
          });

      },
      ShowBasketDetails(){

            this.BasketOverviewRightSide.forEach(selector => {
                    
                    let element = $(selector);

                    if(element.length > 0 ){
                        //show all element inside the right side of the checkout page
                          $(element).children().each(function (index) {
                                $(this).show()
                          });
                        
                        //show the netseasy pay button

            
                            this.PlaceOrderButtons.forEach(selector => {
                                  $(selector).toggle()
                            });
                            
                          $("#NetsEasyPay").appendTo(selector + ' .sticky-element');
                        
                    }
              });



      },
      showSpin(){
              $('#NetsEasyPayBtnArrow').removeClass('fa-arrow-right').addClass('fa-circle-o-notch fa-spin');
      },
      hideSpin(){
            $('#NetsEasyPayBtnArrow').removeClass('fa-circle-o-notch fa-spin').addClass('fa-arrow-right');
      }
  },
  
}
</script>

<style lang="scss" scoped>

</style>
