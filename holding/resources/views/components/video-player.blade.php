@props(['video'])

@if (is_array($video) && filled($video['src'] ?? null))
    <section class="k-section k-section-tight" aria-label="{{ __('ui.video.title') }}">
        <div class="k-wrap">
            <figure class="k-video" data-video>
                <div class="k-video-frame">
                    @if ($video['type'] === 'file')
                        <video controls preload="metadata" playsinline @if ($video['poster']) poster="{{ $video['poster'] }}" @endif>
                            <source src="{{ $video['src'] }}" type="video/mp4">
                        </video>
                    @elseif ($video['poster'])
                        <button type="button" class="k-video-poster" data-video-play>
                            <img src="{{ $video['poster'] }}" alt="">
                            <span class="k-video-play" aria-hidden="true"></span>
                            <span class="k-sr">{{ __('ui.video.play') }}</span>
                        </button>
                        <template>
                            <iframe
                                src="{{ $video['src'] }}"
                                title="{{ __('ui.video.title') }}"
                                allow="fullscreen; picture-in-picture"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </template>
                    @else
                        <iframe
                            src="{{ $video['src'] }}"
                            title="{{ __('ui.video.title') }}"
                            allow="fullscreen; picture-in-picture"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    @endif
                </div>
            </figure>
        </div>
    </section>
@endif
