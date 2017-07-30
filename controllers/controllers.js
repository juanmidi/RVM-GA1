app.controller('loginCtrl', function ($scope, $location, LoginService) {
    $scope.formSubmit = function () {
        var myDataPromise = LoginService.login($scope.username, $scope.password);
        myDataPromise.then(function (result) {
            if (result) {
                $scope.error = '';
                $scope.username = '';
                $scope.password = '';
                $scope.role = LoginService.role();
                $location.path('/inicio');
            } else {
                $scope.error = "Usuario o contraseña incorrecta.";
            }
        });
    };
});

app.controller('mainController', function ($scope, LoginService) {
    var f = new Date();
    //var fecha_f =  (f.getDate() + 1) + '-' + (f.getMonth() + 1) + '-' +  f.getFullYear();
    var fecha_f = f.getFullYear() + '-' + (f.getMonth() + 1) + '-' + (f.getDate());
    $scope.fecha = format_fecha(fecha_f); //.toString();
    $scope.id_mes = f.getMonth() + 1;
});

app.controller('inicioCtrl', function ($scope, LoginService) {
    $scope.role = LoginService.role();

});

app.controller('reciboCtrl', function ($scope, $routeParams, services, $window, $timeout, $route) {
    var AlumnoID = ($routeParams.AlumnoID) ? parseInt($routeParams.AlumnoID) : 0;
    var mes = ($routeParams.mes) ? parseInt($routeParams.mes) : 0;

    var d = new Date();
    var hora = d.getHours() + ":" + d.getMinutes();
    $scope.hora = hora;

    services.getRecibo(AlumnoID, mes).then(function (data) {
        $scope.servicios = data.data;
    });

    services.getNumeros().then(function (data) {
        $scope.numero = pad(parseInt(data.data.num_rec) + 1, 6);
    });

    $scope.resultados = {
        totalrow: 0,
        totalcol: 0
    };

    $scope.getTotalCol = function () {
        $timeout(function () {
            var suma = 0,
                index = 0;
            $('table tr').not(':first').each(function () {
                if ($(this).find('td').eq(6).text() === 'true') {
                    $("#fila-" + index).removeClass("no-print");
                    suma += parseFloat($(this).find('td').eq(5).text());
                } else {
                    $("#fila-" + index).addClass("no-print");
                }
                index++;
            })
            $scope.resultados.totalcol = suma;
            $scope.resultados.numtoletras = (suma > 0) ? "Son " + NumeroALetras(suma) : '';
            $scope.$apply();
        });
    }

    $scope.imprimirRecibo = function (serv, num) {
        services.updateRecibo(serv, num).then(function (data) {});
    }

    $scope.imprimir = function () {
        if ($window.confirm("¿imprime?")) {
            //agrega clase de ancho fijo para el recibo
            $("#original").addClass("alto-fijo-recibo")
            $("#original").clone().appendTo("#copia");
            $("#label-original").removeClass("hidden");
            $("#linea-punteada-1").removeClass("hidden");
            $(".lineaenblanco").removeClass("hidden");
            $("#label-copia").removeClass("hidden");
            $("#linea-punteada-2").removeClass("hidden");
            $scope.imprimirRecibo($scope.servicios, $scope.numero);
            $window.print();
            $route.reload();
        }
    }

    $scope.alumno_id = $routeParams.AlumnoID;

})

