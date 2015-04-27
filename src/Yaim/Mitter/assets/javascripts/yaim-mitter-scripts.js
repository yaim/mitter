$(document).ready(function(){
	$("*[data-conditional]").each(function(){
		alert($(this).prev("select"));
	});

	$("*[data-selectAjax]").select2();

	$("*[data-dateTimePicker]").each(function(){
		if (!$(this).val()) {
			if (typeof $(this).data('default') !== 'undefined') {
				$(this).datetimepicker("setDate", new Date());
			}
		};
		$(this).datetimepicker();
	});

	$("*[data-datePicker]").each(function(){
		$(this).datepicker();
	});

	$("*[data-moment]").each(function(){
		momentFromNow($(this));
	});

	$("*[data-nicescroll]").each(function(){
		$(this).niceScroll();
	});

	$("*[data-timePicker]").each(function(){
		$(this).timepicker({
			minuteStep: 1,
			secondStep: 1,
			showSeconds: true,
			showMeridian: false,
			defaultTime: false,
		});
	});

	$("*[data-repeat]").each(function(){
		appendRepeatButton($(this));
		appendRemoveButton($(this).data('name'));
	});

	$("*[data-group]").each(function(){
		groupButton($(this));
	});

	$(".repeatButton").click(function(){
		repeatElement($("[data-name='"+$(this).data("repeat-element")+"']:last"));
		return false;
	});

	$(".row-remove-key").click(function(){
		removeFormGroupRow($(this));
		return false;
	});

	$("[data-group-add]").click(function(){
		groupAdd($(this));
	});

	$("[data-group-remove]").click(function(){
		groupRemove($(this));
	});

});

function momentFromNow(element)
{
	time = element.data('moment');
	fromNow = moment(time).fromNow();
	element.html(fromNow);
}

// @todo
function conditionalApi()
{
}

function removeFormGroupRow(element)
{
	element.closest('.form-group').remove();
}

function appendRepeatButton(element)
{
	element.after('<button data-repeat-element="'+element.data('name')+'" class="btn btn-labeled btn-success repeatButton"><span class="btn-label fa fa-plus"></span>Add More</button>');
}

function appendRemoveButton(name)
{
	placeholder = name+'[placeholder]';
	$("[name*='"+name+"']").each(function(){
		if(typeof $(this).attr('data-hidden-placeholder') == 'undefined') {
			$('<span class="row-remove-key fa-times"></span>').appendTo($(this).closest(".row"));
		}
	});

}

function repeatElement(element)
{
	append = element.clone().find(':input').each(function() {
		this.name = this.name.replace(/\[(\d+)\]/, function(str,p1) {
			return '[' + (parseInt(p1,10)+1) + ']';
		});
		this.id = this.name;
		this.value = "";

		if(typeof($(this).data("old")) !== 'undefined') {
			$(this).data("old", "");
		}

	}).end();

	append.find('.select2-container').remove();
	append.find('.not-in-repeat').remove();
	append.find('.link-to-relation').attr('href', '').addClass('disabled').removeClass('link-to-relation');

	append.find("[data-selectAjax]").select2();

	element.after(append);

	append.find(".row-remove-key").each(function(){
		$(this).click(function() {
			removeFormGroupRow($(this));
		});
	});

	return false;
}

function groupButton(field)
{
	field.after('<span data-group-add style="cursor: pointer; position: absolute; top: 3px; color: green; right: 20px; background-color: #eee; width: 15px; height: 15px; font-size: 15px; text-align: center;">+</span>');
	field.after('<span data-group-remove style="cursor: pointer; position: absolute; top: 19px; color: red; right: 20px; background-color: #eee; width: 15px; height: 15px; font-size: 15px; text-align: center;">-</span>');
}

function groupAdd(field)
{
	var group = field.closest("div");
	append = group.clone();

	append.find(':input').each(function(){
		this.value = "";
	});

	append.find("[data-group-add]").each(function(){
		$(this).click(function(){
			groupAdd($(this));
		});
	});

	append.find("[data-group-remove]").each(function(){
		$(this).click(function(){
			groupRemove($(this));
		});
	});

	group.after(append);

	return false;
}

function groupRemove(field)
{
	field.closest("div").remove();
}
