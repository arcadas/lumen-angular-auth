(function() {

    angular
        .module('app', [])
        .constant('API', 'http://localhost')
        .config(function($httpProvider) {
            $httpProvider.interceptors.push('authInterceptor');
        });

})();
