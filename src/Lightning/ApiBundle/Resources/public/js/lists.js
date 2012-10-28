var Lightning = Lightning || {};

Lightning.App = function () {

    this.container = $('#container');
    this.loading = $('.loading');

    $(document).on('click', 'ul.lists a', $.proxy(this.getList, this));
    $(document).on('click', 'ul.items a', function () { return false; });

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
            Account: 'http://localhost:8000/accounts/6?secret=3cba86e5c3d02d0ddffcce7c42f4a685'
        },
        success: $.proxy(this.lists, this)
    });
};

Lightning.App.prototype.lists = function (data) {

    this.load(false);
    this.container.html(Twig.render(Lightning.templates.lists, data));

    $('#user').html('<a href="">Logout</a>');

    this.timeout = setTimeout($.proxy(this.poll, this), 1000);
};

Lightning.App.prototype.getList = function (e) {

    clearTimeout(this.timeout);

    var url = $(e.target).attr('href');

    var ajax = $.ajax({
        url: url,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
            Account: 'http://localhost:8000/accounts/6?secret=3cba86e5c3d02d0ddffcce7c42f4a685'
        },
        success: $.proxy(this.list, this)
    });

    this.load(true);
    this.container.html('');

    return false;
};

Lightning.App.prototype.list = function (data) {

    this.load(false);
    this.container.html(Twig.render(Lightning.templates.list, data));
};

$(function () {
    var app = new Lightning.App();
});
