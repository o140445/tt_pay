define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // 基于准备好的dom，初始化echarts实例
            // var myChart = Echarts.init(document.getElementById('echart'), 'walden');
            //
            // // 指定图表的配置项和数据
            // var option = {
            //     title: {
            //         text: '',
            //         subtext: ''
            //     },
            //     color: [
            //         "#18d1b1",
            //         "#3fb1e3",
            //         "#626c91",
            //         "#a0a7e6",
            //         "#c4ebad",
            //         "#96dee8"
            //     ],
            //     tooltip: {
            //         trigger: 'axis'
            //     },
            //     legend: {
            //         data: [__('Register user')]
            //     },
            //     toolbox: {
            //         show: false,
            //         feature: {
            //             magicType: {show: true, type: ['stack', 'tiled']},
            //             saveAsImage: {show: true}
            //         }
            //     },
            //     xAxis: {
            //         type: 'category',
            //         boundaryGap: false,
            //         data: Config.column
            //     },
            //     yAxis: {},
            //     grid: [{
            //         left: 'left',
            //         top: 'top',
            //         right: '10',
            //         bottom: 30
            //     }],
            //     series: [{
            //         name: __('Register user'),
            //         type: 'line',
            //         smooth: true,
            //         areaStyle: {
            //             normal: {}
            //         },
            //         lineStyle: {
            //             normal: {
            //                 width: 1.5
            //             }
            //         },
            //         data: Config.userdata
            //     }]
            // };
            //
            // // 使用刚指定的配置项和数据显示图表。
            // myChart.setOption(option);



            // echart_profit
            var profitChart = Echarts.init(document.getElementById('echart_profit'), 'walden');
            // 指定图表的配置项和数据 通过后台
            // 配置图表的基本样式
            var profitOption = {
                title: {
                    text: '利润统计'
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: []  // 动态填充
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
                    data: [],
                    boundaryGap: false,
                },
                yAxis: {
                    type: 'value'
                },
                grid: [{
                    left: 30,
                    top: 30,
                    right: 30,
                    bottom: 30
                }],
                series: []  // 动态填充
            };

            // 获取Profit数据
            $.ajax({
                url: 'finance/profits/stat/count',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.code === 1) {
                        // 设置 xAxis 的数据
                        profitOption.xAxis.data = response.data.xAxis;

                        // 动态设置图例和 series 数据
                        response.data.series.forEach(function (seriesItem) {
                            // 添加图例
                            profitOption.legend.data.push(seriesItem.name);

                            // 添加系列数据
                            profitOption.series.push({
                                name: seriesItem.name,
                                type: 'line',
                                data: seriesItem.data,
                                areaStyle: {
                                    normal: {}
                                },
                                lineStyle: {
                                    normal: {
                                        width: 1.5
                                    }
                                },
                                smooth: true,
                            });
                        });

                        // 使用新数据更新图表
                        profitChart.setOption(profitOption);
                    } else {
                        console.error('获取数据失败:', response.msg);
                    }
                },
                error: function () {
                    console.error('无法请求数据');
                }
            });

            // echart_order
            var orderChart = Echarts.init(document.getElementById('echart_order'), 'walden');
            // 指定图表的配置项和数据 通过后台
            // 配置图表的基本样式
            var orderOption = {
                title: {
                    text: '单据统计'
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: []  // 动态填充
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
                    data: [],
                    boundaryGap: false,
                },
                yAxis: {
                    type: 'value'
                },
                grid: [{
                    left: 30,
                    top: 30,
                    right: 30,
                    bottom: 30
                }],
                series: []  // 动态填充
            };

            // 获取Order数据
            $.ajax({
                url: 'finance/profits/stat/order',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.code === 1) {
                        // 设置 xAxis 的数据
                        orderOption.xAxis.data = response.data.xAxis;

                        // 动态设置图例和 series 数据
                        response.data.series.forEach(function (seriesItem) {
                            // 添加图例
                            orderOption.legend.data.push(seriesItem.name);

                            // 添加系列数据
                            orderOption.series.push({
                                name: seriesItem.name,
                                type: 'line',
                                data: seriesItem.data,
                                areaStyle: {
                                    normal: {}
                                },
                                lineStyle: {
                                    normal: {
                                        width: 1.5
                                    }
                                },
                                smooth: true,
                            });
                        });

                        // 使用新数据更新图表
                        orderChart.setOption(orderOption);
                    } else {
                        console.error('获取数据失败:', response.msg);
                    }
                },
                error: function () {
                    console.error('无法请求数据');
                }
            });


            $(window).resize(function () {
                // myChart.resize();
                profitChart.resize();
                orderChart.resize();

            });

            // $(document).on("click", ".btn-refresh", function () {
            //     setTimeout(function () {
            //         myChart.resize();
            //     }, 0);
            // });
            $(document).on("click", ".btn-refresh-profit", function () {
                setTimeout(function () {
                    profitChart.resize();
                }, 0);
            });

            $(document).on("click", ".btn-refresh-order", function () {
                setTimeout(function () {
                    orderChart.resize();
                }, 0);
            });

        }
    };

    return Controller;
});
