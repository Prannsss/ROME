<!-- <div class="row"> -->			
  <div class="col-md-11 col-xs-12 col-sm-12">
  	<div class="alert alert-info" role="alert">
  		<?php
			if(isset($errMsg)){
				echo '<div style="color:#28a745;text-align:center;font-size:17px;">'.$errMsg.'</div>';
			}
		?>
  		<h2 class="text-center">Register Room</h2>
  		<form action="" method="POST" enctype="multipart/form-data">
		  	 <div class="row">
		  	 	<div class="col-md-4">
			  	  <div class="form-group">
				    <label for="fullname">Full Name</label>
				    <input type="hidden" class="form-control" id="id" name="id" value="<?php echo $data['id']; ?>">
				    <input type="text" class="form-control" id="fullname" placeholder="Full Name" name="fullname" value="<?php echo $data['fullname']?$data['fullname']:''; ?>" required>
				  </div>
				 </div>

				<div class="col-md-4">
				  <div class="form-group">
				    <label for="mobile">Mobile</label>
				    <input type="text" class="form-control" id="mobile" placeholder="Mobile" name="mobile" value="<?php echo $data['mobile']?$data['mobile']:''; ?>" required>
				  </div>
				 </div>

				<div class="col-md-4">
				  <div class="form-group">
				    <label for="alternat_mobile">Alternat Mobile</label>
				    <input type="text" class="form-control" id="alternat_mobile" placeholder="Alternat Mobile" name="alternat_mobile" value="<?php echo $data['alternat_mobile']?$data['alternat_mobile']:''; ?>">
				  </div>
				</div>
			</div>

			<div class="row">
		  	 	<div class="col-md-4">
				  <div class="form-group">
				    <label for="email">Email</label>
				    <input type="email" class="form-control" id="email" placeholder="Email" name="email" value="<?php echo $data['email']?$data['email']:''; ?>" required>
				  </div>
				 </div>

				 <div class="col-md-4">
				  <div class="form-group">
				    <label for="plot_number">Plot Number/Home Number</label>
				    <input type="text" class="form-control" id="plot_number" placeholder="Plot Number/Home Number" name="plot_number" value="<?php echo $data['plot_number']?$data['plot_number']:''; ?>" required>
				  </div>
				 </div>

				 <div class="col-md-4">
				  <div class="form-group">
				    <label for="rooms">Available Rooms</label>
				    <input type="text" class="form-control" id="rooms" placeholder="1BHK/2BHK/3BHK/1RK" name="rooms" value="<?php echo $data['rooms']?$data['rooms']:''; ?>" required>
				  </div>
				 </div>
			</div>

			<div class="row">
				<div class="col-md-4">
			  <div class="form-group">
			    <label for="country">Country</label>
			    <input type="country" class="form-control" id="country" placeholder="Country" name="country" value="<?php echo $data['country']?$data['country']:''; ?>" required>
			  </div>
			  </div>

			  <div class="col-md-4">
			  <div class="form-group">
			    <label for="state">State</label>
			    <input type="state" class="form-control" id="state" placeholder="State" name="state" value="<?php echo $data['state']?$data['state']:''; ?>" required>
			  </div>
			  </div>
			  <div class="col-md-4">
			  <div class="form-group">
			    <label for="city">City</label>
			    <input type="city" class="form-control" id="city" placeholder="City" name="city" value="<?php echo $data['city']?$data['city']:''; ?>" required>
			  </div>
			  </div>
			 </div>

			 <div class="row">
			 	<div class="col-md-2">
			 <div class="form-group">
			    <label for="rent">Rent</label>
			    <input type="rent" class="form-control" id="rent" placeholder="Rent" name="rent" value="<?php echo $data['rent']?$data['rent']:''; ?>" required>
			  </div>
			  </div>

			  <div class="col-md-2">
			 <div class="form-group">
			    <label for="sale">Sale</label>
			    <input type="sale" class="form-control" id="sale" placeholder="Sale" name="sale" value="<?php echo $data['sale']?$data['sale']:''; ?>">
			  </div>
			  </div>


			  <div class="col-md-4">
			  <div class="form-group">
			    <label for="deposit">Deposit</label>
			    <input type="deposit" class="form-control" id="deposit" placeholder="Deposit" name="deposit" value="<?php echo $data['deposit']?$data['deposit']:''; ?>" required>
			  </div>
			  </div>
			  <div class="col-md-4">

			  <div class="form-group">
			    <label for="accommodation">Accommodation</label>
			    <input type="accommodation" class="form-control" id="accommodation" placeholder="Accommodation" name="accommodation" value="<?php echo $data['accommodation']?$data['accommodation']:''; ?>" required>
			  </div>
			  </div>
			  </div>

			   <div class="row">
			 	<div class="col-md-4">
			  <div class="form-group">
			    <label for="description">Description</label>
			    <input type="description" class="form-control" id="description" placeholder="Description" name="description" value="<?php echo $data['description']?$data['description']:''; ?>" required>
			  </div>
			   </div>
			  <div class="col-md-4">
			  <div class="form-group">
			    <label for="landmark">Landmark</label>
			    <input type="landmark" class="form-control" id="landmark" placeholder="landmark" name="landmark" value="<?php echo $data['landmark']?$data['landmark']:''; ?>" required>
			  </div>
			   </div>
			  <div class="col-md-4">
			  <div class="form-group">
			    <label for="address">Address</label>
			    <input type="address" class="form-control" id="address" placeholder="Address" name="address" value="<?php echo $data['address']?$data['address']:''; ?>" required>
			  </div>
			   </div>
			    </div>				  
			  
			   <div class="row">
			   	<div class="col-4">
			 		 <div class="form-group">
					    <label for="vacant">Vacant/Occupied</label>
					    <select class="form-control" id="vacant" name="vacant">
					      <option value="1" <?php if($data['vacant'] == '1'){echo 'selected';}?>>Vacant</option>
					      <option value="0" <?php if($data['vacant'] == '0'){echo 'selected';}?>>Occupied</option>
					    </select>
					  </div>
			 	</div>
			 	<div class="col-md-4">
			  <div class="form-group">
			    <label for="other">Other</label>
			    <input type="other" class="form-control" id="other" placeholder="Other" name="other" value="<?php echo $data['other']?$data['other']:''; ?>" required>
			  </div>
			  </div>
			  <div class="col-md-4">
			  <div class="form-group">
					<label for="image">Property Image (Optional: Upload to replace)</label>
					<input type="file" class="form-control-file" id="image" name="image" accept="image/*">
					<?php 
						// Define the uploads directory relative to the root
						$uploads_dir = '../uploads/';
						if (!empty($data['image'])):
							$image_filename = htmlspecialchars($data['image']);
							$image_path = $uploads_dir . $image_filename;
							// Check if the file exists before displaying
							if (file_exists($image_path)):
					?>
						<p class="mt-2 mb-0">Current Image:</p>
						<img src="<?php echo $image_path; ?>" alt="Current Property Image" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px;">
						<input type="hidden" name="current_image" value="<?php echo $image_filename; ?>">
					<?php else: ?>
						<p class="mt-2 mb-0 text-warning">Current image file (<?php echo $image_filename; ?>) not found.</p>
						<input type="hidden" name="current_image" value="<?php echo $image_filename; // Keep track even if file missing ?>">
					<?php endif; ?>
					<?php else: ?>
						<p class="mt-2 mb-0 text-muted">No current image.</p>
						<input type="hidden" name="current_image" value="">
					<?php endif; ?>
				</div>
			  </div>
			  </div>			
			  <button type="submit" class="btn btn-primary" name='register_individuals' value="register_individuals">Update Property</button>
			</form>	
			</div>			
  	</div>
<!-- </div> -->