app.controller('facturacionCtrl', function ($scope, services, $routeParams) {

    var suma = 0;

    $scope.init = function () {
        //carga la fecha actual en el controlador
        var fecha = format_fecha_d(Date());
        $("#datepicker").val(fecha);
    }

    angular.element(document).ready(function () {
        $scope.verFacturacion();
        $('#datepicker').on('change', function (ev) {
            $scope.verFacturacion();
        })
    })

    $scope.verFacturacion = function () {
        var fecha = $("#datepicker").val();
        services.getFacturacion(fecha).then(function (data) {
            $scope.facturacion = data.data;
            // focus('datepicker');
        });
    }

    $scope.getTotal = function () {
        if ($scope.facturacion != undefined) {
            var total = 0;
            for (var i = 0; i < $scope.facturacion.length; i++) {
                var product = $scope.facturacion[i];
                total += parseFloat(product.importe);
            }
            return total;
        }
    }

    $scope.getTotalAnual = function () {
        if ($scope.resumen != undefined) {
            var total = 0;
            var result = 0;
            for (var i = 0; i < $scope.resumen.length; i++) {
                result = $scope.resumen[i];
                total += parseFloat(result.total);
            }
            return total;
        }
    }

    $scope.verResumen = function (dia) {
        if (dia) {
            $("#datepicker").removeClass("hidden");
            $("#anio").addClass("hidden");
            $("#li-dia").addClass("active");
            $("#li-resumen").removeClass("active");
            $("#lbl-fecha").text("Fecha");
            $("#div-factura").removeClass("hidden");
            $("#div-resumen").addClass("hidden");
            $("#total").removeClass("hidden");
            $("#totalanual").addClass("hidden");
            $scope.verFacturacion();
        } else {
            $("#datepicker").addClass("hidden");
            $("#anio").removeClass("hidden");
            $("#li-dia").removeClass("active");
            $("#li-resumen").addClass("active");
            $("#lbl-fecha").text("Año");
            $("#div-factura").addClass("hidden");
            $("#div-resumen").removeClass("hidden");
            $("#total").addClass("hidden");
            $("#totalanual").removeClass("hidden");
            var a=new Date();
            var anio=a.getFullYear();
            console.log("vejerto "+anio);
            $scope.facturacionMensual(anio);
        }
    }

    $scope.facturacionMensual = function (anio) {
        services.getFacturacionMensual(anio).then(function (data) {
            $scope.resumen = data.data;
            console.log(data.data)
        });
        
        // for (var mes = 1; mes <= 12; mes++) {
        //     services.getFacturacionMensual(mes, anio).then(function (data) {
        //         $scope.facturacion = data.data;
        //         console.log(data.data)
        //     });
        // }
    }

    angular.element(document).ready(function () {
        $("#anio").on("change", function () {
            $scope.facturacionMensual($(this).val());
        })
    })

    $scope.init();

});

app.controller('morososCtrl', function ($scope, services, $routeParams, $location) {
    var mes = $routeParams.mes;
    services.getMorosos(mes).then(function (data) {
        $scope.morosos = data.data;
    });

    $scope.update = function () {
        if (confirm("¿Marca como pagos los conceptos seleccionados?") == true) {
            var ids = [],
                fechas = [],
                columnaId = 1,
                columnaFecha = 5,
                id;
            $('#tablita tr').not(':first').each(function () {
                if ($(this).find('input[type="checkbox"]').is(':checked')) {
                    id = $(this).find('td').eq(columnaId).text();
                    fecha = $(this).find('td').eq(columnaFecha).text();
                    ids.push(id);
                    fechas.push(fecha);
                    $("#fila-" + id).hide(1000);
                    // $(this).find('td').eq(columnaId).text()
                }
            })

            services.updateMoroso(ids, fechas);
        }
    }

    $scope.recibo = function (idAlumno) {
        var c = "/recibo/" + idAlumno + "/mes/" + mes;
        $location.path(c);
    }

    $scope.delete = function (id) {
        if (confirm("Está seguro que quiere borrar el concepto adeudado?") == true)
            services.deleteRecibo(id);
        $("#fila-" + id).hide(1000);
    }
});

app.controller('listCtrl', function ($scope, services) {
    services.getAlumnos().then(function (data) {
        $scope.alumnos = data.data;
    });

    var f = new Date();
    $scope.mesActual = f.getMonth() + 1;
});

