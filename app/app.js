var app = angular.module('myApp', ['ngRoute']);

//constantes
app.constant('OPTIONS', {
  campos_obligatorios: false,
  bobo: 2
})

app.config(['$locationProvider','$routeProvider', 
  function ($locationProvider, $routeProvider ) {
    $locationProvider.hashPrefix('');
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
      .when('/notamoroso/:AlumnoID/mes/:mes', {
        title: 'Nota a Moroso',
        templateUrl: 'partials/notamoroso.html',
        controller: 'notaMorosoCtrl',
        resolve: {
          deuda: function(services, $route){
            var AlumnoID = $route.current.params.AlumnoID;
            var mes = $route.current.params.mes;
            return services.notaMoroso(AlumnoID, mes);  
          },
          sistema: function(services){
            return services.getDatosSistema();  
          }
        }
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
        controller: 'tomarListaCtrl'
      })
      .when('/perfil', {
        title: 'Perfil',
        templateUrl: 'partials/profile.html',
        controller: 'perfilCtrl'
      })
      .when('/cursos', {
        title: 'Cursos',
        templateUrl: 'partials/cursos.html',
        controller: 'cursosCtrl'
      })
      .when('/configuracion', {
        title: 'Configuración',
        templateUrl: 'partials/config.html',
        controller: 'configuracionCtrl'
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

