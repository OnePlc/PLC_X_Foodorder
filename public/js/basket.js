function loadBasket() {
    $.post('/foodorder/api/cart', {}, function (retHTML) {
        $('.ajax-cart').html(retHTML);
    });
}

$(function() {
   $('.cart-additem').on('click', function() {
       var iItemID = $(this).attr('plc-item-id');

       $.post('/foodorder/api/select', {item_id:iItemID}, function (retHTML) {
           Swal.fire({
               html: retHTML,
               showCancelButton: true
           }).then((result) => {
               if(result.isConfirmed) {
                   var iAmount = parseInt($('#plc-cart-amount').val());

                   $.post('/foodorder/api/cart', {amount:iAmount,item_id:iItemID}, function (retHTML) {
                       $('.ajax-cart').html(retHTML);
                   });
               }

           });
       });
   });

   $(document).on('click', '.plc-cart-rm', function() {
       console.log('rm cart pos');
       var iItemID = $(this).attr('plc-item-id');
       var oParent = $(this).parent('div').parent('div').parent('li');
       $.post('/foodorder/api/rmcartpos', {item_id:iItemID}, function(retVal) {
           oParent.remove();
       });

       return false;
   });
});

loadBasket();