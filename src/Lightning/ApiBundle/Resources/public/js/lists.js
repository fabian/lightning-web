var Lightning = Lightning || {};

Lightning.App = function () {

    this.container = $('#container');
    this.loading = $('.loading');

    this.container.html(Twig.render(Lightning.templates.welcome, {href: Lightning.URL_ACCOUNT}));

    $('.send-request').click($.proxy(this.sendRequest, this));
};

Lightning.App.prototype.load = function (loading) {
    this.loading.css('visibility', loading ? 'visible' : 'hidden');
};

Lightning.App.prototype.sendRequest = function () {

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
            Account: 'http://lightningapp.ch/accounts/2?secret=173341cec52486c67e72f9f20ae8fd1e'
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
