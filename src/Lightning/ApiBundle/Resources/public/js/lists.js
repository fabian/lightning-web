$(function () {

    $.ajax({
        url: '',
        dataType: 'json',
        headers: { 
            Accept: 'application/json; charset=utf-8'
        },
        success: function (data) {

            var ul = $(document.createElement('ul'));

            $(data.lists).each(function (i, list) {
                var li = $(document.createElement('li'));
                li.text(list.title);
                ul.append(li);
            });

            $('p').append(ul);
        }
    });

});
