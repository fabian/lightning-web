var Lightning = Lightning || {};

Lightning.App = function () {

    this.container = $('#container');
    this.loading = $('.loading');

    this.container.html('<p>You need to send an access request to your mobile phone to edit your lists and items.</p>'); 

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

    this.poll();

    return false;
};

Lightning.App.prototype.poll = function () {

    $.ajax({
        url: Lightning.URL_LISTS,
        dataType: 'json',
        headers: { 
            Accept: 'application/json; charset=utf-8',
            Account: 'http://localhost:8000/accounts/1?secret=97e3aaa01680c301117295d6c50d3a4e'
        },
        success: $.proxy(this.lists, this)
    });
};

Lightning.App.prototype.lists = function (data) {

    this.load(false);
    this.container.html(Twig.render(Lightning.templates.lists, data));

    $('#user').html('<a href="">Logout</a>');

    setTimeout($.proxy(this.poll, this), 1000);
};

$(function () {
    var app = new Lightning.App();
});
