$(function() {
    console.log('Foodlist init');

    $('.food-list-item').on('click', function() {
        console.log('click food - open footer');
        $('.food-list-item .card-footer').addClass('d-none');
        $(this).find('.card-footer').removeClass('d-none');
        $(this).find('.fa-plus').removeClass('fa-plus').addClass('fa-times');

        return false;
    });

    // When the user scrolls the page, execute myFunction
    window.onscroll = function() {myFunction()};

    // Get the header
    var header = document.getElementById("plc-category-filter");
    var cart = document.getElementById("plc-shop-cart");
    var logo = document.getElementById("plc-category-logo");
    var iWidth = header.offsetWidth;
    var iCartWidth = cart.offsetWidth;

    // Get the offset position of the navbar
    var sticky = header.offsetTop;

    // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
    function myFunction() {
        if (window.pageYOffset > sticky) {
            header.classList.remove("bg-light");
            header.classList.add("bg-dark");
            logo.classList.remove("d-none");
            header.classList.add("sticky");
            cart.classList.add("stickycart");
            //logo.classList.add("stickyheader");
            $('#indexCategoryList').css({width:iWidth});
            $('#plc-shop-cart').css({width:iCartWidth});

        } else {
            header.classList.remove("bg-dark");
            header.classList.add("bg-light");
            logo.classList.add("d-none");
            header.classList.remove("sticky");
            cart.classList.remove("stickycart");
            //logo.classList.remove("stickyheader");
        }
    }
});