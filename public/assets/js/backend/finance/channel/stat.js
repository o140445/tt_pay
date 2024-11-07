define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/channel/stat/index' + location.search,
                    add_url: 'finance/channel/stat/add',
                    edit_url: 'finance/channel/stat/edit',
                    del_url: 'finance/channel/stat/del',
                    multi_url: 'finance/channel/stat/multi',
                    import_url: 'finance/channel/stat/import',
                    table: 'channel_stat',
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
                        {field: 'channel_id', title: __('Channel_id')},
                        {field: 'date', title: __('Date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'in_order_count', title: __('In_order_count'),  operate:false},
                        {field: 'in_order_success_count', title: __('In_order_success_count'),  operate:false},
                        {field: 'in_order_amount', title: __('In_order_amount'),  operate:false},
                        {field: 'in_order_success_amount', title: __('In_order_success_amount'),  operate:false},
                        {field: 'in_channel_fee', title: __('In_channel_fee'),  operate:false},
                        {field: 'in_success_rate', title: __('In_success_rate'),  operate:false, formatter: function (value, row, index) {
                            return  ( row.in_success_rate * 100).toFixed(2) + '%';
                        }},
                        {field: 'out_order_count', title: __('Out_order_count'),  operate:false},
                        {field: 'out_order_success_count', title: __('Out_order_success_count'), operate:false},
                        {field: 'out_order_amount', title: __('Out_order_amount'), operate:false},
                        {field: 'out_order_success_amount', title: __('Out_order_success_amount'),  operate:false},
                        {field: 'out_channel_fee', title: __('Out_channel_fee'),  operate:false},
                        {field: 'out_success_rate', title: __('Out_success_rate'), operate:false, formatter: function (value, row, index) {
                            return  ( row.out_success_rate * 100).toFixed(2) + '%';
                            }
                        },
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
