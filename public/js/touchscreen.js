function loadOrders() {
    $.post('/foodorder/api/orderlist', {}, function(retHTML) {
        $('.currentOrdersFrame').html(retHTML);


        // Code goes here
        var i = 20;

        var counterBack = setInterval(function () {
            i--;
            if (i > 0) {
                $('.progress-bar').css('width', i + '%');
            } else {
                loadOrders();
                clearInterval(counterBack);
            }

        }, 1000);
    });
}
$(function() {
    loadOrders();
});