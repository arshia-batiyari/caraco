<form class="k-admin-filters" method="get">
    <div class="k-field">
        <label for="admin-q">{{ __('admin.search') }}</label>
        <input id="admin-q" name="q" type="search" value="{{ request('q') }}" maxlength="80">
    </div>
    @if ($showPublished ?? true)
        <div class="k-field">
            <label for="admin-published">{{ __('admin.published') }}</label>
            <select id="admin-published" name="published">
                <option value="">{{ __('admin.all_states') }}</option>
                <option value="1" @selected(request('published') === '1')>{{ __('admin.published') }}</option>
                <option value="0" @selected(request('published') === '0')>{{ __('admin.draft') }}</option>
            </select>
        </div>
    @endif
    @if ($showSort ?? true)
        <div class="k-field">
            <label for="admin-sort">{{ __('admin.position') }}</label>
            <select id="admin-sort" name="sort">
                <option value="">{{ __('admin.sort_default') }}</option>
                <option value="updated_at" @selected(request('sort') === 'updated_at')>{{ __('admin.sort_updated') }}</option>
            </select>
        </div>
    @endif
    <button class="k-btn k-btn-ink" type="submit">{{ __('admin.apply') }}</button>
</form>
