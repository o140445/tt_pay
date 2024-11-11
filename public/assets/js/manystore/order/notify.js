define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/notify/index' + location.search,
                    add_url: 'order/notify/add',
                    edit_url: 'order/notify/edit',
                    del_url: 'order/notify/del',
                    multi_url: 'order/notify/multi',
                    import_url: 'order/notify/import',
                    table: 'member_wallet_freeze',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                search:false,
                showColumns: false,
                showExport: false,
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},

                        {field: 'notify_status', title: __('Notify_status'), operate: false, searchList: {"1":__('Notify_status success'),"2":__('Notify_status fail')}, formatter: Table.api.formatter.status},

                        {field: 'notify_result', title: __('Notify_result'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'notify_url', title: __('Notify_url'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'notify_data', title: __('Notify_data'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('Create_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
