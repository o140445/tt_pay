define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/freeze/index' + location.search,
                    add_url: 'order/freeze/add',
                    edit_url: 'order/freeze/edit',
                    del_url: 'order/freeze/del',
                    multi_url: 'order/freeze/multi',
                    import_url: 'order/freeze/import',
                    table: 'member_wallet_freeze',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status Freeze'),"1":__('Status Unfreeze')}, formatter: Table.api.formatter.label},
                        {field: 'freeze_type', title: __('Freeze_type'), searchList:{
                                "3": __('Freeze_type_manual'),
                                "5": __('Freeze_type_pay'),
                                "6": __('Freeze_type_withdraw'),
                                "7": __('Freeze_type_cycle')

                        }, formatter: Table.api.formatter.label},
                        {field: 'remark', title: __('Remark'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'thaw_time', title: __('Thaw_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // 解冻按钮
                        {
                            field: 'buttons',
                            operate: false,
                            title: __('操作'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'ajax',
                                    text: __('解冻'),
                                    title: __('解冻'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'order/freeze/unfreeze',
                                    confirm: "确认: 解冻", //提示文字
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        //刷新表格
                                        table.bootstrapTable('refresh');
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        }
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
