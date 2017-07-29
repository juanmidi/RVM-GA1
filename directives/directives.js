app.directive('focus',
    function ($timeout) {
        return {
            scope: {
                trigger: '@focus'
            },
            link: function (scope, element) {
                scope.$watch('trigger', function (value) {
                    if (value === "true") {
                        $timeout(function () {
                            element[0].focus();
                        });
                    }
                });
            }
        };
    })



app.directive('editableTd', ['$filter', function ($filter) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var result;
            element.css("cursor", "pointer");
            element.attr('contenteditable', 'true');
            element.bind('blur change', function () {
                result = element.text() === '' ? '0' : parseFloat(element.text());
                result =  $filter("number")(result, 2);
                element.text(result);
                scope.servicios[attrs.row][attrs.field] = result;
                scope.$apply();
                // scope.getTotalRow();
                // console.log("en directiva")
                scope.getTotalCol();
                // //console.log(attrs.row + " " + attrs.field)
            });
            element.bind('click', function () { // Referencia: Inciso 3
                document.execCommand('selectAll', false, null)
            });
        }
    };
}])



app.directive('loading', ['$http', function ($http) {
    return {
        restrict: 'A',
        link: function (scope, elm, attrs) {
            scope.isLoading = function () {
                return $http.pendingRequests.length > 0;
            };

            scope.$watch(scope.isLoading, function (v) {
                if (v) {
                    elm.show();
                } else {
                    elm.hide();
                }
            });
        }
    };

}])


app.directive('toolsPopup', function(){
    return{
       restrict: 'C',
       link: function(scope, element, attrs){
          element.hover(function(e){
              e.stopPropagation();
              element.children().toggleClass('hidden');
              $
          })
       }
    }
})