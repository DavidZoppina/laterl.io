"use strict";
function Payment(){
    var self= this;
    this.init= function(){
        self.action();
    };

    this.action = function(){
        $(document).on('change', '.input-payment-change', function(){
            var that = $(this);
            var url = that.data("url");
            if(that.is(':checked')){
                window.location.assign(url+"/2");
            }else{
                window.location.assign(url+"/1");                
            }
        });
    };
}

var Payment = new Payment();
$(function(){
    Payment.init();
});