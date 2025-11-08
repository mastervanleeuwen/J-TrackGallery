jQuery(function() {
    document.formvalidator.setHandler('title',
        function (value) {
            regex=/^[^x]+$/;
            return regex.test(value);
        });
