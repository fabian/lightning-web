var Lightning = Lightning || {};

$(function () {

    var url = Lightning.URL_ACCOUNT;

    var container = $('#container');
    container.html('<p>To access your lists you need to send an access request to your mobile phone.</p>'); 

    var message = $(document.createElement('p'));
    var link = $(document.createElement('a')).attr('href', url).text('Send Request');
    link.click(function (e) {

        var i = 0;
        setInterval(function () {
            i = (i + 1) % 4;
            var message = '<p>Sending access request';
            for (var j = 0; j < i; j++) {
                message += '.';
            }
            message += '</p>';
            container.html(message);
        }, 300);

        return false;
    });
    message.append(link);
    container.append(message);

    $('#user').html('<a href="">Logout</a>');

    return;

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
