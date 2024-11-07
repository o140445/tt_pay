define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/profit/index' + location.search,
                    add_url: 'finance/profit/add',
                    edit_url: 'finance/profit/edit',
                    del_url: 'finance/profit/del',
                    multi_url: 'finance/profit/multi',
                    import_url: 'finance/profit/import',
                    table: 'profit',
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
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'order_type', title: __('单据类型'), searchList: {"1":__('代收'),"2":__('代付')}, formatter: Table.api.formatter.normal},
                        {field: 'order_amount', title: __('Order_amount'), operate:false},
                        {field: 'fee', title: __('Fee'), operate:false},
                        {field: 'channel_fee', title: __('Channel_fee'), operate:false},
                        {field: 'commission', title: __('Commission'), operate:false},
                        {field: 'profit', title: __('Profit'), operate:false},
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
