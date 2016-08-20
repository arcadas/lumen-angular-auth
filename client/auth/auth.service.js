(function() {

    angular
        .module('app')
        .service('auth', authService);

    function authService($window) {
        var self = this;

        self.parseJwt = function(token) {
            var base64Url = token.split('.')[1];
            var base64 = base64Url.replace('-', '+').replace('_', '/');
            return JSON.parse($window.atob(base64));
        };

        self.saveToken = function(token) {
            $window.localStorage['jwtToken'] = token;
        };

        self.saveUserName = function(userName) {
            $window.localStorage['userName'] = userName;
        };

        self.getToken = function() {
            return $window.localStorage['jwtToken'];
        };

        self.getUserName = function() {
            return $window.localStorage['userName'];
        };

        self.isAuthed = function() {
            var token = self.getToken();
            if(token) {
                var params = self.parseJwt(token);
                return Math.round(new Date().getTime() / 1000) <= params.exp;
            } else {
                return false;
            }
        };

        self.logout = function() {
            $window.localStorage.removeItem('jwtToken');
        }
    }

})();
