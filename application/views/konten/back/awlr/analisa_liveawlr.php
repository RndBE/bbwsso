<!--<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/no-data-to-display.js"></script>
<?php 
if($this->input->get('theme')=='dark')
{
	echo '<script src="https://code.highcharts.com/themes/dark-unica.js"></script>';
}
else
{
	echo '<script src="https://code.highcharts.com/js/themes/grid.js"></script>';
}
?>
 -->



<?php 
$ambilwaktu = mktime(date("H")-1, date("i"), 0, date("m"), date("d"), date("Y"));
$ambilwaktu2=date("Y-m-d H:i",$ambilwaktu);
$query_sensor=$this->db->query('select * from parameter_sensor where logger_id ="'.$this->session->userdata('idlogger').'" and kolom_sensor !="debit" order by LENGTH(kolom_sensor),kolom_sensor ');
foreach($query_sensor->result() as $vardata)
{
	${'data_'.$vardata->kolom_sensor}=array();
}
$tabel = $this->db->where('id_logger',$this->session->userdata('idlogger'))->get('t_logger')->row();
$query_data=$this->db->query('select * from '.$tabel->tabel_main.' where code_logger ="'.$this->session->userdata('idlogger').'" and waktu >= "'.$ambilwaktu2.'"');
foreach($query_data->result() as $dtsen )
{
	$tahun=date('Y',strtotime($dtsen->waktu));
	$bulan=date('m',strtotime($dtsen->waktu));
	$hari=date('d',strtotime($dtsen->waktu));
	$jam=date('H',strtotime($dtsen->waktu));
	$menit=date('i',strtotime($dtsen->waktu));
	foreach($query_sensor->result() as $vardata2)
	{
		$kolom=$vardata2->kolom_sensor ;
		${'data_'.$kolom}[]="[ Date.UTC(".$tahun.",".$bulan."-1,".$hari.",".$jam.",".$menit."),". $dtsen->$kolom ."]";
	}

	$waktuterakhir=$dtsen->waktu;
	if($waktuterakhir >= $ambilwaktu2)
	{
		$color="green";
		$status_logger="Koneksi Terhubung";
	}
	else{
		$color="red";
		$status_logger="Koneksi Terputus";
	}

}

?>

