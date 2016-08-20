(function() {

    angular
        .module('app')
        .factory('authInterceptor', authInterceptor);

    function authInterceptor(API, auth) {
        return {

            // Automatically attach Authorization header
            request: function(config) {
                var token = auth.getToken();
                if(config.url.indexOf(API) === 0 && token) {
                    config.headers.Authorization = 'Bearer ' + token;
                }
                return config;
            },

            // If a token was sent back, save it
            response: function(res) {
                if(res.config.url.indexOf(API) === 0 && res.data.success.token) {
                    auth.saveToken(res.data.success.token);
                    auth.saveUserName(res.data.success.user.name);
                }
                return res;
            }
        }
    }

})();
