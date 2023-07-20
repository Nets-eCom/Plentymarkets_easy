<template>

  <div id="NPay" :class="btnStyleOldCeres" v-if="DefaultPlaceOrderButton == 'hide'">
      <button id="NPayBtn" @click="NCheckout" :class="'btn btn-block  btn-appearance '+ btnStyleNewCeres">
          <i id="NPayBtnArrow" class="fa fa-arrow-right" aria-hidden="true"></i>
         {{ $translate("Ceres::Template.checkoutBuyNow")  }} 
      </button>
      <div id="N-container-div"></div>
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

    isNCheckoutVisiable : false,
    btnStyle : 'btn btn-block btn-success btn-appearance',
    btnStyleOldCeres : null,
    btnStyleNewCeres : null,
    SuccessPayId : null
   }
},
computed:{
  checkoutValidation:function(){
     return vueApp.$store.state.checkout.validation; 
   },
   methodOfPaymentId:function(){
     return vueApp.$store.state.checkout.payment.methodOfPaymentId;
   },
   contactWish:function(){
     return vueApp.$store.state.checkout.contactWish; 
   },
   customerSign:function(){
     return vueApp.$store.state.checkout.customerSign; 
   },
   shippingPrivacyHintAccepted:function(){
     return vueApp.$store.state.checkout.shippingPrivacyHintAccepted; 
   },
   newsletterSubscription:function(){
     return vueApp.$store.state.checkout.newsletterSubscription; 
   },
   isBasketLoading:function(){
     return vueApp.$store.state.basket.isBasketLoading; 
   },
   basketItemQuantity:function(){
     return vueApp.$store.state.basket.data.itemQuantity; 
   },
   basketAmount:function(){
     return vueApp.$store.state.basket.data.basketAmount; 
   },
   isBasketInitiallyLoaded:function(){
     return vueApp.$store.state.basket.isBasketInitiallyLoaded; 
   },
   CheckoutbillingAddress:function(){
     return vueApp.$store.state.address.billingAddress; 
   },
   CheckoutDeliveryAddress:function(){
     return vueApp.$store.state.address.deliveryAddress; 
   },
   shippingProfileId:function(){
    
    return vueApp.$store.state.basket.data.shippingProfileId; 
   },
   DefaultPlaceOrderButton() {

         return this.methodOfPaymentId == this.MethodId ? 'hide' : 'show'
   }
   
},
watch: {
  DefaultPlaceOrderButton:function(value){
   
          this.isNCheckoutVisiable = false;

          this.ShowBasketDetails()

  },
  CheckoutbillingAddress:function(value){

    if(this.isNCheckoutVisiable){
    
        this.ShowBasketDetails()
        this.HideNChekout()

   
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

        if(this.isNCheckoutVisiable){
            this.ShowBasketDetails()
            this.HideNChekout()
           
        }
        
  },
  shippingProfileId : function(value){
      if(this.isNCheckoutVisiable){
          
          this.ShowBasketDetails()
          this.HideNChekout()
        
      }
  }
      
},
mounted: function () {

  const urlParams = new URLSearchParams(window.location.search);
  this.SuccessPayId = urlParams.get('paymentId')

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

    
     this.alterNetsDetailsContainer()

     if(this.SuccessPayId != null){
          this.IntializeNetsPayment(this.SuccessPayId)
     }
    

     
     
  })
},
methods: {
  alterNetsDetailsContainer(){
   
    if(this.Icons.length > 0 ){
        let element = $(`li.method-list-item[data-id='${this.MethodId}']`); 

        if(element.length != 0){
          let content =  element.find('div.content');
          let small = element.find('small');
          let details = small[0]
          let description = small[1] ? '<div>' + small[1].outerHTML + '</div>' : '';
          
          let newContent = `<div class="content new-content"></div>`
          let name = `<span style="margin-right:15px" >${this.MethodName}</span>`;
          let icons = '';
          this.Icons.forEach(icon => {
              var [path,width] = icon.split(',')
              icons += `<img src="${path}" style="margin-right:10px"  width= "${width}" class="logo-images">`
          });

          if($('div.new-content').length == 0){
            content.after(newContent);
          }
          

          $('div.new-content').html('').append(name).append(icons).append(details).append(description);
          
          content.hide()
          $('div.new-content').show()

        }
        if(element.length == 0){
          $('div.new-content').remove()
          $('div.content').show() 

        }


     }
  },
  async NCheckout(){
  
    
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
                          this.IntializeNetsPayment(paymentId)

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
  getFormattedLanguage(){
     const storeLanguage = window.App.defaultLanguage;
     const PredefinedLanguage = {
        'en' : 'en-GB',
        'de' : 'de-DE',
        'bg' : 'en-GB',
        'cn' : 'en-GB',
        'cz' : 'en-GB',
        'da' : 'da-DK',
        'es' : 'es-ES',
        'fr' : 'fr-FR',
        'it' : 'it-IT',
        'nl' : 'nl-NL',
        'nn' : 'en-GB',
        'pl' : 'pl-PL',
        'pt' : 'en-GB',
        'ro' : 'en-GB',
        'ru' : 'en-GB',
        'se' : 'sv-SE',
        'sk' : 'sk-SK',
        'tr' : 'en-GB',
        'vn' : 'en-GB', 

     }

    
     return PredefinedLanguage[storeLanguage] ? PredefinedLanguage[storeLanguage] 
                                              : PredefinedLanguage['en'] ;

  },

  IntializeNetsPayment(paymentId){
    
    const netsLanguage = this.getFormattedLanguage()
    console.log(netsLanguage);
    const checkoutOptions = {
                                  checkoutKey: this.checkoutKey, 
                                  paymentId: paymentId,
                                  containerId: "N-container-div",
                                  language: netsLanguage
                          };
    
     this.ShowNChekout()

     let checkout = new Dibs.Checkout(checkoutOptions);
              
      window.scrollTo(0, 0);
                
      checkout.on('payment-completed',( response ) => {
            this.PlaceOrder()
      })
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
                  //console.log('response execute payment') 
                  //console.log(response) 
              })
              .fail(error =>{
                  
                   window.CeresNotification.error(error);

              })
              .always(() =>{

                  navigateTo('/place-order/');

              });


  },
  ShowNChekout(){

       this.isNCheckoutVisiable = true;
  
       $('#N-container-div').html('')
       $('#NPayBtn').hide()
       
       this.hideSpin()

      this.BasketOverviewRightSide.forEach(selector => {
                               
          let element = $(selector);

          if(element.length > 0 ){
            //hide all element inside the right side of the checkout page
            element.children().each(function (index) {
                $(this).hide()
            });                  
            //show the N checkout
            $("#NPay").appendTo(selector);
          }

      });

  },
  HideNChekout(){

      if(this.SuccessPayId)
            return true;

      this.isNCheckoutVisiable = false;
      
      $('#N-container-div').html('')
      
      this.PlaceOrderButtons.forEach(selector => {
                $(selector).hide()
      });

      $('#NPay').show().children().show();
      

 },
  ShowBasketDetails(){
        if(this.SuccessPayId)
            return true;

        this.BasketOverviewRightSide.forEach(selector => {
                 
                 let element = $(selector);

                 if(element.length > 0 ){
                    //show all element inside the right side of the checkout page
                      $(element).children().each(function (index) {
                            $(this).show()
                      });
                    
                    //show the N pay button

        
                        this.PlaceOrderButtons.forEach(selector => {
                              $(selector).toggle()
                        });
                        
                      $("#NPay").appendTo(selector + ' .sticky-element');
                    
                 }
          });



  },
  showSpin(){
          $('#NPayBtnArrow').removeClass('fa-arrow-right').addClass('fa-circle-o-notch fa-spin');
  },
  hideSpin(){
         $('#NPayBtnArrow').removeClass('fa-circle-o-notch fa-spin').addClass('fa-arrow-right');
  }
},

}
</script>

<style lang="scss" scoped>

</style>
