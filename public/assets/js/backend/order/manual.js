define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/manual/index' + location.search,
                    add_url: 'order/manual/add',
                    edit_url: 'order/manual/edit',
                    del_url: 'order/manual/del',
                    multi_url: 'order/manual/multi',
                    import_url: 'order/manual/import',
                    table: 'order_manual',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'channel_order_no', title: __('Channel_order_no'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'channel_id', title: __('Channel_id')},
                        {field: 'status', title: __('状态'), searchList: {
                            "1":__('未支付'),
                            "2":__('支付成功'),
                            "3":__('支付失败'),
                            "4":__('退款'),
                            }, formatter: Table.api.formatter.status},
                        {field: 'e_no', title: __('E_no'), operate: 'LIKE'},
                        {field: 'msg', title: __('Msg'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
