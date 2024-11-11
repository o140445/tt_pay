define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/wallet/index' + location.search,
                    add_url: 'finance/wallet/add',
                    edit_url: 'finance/wallet/edit',
                    del_url: 'finance/wallet/del',
                    multi_url: 'finance/wallet/multi',
                    import_url: 'finance/wallet/import',
                    table: 'member_wallet_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'before_balance', title: __('Before_balance'), operate:false},
                        {field: 'after_balance', title: __('After_balance'), operate:false},
                        {field: 'type_name', title: __('Type'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.label},
                        {field: 'type', title: __('Type'), visible: false, searchList: $.getJSON('finance/wallet/type'), formatter: Table.api.formatter.label},
                        {field: 'remark', title: __('Remark'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
