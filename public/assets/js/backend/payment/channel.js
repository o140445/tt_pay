define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payment/channel/index',
                    add_url: 'payment/channel/add',
                    edit_url: 'payment/channel/edit',
                    del_url: 'payment/channel/del',
                    multi_url: 'payment/channel/multi',
                    table: 'channel',
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
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'title', title: __('Title'), operate: 'LIKE', },
                        {field: 'code', title: __('Code')},
                        {field: 'mch_id', title: __('MchId')},
                        {field: 'status', title: __('Status'), searchList: {0:'禁用',1:'正常'}, formatter: Table.api.formatter.toggle},
                        {field: 'is_in', title: __('IsIn'), searchList: {0:__('Disabled'),1:__('Enabled')}, formatter: Table.api.formatter.status},
                        {field: 'is_out', title: __('IsOut'), searchList: {0:__('Disabled'),1:__('Enabled')}, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('CreateTime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'update_time', title: __('UpdateTime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
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