define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {

            // 给表单绑定事件
            Form.api.bindevent($("#shop-form"));
        },
    };
    return Controller;
});
