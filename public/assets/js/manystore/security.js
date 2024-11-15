define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload'], function ($, undefined, Backend, Table, Form, Upload) {

    var Controller = {
        index: function () {
            Form.api.bindevent($("#shop-form"));
        },
        bindgoogle: function () {
            Controller.api.bindevent();
        },
    };
    return Controller;
});
