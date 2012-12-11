var Lightning = Lightning || {};

Lightning.COOKIE_TOKEN = 'lightning_access_token';

Lightning.App = function () {

    this.interval = 1000;

    this.container = $('#container');
    this.loading = $('.loading');
    this.back = $('.back');
    this.title = $('.title');

    $(document).on('click', 'ul.lists a', $.proxy(this.getList, this));
    $(document).on('click', 'ul.items a', function () { return false; });
    $(document).on('click', '.back a', $.proxy(this.loadLists, this));
    $(document).on('click', '#user a', $.proxy(this.logout, this));

    var token = this.readCookie(Lightning.COOKIE_TOKEN);
    if (token) {

        this.token = token;

        this.setLoading(true);

        this.pollLists();
        this.renderLogout();

    } else {

        this.container.html(Twig.render(Lightning.templates.welcome, {href: ''}));

        $('.send-request').click($.proxy(this.sendRequest, this));
    }
};

Lightning.App.prototype.setLoading = function (loading) {
    this.loading.css('visibility', loading ? 'visible' : 'hidden');
};

Lightning.App.prototype.setBack = function (loading) {
    this.back.css('visibility', loading ? 'visible' : 'hidden');
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

Lightning.App.prototype.logout = function (data) {

    this.eraseCookie(Lightning.COOKIE_TOKEN);

    window.location.reload(true); 


    return false;
};

Lightning.App.prototype.showRequest = function (data) {

    this.token = data.url;
    this.writeCookie(Lightning.COOKIE_TOKEN, this.token, 1);

    this.setLoading(true);

    this.container.html('<p>Please allow access on your mobile phone by typing the following numbers:</p><p class="challenge">' + data.challenge + '<p>');

    this.pollLists();

    return false;
};

Lightning.App.prototype.writeCookie = function (name, value, days) {

    var date = new Date(),
        expires;

    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = '; expires=' + date.toGMTString();

    document.cookie = name + '=' + value + expires + '; path=/';
};

Lightning.App.prototype.readCookie = function (name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1, c.length);
        }
        if (c.indexOf(nameEQ) == 0) {
            return c.substring(nameEQ.length, c.length);
        }
    }
    return null;
};

Lightning.App.prototype.eraseCookie = function(name) {
    this.writeCookie(name, '', -1);
};

Lightning.App.prototype.pollLists = function (e) {

    clearTimeout(this.timeout);

    this.timeout = setTimeout($.proxy(this.loadLists, this), this.interval);
};

Lightning.App.prototype.loadLists = function () {

    this.ajax = $.ajax({
        url: Lightning.URL_LISTS,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
            AccessToken: this.token
        },
        success: $.proxy(this.renderLists, this),
        statusCode: {
            401: $.proxy(this.pollLists, this),
            403: $.proxy(this.renderError, this)
        }
    });

    return false;
};

Lightning.App.prototype.renderError = function (data) {

    this.container.html('<p>Error ' + data.status + ': ' + data.statusText + '</p>');
};

Lightning.App.prototype.renderLists = function (data) {

    this.setBack(false);
    this.setLoading(false);
    this.title.text('Lightning');
    this.container.html(Twig.render(Lightning.templates.lists, data));

    this.renderLogout();
};

Lightning.App.prototype.renderLogout = function (data) {

    $('#user').html('<a href="">Logout</a>');
};

Lightning.App.prototype.getList = function (e) {

    clearTimeout(this.timeout);
    this.ajax.abort();

    this.list = $(e.target);
    var url = this.list.attr('href');

    var ajax = $.ajax({
        url: url,
        dataType: 'json',
        headers: {
            Accept: 'application/json; charset=utf-8',
            AccessToken: this.token
        },
        success: $.proxy(this.renderList, this)
    });

    this.setLoading(true);
    this.container.html('');

    return false;
};

Lightning.App.prototype.renderList = function (data) {

    this.setBack(true);
    this.setLoading(false);
    this.title.text(this.list.text());
    this.container.html(Twig.render(Lightning.templates.list, data));
};

$(function () {
    var app = new Lightning.App();
});
