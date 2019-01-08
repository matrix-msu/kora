
function initializePagination () {
	$('.onboarding-pagination-js .dots').children().remove();
	$('.onboarding-pagination-js').removeClass('hidden');

	let count = $('.paths div:not(.hidden)').children('section').length + 1
 
	for ( let i = 0; i < count; i++ ) {

		if ( i === 0 )
			$('.onboarding-pagination-js .dots').append('<span class="dot tooltip" tooltip="Welcome"></span>');
		else {
			let tooltip = $('.paths div:not(.hidden) section:nth-child(' + i + ')').attr('id');
			$('.onboarding-pagination-js .dots').append('<span class="dot tooltip" tooltip="' + tooltip + '"></span>');
		}
	}

	paginate ( 1 )
}

$('.not-new-js').click(function (e) {
	e.preventDefault();

    $('#onboarding-home').addClass('hidden');
	$('.paths div:first-child').removeClass('hidden');

	initializePagination ()
});

$('.new-to-kora-js').click(function (e) {
    e.preventDefault();

    $('#onboarding-home').addClass('hidden');
	$('.paths div:last-child').removeClass('hidden');

	initializePagination ()
});

function paginate (that) {

	// Show/hide pages
	$('.paths')
		.children( 'div:not(.hidden)' )
		.children( 'section' )
		.addClass( 'hidden' );

	$('.paths')
		.children( 'div:not(.hidden)' )
		.children( 'section:nth-child(' + that + ')' )
		.removeClass('hidden');

	// Update dots
	$('.onboarding-pagination-js .dots .dot').removeClass('active');
	$('.onboarding-pagination-js .dots .dot')[that].classList.add('active')

	// Change 'Continue' button to read 'Finish' when we reach last page
	if ( that == ( $('.dots .dot').length - 1 ) ) {
		$('.next-js').addClass('hidden');
		$('.finish-js').removeClass('hidden');
	} else {
		$('.next-js').removeClass('hidden');
		$('.finish-js').addClass('hidden');
	}

	if ( that === 0 ) {
		$('.onboarding-pagination-js').addClass('hidden');
		$('#onboarding-home').removeClass('hidden');
		$('.paths > div').addClass('hidden');
	}
}

$('.onboarding-modal-js .prev-js').click(function (e) {
	e.preventDefault();

	paginate ( $('.dots .dot.active').index() - 1 )
});

$('.onboarding-modal-js .next-js').click(function (e) {
	e.preventDefault();

	paginate ( $('.dots .dot.active').index() + 1 )
});

$('.onboarding-pagination-js .dots').on('click', '.dot', function (e) {
	e.preventDefault();

	paginate ( $(this).index() )
});

$(document).ready(function () {
    Kora.Modal.initialize();

	Kora.Modal.open($('.onboarding-modal-js'));

	$('.body.onboarding .single-select').chosen({
		disable_search_threshold: 1,
		width: '100%'
	});
});
