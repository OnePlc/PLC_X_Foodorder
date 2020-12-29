function loadBasket() {
    $.post('/foodorder/api/cart', {}, function (retHTML) {
        $('.ajax-cart').html(retHTML);
    });
}

$(function() {
   $('.cart-additem').on('click', function() {
       var iItemID = $(this).attr('plc-item-id');

       $.post('/foodorder/api/cart', {amount:1,item_id:iItemID}, function (retHTML) {
           $('.ajax-cart').html(retHTML);
       });
   });
});

loadBasket();