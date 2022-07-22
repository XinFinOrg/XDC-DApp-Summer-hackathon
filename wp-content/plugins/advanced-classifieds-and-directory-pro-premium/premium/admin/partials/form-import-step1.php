<?php

/**
 * CSV Import Form: Step 1.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<table id="acadp-import-step1" class="form-table">
	<tbody>
		<tr> 
			<th scope="row">
				<label>
					<?php esc_html_e( 'CSV File', 'advanced-classifieds-and-directory-pro' ); ?>
					<span class="acadp-text-required">*</span>
				</label>

				<p class="description">
					<?php esc_html_e( 'Download', 'advanced-classifieds-and-directory-pro' ); ?>&nbsp;<a href="<?php echo ACADP_PLUGIN_URL . 'premium/admin/assets/downloads/sample-listings.csv'; ?>">sample-listings.csv</a>
				</p>
			</th>
			<td>
				<button type="button" id="acadp-import-csv_file-button" class="button button-secondary acadp-import-upload-button" data-field="csv_file">
					<?php esc_html_e( 'Upload File', 'advanced-classifieds-and-directory-pro' ); ?>
				</button>

				<span id="acadp-import-csv_file-name"></span>
				<input type="hidden" id="acadp-import-csv_file" />				
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Images ZIP archive', 'advanced-classifieds-and-directory-pro' ); ?>
				</label>

				<p class="description">
					<?php esc_html_e( 'Download', 'advanced-classifieds-and-directory-pro' ); ?>&nbsp;<a href="<?php echo ACADP_PLUGIN_URL . 'premium/admin/assets/downloads/sample-images.zip'; ?>">sample-images.zip</a>
				</p>
			</th>
			<td>
				<button type="button" id="acadp-import-images_file-button" class="button button-secondary acadp-import-upload-button" data-field="images_file">
					<?php esc_html_e( 'Upload File', 'advanced-classifieds-and-directory-pro' ); ?>
				</button>

				<span id="acadp-import-images_file-name"></span>
				<input type="hidden" id="acadp-import-images_file" />

				<p class="description">
					<?php esc_html_e( 'Upload ZIP archive with images for listings. Specify the image file names in one CSV column and separate them with a special delimiter (Categories, Multi Values separator). Note, that image files must be in the root inside a ZIP archive, not inside the folder.', 'advanced-classifieds-and-directory-pro' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Columns separator', 'advanced-classifieds-and-directory-pro' ); ?>
					<span class="acadp-text-required">*</span>
				</label>
			</th>
			<td>
				<input type="text" id="acadp-import-columns_separator" value="," />
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Categories, Multi Values separator', 'advanced-classifieds-and-directory-pro' ); ?>
					<span class="acadp-text-required">*</span>
				</label>
			</th>
			<td>
				<input type="text" id="acadp-import-values_separator" value=";" />

				<p class="description">
					<?php _e( '<strong>Multi Values</strong> - are such fields, those may have more than one value, for example, categories, images, checkboxes custom field items. Separate them in one CSV column with the special delimiter in this field.', 'advanced-classifieds-and-directory-pro' ); ?>
				</p>				
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Create a new category/location', 'advanced-classifieds-and-directory-pro' ); ?>
				</label>
			</th>
			<td>
				<label>
					<input type="checkbox" id="acadp-import-add_new_term" value="1" checked="checked" />
					<?php esc_html_e( "Create new when a category/location in the CSV file is not found", 'advanced-classifieds-and-directory-pro' ); ?>			
				</label>

				<p class="description">
					<?php _e( 'Use ">" symbol for categories-subcategories hierarchy. <strong>Example of subcategories import:</strong> "Business services > Advertising ; Real estate > Properties > Commercial". 2 root categories will be created, if they were not existed before import: "Business services" and "Real estate". And all their subcategories will be created in this hierarchy: "Advertising", "Properties" and "Commercial". Locations import works in the same way.', 'advanced-classifieds-and-directory-pro' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Create a new user', 'advanced-classifieds-and-directory-pro' ); ?>
				</label>
			</th>
			<td>
				<label>
					<input type="checkbox" id="acadp-import-add_new_user" value="1" checked="checked" />
					<?php esc_html_e( 'Create a new user when an "Author" in the CSV file is not found. Note the "Author" column should be an email address to get this work', 'advanced-classifieds-and-directory-pro' ); ?>			
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label>
					<?php esc_html_e( 'Use Google Maps API to find the location coordinates', 'advanced-classifieds-and-directory-pro' ); ?>
				</label>
			</th>
			<td>
				<label>
					<input type="checkbox" id="acadp-import-do_geocode" value="1" checked="checked" />
					<?php esc_html_e( "Required when you don't have coordinates (latitude, longitude) to import, but need listings map markers", 'advanced-classifieds-and-directory-pro' ); ?>			
				</label>
			</td>
		</tr>
	</tbody>
</table>