app.controller('editCtrl', function ($scope, $route, $rootScope, $location, $routeParams,
    services, customer, cursos, configuracion) {

    var AlumnoID = ($routeParams.AlumnoID) ? parseInt($routeParams.AlumnoID) : 0;
    $rootScope.title = (AlumnoID > 0) ? 'Editar Alumno' : 'Agregar Alumno';
    $scope.buttonText = (AlumnoID > 0) ? 'Actualizar Alumno' : 'Agregar nuevo Alumno';
    var tmpFecha = format_fecha(customer.data.fecha_nac, "mm-dd-yyyy");
    customer.data.fecha_nac = new Date(tmpFecha);
    var original = customer.data;

    $scope.configuracion = configuracion;
    original._id = AlumnoID;
    $scope.customer = angular.copy(original);
    $scope.customer._id = AlumnoID;
    $scope.cursos = cursos.data;

    $scope.generarDeuda = function (id) {
        //borra deuda anterior no paga
        services.borrarDeudaAlumno(id).then(function (data) {});
        //genera nueva deuda
        services.generarDeudaAlumno(id).then(function (data) {});
    }

    $scope.isClean = function () {
        return angular.equals(original, $scope.customer);
    }

    $scope.deleteAlumno = function (customer) {
        $location.path('/alumnos');
        if (confirm("Está seguro que quiere borrar el alumno número: " + $scope.customer._id) == true)
            services.deleteAlumno(customer.id);
        $route.reload();
    };

    $scope.saveAlumno = function (customer) {
        $location.path('/alumnos');
        if (AlumnoID <= 0) {
            services.insertAlumno(customer).then(function (status) {
                $scope.generarDeuda(status.data.id);
            });
            services.getAlumnos();
        } else {
            services.updateAlumno(AlumnoID, customer);
            $scope.generarDeuda(AlumnoID);
        }
        $route.reload();
    };
});

app.controller('tomarListaCtrl', function ($scope, $timeout, services, cursos, configuracion) {
    $scope.cursos = cursos.data;

    $scope.init = function () {
        //carga la fecha actual en el controlador
        var fecha = format_fecha_d(Date());
        $("#datepicker").val(fecha);
    }

    $scope.init();

    angular.element(document).ready(function () {
        $scope.pasarLista();
        $("#curso").on("change", function () {
            $scope.pasarLista();
        })
        $("#datepicker").on("change", function () {
            $scope.pasarLista();
        })
    })

    $scope.pasarLista = function () {
        var curso = $("#curso").val();
        var fecha = $("#datepicker").val();
        services.getPasarLista(curso, fecha).then(function (data) {
            $scope.listado = data.data;
            $scope.totalPresentes();
            $scope.totalAlumnos = data.data.length;
            $scope.fechaentexto = fecha_en_texto(fecha);
        });
    }

    $scope.totalPresentes = function () {
        $timeout(function () {
            var cuenta = 0,
                index = 0;
            $('table tr').not(':first').each(function () {
                if ($(this).find(':checkbox:checked').length) {
                    cuenta++;
                }
                index++;
            })
            $scope.total = cuenta;
            $scope.$apply();
        });
    }

    $scope.actualizar = function (id, check) {
        console.log(id, check)
        var curso = $("#curso").val();
        var fecha = $("#datepicker").val();
        services.getPresente(id, fecha, check, curso).then(function (data) {
            $scope.totalPresentes();
        });
    }

})

app.controller('cursosCtrl', function ($scope, services, configuracion) {
    services.getCursosUsuarios().then(function (data) {
        $scope.cursos = data.data;
        console.log(data.data)
    })

    $scope.caca = function (id, mostrar) {
        console.log(id)
        swal("id usuario: " + id);
    }
    /*
        $scope.cursoClick = function(id){
            $("#curso-" + id).removeClass("hidden");
        } 

        $scope.cursoLeave = function(id){
            $("#curso-" + id).addClass("hidden");
        } 

        $scope.profeClick = function(id, idx){
            console.log(id, idx)
            // $("#profe-" + id).removeClass("hidden");
            console.log($(this).text());   
        } 

        $scope.profeLeave = function(id){
            $("#profe-" + id).addClass("hidden");
        } 
    */

})