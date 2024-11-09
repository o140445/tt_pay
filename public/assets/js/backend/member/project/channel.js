define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/project/channel/index',
                    add_url: 'member/project/channel/add'+ location.search,
                    edit_url: 'member/project/channel/edit',
                    del_url: 'member/project/channel/del',
                    multi_url: 'member/project/channel/multi',
                    import_url: 'member/project/channel/import',
                    table: 'member_project_channel',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                showColumns: false,
                showExport: false,
                search: false,

                queryParams: function (params) {
                    var filter = JSON.parse(params.filter);
                    var type = Fast.api.query('type');
                    var member_id = Fast.api.query('member_id');

                    if (filter.type == undefined && type != undefined) {
                        filter.type = type;
                    }
                    if (filter.member_id == undefined && member_id != undefined) {
                        filter.member_id = member_id;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'member_id', title: __('MemberId')},
                        {field: 'project.name', title: __('Project')},
                        {field: 'channel.title', title: __('Channel')},
                        {field: 'type', title: __('Type'), searchList: {1:__('代收'),2:__('代付')}, formatter: Table.api.formatter.normal},
                        {field: 'sub_member_id', title: __('SubMemberId')},
                        {field: 'status', title: __('Status'), searchList: {0:__('关闭'),1:__('正常')}, formatter: Table.api.formatter.status},
                        {field: 'fixed_rate', title: __('固定税率'), operate:false},
                        // 显示 % 号
                        {field: 'rate', title: __('税率'), operate:false, formatter: function (value, row, index) {
                            return value + '%';
                            }
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {

            //row[project_id] 变化时，获取渠道
            $(document).on("change", "select[name='row[project_id]']", function () {
                var project_id = $(this).val();
                // 如果没有选择项目，清空渠道选项
                if (!project_id) {
                    $("#c-channel_id").html('<option value="">请选择</option>').selectpicker('refresh');
                    return;
                }

                // 发起 AJAX 请求，获取项目对应的渠道列表
                Fast.api.ajax({
                    url: 'payment/project/getChannelListByProjectId',
                    data: { project_id: project_id },
                    method: 'GET'
                }, function (res) {
                    // 清空当前的渠道选项
                    $("#c-channel_id").html('<option value="">请选择</option>');

                    console.log(res);
                    // 遍历响应数据并生成新的选项
                    $.each(res, function (index, item) {
                        $("#c-channel_id").append('<option value="' + item.id + '">' + item.name + '</option>');
                    });

                    // 刷新 selectpicker 状态
                    $("#c-channel_id").selectpicker('refresh');
                    return false;
                });
            });

            $(document).ready(function () {
                // 获取代理商身份
                var isAgent = $('#user-info').data('is-agent');

                // 判断如果是代理商就显示 Sub_member 字段
                if (isAgent) {
                    $('#sub-member').show();
                }
            });


            Controller.api.bindevent();
        },
        edit: function () {

            //row[project_id] 变化时，获取渠道
            $(document).on("change", "select[name='row[project_id]']", function () {
                var project_id = $(this).val();
                // 如果没有选择项目，清空渠道选项
                if (!project_id) {
                    $("#c-channel_id").html('<option value="">请选择</option>').selectpicker('refresh');
                    return;
                }

                // 发起 AJAX 请求，获取项目对应的渠道列表
                Fast.api.ajax({
                    url: 'payment/project/getChannelListByProjectId',
                    data: { project_id: project_id },
                    method: 'GET'
                }, function (res) {
                    // 清空当前的渠道选项 name="row[channel_id]"
                    $("#c-channel_id").html('<option value="">请选择</option>');

                    console.log(res);
                    // 遍历响应数据并生成新的选项
                    $.each(res, function (index, item) {
                        $("#c-channel_id").append('<option value="' + item.id + '">' + item.name + '</option>');
                    });

                    // 刷新 selectpicker 状态
                    // $("#c-channel_id").selectpicker('refresh');
                    return false;
                });
            });

            $(document).ready(function () {
                // 获取代理商身份
                var isAgent = $('#user-info').data('is-agent');

                // 判断如果是代理商就显示 Sub_member 字段
                if (isAgent) {
                    $('#sub-member').show();
                }
            });

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