<script type="text/javascript"> 
	var MQTTbroker = 'mqtt.beacontelemetry.com';
	var MQTTport = 8083;
	var MQTTsubTopic = '<?php echo $this->session->userdata('idlogger') ?>';
	<?php
	foreach($query_sensor->result() as $var)
	{

		echo "var chart".$var->nama_parameter.";";
	}
	?>

	var dataTopics = new Array();
	var client = new Paho.MQTT.Client(MQTTbroker, MQTTport,
									  "clientid_" + parseInt(Math.random() * 100, 10));
	client.onMessageArrived = onMessageArrived;
	client.onConnectionLost = onConnectionLost;


	var options = {
		timeout: 3,
		useSSL: true,
		userName : "userlog",
		password : "b34c0n",

		onSuccess: function () {
			console.log("mqtt connected");
			client.subscribe(MQTTsubTopic, {qos: 0});
		},
		onFailure: function (message) {
		}
	};

	function onConnectionLost(responseObject) {
	};
	function onMessageArrived(message) {
		console.log(message.payloadString);
		if (dataTopics.indexOf(message.destinationName) < 0){
			dataTopics.push(message.destinationName);
			var y = dataTopics.indexOf(message.destinationName);
			var dataLog = message.payloadString;
			var dataLogObj = JSON.parse(dataLog);
			//var tanggal=dataLogObj.tanggal+" "+dataLogObj.jam;
			//var jam=dataLogObj.jam;
			var tanggal=dataLogObj.waktu;
			var jam=new Date(tanggal).getHours();

			var tahun = new Date(tanggal).getFullYear();
			var bulan = new Date(tanggal).getMonth()
			var hari = new Date(tanggal).getDate();
			var jam1 = new Date(tanggal).getHours();
			var menit = new Date(tanggal).getMinutes();

			var waktu = new Date(tahun,bulan,hari,jam1+7,menit,0).getTime();

			<?php 
			foreach($query_sensor->result() as $sensor) 
			{
				echo "var ".$sensor->kolom_sensor ."= dataLogObj.".$sensor->kolom_sensor.";";
				echo "var newseries".$sensor->id_param." = {
                  id: y,
				  name: '".$sensor->nama_parameter ."',//nama Series
                  data: []
                  };

        chart".$sensor->nama_parameter.".redraw(newseries".$sensor->id_param.");

		  ";
			}
			?>
		};

		var y = dataTopics.indexOf(message.destinationName);
		var dataLog = message.payloadString;
		var dataLogObj = JSON.parse(dataLog);
		//var tanggal=dataLogObj.tanggal+' '+dataLogObj.jam;
		//var jam=dataLogObj.jam;
		var tanggal=dataLogObj.waktu;
		var jam=new Date(tanggal).getHours();

		var tahun = new Date(tanggal).getFullYear();
		var bulan = new Date(tanggal).getMonth()
		var hari = new Date(tanggal).getDate();
		var jam1 = new Date(tanggal).getHours();
		var menit = new Date(tanggal).getMinutes();

		var waktu = new Date(tahun,bulan,hari,jam1+7,menit,0).getTime();
		<?php 
		foreach($query_sensor->result() as $sensor2) 
		{
			echo 'var '.$sensor2->kolom_sensor .'= dataLogObj.'.$sensor2->kolom_sensor.';';
			echo '
     var plotMqtt'.$sensor2->id_param.' = [waktu, Number('.$sensor2->kolom_sensor .')];

      if (isNumber('.$sensor2->kolom_sensor .')) { 
        plot'.$sensor2->id_param .'(plotMqtt'.$sensor2->id_param .', y);	//send it to the plot function
      };';
		}
		?>
	};

	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	};
	function init() {
		Highcharts.setOptions({

			time: {
				useUTC: false
			}

		});


		client.connect(options);
	};

	<?php
	foreach($query_sensor->result() as $plotchart) 
	{
		echo "
      function plot".$plotchart->id_param."(point, chartno) {
        //console.log(point);

			   var series".$plotchart->id_param." = chart".$plotchart->nama_parameter.".series[0],
                shift = series".$plotchart->id_param.".data.length > 5; // shift if the series is
                                                 // longer than 20

            chart".$plotchart->nama_parameter.".series[chartno].addPoint(point, true, shift);
		  };"; }?>
	//settings for the chart
	$(document).ready(function() {
		<?php 
		foreach($query_sensor->result() as $sensor3) 
		{
			$nm = str_replace('_',' ',$sensor3->nama_parameter);
			echo "

        chart".$sensor3->nama_parameter." = new Highcharts.Chart({
            chart: {
                renderTo: 'container".$sensor3->nama_parameter."',
				zoomType :'xy',
                defaultSeriesType: '".$sensor3->tipe_graf."',
				styledMode: true,
            },
            title: {
                text: '".$nm."'
            },
            subtitle: {
                                  text: ' '
                          },
            xAxis: [{

				type: 'datetime',
            dateTimeLabelFormats: { // don't display the dummy year
            millisecond: '%H:%M',
            second: '%H:%M',
            minute: '%H:%M',
            hour: '%H:%M',
            day: '%e. %b %y',
            week: '%e. %b %y',
            month: '%b \'%y',
            year: '%Y'
			},
			crosshair: true

            }],
            yAxis: [{
                minPadding: 0.2,
                maxPadding: 0.2,
                title: {
                    text: '".$nm."',

                }
            }],
			  plotOptions: {
						series: {
						  dataLabels: {
							enabled: true,
							shape:'square',
							backgroundColor: 'rgba(252, 255, 197, 0.7)',
							borderWidth: 2,
							borderRadius: 5,
							borderColor: '#AAA',
							padding: 5,
							y: -10,
							align:'top',
							style: {
										fontWeight: 'bold'
									},
							formatter: function() {
							  var seriesPoints = this.series.points;
							  if (this.point === seriesPoints[seriesPoints.length - 1]) {
								return 'Waktu : '+Highcharts.dateFormat('%H:%M %d-%m-%Y',this.x)+'<br>".$sensor3->nama_parameter." : '+this.y +' ".$sensor3->satuan."';
							  }
							}
						  }
						}
					  },
			tooltip: {
             xDateFormat: 'Tanggal %d-%m-%Y %H:%M',
            shared: true
        }, credits: {
                enabled: false
            },
            series: [ {
            name: '".$sensor3->nama_parameter."',
            data: [".join(${"data_".$sensor3->kolom_sensor},',') ."],

            tooltip: {
                valueSuffix: ' ".$sensor3->satuan."',
                 valueDecimals: 3,
            }
         }]
        });";

		}
		?>
	});

</script>

<?php 
$query_lokasi=$this->db->query('select * from t_logger inner join t_lokasi ON t_lokasi.idlokasi=t_logger.lokasi_logger where id_logger="'.$this->session->userdata('idlogger').'"');
foreach($query_lokasi->result() as $lokasi)
{
	$lokasi_logger=$lokasi->nama_lokasi;
}

?>

<div class="container-md">
	<div class="page-header d-print-none">
		<div class="row g-3 align-items-center">
			<div class="col-auto">
				<span class="status-indicator status-<?php echo $color?> status-indicator-animated">
					<span class="status-indicator-circle"></span>
					<span class="status-indicator-circle"></span>
					<span class="status-indicator-circle"></span>
				</span>
			</div>
			<div class="col">
				<h2 class="page-title">
					Pos AWLR De
				</h2>
				<div class="text-muted">
					<ul class="list-inline list-inline-dots mb-0">
						<li class="list-inline-item"><span class="text-<?php echo $color?>"><?php echo $status_logger ?></span></li>
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>


<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-md-12">
				<div class="row row-cards">
					<?php foreach($query_sensor->result() as $sensor4)
{
	echo '

				  <div class="col-md-6">
					<div class="card">
					  <div class="card-body">
						<h3 class="card-title"> </h3>

						<div id="container'.$sensor4->nama_parameter.'" ></div>
                  </div>
                </div>
              </div>

								';
}?>
				</div>
			</div>
		</div>
	</div>
</div> 


<script src="<?php echo base_url();?>js/highcharts.js"></script>
<script src="<?php echo base_url();?>js/modules/data.js"></script>
<script src="<?php echo base_url();?>js/modules/exporting.js"></script>
<script src="<?php echo base_url();?>js/highcharts-more.js"></script>
<script src="<?php echo base_url();?>js/themes/grid.js"></script> 
<!--<script src="<?php echo base_url();?>js/themes/dark-unica.js"></script> -->
<script src="<?php echo base_url();?>js/modules/no-data-to-display.js"></script>



