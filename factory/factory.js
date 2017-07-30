app.factory('configuracion', function (OPTIONS) {
    return {
        opciones: OPTIONS
    }
});


app.factory("services", ['$http', function ($http) {
    var serviceBase = 'services/'
    var obj = {};
    obj.getAlumnos = function () {
        return $http.get(serviceBase + 'alumnos');
    }

    obj.getAlumno = function (AlumnoID) {
        return $http.get(serviceBase + 'alumno?id=' + AlumnoID);
    }

    obj.insertAlumno = function (customer) {
        return $http.post(serviceBase + 'insertAlumno', customer).then(function (results) {
            return results;
        });
    };

    obj.updateAlumno = function (id, customer) {
        return $http.post(serviceBase + 'updateAlumno', {
            id: id,
            customer: customer
        }).then(function (status) {
            return status.data;
        });
    };

    obj.deleteAlumno = function (id) {
        return $http.delete(serviceBase + 'deleteAlumno?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.generarDeudaAlumno = function (id) {
        return $http.get(serviceBase + 'generarDeudaAlumno?id=' + id);
    };

    obj.borrarDeudaAlumno = function (id) {
        return $http.delete(serviceBase + 'borrarDeudaAlumno?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.getRecibo = function (AlumnoID, mes) {
        return $http.get(serviceBase + 'recibo?id=' + AlumnoID + "&mes=" + mes);
    };

    obj.getFacturacion = function (fecha) {
        return $http.get(serviceBase + 'facturacion?fecha=' + fecha);
    };

    obj.getFacturacionMensual = function (anio) {
        return $http.get(serviceBase + 'facturacion_mensual?anio=' + anio);
    };

    obj.getMorosos = function (mes) {
        return $http.get(serviceBase + 'morosos?mes=' + mes);
    };

    obj.getCursos = function () {
        return $http.get(serviceBase + 'cursos');
    };
/////////////////////////////////////
    obj.getCursosUsuarios = function () {
        return $http.get(serviceBase + 'cursos_usuarios');
    };

    obj.getCursosUpdate = function () {
        // return $http.get(serviceBase + 'XXXXXXXXXXXXX');
    };

    obj.getCursosInsert = function () {
        // return $http.get(serviceBase + 'XXXXXXXXXXXXX');
    };

    obj.getFamilias = function (idAlumno) {
        return $http.get(serviceBase + 'familias?idalumno=' + idAlumno);
    };

    obj.getNumeros = function () {
        return $http.get(serviceBase + 'numeros');
    };

    obj.insertFamilia = function (nombreFamilia) {
        return $http.post(serviceBase + 'insertFamilia', {
            familia: nombreFamilia
        }).then(function (results) {
            return results;
        });
    };

    obj.updateMoroso = function (id, fecha) {
        return $http.post(serviceBase + 'updateMoroso', {
            id: id,
            fecha: fecha
        }).then(function (status) {
            return status.data;
        });
    };

    obj.updateRecibo = function (data, num_rec) {
        return $http.post(serviceBase + 'updateRecibo', {
            data: data,
            num_rec: num_rec
        }).then(function (status) {
            return status.data;
        });
    };

    obj.deleteRecibo = function (id) {
        return $http.delete(serviceBase + 'deleteRecibo?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.getLogin = function (user, pass) {
        return $http.post(serviceBase + 'login', {
            user: user,
            pass: pass
        }).then(function (results) {
            return results;
        });
    };

    obj.getPasarLista = function (curso_id, fecha) {
        return $http.get(serviceBase + 'pasar_lista?id=' + curso_id + "&fecha=" + fecha);
    };

    obj.getPresente = function (idalumno, fecha, estado, idcurso) {
        return $http.get(serviceBase + 'presente?idalumno=' + idalumno + "&fecha=" + fecha + "&estado=" + estado + "&idcurso=" + idcurso);
    };

    return obj;
}]);


app.factory('LoginService', function ($rootScope, services, $http) {
    var isAuthenticated = false,
        role = '';

    return {
        login: function (username, password) {
            var serviceBase = 'services/';
            return $http.post(serviceBase + 'login', {
                user: username,
                pass: password
            }).then(function (results) {
                isAuthenticated = username === results.data.user && password === results.data.pass;
                role = results.data.role;
                return role; //isAuthenticated;
            });
        },
        isAuthenticated: function () {
            $rootScope.logged = isAuthenticated;
            return isAuthenticated;
        },
        role: function () {
            $rootScope.role = role;
            return role;
        }
    };
});