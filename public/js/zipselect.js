$(function() {
    console.log('Zipselect init');

    $('#searchLocation').select2({
        placeholder: "Ihr Wohnort",
        ajax: {
            type: 'POST',
            url: '/foodorder/api/zip?authkey=ONVC-OPMZ-5WXK-JJLP-CL6G-6U5Q-ML0W-VKM9&authtoken=123456',
            dataType: 'json'
        }
    });

    $('#searchLocation').on('select2:select', function(e) {
        var iZip = e.params.data.id;
        window.location = '/somerandom-seo-'+iZip;
    });
});