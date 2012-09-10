var Lightning = Lightning || {};

Lightning.App = function () {

    this.container = $('#container');
    this.loading = $('.loading');

    this.container.html('<p>To access your lists you need to send an access request to your mobile phone.</p>'); 

    var message = $(document.createElement('p'));
    var link = $(document.createElement('a')).attr('href', Lightning.URL_ACCOUNT).text('Send Request');
    link.click($.proxy(this.login, this));
    message.append(link);
    this.container.append(message);
};

Lightning.App.prototype.load = function (loading) {
    this.loading.css('visibility', loading ? 'visible' : 'hidden');
};

Lightning.App.prototype.login = function () {

    this.load(true);
    this.container.html('<p>Please allow access on your mobile phone<p>');

    $.ajax({
        url: Lightning.URL_LISTS,
        dataType: 'json',
        headers: { 
            Accept: 'application/json; charset=utf-8'
        },
        success: $.proxy(this.lists, this)
    });

    return false;
};


Lightning.App.prototype.lists = function (data) {

    var ul = $(document.createElement('ul'));

    $(data.lists).each(function (i, list) {
        var li = $(document.createElement('li'));
        var a = $(document.createElement('a')).attr('href', '').text(list.title);
        li.append(a);
        ul.append(li);
    });

    this.load(false);
    this.container.html('').append(ul);

    $('#user').html('<a href="">Logout</a>');
};

$(function () {
    var app = new Lightning.App();
});
