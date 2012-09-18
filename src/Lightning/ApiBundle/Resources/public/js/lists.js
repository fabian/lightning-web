var Lightning = Lightning || {};

YUI().use('*', function (Y) {

    Lightning.App = function () {

        this.container = Y.one('#container');
        this.loading = Y.one('.loading');
        this.account = '';

        this.container.setHTML(Twig.render(Lightning.templates.welcome, {href: Lightning.URL_ACCOUNT}));

        Y.one('.send-request').on('click', Y.bind(this.sendRequest, this));
    };

    Lightning.App.prototype.load = function (loading) {
        this.loading.setStyle('visibility', loading ? 'visible' : 'hidden');
    };

    Lightning.App.prototype.sendRequest = function (e) {

        e.preventDefault();

        this.load(true);
        this.container.setHTML('<p>Please allow access on your mobile phone<p>');

        Y.io(Lightning.URL_ACCOUNT, {
            method: 'POST',
            headers: { 
                Accept: 'application/json; charset=utf-8'
            },
            on: {success: Y.bind(this.accessed, this)}
        });

        return false;
    };

    Lightning.App.prototype.accessed = function (id, e) {

        var account = JSON.parse(e.responseText);

        this.account = 'secret';

        this.poll();
    };

    Lightning.App.prototype.poll = function () {

        Y.io(Lightning.URL_LISTS, {
            headers: {
                Accept: 'application/json; charset=utf-8',
                Account: this.account
            },
            on: {success: Y.bind(this.lists, this)}
        });
    };

    Lightning.App.prototype.lists = function (id, e) {

        var data = JSON.parse(e.responseText);

        this.load(false);
        this.container.setHTML(Twig.render(Lightning.templates.lists, data));

        Y.one('#user').setHTML('<a href="">Logout</a>');

        setTimeout(Y.bind(this.poll, this), 1000);
    };

    var app = new Lightning.App();
});
