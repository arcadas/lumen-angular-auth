(function() {

    angular
        .module('app')
        .service('user', userService);

    function userService($http, API, auth) {

        var self = this;

        self.register = function(name, email, password, password_confirmation) {
            return $http.post(API + '/auth/register', {
                name: name,
                email: email,
                password: password,
                password_confirmation: password_confirmation
            })
                .then(function(res) {
                    auth.saveToken(res.data.token);
                    return res;
                })
        };

        self.login = function(email, password) {
            return $http.post(API + '/auth/login', {
                email: email,
                password: password
            })
        }
    }

})();
