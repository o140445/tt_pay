define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '单据统计',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: []
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {},
                grid: [{
                    left: 30,
                    top: 30,
                    right: 30,
                    bottom: 30
                }],
                series: []
            };


            //动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
            //     Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));
            //     var amount = Math.floor(Math.random() * 200) + 20;
            //     Orderdata.createdata.push(amount);
            //     Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);
            //
            //     //按自己需求可以取消这个限制
            //     if (Orderdata.column.length >= 20) {
            //         //移除最开始的一条数据
            //         Orderdata.column.shift();
            //         Orderdata.paydata.shift();
            //         Orderdata.createdata.shift();
            //     }
            //     myChart.setOption({
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //             name: __('Sales'),
            //             data: Orderdata.paydata
            //         },
            //             {
            //                 name: __('Orders'),
            //                 data: Orderdata.createdata
            //             }]
            //     });
            // }, 2000);
            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-refresh", function () {
                setTimeout(function () {
                    myChart.resize();
                }, 0);
            });

            $(document).ready(function () {
                // 获取代理商身份
                var isAgent = $('#user-info').data('is-agent');

                // 判断如果是代理商示  total_user today_commission 就显示
                if (isAgent) {
                    $('#total_user').show();
                    $('#today_commission').show();
                }else{
                    $('#today_out_amount').show();
                    $('#today_in_amount').show();

                }

                // 赋值 给
                Orderdata.data.forEach(function (item) {

                    option.legend.data.push(item.name);
                    option.series.push({
                        name: item.name,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {}
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: item.list
                    });
                });


                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
            });

        }
    };

    return Controller;
});
