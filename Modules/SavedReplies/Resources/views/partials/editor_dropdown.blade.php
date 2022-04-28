<ul class="hidden" id="sr-dropdown-list">
	@if (count($saved_replies) > 20)
		<li>
			<a href="#" class="sr-li-search">
				<input type"text" class="form-control" placeholder="{{ __('Search') }}…" autocomplete="off" onkeyup="srSearch(this)"/>
			</a>
		</li>
	@endif
	@include('savedreplies::partials/editor_dropdown_tree', ['saved_replies' => \SavedReply::listToTree($saved_replies)])
	<li class="divider"></li>
	<li><a href="#" data-value="">{{ __('Save This Reply') }}…</a></li>
</ul>