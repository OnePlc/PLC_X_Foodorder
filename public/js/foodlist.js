$(function() {
    console.log('Foodlist init');

    $('.food-list-item').on('click', function() {
        console.log('click food - open footer');
        $('.food-list-item .card-footer').addClass('d-none');
        $(this).find('.card-footer').removeClass('d-none');
        $(this).find('.fa-plus').removeClass('fa-plus').addClass('fa-times');

        return false;
    });
});