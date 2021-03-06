<hr/>

<footer>
	<!-- Link to trigger modal -->
	<p class="pull-right"><a href="#modalHelp" data-toggle="modal"><i class="icon-question-sign"></i> Need Help</a>? Williams College, <?php echo date('Y'); ?>
	</p>

	<!-- Modal -->
	<div id="modalHelp" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalHelpLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="modalHelpLabel">Help FAQ</h3>
		</div>
		<div class="modal-body">
			<ol>
                <li><p>To sign in use your Williams username and password.</p></li>
                <li><p>Digitial Field Notebooks is a work in progress - some parts are yet-to-be-implemented. If you have specific questions and/or comments please contact the address below.</p></li>
			</ol>

			<p>&nbsp;</p>
			<p><i class="icon-question-sign"></i> More questions?</p>
				<?php
				if (isset($managersList)) {
					# show list of managers for this group
					echo "<p>Please contact: " . $managersList . "</p>";
				}
				else {
					# show default suypport address
					echo "<p>Please contact: <a href=\"mailto:cwarren@" . INSTITUTION_DOMAIN . "?subject=DigitalFieldNotebooks_Help_Request\"><i class=\"icon-envelope\"></i> cwarren@williams.edu</a></p>";
				}
				?>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
	</div>
</footer>

</div> <!-- /container -->

</body>
</html>