var Lightning = Lightning || {};

Lightning.App = function () {

    this.interval = 1000;

    this.container = $('#container');
    this.loading = $('.loading');

    $(document).on('click', 'ul.lists a', $.proxy(this.getList, this));
    $(document).on('click', 'ul.items a', function () { return false; });

    this.container.html(Twig.render(Lightning.templates.welcome, {href: ''}));

    $('.send-request').click($.proxy(this.sendRequest, this));
};

Lightning.App.prototype.setLoading = function (loading) {
    this.loading.css('visibility', loading ? 'visible' : 'hidden');
};

Lightning.App.prototype.sendRequest = function (data) {

    this.setLoading(true);

    $.ajax({
        type: 'POST',
        url: Lightning.URL_ACCESS_TOKEN,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
        },
        success: $.proxy(this.showRequest, this)
    });

    return false;
};

Lightning.App.prototype.showRequest = function (data) {

    this.token = data;

    this.setLoading(true);

    this.container.html('<p>Please allow access on your mobile phone by typing the following numbers:</p><p class="challenge">' + this.token.challenge + '<p>');

    this.pollLists();

    return false;
};

Lightning.App.prototype.pollLists = function () {

    clearTimeout(this.timeout);

    this.timeout = setTimeout($.proxy(this.loadLists, this), this.interval);
};

Lightning.App.prototype.getTokenUrl = function () {
    return Lightning.URL_ACCOUNT + '/access_tokens/' + this.token.id + '?challenge=' + this.token.challenge;
};

Lightning.App.prototype.loadLists = function () {

    $.ajax({
        url: Lightning.URL_LISTS,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
            AccessToken: this.getTokenUrl()
        },
        success: $.proxy(this.lists, this),
        error: $.proxy(this.pollLists, this),
    });
};

Lightning.App.prototype.lists = function (data) {

    this.setLoading(false);
    this.container.html(Twig.render(Lightning.templates.lists, data));

    $('#user').html('<a href="">Logout</a>');

    this.pollLists();
};

Lightning.App.prototype.getList = function (e) {

    clearTimeout(this.timeout);

    var url = $(e.target).attr('href');

    var ajax = $.ajax({
        url: url,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
            AccessToken: this.getTokenUrl()
        },
        success: $.proxy(this.list, this)
    });

    this.setLoading(true);
    this.container.html('');

    return false;
};

Lightning.App.prototype.list = function (data) {

    this.setLoading(false);
    this.container.html(Twig.render(Lightning.templates.list, data));
};

$(function () {
    var app = new Lightning.App();
});
