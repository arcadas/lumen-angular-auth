(function() {

    angular
        .module('app')
        .controller('Main', MainCtrl);

    function MainCtrl(user, auth) {
        var self = this;

        function handleRequest(res) {
            var token = res.data ? res.data.token : null;
            self.message = res.data.message;
        }

        self.login = function () {
            user.login(self.email, self.password)
                .then(handleRequest, handleRequest)
        };

        self.register = function () {
            user.register(self.name, self.email, self.password, self.password_confirmation)
                .then(handleRequest, handleRequest)
        };

        self.logout = function () {
            auth.logout && auth.logout()
        };

        self.isAuthed = function () {
            return auth.isAuthed ? auth.isAuthed() : false
        };

        self.getUserName = function () {
            return auth.getUserName() ? auth.getUserName() : false
        };
    }

})();
