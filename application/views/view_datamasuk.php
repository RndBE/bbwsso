<html>
<head>
   <title>Data of Sensor</title>


<link rel="stylesheet" href="<?php echo base_url()?>template_back/css/bootstrap.min.css" />

<link rel="stylesheet" href="<?php echo base_url()?>template_back/css/datepicker.css" />


    
</head>
<body>
     <div class="container-fluid">
       
<!----------  ------>
 
<h1>Data <?php echo $this->session->userdata('log_id')?> / <?php echo $this->session->userdata('waktu')?></h1>

<hr/>
<div class="row-fluid">
<div  class="span4">

<?php echo form_open('datamasuk/sesi_logger');?>
Id Logger
<input type="text" name="logger_id" />
<input value="Cari" type="submit"/>
<?php echo form_close();?>
</div>

<div  class="span4">
 <?php echo form_open('datamasuk/set_tgl') ;?>

Pilih Tanggal

<input type="input" name="tgl" id="tgl" class="tgl" placeholder="Tanggal" />


<input type="submit"  value="Tampil"/>

<?php echo form_close();?>
</div>
<div  class="span4">
<?php echo form_open('datamasuk/data');?>
<input value="Refresh" type="submit"/>
<?php echo form_close();?>
</div>
</div>
<div class="row-fluid">
<div class="span12">
<table class="oncom" border="1" >
      <tr>
	
         <td>&nbsp;Waktu&nbsp;</td>
         <td>&nbsp;Sensor1&nbsp;</td>
         <td>&nbsp;Sensor2&nbsp;</td>
         <td>&nbsp;Sensor3&nbsp;</td>
         <td>&nbsp;Sensor4&nbsp;</td>
         <td>&nbsp;Sensor5&nbsp;</td>
         <td>&nbsp;Sensor6&nbsp;</td>
         <td>&nbsp;Sensor7&nbsp;</td>
         <td>&nbsp;Sensor8&nbsp;</td>
         <td>&nbsp;Sensor9&nbsp;</td>
         <td>&nbsp;Sensor10&nbsp;</td>
         <td>&nbsp;Sensor11&nbsp;</td>
         <td>&nbsp;Sensor12&nbsp;</td> 
         <td>&nbsp;Sensor13&nbsp;</td>
         <td>&nbsp;Sensor14&nbsp;</td>
         <td>&nbsp;Sensor15&nbsp;</td>
         <td>&nbsp;Sensor16&nbsp;</td>

         

       </tr>
<?php      
   foreach ($data as $row) :
      ?>
     <tr>
         
         <td>&nbsp;<?php echo $row->waktu ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor1 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor2 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor3 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor4 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor5 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor6 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor7 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor8 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor9 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor10 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor11 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor12 ?>&nbsp;</td> 
         <td>&nbsp;<?php echo $row->sensor13 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor14 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor15 ?>&nbsp;</td>
         <td>&nbsp;<?php echo $row->sensor16 ?>&nbsp;</td>

        
       </tr>

 <?php    
   endforeach;
?>
</table>
</div></div>
 <!-- jQuery (Necessary for All JavaScript Plugins) -->
    <script src="<?php echo base_url()?>template_front/js/jquery/jquery-2.2.4.min.js"></script>

<script src="<?php echo base_url()?>template_back/js/bootstrap-datepicker.js"></script> 
<script type="text/javascript">
 $(document).ready(function(){
    $('.datepicker').datepicker();
    
     $('.tgl').datepicker({
              locale: 'ru' ,
              format: 'yyyy-mm-dd',
              autoClose: true
    });

    
});

</script>
</body>
</html>