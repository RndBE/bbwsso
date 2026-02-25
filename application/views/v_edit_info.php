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

		<h3>Edit Data</h3>
	</center>
	<?php foreach($user as $u){ ?>
		<center>
		<div class='mx-4 card' style="width:40%">

	<form action="<?php echo base_url(). 'editinfo/update_info'; ?>" method="post">
		<table style="margin:20px auto;" class='table table-striped table-hover '>
			<tr>
				<td>Id Logger</td>
				<td>
					<input type="text" name="idlogger" class="form-control" value="<?php echo $u->id_logger ?>" readonly>
				</td>
			</tr>

			<tr>
				<td>Id</td>
				<td>
					<input type="text" name="id" class="form-control" value="<?php echo $u->id ?>" readonly>
				</td>
			</tr>
			<tr>
				<td>Id Lokasi</td>
				<td>
					<input type="text" name="idlokasi" class="form-control" value="<?php echo $u->idlokasi ?>" readonly>
				</td>
			</tr>

			<tr>
				<td>Id Info</td>
				<td>
					<input type="text" name="idinfo" class="form-control" value="<?php echo $u->id_inf ?>" readonly>
				</td>
			</tr>
			
			<tr>
				<td>Nama Logger</td>
				<td><input type="text" name="namalogger" class="form-control" value="<?php echo $u->nama_logger ?>" required></td>
			</tr>
			<tr>
				<td>Seri Logger</td>
				<td><input type="text" name="serilogger" class="form-control" value="<?php echo $u->seri ?>" required></td>
			</tr>
			<tr>
				<td>Sensor</td>
				<td><input type="text" name="sensor" class="form-control" value="<?php echo $u->sensor ?>" ></td>
			</tr>

			<tr>
				<td>No Seluler</td>
				<td><input type="text" name="nosell" class="form-control" value="<?php echo $u->nosell ?>" required></td>
			</tr>
			<tr>
				<td>Nama Lokasi</td>
				<td><input type="text" name="namalok" class="form-control" value="<?php echo $u->nama_lokasi ?>" required></td>
			</tr>
			<tr>
				<td>Latitude</td>
				<td><input type="text" name="lat" class="form-control" value="<?php echo $u->latitude ?>" required></td>
			</tr>
			<tr>
				<td>Longitude</td>
				<td><input type="text" name="long" class="form-control" value="<?php echo $u->longitude ?>" required></td>
			</tr>

			<tr>
				<td>Elevasi</td>
				<td><input type="text" name="elev" class="form-control" value="<?php echo $u->elevasi ?>" ></td>
			</tr>

			<tr>
				<td>Pemasangan</td>
				<td><input type="text" name="pemasangan" class="form-control" value="<?php echo $u->tgl_aktif ?>" ></td>
			</tr>
			
			<tr>
				<td>Garansi</td>
				<td><input type="text" name="garansi" class="form-control" value="<?php echo $u->garansi ?>" ></td>
			</tr>
			
			<tr>
				<td>Tanggal Kontrak</td>
				<td><input type="text" name="tglkontrak" class="form-control" value="<?php echo $u->tgl_kontrak ?>" ></td>
			</tr>
			
			<tr>
				<td>Nomor Kontrak</td>
				<td><input type="text" name="nokontrak" class="form-control" value="<?php echo $u->no_kontrak ?>" ></td>
			</tr>
			
			<tr>
				<td></td>
				<td><input class="button btn btn-outline-primary" type="submit" value="Simpan"></td>
			</tr>
		</table>
			</form>	</div></center>
	<?php } ?>