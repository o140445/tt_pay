define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/member/index',
                    add_url: 'member/member/add',
                    edit_url: 'member/member/edit',
                    del_url: 'member/member/del',
                    multi_url: 'member/member/multi',
                    add_balance_url: 'member/member/addBalance',
                    table: 'member',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'mch_id', title: __('MchId'), sortable: true},
                        {field: 'username', title: __('UserName')},
                        {field: 'wallet.balance', title: __('Balance'), operate:false, formatter: Table.api.formatter.price},
                        {field: 'wallet.blocked_balance', title: __('BlockedBalance'), operate:false},
                        {field: 'status', title: __('Status'), searchList: {0:'禁用',1:'正常'}, formatter: Table.api.formatter.toggle},
                        {field: 'is_agency', title: __('IsAgency'), searchList: {1:'代理',0:'商户'}, formatter: Table.api.formatter.label},
                        {field: 'is_sandbox', title: __('IsSandbox'), searchList: {"0":__('TypeReal'),"1":__('TypeSandbox')}, formatter: Table.api.formatter.label},
                        {field: 'is_open_web_pay', title: __('IsOpenWebPay'), searchList: {0:__('关闭'), 1:__('开启')}, formatter: Table.api.formatter.label},
                        {field: 'agency_id', title: __('AgencyId')},
                        {field: 'last_login_time', title: __('LastLoginTime'), formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('CreateTime'), formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'ajax',
                                    text: __('重置APIKEY'),
                                    title: __('重置APIKEY'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'member/member/resetApiKey',
                                    confirm: '确认: 重置APIKEY',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },

                                {
                                    name: 'ajax',
                                    text: __('重置Google验证'),
                                    title: __('重置Google验证'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'member/member/resetGoogle',
                                    confirm: '确认: 重置Google验证',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                // 添加余额弹框
                                {
                                    name: 'dialog',
                                    text: __('编辑金额'),
                                    title: __('编辑金额'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'member/member/addBalance',
                                    callback: function (data, ret) {
                                        // 重新加载表格
                                        table.bootstrapTable('refresh');
                                    }
                                },

                                // 设置通道
                                {
                                    name: 'dialog',
                                    text: __('设置通道'),
                                    title: __('设置通道'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'member/project/channel/index?member_id={id}&type=1',
                                    callback: function (data, ret) {
                                        // 重新加载表格
                                        table.bootstrapTable('refresh');
                                    }
                                },

                                // 测试通道
                                {
                                    name: 'dialog',
                                    text: __('测试通道'),
                                    title: __('测试通道'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'member/project/channel/in?member_id={id}',
                                    callback: function (data, ret) {
                                        table.bootstrapTable('refresh');
                                    }
                                },
                            ],
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
        add_balance: function () {
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