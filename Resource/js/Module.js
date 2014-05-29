/**
 * Created by dp on 23.05.14.
 */
var Module = {
    install: function () {
        Ice.call(
            'Ice:Module_Install',
            {},
            function (result) {
               console.log('install complete');
            }
        );
    }
}