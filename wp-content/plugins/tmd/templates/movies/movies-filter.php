<div class="filter-row elementor-section elementor-section-boxed">
	<div class="elementor-container row-flex">
		<div class="filter-containers"></div>
		<div class="filter-containers">
			<span class="filter-label">Sort By:</span>
			<select class="filter-dd" data-type="<?php echo $type; ?>" id="filter-by-year">Year
				<?php
				$currentYear = date('Y');
				if ($type == 'upcoming') {
					for ($i = 0; $i <= 19; $i++) {
						$year = date('Y', strtotime('+' . $i . ' year'));
						echo '<option>' . $year . '</option>';
					}
				} else {
					for ($i = 0; $i <= 19; $i++) {
						$year = date('Y', strtotime('-' . $i . ' year'));
						echo '<option>' . $year . '</option>';
					}
				}
				?>
			</select>
			<select class="filter-dd" data-type="<?php echo $type; ?>" id="filter-by-month">Month
				<?php
				foreach ($months as $key => $month) {
					$selected = '';
					if ($month == date('F')) {
						$selected = 'selected';
					}
				?>
					<option value="<?php echo sprintf("%02d", $key) ?>" <?php echo $selected ?>><?php echo $month ?></option>
				<?php
				}
				?>
			</select>
		</div>
	</div>
</div>