define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payment/project/index',
                    add_url: 'payment/project/add',
                    edit_url: 'payment/project/edit',
                    del_url: 'payment/project/del',
                    multi_url: 'payment/project/multi',
                    table: 'project',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE', },
                        {field: 'config_area.name', title: __('Area'), operate: false },
                        {field: 'status', title: __('Status'), searchList: {0:'禁用',1:'正常'}, formatter: Table.api.formatter.toggle},
                        // channels 是一个数组，需要自定义格式化
                        {field: 'channels', title: __('Channel'), operate: false, formatter: function (value, row, index) {
                            var channels = row.channels;
                            var html = '';
                            for (var i = 0; i < channels.length; i++) {
                                html += '<span class="label label-success">' + channels[i] + '</span> ';

                            }
                            return html;
                        }},
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