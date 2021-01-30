$(function () {
    $('.plc-toggle-shop').on('change', function() {
        $.post('/foodorder/api/toggleshop', {}, function() {

        });

        return false;
    });
});