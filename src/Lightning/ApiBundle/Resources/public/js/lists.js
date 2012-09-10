var Lightning = Lightning || {};

$(function () {

    $.ajax({
        url: Lightning.URL_LISTS,
        dataType: 'json',
        headers: { 
            Accept: 'application/json; charset=utf-8'
        },
        success: function (data) {

            var ul = $(document.createElement('ul'));

            $(data.lists).each(function (i, list) {
                var li = $(document.createElement('li'));
                var a = $(document.createElement('a')).attr('href', '').text(list.title);
                li.append(a);
                ul.append(li);
            });

            $('#container').append(ul);
        }
    });

});
