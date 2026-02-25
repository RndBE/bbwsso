<!--		<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th, a {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
			
			.button {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
}

.button2 {background-color: #008CBA;} /* Blue */
</style>-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<center>

		<h3>Tambah Data</h3>
	</center>

		<center>
		<div class='mx-4 card' style="width:40%">
	<form action="<?php echo base_url(). 'editinfo/tambah_info'; ?>" method="post">
		<table style="margin:20px auto;" class='table table-striped table-hover '>
					
			<tr>
				<td>Code Logger</td>
				<td><input type="text" name="codelogger" class="form-control" value="" required></td>
			</tr>
	
			<tr>
				<td>Nama Logger</td>
				<td><input type="text" name="namalogger" class="form-control" value="" placeholder="contoh AWLR_Hulu" required></td>
			</tr>
			<tr>
				<td>Seri Logger</td>
				<td><input type="text" name="serilogger" class="form-control" placeholder="contoh BL-1100" value="" required></td>
			</tr>
			<tr>
				<td>Sensor</td>
				<td><input type="text" name="sensor" class="form-control" value="" required></td>
			</tr>

			<tr>
				<td>No Seluler</td>
				<td><input type="text" name="nosell" class="form-control" value="" placeholder="+628xxxxxx" required></td>
			</tr>
			<tr>
				<td>Nama Lokasi</td>
				<td><input type="text" name="namalok" class="form-control" value="" placeholder="Pos xxxxx" required></td>
			</tr>
			<tr>
				<td>Latitude</td>
				<td><input type="text" name="lat" class="form-control" value="" placeholder="contoh -7.538667" required></td>
			</tr>
			<tr>
				<td>Longitude</td>
				<td><input type="text" name="long" class="form-control" value="" placeholder="contoh 110.016491" required></td>
			</tr>

			<tr>
				<td>Elevasi</td>
				<td><input type="text" name="elev" class="form-control" value="" placeholder="0.00 m" ></td>
			</tr>

			<tr>
				<td>Pemasangan</td>
				<td><input type="text" name="pemasangan" class="form-control" value="" placeholder="TTTT-BB-HH" ></td>
			</tr>

			<tr>
				<td>Garansi</td>
				<td><input type="text" name="garansi" class="form-control" value="" placeholder="TTTT-BB-HH"></td>
			</tr>
			<tr>
				<td>Imei</td>
				<td><input type="text" name="imei" class="form-control" value="" placeholder="contoh 86981 6054715858"></td>
			</tr>
			<tr>
				<td></td>
				<td><input class="button btn btn-outline-success" type="submit" value="Tambah"></td>
			</tr>
		</table>
			</form>	</div></center>
