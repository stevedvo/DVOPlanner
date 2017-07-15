function init()
{
	offset = getParameterByName('offset');

	if (offset = null)
	{
		offset = 0;
	}

	$("#prevWkBtn").click(function()
	{
		offset = offset - (3600*24*7);
		location.href="homepage.php?offset="+offset;
	});

	$("#prevDayBtn").click(function()
	{
		offset = offset - (3600*24*1);
		location.href="homepage.php?offset="+offset;
	});

	$("#todayBtn").click(function()
	{
		location.href="homepage.php";
	});

	$("#nextDayBtn").click(function()
	{
		offset = offset + (3600*24*1);
		location.href="homepage.php?offset="+offset;
	});

	$("#nextWkBtn").click(function()
	{
		offset = offset + (3600*24*7);
		location.href="homepage.php?offset="+offset;
	});

	// sets background of row and descendant elements according to status
	$("tr[data-status='To Do'], tr[data-status='To Do'] td *").css('background','#FFAAAA');
	$("tr[data-status='Complete'], tr[data-status='Complete'] td *").css('background','#AAFFAA');
	$("tr[data-status='Postponed'], tr[data-status='Postponed'] td *").css('background','#AAAAAA');
	$("tr[data-status='Cancelled'], [data-status='Cancelled'] td *").css('background','#AAAAAA');

	// updates displayed status to user on button click
	// performs AJAX POST to update the DB
	$(".markComp").click(function()
	{
		$(this).parent().parent().attr("data-status", "Complete");
		$(this).parent().prev().prev().html("Complete");
		$("tr[data-status='Complete'], tr[data-status='Complete'] td *").css('background','#AAFFAA');
		$.ajax(
		{
			type: 	"POST",
			url: 	"ajaxStatus.php",
			data:
			{
				instID: 	$(this).attr("data-instID"), 
				instStatus:	"'Complete'"
			}
		});
	});
	$(".markCanx").click(function()
	{
		$(this).parent().parent().attr("data-status", "Cancelled");
		$(this).parent().prev().prev().prev().html("Cancelled");
		$("tr[data-status='Cancelled'], [data-status='Cancelled'] td *").css('background','#AAAAAA');
		$.ajax(
		{
			type: 	"POST",
			url: 	"ajaxStatus.php",
			data:
			{
				instID: 	$(this).attr("data-instID"), 
				instStatus:	"'Cancelled'"
			}
		});
	});
	$(".mark2mo").click(function()
	{
		$(this).parent().parent().attr("data-status", "Postponed");
		$(this).parent().prev().prev().prev().prev().html("Postponed");
		$("tr[data-status='Postponed'], tr[data-status='Postponed'] td *").css('background','#AAAAAA');
		$.ajax(
		{
			type: 	"POST",
			url: 	"ajaxStatus.php",
			data:
			{
				instID: 	$(this).attr("data-instID"), 
				instStatus: "'Postponed'"
			}
		});
		$.ajax(
		{
			type: 	"POST",
			url: 	"ajaxNewInst.php",
			data:
			{
				eventID: 		$(this).attr("data-eventID"),
				instNewDate: 	$(this).attr("data-newDate"),  
				instTime: 		$(this).attr("data-instTime"),  
				instTravel: 	$(this).attr("data-travelTime")
			}
		});
	});

}

document.addEventListener("DOMContentLoaded", init, false);

function getParameterByName(name, url)
{
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
	results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}