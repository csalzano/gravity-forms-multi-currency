		<li>
			<label for="form_currency"><?php esc_html_e( 'Currency', 'gravity-forms-multi-currency' ); ?></label>
			<select id="form_currency" name="form_currency" style="width:200px">
				<option value=""><?php esc_html_e( 'Default currency', 'gravity-forms-multi-currency' ); ?> (<?php echo esc_html( $this->gf_get_default_currency() ); ?>)</option>
				<?php
				foreach ( RGCurrency::get_currencies() as $code => $currency ) :
					?>
				<option <?php selected( rgar( $form, 'currency' ), $code, true ); ?> value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $currency['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</li>
