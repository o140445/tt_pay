define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/in/index' + location.search,
                    add_url: 'order/in/add',
                    edit_url: 'order/in/edit',
                    del_url: 'order/in/del',
                    multi_url: 'order/in/multi',
                    import_url: 'order/in/import',
                    table: 'order_in',
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
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'member_order_no', title: __('Member_order_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'channel_order_no', title: __('Channel_order_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'true_amount', title: __('True_amount'), operate:false},
                        {field: 'fee_amount', title: __('Fee_amount'), operate:false},
                        {field: 'project_id', title: __('Project_id')},
                        {field: 'channel_id', title: __('Channel_id')},
                        {field: 'status', title: __('Status'),  formatter: Table.api.formatter.label, searchList: $.getJSON('order/in/status')},
                        {field: 'notify_status', title: __('Notify_status')},
                        {field: 'area_id', title: __('Area_id'), formatter: Table.api.formatter.label, searchList: $.getJSON('member/config/area/list')},
                        {field: 'notify_count', title: __('Notify_count'), operate: false},
                        {field: 'e_no', title: __('E_no'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'error_msg', title: __('Error_msg'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'pay_success_date', title: __('Pay_success_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
