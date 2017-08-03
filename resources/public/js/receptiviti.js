(function($, r){

    $(document).ready(function(){
        $("#main").addClass("r-loading");
        $("#r-submit").on("click", function(e){
            if($("#ra-handle").val().length == 0) {
                e.preventDefault();
                return false;
            }
            $("#main").addClass("r-loading");
        });

        initChart();
        $("#main").removeClass("r-loading");
    });

    function initChart(){
        var values      = window.graphValues;
        var colors      = window.graphColors;

        //$.jqplot.config.enablePlugins = true;
        var plot        = $("#r_graph").jqplot(
                                [values],
                                {
                                    height: '800',
                                    //animate: !$.jqplot.use_excanvas,
                                    grid: {
                                        backgroundColor: '#ffffff',
                                        drawBorder: false,
                                        shadow: false,
                                        borderColor: '#ffffff',
                                        gridLineWidth: 0.5,
                                        borderWidth: 0
                                    },
                                    seriesDefaults:{
                                        renderer: $.jqplot.BarRenderer,
                                        pointLabels: {
                                            show: true, 
                                            location: 'e',
                                            edgeTolerance: -100
                                        },
                                        rendererOptions: {
                                            barDirection: 'horizontal',
                                            barWidth: 20,
                                            highlightMouseOver: false,
                                            highlightMouseDown: false,
                                            shadowAlpha: 0,
                                            varyBarColor: true
                                        },
                                        seriesColors: colors
                                    },
                                    axes: {
                                        yaxis: {
                                            renderer: $.jqplot.CategoryAxisRenderer,
                                            drawMajorGridlines: false
                                        },
                                        xaxis: {
                                            label: r.i18n["label-x"],
                                            ticks: [0, 20, 40, 60, 80, 100],
                                            tickOptions: {
                                                formatString: '%.2f'
                                            } 
                                        }
                                    }
                                }
        );
    }
})(jQuery, receptiviti);
