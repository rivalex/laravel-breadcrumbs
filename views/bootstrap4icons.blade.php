@if (count($breadcrumbs))

    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)

            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb->url }}">
                        <i class="{{ $breadcrumb->faIcon ?? '' }} mr-2" aria-hidden="true"></i>{!! $breadcrumb->title !!}
                    </a>
                </li>
            @else
                <li class="breadcrumb-item active">
                    <i class="{{ $breadcrumb->faIcon ?? '' }} mr-2" aria-hidden="true"></i>{!! $breadcrumb->title !!}
                </li>
            @endif

        @endforeach
    </ol>

@endif
