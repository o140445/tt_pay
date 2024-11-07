define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/profits/stat/index' + location.search,
                    add_url: 'finance/profits/stat/add',
                    edit_url: 'finance/profits/stat/edit',
                    del_url: 'finance/profits/stat/del',
                    multi_url: 'finance/profits/stat/multi',
                    import_url: 'finance/profits/stat/import',
                    table: 'profit_stat',
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
                        {field: 'area_id', title: __('Area_id'), visible:false, searchList: $.getJSON('member/config/area/list')},
                        {field: 'area.name', title: __('地区'), operate:false},
                        {field: 'date', title: __('Date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'in_order_count', title: __('In_order_count'), operate:false},
                        {field: 'in_order_amount', title: __('In_order_amount'), operate:false},
                        {field: 'in_fee', title: __('In_fee'), operate:false},
                        {field: 'in_channel_fee', title: __('In_channel_fee'), operate:false},
                        {field: 'in_commission', title: __('In_commission'), operate:false},
                        {field: 'in_profit', title: __('In_profit'), operate:false},
                        {field: 'out_order_count', title: __('Out_order_count'), operate:false},
                        {field: 'out_order_amount', title: __('Out_order_amount'), operate:false},
                        {field: 'out_fee', title: __('Out_fee'), operate:false},
                        {field: 'out_channel_fee', title: __('Out_channel_fee'), operate:false},
                        {field: 'out_commission', title: __('Out_commission'), operate:false},
                        {field: 'out_profit', title: __('Out_profit'), operate:false},
                        {field: 'profit', title: __('Profit'), operate:false},
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
