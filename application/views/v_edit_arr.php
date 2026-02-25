	<center>

		<h3>Edit Data</h3>
	</center>
	<?php foreach($user as $u){ ?>
	<form action="<?php echo base_url(). 'datamasuk/update_arr_crud'; ?>" method="post">
		<table style="margin:20px auto;">
			<tr>
				<td>Code Logger</td>
				<td>
					<?php echo $u->code_logger ?>
				</td>
			</tr>
			<tr>
				<td>Waktu</td>
				<td>
					<?php echo $u->waktu ?>
				</td>
			</tr>
			<tr>
				<td>Id</td>
				<td>
					<input type="text" name="id" value="<?php echo $u->id ?>" readonly>
				</td>
			</tr>
			<tr>
				<td>Sensor 1</td>
				<td><input type="text" name="sensor1" value="<?php echo $u->sensor1 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 2</td>
				<td><input type="text" name="sensor2" value="<?php echo $u->sensor2 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 3</td>
				<td><input type="text" name="sensor3" value="<?php echo $u->sensor3 ?>"></td>
			</tr>

			<tr>
				<td>Sensor 4</td>
				<td><input type="text" name="sensor4" value="<?php echo $u->sensor4 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 5</td>
				<td><input type="text" name="sensor5" value="<?php echo $u->sensor5 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 6</td>
				<td><input type="text" name="sensor6" value="<?php echo $u->sensor6 ?>"></td>
			</tr>

			<tr>
				<td>Sensor 7</td>
				<td><input type="text" name="sensor7" value="<?php echo $u->sensor7 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 8</td>
				<td><input type="text" name="sensor8" value="<?php echo $u->sensor8 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 9</td>
				<td><input type="text" name="sensor9" value="<?php echo $u->sensor9 ?>"></td>
			</tr>

			<tr>
				<td>Sensor 10</td>
				<td><input type="text" name="sensor10" value="<?php echo $u->sensor10 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 11</td>
				<td><input type="text" name="sensor11" value="<?php echo $u->sensor11 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 12</td>
				<td><input type="text" name="sensor12" value="<?php echo $u->sensor12 ?>"></td>
			</tr>

			<tr>
				<td>Sensor 13</td>
				<td><input type="text" name="sensor13" value="<?php echo $u->sensor13 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 14</td>
				<td><input type="text" name="sensor14" value="<?php echo $u->sensor14 ?>"></td>
			</tr>
			<tr>
				<td>Sensor 15</td>
				<td><input type="text" name="sensor15" value="<?php echo $u->sensor15 ?>"></td>
			</tr>

			<tr>
				<td>Sensor 16</td>
				<td><input type="text" name="sensor16" value="<?php echo $u->sensor16 ?>"></td>
			</tr>
			
			
			<tr>
				<td></td>
				<td><input type="submit" value="Simpan"></td>
			</tr>
		</table>
	</form>	
	<?php } ?>