/* global spData */
jQuery(function ($) {

	/* ------------------------------------------------------------------
	 *  Utils
	 * ------------------------------------------------------------------ */
	const nonce = spData.nonce;
	const ajax  = spData.ajax;

	/* Fetch items for the “Item” <select> ------------------------------ */
	function loadItems($row) {
		const $type = $row.find('.sp-type');
		const $item = $row.find('.sp-item');

		$item.html('<option>' + spData.texts.loading + '</option>');

		$.post(ajax, {
			action   : 'sp_fetch_items',
			nonce    : nonce,
			post_type: $type.val()
		})
		.done(res => {
			if (!res.success) { alert(spData.texts.error); return; }

			let html = '<option value="">--</option>';
			res.data.forEach(r => {
				html += `<option value="${r.id}">${r.title}</option>`;
			});
			$item.html(html);
		})
		.fail(() => alert(spData.texts.error));
	}

	// First row → initial load
	loadItems($('.sp-row'));

	$(document).on('change', '.sp-type', function () {
		loadItems($(this).closest('tr'));
	});

	/* ------------------------------------------------------------------
	 *  Add / remove rows
	 * ------------------------------------------------------------------ */

	// ADD
	$(document).on('click', '.sp-add-row', function (e) {
		e.preventDefault();

		const $first = $('#sp-schedule-rows tbody tr:first');
		const $clone = $first.clone(true);

		// reset form fields
		$clone.find('input,select').val('');
		$first.closest('tbody').append($clone);

		// fetch items for this new row
		loadItems($clone);
	});

	// REMOVE
	$(document).on('click', '.sp-remove-row', function () {
		$(this).closest('tr').remove();
	});

	/* ------------------------------------------------------------------
	 *  Save schedules
	 * ------------------------------------------------------------------ */
	$('#sp-save').on('click', function (e) {
		e.preventDefault();

		const data = {
			action       : 'sp_save_schedules',
			nonce        : nonce,
			schedule_time: [],
			post_type    : [],
			post_id      : [],
			post_status  : []
		};

		$('#sp-schedule-rows tbody tr').each(function () {
			data.schedule_time.push($(this).find('input[type="datetime-local"]').val());
			data.post_type.push($(this).find('.sp-type').val());
			data.post_id.push($(this).find('.sp-item').val());
			data.post_status.push($(this).find('select[name="post_status[]"]').val());
		});

		$.post(ajax, data)
		.done(res => {
			if (!res.success) { alert(spData.texts.error); return; }
			alert(res.data.message);
			location.reload();
		})
		.fail(() => alert(spData.texts.error));
	});

	/* ------------------------------------------------------------------
	 *  Delete schedule
	 * ------------------------------------------------------------------ */
	$('#sp-schedule-table').on('click', '.sp-delete', function (e) {
		e.preventDefault();
		if (!confirm('Delete schedule?')) return;

		const $tr = $(this).closest('tr');

		$.post(ajax, {
			action: 'sp_delete_schedule',
			nonce : nonce,
			id    : $tr.data('id')
		})
		.done(() => $tr.fadeOut())
		.fail(() => alert(spData.texts.error));
	});

	/* ------------------------------------------------------------------
	 *  Run now
	 * ------------------------------------------------------------------ */
	$('#sp-schedule-table').on('click', '.sp-run-now', function (e) {
		e.preventDefault();

		const $tr = $(this).closest('tr');

		$.post(ajax, {
			action: 'sp_run_schedule_now',
			nonce : nonce,
			id    : $tr.data('id')
		})
		.done(() => location.reload())
		.fail(() => alert(spData.texts.error));
	});
});
/* ------------------------------------------------------------------
 *  Auto refresh schedules table
 * ------------------------------------------------------------------ */
function refreshSchedules() {
    $.post(ajax, {
        action: 'sp_refresh_schedules',
        nonce : nonce
    })
    .done(res => {
        if (res.success) {
            $('#sp-schedule-table tbody').html(res.data.html);
        }
    });
}

// Har 30 second me refresh
setInterval(refreshSchedules, 30000);
