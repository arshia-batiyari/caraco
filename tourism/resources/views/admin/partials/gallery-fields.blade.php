<fieldset>
    <legend>{{ __('admin.gallery') }}</legend>
    @foreach ($images as $image)
        <div class="k-admin-image">
            <img src="{{ \App\Support\ImageStore::url($image->path) }}" alt="">
            <label class="k-check"><input type="checkbox" name="remove_images[]" value="{{ $image->id }}"><span>{{ __('admin.remove_image') }}</span></label>
            @include('admin.partials.translation-tabs', [
                'namePrefix' => 'images_meta['.$image->id.'][translations]',
                'rows' => $image->translations->keyBy('locale'),
                'fields' => [
                    'alt' => ['admin.image_alt', false],
                    'caption' => ['admin.caption', false],
                ],
            ])
        </div>
    @endforeach
    <div class="k-field">
        <label for="gallery-images">{{ __('admin.gallery') }}</label>
        <input id="gallery-images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
    </div>
</fieldset>
