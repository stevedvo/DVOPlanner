function init()
{
	offset = getParameterByName('offset');

	if (offset == null)
	{
		offset = 0;
	}
	else
	{
		offset = parseInt(offset);
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

	addStatusUpdateActions();
	autoCompleteQuickAdd();
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

function autoCompleteQuickAdd()
{
	if (typeof availableEvents === 'object' && $("input#quick-add").length > 0)
	{
		$("input#quick-add").keyup(function()
		{
			$input = $(this);
			results = "";

			$("ul.autocomplete-container").empty();

			if ($input.val() != "")
			{
				$.each(availableEvents, function(key, details)
				{
					haystack = details.description.toLowerCase();
					needle = $input.val().toLowerCase();

					if (haystack.indexOf(needle) != -1)
					{
						results+= "<li data-id='"+details.id+"' data-type='"+details.type+"' data-duration='"+details.duration+"'>"+details.description+"</li>";
					}
				})
			}

			if (results.length > 0)
			{
				$("ul.autocomplete-container").append(results);

				$("ul.autocomplete-container li").click(function()
				{
					$input.val($(this).text());
					$input.siblings("input[name='event_id']").val($(this).data('id'));
					$input.siblings("input[name='type']").val($(this).data('type'));
					$input.siblings("input[name='duration']").val($(this).data('duration'));
					$("ul.autocomplete-container").empty();
				});
			}
			else
			{
				$input.siblings("input[name='event_id']").val('');
			}
		});

		$("input[name='quick-add']").click(function(e)
		{
			e.preventDefault();

			if ($(this).siblings("input[name='event_id']").val() != "")
			{
				request =
				{
					eventID     : $(this).siblings("input[name='event_id']").val(),
					type        : $(this).siblings("input[name='type']").val(),
					description : $(this).siblings("input[name='description']").val(),
					duration    : $(this).siblings("input[name='duration']").val(),
					instNewDate : $(this).siblings("input[name='date']").val()
				};

				$.ajax(
				{
					type        : "POST",
					url         : "ajaxNewInst.php",
					dateType    : "json",
					data        : request
				}).done(function(data)
				{
					var newInstance = "";
					newInstance+=
						'<article class="" data-status="To Do">'+
							'<form class="" method="POST">'+
								'<input type="hidden" name="instance_id" value="'+data+'" />'+
								'<input type="hidden" name="event_id" value="'+request.eventID+'" />'+
								'<input type="hidden" name="inst_date" value="'+request.instNewDate+'" />'+
								'<input type="hidden" name="description" value="'+request.description+'" />'+
								'<input type="hidden" name="currStatus" value="To Do" />'+
								'<div class="task-description-container result-item">'+
									'<a href="view1event.php?event_id='+request.eventID+'">'+request.description+'</a>'+
								'</div>'+
								'<div class="task-type-container result-item">'+
									'<a href="view1event.php?event_id='+request.eventID+'">'+request.type+'</a>'+
								'</div>'+
								'<div class="start-travel-container result-item">'+
									'Start Travel: 00:00'+
								'</div>'+
								'<div class="task-travel-container result-item">'+
									'Travel: <input name="travel_time" type="number" min="0" />'+
								'</div>'+
								'<div class="start-task-container result-item">'+
									'Start Task: <input name="inst_time" type="time" />'+
								'</div>'+
								'<div class="end-task-container result-item">'+
									'End Task: 00:00'+
								'</div>'+
								'<div class="task-status-container result-item">'+
									'To Do'+
								'</div>'+
								'<div class="task-update-container result-item">'+
									'<input id="'+data+'" class="fw" type="submit" value="Update" />'+
								'</div>'+
								'<div class="task-complete-container change-status">'+
									'<button class="markComp" data-instID="'+data+'">Complete</button>'+
								'</div>'+
								'<div class="task-cancel-container change-status">'+
									'<button class="markCanx" data-instID="'+data+'">Cancel</button>'+
								'</div>'+
								'<div class="task-postpone-container change-status">'+
									'<button class="mark2mo" data-instID="'+data+'" data-eventID="'+request.eventID+'">+1 day</button>'+
								'</div>'+
							'</form>'+
						'</article>';

						$("p.no-events").remove();
						$("section.results-container").append(newInstance);
						addStatusUpdateActions();
				}).fail(function(data)
				{
					console.log(data);
				});
			}
			else
			{
				console.log("empty");
			}
		});
	}
}

function addStatusUpdateActions()
{
	// unbind any previous bindings to prevent duplication
	$(".markComp").off('click');
	$(".markCanx").off('click');
	$(".mark2mo").off('click');

	// updates displayed status to user on button click
	// performs AJAX POST to update the DB
	$(".markComp").click(function(e)
	{
		e.preventDefault();
		$this = $(this);
		$.ajax(
		{
			type    : "POST",
			url     : "ajaxStatus.php",
			data    :
			{
				instID      : $this.attr("data-instID"),
				instStatus  : "'Complete'"
			}
		}).done(function(data)
		{
			console.log(data);
			$this.parents("article").attr("data-status", "Complete");
			$this.parents("article").find(".task-status-container").html("Complete");
		}).fail(function(data)
		{
			console.log(data);
		});
	});

	$(".markCanx").click(function(e)
	{
		e.preventDefault();
		$this = $(this);
		$.ajax(
		{
			type    : "POST",
			url     : "ajaxStatus.php",
			data    :
			{
				instID      : $this.attr("data-instID"),
				instStatus  : "'Cancelled'"
			}
		}).done(function(data)
		{
			$this.parents("article").attr("data-status", "Cancelled");
			$this.parents("article").find(".task-status-container").html("Cancelled");
		}).fail(function(data)
		{
			console.log(data);
		});
	});

	$(".mark2mo").click(function(e)
	{
		e.preventDefault();
		$this = $(this);
		$.ajax(
		{
			type    : "POST",
			url     : "ajaxNewInst.php?offset="+offset,
			data    :
			{
				eventID         : $this.attr("data-eventID"),
				instNewDate     : $this.attr("data-newDate"),
				instTime        : $this.attr("data-instTime"),
				instTravel      : $this.attr("data-travelTime")
			}
		}).done(function(data)
		{
			$.ajax(
			{
				type    : "POST",
				url     : "ajaxStatus.php",
				data    :
				{
					instID      : $this.attr("data-instID"),
					instStatus  : "'Postponed'"
				}
			}).done(function(data)
			{
				$this.parents("article").attr("data-status", "Postponed");
				$this.parents("article").find(".task-status-container").html("Postponed");
			}).fail(function(data)
			{
				console.log(data);
			});
		}).fail(function(data)
		{
			console.log(data);
		});
	});
}
