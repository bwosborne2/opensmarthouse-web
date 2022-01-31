<html>
<head>
    <meta charset="UTF-8">
    <title> Test Page </title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />

</head>
<body>
<h5 >NEW Create Device</h5>
	

<div class="modal-body">
	<form method="post" action="zwaveCreate.php">
		<div class="form-group row">
			<label for="input1" class="col-sm-2 col-form-label col-4">Label</label>
			<div class="col-sm-10 col-8">
				<input type="text" class="form-control" id="name" name="device_name" aria-describedby="input1_help" placeholder="Enter the device name">
				<small id="input1_help" class="form-text text-muted">The short name or model number of the device.</small>
			</div>
		</div>
		<div class="form-group row">
			<label for="input2" class="col-sm-2 col-form-label col-4">Description</label>
			<div class="col-sm-10 col-8">
				<input type="text" class="form-control" id="description" name="device_desc" aria-describedby="input1_help" placeholder="Enter the device description">
				<small id="input1_help1" class="form-text text-muted">The device description.</small>
			</div>
		</div>
		<div class="form-group row">
			<label for="input4" class="col-sm-2 col-form-label col-4">Category</label>
			<div class="col-sm-10 col-8">
				<select name="device_category" id="category" aria-describedby="input4_help">
					<option label>Select a Category</option>
					<option value="1">Battery</option>
					<option value="2">Blinds</option>
					<option value="3">Camera</option>
					<option value="4">Car</option>
					<option value="5">Cleaning Robot</option>
					<option value="6">Door</option>
					<option value="7">Front Door</option>
					<option value="8">Garage Door</option>
					<option value="9">HVAC</option>
					<option value="10">Inverter</option>
					<option value="11">Lawn Mower</option>
					<option value="12">Light Bulb</option>
					<option value="13">Lock</option>
					<option value="14">Motion Detector</option>
					<option value="15">Power Outlet</option>
					<option value="16">Projector</option>
					<option value="17">Radiator Control</option>
					<option value="18">Sensor</option>
					<option value="19">Siren</option>
					<option value="20">Smoke Detector</option>
					<option value="21">Wall Switch</option>
					<option value="22">Window</option>
					<option value="23">Remote Control</option>
					<option value="24">Valve</option>
					<option value="25">Controller</option>
					<option value="26">Controller</option>
				</select>
				<small id="input4_help1" class="form-text text-muted">The device category.</small>
			</div>
		</div>
		<div class="form-group row">
			<label for="input3" class="col-sm-2 col-form-label col-4">XML</label>
			<div class="col-sm-10 col-8">
				<textarea id="device_xml" name="device_xml" class="form-control"></textarea>
			</div>
		</div>
		<div class="modal-footer">
	<button type="submit" class="btn btn-primary">Create Device</button>
</div>
	</form>
</div>


</body>
</html>