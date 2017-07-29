var app = angular.module('myApp', ['ngRoute']);

//constantes
app.constant('OPTIONS', {
  campos_obligatorios: false,
  bobo: 2
})

app.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider
      .when('/', {
        title: 'Ingresar',
        templateUrl: 'partials/inicio.html',
        controller: 'inicioCtrl'
      })
      .when('/inicio', {
        title: 'Ingresar',
        templateUrl: 'partials/inicio.html',
        controller: 'inicioCtrl'
      })
      .when('/login', {
        title: 'Ingresar',
        templateUrl: 'partials/login.html',
        controller: 'loginCtrl'
      })
      .when('/alumnos', {
        title: 'Alumnos',
        templateUrl: 'partials/alumnos.html',
        controller: 'listCtrl'
      })
      .when('/recibo/:AlumnoID/mes/:mes', {
        title: 'Recibo',
        templateUrl: 'partials/recibo.html',
        controller: 'reciboCtrl'
      })
      .when('/facturacion/:fecha', {
        title: 'Facturación',
        templateUrl: 'partials/facturacion.html',
        controller: 'facturacionCtrl'
      })
      .when('/morosos/:mes', {
        title: 'Morosos',
        templateUrl: 'partials/morosos.html',
        controller: 'morososCtrl'
      })
      .when('/edit-alumno/:AlumnoID', {
        title: 'Editar Alumno',
        templateUrl: 'partials/edit-alumno.html',
        controller: 'editCtrl',
        resolve: {
          customer: function(services, $route){
            var AlumnoID = $route.current.params.AlumnoID;
            return services.getAlumno(AlumnoID);  
          },
          cursos: function(services){
            return services.getCursos();  
          }
        }
      })
      .when('/presentes', {
        title: 'Presentes',
        templateUrl: 'partials/presentes.html',
        controller: 'tomarListaCtrl',
        resolve: {
          cursos: function(services){
            return services.getCursos();  
          }
        }
      })
      .when('/cursos', {
        title: 'Cursos',
        templateUrl: 'partials/cursos.html',
        controller: 'cursosCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
}])

app.run(['$location', '$rootScope', 'LoginService', function($location, $rootScope, LoginService) {
    $rootScope.logged = LoginService.isAuthenticated();

    $rootScope.$on('$routeChangeStart', function(event)
    {
      if(!LoginService.isAuthenticated()){
        event.preventDefault();
        $location.path('/login');
      } else {

      }
    })
}])

