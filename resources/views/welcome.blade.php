<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="//fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Tides</div>
                <canvas id="myChart" width="1000" height="400"></canvas>

            </div>
        </div>
        <script src="Chart.js"></script>

        <script>
        var data = {
            labels: [@foreach($tides as $tide) "{{(new \Carbon\Carbon($tide->time))->format("h:m")}}", @endforeach],
            datasets: [
                {
                    label: "Chart",
                    strokeColor: "rgba(255, 0, 0, 0.9)",
                    data: [@foreach($tides as $tide) "{{$tide->height}}", @endforeach]
                },

            ]
        };
        var ctx = document.getElementById("myChart").getContext("2d");

        var myLineChart = new Chart(ctx).Line(data, {
            datasetFill : false,
            scaleLabel : "<%= parseFloat(value).toFixed(2) + 'm' %>"


        });

    </script>
    </body>
</html>
