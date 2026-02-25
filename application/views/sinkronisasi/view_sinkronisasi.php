<h3> Sinkronisasi data </h3>
<hr/>

<?php

 echo form_open('ftp/set_sinkronisasi');

?>

<table>
<tr><td><label> ID Logger </label> </td><td><input type="text" name="id_logger"  /></td></tr>
<tr><td><label> Tanggal </label></td><td> <input type="date" name="tanggal"  /></td></tr>

<tr><td>
	<input type="submit" value="Simpan"/></td></tr>
</table>
<?php 
 echo form_close();
echo $this->session->flashdata('pesan');
?>
